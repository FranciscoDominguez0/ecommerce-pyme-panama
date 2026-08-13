<?php

namespace Tests\Feature\Inventario;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\VarianteProducto;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Admin\BaseAdminTest;

/**
 * Pruebas de ENTRADA de stock del módulo de inventario (FASE 14).
 *
 * Ruta cubierta: POST /admin/inventario/entrada
 * Validaciones reales del controlador:
 *   producto_id  → required|integer|exists:productos,id
 *   variante_id  → nullable|integer|exists:variantes_producto,id
 *   cantidad     → required|integer|min:1
 *   motivo       → required|string|max:255
 */
class EntradaInventarioTest extends BaseAdminTest
{
    #[Test]
    public function una_entrada_incrementa_el_stock_del_producto(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create(['stock' => 10]);

        $this->actingAs($admin)
            ->post('/admin/inventario/entrada', [
                'producto_id'       => $producto->id,
                'cantidad'          => 5,
                'motivo'            => 'Compra a proveedor',
                'proveedor'         => 'Distribuidora Panamá S.A.',
                'factura_proveedor' => 'F-2026-001',
                'notas'             => 'Lote nuevo',
            ])
            ->assertRedirect(route('admin.inventario.index'))
            ->assertSessionHas('success');

        $this->assertSame(15, $producto->fresh()->stock);
    }

    #[Test]
    public function una_entrada_incrementa_el_stock_de_la_variante(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create(['stock' => 99]);
        $variante = VarianteProducto::factory()->create(['producto_id' => $producto->id, 'stock' => 4]);

        $this->actingAs($admin)
            ->post('/admin/inventario/entrada', [
                'producto_id' => $producto->id,
                'variante_id' => $variante->id,
                'cantidad'    => 6,
                'motivo'      => 'Reabastecimiento de tallas',
            ])
            ->assertRedirect(route('admin.inventario.index'));

        $this->assertSame(10, $variante->fresh()->stock);
        // El stock del producto base NO se toca cuando se usa una variante.
        $this->assertSame(99, $producto->fresh()->stock);
    }

    #[Test]
    public function una_entrada_crea_el_movimiento_con_tipo_cantidad_motivo_y_usuario(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create(['stock' => 10]);

        $this->actingAs($admin)
            ->post('/admin/inventario/entrada', [
                'producto_id'       => $producto->id,
                'cantidad'          => 5,
                'motivo'            => 'Compra a proveedor',
                'proveedor'         => 'Distribuidora Panamá S.A.',
                'factura_proveedor' => 'F-2026-001',
                'notas'             => 'Lote nuevo',
            ])
            ->assertRedirect(route('admin.inventario.index'));

        $this->assertDatabaseHas('movimientos_inventario', [
            'producto_id'          => $producto->id,
            'variante_producto_id' => null,
            'usuario_id'           => $admin->id,
            'pedido_id'            => null,
            'tipo'                 => 'entrada',
            'cantidad'             => 5,
            'stock_antes'          => 10,
            'stock_despues'        => 15,
            'motivo'               => 'Compra a proveedor',
            'proveedor'            => 'Distribuidora Panamá S.A.',
            'factura_proveedor'    => 'F-2026-001',
            'notas'                => 'Lote nuevo',
        ]);
    }

    #[Test]
    public function una_entrada_con_cantidad_invalida_es_rechazada(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create(['stock' => 10]);

        foreach ([0, -3, 'abc'] as $cantidad) {
            $this->actingAs($admin)
                ->post('/admin/inventario/entrada', [
                    'producto_id' => $producto->id,
                    'cantidad'    => $cantidad,
                    'motivo'      => 'Prueba',
                ])
                ->assertSessionHasErrors('cantidad');
        }

        // Nada cambió: el stock sigue intacto y no hay movimientos.
        $this->assertSame(10, $producto->fresh()->stock);
        $this->assertSame(0, MovimientoInventario::count());
    }

    #[Test]
    public function una_entrada_sin_motivo_es_rechazada(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create(['stock' => 10]);

        $this->actingAs($admin)
            ->post('/admin/inventario/entrada', [
                'producto_id' => $producto->id,
                'cantidad'    => 5,
            ])
            ->assertSessionHasErrors('motivo');

        $this->assertSame(10, $producto->fresh()->stock);
        $this->assertSame(0, MovimientoInventario::count());
    }

    #[Test]
    public function una_entrada_con_producto_inexistente_es_rechazada(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->post('/admin/inventario/entrada', [
                'producto_id' => 999999,
                'cantidad'    => 5,
                'motivo'      => 'Prueba',
            ])
            ->assertSessionHasErrors('producto_id');
    }

    #[Test]
    public function una_entrada_con_variante_inexistente_es_rechazada(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create(['stock' => 10]);

        $this->actingAs($admin)
            ->post('/admin/inventario/entrada', [
                'producto_id' => $producto->id,
                'variante_id' => 999999,
                'cantidad'    => 5,
                'motivo'      => 'Prueba',
            ])
            ->assertSessionHasErrors('variante_id');
    }
}
