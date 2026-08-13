<?php

namespace Tests\Feature\Inventario;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\VarianteProducto;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Admin\BaseAdminTest;

/**
 * Pruebas de AJUSTE manual de stock del módulo de inventario (FASE 14).
 *
 * Ruta cubierta: POST /admin/inventario/ajuste
 * Validaciones reales del controlador:
 *   producto_id → required|integer|exists:productos,id
 *   variante_id → nullable|integer|exists:variantes_producto,id
 *   nuevo_stock → required|integer|min:0
 *   motivo      → required|string|max:255
 *
 * El movimiento de tipo "ajuste" guarda la diferencia en valor absoluto
 * (cantidad = |nuevo_stock - stock_antes|) según InventarioService::registrarAjuste.
 */
class AjusteInventarioTest extends BaseAdminTest
{
    #[Test]
    public function un_ajuste_actualiza_correctamente_el_stock(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create(['stock' => 10]);

        $this->actingAs($admin)
            ->post('/admin/inventario/ajuste', [
                'producto_id' => $producto->id,
                'nuevo_stock' => 25,
                'motivo'      => 'Conteo físico',
            ])
            ->assertRedirect(route('admin.inventario.index'))
            ->assertSessionHas('success');

        $this->assertSame(25, $producto->fresh()->stock);
    }

    #[Test]
    public function un_ajuste_a_la_baja_actualiza_correctamente_el_stock(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create(['stock' => 10]);

        $this->actingAs($admin)
            ->post('/admin/inventario/ajuste', [
                'producto_id' => $producto->id,
                'nuevo_stock' => 4,
                'motivo'      => 'Merma detectada',
            ])
            ->assertRedirect(route('admin.inventario.index'));

        $this->assertSame(4, $producto->fresh()->stock);
    }

    #[Test]
    public function un_ajuste_actualiza_el_stock_de_la_variante(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create(['stock' => 99]);
        $variante = VarianteProducto::factory()->create(['producto_id' => $producto->id, 'stock' => 8]);

        $this->actingAs($admin)
            ->post('/admin/inventario/ajuste', [
                'producto_id' => $producto->id,
                'variante_id' => $variante->id,
                'nuevo_stock' => 12,
                'motivo'      => 'Conteo físico de tallas',
            ])
            ->assertRedirect(route('admin.inventario.index'));

        $this->assertSame(12, $variante->fresh()->stock);
        $this->assertSame(99, $producto->fresh()->stock);
    }

    #[Test]
    public function un_ajuste_crea_un_movimiento_de_tipo_ajuste_con_la_diferencia(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create(['stock' => 10]);

        $this->actingAs($admin)
            ->post('/admin/inventario/ajuste', [
                'producto_id' => $producto->id,
                'nuevo_stock' => 16,
                'motivo'      => 'Conteo físico',
                'notas'       => 'Se encontraron unidades extras en bodega',
            ])
            ->assertRedirect(route('admin.inventario.index'));

        $this->assertDatabaseHas('movimientos_inventario', [
            'producto_id'          => $producto->id,
            'usuario_id'           => $admin->id,
            'pedido_id'            => null,
            'tipo'                 => 'ajuste',
            'cantidad'             => 6, // |16 - 10|
            'stock_antes'          => 10,
            'stock_despues'        => 16,
            'motivo'               => 'Conteo físico',
            'notas'                => 'Se encontraron unidades extras en bodega',
        ]);
    }

    #[Test]
    public function un_ajuste_a_la_baja_guarda_la_diferencia_en_valor_absoluto(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create(['stock' => 10]);

        $this->actingAs($admin)
            ->post('/admin/inventario/ajuste', [
                'producto_id' => $producto->id,
                'nuevo_stock' => 3,
                'motivo'      => 'Merma detectada',
            ])
            ->assertRedirect(route('admin.inventario.index'));

        $this->assertDatabaseHas('movimientos_inventario', [
            'producto_id'   => $producto->id,
            'tipo'          => 'ajuste',
            'cantidad'      => 7, // |3 - 10|
            'stock_antes'   => 10,
            'stock_despues' => 3,
        ]);
    }

    #[Test]
    public function un_ajuste_registra_el_usuario_en_el_movimiento(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create(['stock' => 10]);

        $this->actingAs($admin)
            ->post('/admin/inventario/ajuste', [
                'producto_id' => $producto->id,
                'nuevo_stock' => 5,
                'motivo'      => 'Conteo físico',
            ]);

        $movimiento = MovimientoInventario::where('tipo', 'ajuste')->first();
        $this->assertNotNull($movimiento);
        $this->assertSame($admin->id, $movimiento->usuario_id);
    }

    #[Test]
    public function un_ajuste_requiere_motivo(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create(['stock' => 10]);

        $this->actingAs($admin)
            ->post('/admin/inventario/ajuste', [
                'producto_id' => $producto->id,
                'nuevo_stock' => 20,
            ])
            ->assertSessionHasErrors('motivo');

        $this->assertSame(10, $producto->fresh()->stock);
        $this->assertSame(0, MovimientoInventario::count());
    }

    #[Test]
    public function un_ajuste_con_stock_negativo_es_rechazado(): void
    {
        $admin = $this->crearAdmin();
        $producto = Producto::factory()->create(['stock' => 10]);

        $this->actingAs($admin)
            ->post('/admin/inventario/ajuste', [
                'producto_id' => $producto->id,
                'nuevo_stock' => -1,
                'motivo'      => 'Prueba',
            ])
            ->assertSessionHasErrors('nuevo_stock');

        $this->assertSame(10, $producto->fresh()->stock);
        $this->assertSame(0, MovimientoInventario::count());
    }

    #[Test]
    public function un_ajuste_con_producto_inexistente_es_rechazado(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->post('/admin/inventario/ajuste', [
                'producto_id' => 999999,
                'nuevo_stock' => 5,
                'motivo'      => 'Prueba',
            ])
            ->assertSessionHasErrors('producto_id');
    }
}
