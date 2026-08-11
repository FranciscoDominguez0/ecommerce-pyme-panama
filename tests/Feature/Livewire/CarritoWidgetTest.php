<?php

namespace Tests\Feature\Livewire;

use App\Livewire\CarritoWidget;
use App\Models\Carrito;
use App\Models\Cupon;
use App\Models\ItemCarrito;
use App\Models\Producto;
use App\Models\Usuario;
use Livewire\Livewire;
use Tests\Feature\Admin\BaseAdminTest;

/**
 * Pruebas de reactividad Livewire del CARRITO (FASE 10) — componente CarritoWidget.
 *
 * El componente CarritoWidget es el que renderiza la página cliente/carrito.blade.php
 * (a diferencia de CarritoDrawer y NavbarBadges, que son componentes separados del
 * layout que también consumen CarritoService). Aquí se prueban las acciones
 * incrementar/decrementar/eliminar/aplicarCupon/removerCupon sin recarga de página.
 */
class CarritoWidgetTest extends BaseAdminTest
{
    // =====================================================================
    //  REACTIVIDAD DE CANTIDAD — el subtotal se actualiza sin recargar
    // =====================================================================

    public function test_incrementar_la_cantidad_actualiza_el_subtotal_sin_recargar(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['precio' => 10.00, 'stock' => 10]);
        $item = $this->crearCarritoConItem($usuario, $producto, 2, 10.00);

        Livewire::actingAs($usuario)
            ->test(CarritoWidget::class)
            ->call('incrementar', $item->id)
            ->assertSee('$30.00', false); // 3 × $10.00

