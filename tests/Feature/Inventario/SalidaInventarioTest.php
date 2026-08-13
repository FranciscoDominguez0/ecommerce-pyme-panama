<?php

namespace Tests\Feature\Inventario;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\VarianteProducto;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Admin\BaseAdminTest;

/**
 * Pruebas de SALIDA de stock del módulo de inventario (FASE 14).
 *
 * Ruta cubierta: POST /admin/inventario/salida
 * Validaciones reales del controlador:
 *   producto_id → required|integer|exists:productos,id
 *   variante_id → nullable|integer|exists:variantes_producto,id
 *   cantidad    → required|integer|min:1
 *   motivo      → required|string|max:255
 *
 * Regla de negocio vigente (InventarioService::registrarSalida): NO se permite
 * dejar el stock en negativo; si la cantidad supera el disponible, se lanza una
 * excepción y el controlador regresa con error.
 */
class SalidaInventarioTest extends BaseAdminTest
{
    #[Test]
    public function una_salida_disminuye_el_stock_del_producto(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create(['stock' => 10]);

        $this->actingAs($admin)
            ->post('/admin/inventario/salida', [
                'producto_id' => $producto->id,
                'cantidad'    => 4,
                'motivo'      => 'Venta en mostrador',
                'notas'       => 'Salida manual',
            ])
            ->assertRedirect(route('admin.inventario.index'))
            ->assertSessionHas('success');

        $this->assertSame(6, $producto->fresh()->stock);
    }

    #[Test]
    public function una_salida_disminuye_el_stock_de_la_variante(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create(['stock' => 99]);
        $variante = VarianteProducto::factory()->create(['producto_id' => $producto->id, 'stock' => 8]);

        $this->actingAs($admin)
            ->post('/admin/inventario/salida', [
                'producto_id' => $producto->id,
                'variante_id' => $variante->id,
                'cantidad'    => 3,
                'motivo'      => 'Venta en mostrador',
            ])
            ->assertRedirect(route('admin.inventario.index'));

        $this->assertSame(5, $variante->fresh()->stock);
        $this->assertSame(99, $producto->fresh()->stock);
    }

    #[Test]
    public function una_salida_crea_el_movimiento_con_tipo_cantidad_motivo_y_usuario(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create(['stock' => 10]);

        $this->actingAs($admin)
            ->post('/admin/inventario/salida', [
                'producto_id' => $producto->id,
                'cantidad'    => 4,
                'motivo'      => 'Venta en mostrador',
                'notas'       => 'Salida manual',
            ])
            ->assertRedirect(route('admin.inventario.index'));

        $this->assertDatabaseHas('movimientos_inventario', [
            'producto_id'          => $producto->id,
            'variante_producto_id' => null,
            'usuario_id'           => $admin->id,
            'pedido_id'            => null,
            'tipo'                 => 'salida',
            'cantidad'             => 4,
            'stock_antes'          => 10,
            'stock_despues'        => 6,
            'motivo'               => 'Venta en mostrador',
            'notas'                => 'Salida manual',
        ]);
    }

    #[Test]
    public function una_salida_mayor_al_stock_disponible_es_rechazada_sin_dejar_stock_negativo(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create(['stock' => 3]);

        $this->actingAs($admin)
            ->from('/admin/inventario/salida')
            ->post('/admin/inventario/salida', [
                'producto_id' => $producto->id,
                'cantidad'    => 10,
                'motivo'      => 'Venta en mostrador',
            ])
            ->assertRedirect('/admin/inventario/salida')
            ->assertSessionHas('error');

        // El stock NO queda negativo y no se registra ningún movimiento.
        $this->assertSame(3, $producto->fresh()->stock);
        $this->assertSame(0, MovimientoInventario::count());
    }

    #[Test]
    public function una_salida_con_cantidad_invalida_es_rechazada(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create(['stock' => 10]);

        foreach ([0, -1, 'x'] as $cantidad) {
            $this->actingAs($admin)
                ->post('/admin/inventario/salida', [
                    'producto_id' => $producto->id,
                    'cantidad'    => $cantidad,
                    'motivo'      => 'Prueba',
                ])
                ->assertSessionHasErrors('cantidad');
        }

        $this->assertSame(10, $producto->fresh()->stock);
        $this->assertSame(0, MovimientoInventario::count());
    }

    #[Test]
    public function una_salida_sin_motivo_es_rechazada(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create(['stock' => 10]);

        $this->actingAs($admin)
            ->post('/admin/inventario/salida', [
                'producto_id' => $producto->id,
                'cantidad'    => 2,
            ])
            ->assertSessionHasErrors('motivo');

        $this->assertSame(10, $producto->fresh()->stock);
    }
}