        $this->assertSame(3, $item->fresh()->cantidad);
    }

    public function test_decrementar_la_cantidad_actualiza_el_subtotal_sin_recargar(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['precio' => 10.00, 'stock' => 10]);
        $item = $this->crearCarritoConItem($usuario, $producto, 2, 10.00);

        Livewire::actingAs($usuario)
            ->test(CarritoWidget::class)
            ->call('decrementar', $item->id)
            ->assertSee('$10.00', false); // 1 × $10.00

        $this->assertSame(1, $item->fresh()->cantidad);
    }

    public function test_decrementar_hasta_llegar_a_cero_elimina_el_item(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['precio' => 10.00, 'stock' => 10]);
        $item = $this->crearCarritoConItem($usuario, $producto, 1, 10.00);

        Livewire::actingAs($usuario)
            ->test(CarritoWidget::class)
            ->call('decrementar', $item->id)
            ->assertSee('Tu carrito está vacío');

        $this->assertDatabaseMissing('items_carrito', ['id' => $item->id]);
    }

    // =====================================================================
    //  LÍMITE DE STOCK
    // =====================================================================

    public function test_no_permite_incrementar_mas_allá_del_stock_disponible(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['precio' => 10.00, 'stock' => 2]);
        $item = $this->crearCarritoConItem($usuario, $producto, 2, 10.00);

        Livewire::actingAs($usuario)
            ->test(CarritoWidget::class)
            ->call('incrementar', $item->id)
            ->assertSee('Solo quedan 2 unidades disponibles');

        $this->assertSame(2, $item->fresh()->cantidad);
    }

    // =====================================================================
    //  ELIMINAR
    // =====================================================================

    public function test_eliminar_un_item_desde_el_widget(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['precio' => 10.00, 'stock' => 10]);
        $item = $this->crearCarritoConItem($usuario, $producto, 3, 10.00);

        Livewire::actingAs($usuario)
            ->test(CarritoWidget::class)
            ->call('eliminar', $item->id)
            ->assertSee('Tu carrito está vacío');

        $this->assertDatabaseMissing('items_carrito', ['id' => $item->id]);
    }

    // =====================================================================
    //  CUPÓN (punto de integración con el módulo de Cupones)
    // =====================================================================

    public function test_aplicar_un_cupon_valido_actualiza_el_descuento_y_el_total(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['precio' => 10.00, 'stock' => 10]);
        $this->crearCarritoConItem($usuario, $producto, 2, 10.00); // subtotal $20
        $cupon = Cupon::factory()->create(['codigo' => 'BIENVENIDO', 'tipo' => 'porcentaje', 'valor' => 10]);

        Livewire::actingAs($usuario)
            ->test(CarritoWidget::class)
            ->set('codigoCupon', 'bienvenido')
            ->call('aplicarCupon')
            ->assertSet('tipoMensajeCupon', 'success')
            ->assertSet('mensajeCupon', 'Cupón aplicado exitosamente.')
            ->assertSee('$24.40', false); // 20 + ITBMS 1.40 + envío 5 − descuento 2

        $carrito = Carrito::where('usuario_id', $usuario->id)->first();
        $this->assertSame($cupon->id, $carrito->fresh()->cupon_id);
        $this->assertSame(2.0, (float) $carrito->fresh()->descuento_aplicado);
    }

    public function test_aplicar_un_cupon_invalido_muestra_error_y_no_cambia_el_total(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['precio' => 10.00, 'stock' => 10]);
        $this->crearCarritoConItem($usuario, $producto, 2, 10.00);

        Livewire::actingAs($usuario)
            ->test(CarritoWidget::class)
            ->set('codigoCupon', 'NOEXISTE')
            ->call('aplicarCupon')
            ->assertSet('tipoMensajeCupon', 'error')
            ->assertSet('mensajeCupon', 'El código de cupón ingresado no existe.');

        $carrito = Carrito::where('usuario_id', $usuario->id)->first();
        $this->assertNull($carrito->fresh()->cupon_id);
        $this->assertSame('0.00', (string) $carrito->fresh()->descuento_aplicado);
    }

    public function test_aplicar_un_cupon_expirado_muestra_error_y_no_cambia_el_total(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['precio' => 10.00, 'stock' => 10]);
        $this->crearCarritoConItem($usuario, $producto, 1, 10.00);
        Cupon::factory()->expirado()->create(['codigo' => 'VENCIDO']);

        Livewire::actingAs($usuario)
            ->test(CarritoWidget::class)
            ->set('codigoCupon', 'VENCIDO')
            ->call('aplicarCupon')
            ->assertSet('tipoMensajeCupon', 'error')
            ->assertSet('mensajeCupon', 'Este cupón ha expirado.');

        $carrito = Carrito::where('usuario_id', $usuario->id)->first();
        $this->assertNull($carrito->fresh()->cupon_id);
        $this->assertSame('0.00', (string) $carrito->fresh()->descuento_aplicado);
    }

    public function test_remover_el_cupon_desde_el_widget(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['precio' => 10.00, 'stock' => 10]);
        $this->crearCarritoConItem($usuario, $producto, 1, 10.00);
        $cupon = Cupon::factory()->create(['codigo' => 'BIENVENIDO', 'tipo' => 'porcentaje', 'valor' => 10]);
        $carrito = Carrito::where('usuario_id', $usuario->id)->first();
        $carrito->update(['cupon_id' => $cupon->id, 'descuento_aplicado' => 1.00]);

        Livewire::actingAs($usuario)
            ->test(CarritoWidget::class)
            ->call('removerCupon')
            ->assertSet('mensajeCupon', null);

        $carrito->refresh();
        $this->assertNull($carrito->cupon_id);
        $this->assertSame('0.00', (string) $carrito->descuento_aplicado);
    }

    // =====================================================================
    //  HELPERS
    // =====================================================================

    /**
     * Crea un carrito con un único item (cantidad y precio indicados).
     */
    protected function crearCarritoConItem(Usuario $usuario, Producto $producto, int $cantidad, float $precio): ItemCarrito
    {
        $carrito = Carrito::factory()->create(['usuario_id' => $usuario->id]);

        return ItemCarrito::factory()->create([
            'carrito_id' => $carrito->id,
            'producto_id' => $producto->id,
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
        ]);
    }
}
