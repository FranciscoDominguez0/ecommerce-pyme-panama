<?php

namespace Tests\Feature\Inventario;

use App\Models\Carrito;
use App\Models\Direccion;
use App\Models\ItemCarrito;
use App\Models\MovimientoInventario;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use App\Models\VarianteProducto;
use App\Services\CarritoService;
use App\Services\PedidoService;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Admin\BaseAdminTest;

/**
 * Pruebas de INTEGRACIÓN entre ventas e inventario (FASE 14).
 *
 * Verifica el comportamiento real de PedidoService::crearDesdeCarrito:
 *   1. Una venta completada DESCUENTA el stock (producto o variante).
 *   2. Por cada item se genera automáticamente un movimiento de inventario
 *      tipo "salida" (misma transacción, con motivo "Venta - Pedido #PM-...").
 *   3. El movimiento queda vinculado al pedido y al usuario comprador.
 */
class IntegracionVentasInventarioTest extends BaseAdminTest
{
    #[Test]
    public function una_venta_completada_disminuye_el_stock_del_producto(): void
    {
        $usuario = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $usuario->id]);
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 10]);
        $carrito = $this->crearCarritoConItem($usuario, $producto, 3, 50.00);

        app(PedidoService::class)->crearDesdeCarrito($carrito, $direccion->id, 'contra_entrega', null, null, null);

        $this->assertSame(7, $producto->fresh()->stock);
    }

    #[Test]
    public function una_venta_con_variante_disminuye_el_stock_de_la_variante(): void
    {
        $usuario = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $usuario->id]);
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 99]);
        $variante = VarianteProducto::factory()->create(['producto_id' => $producto->id, 'stock' => 5]);
        $carrito = $this->crearCarritoConItem($usuario, $producto, 2, 60.00, $variante->id);

        app(PedidoService::class)->crearDesdeCarrito($carrito, $direccion->id, 'contra_entrega', null, null, null);

        $this->assertSame(3, $variante->fresh()->stock);
        $this->assertSame(99, $producto->fresh()->stock);
    }

    #[Test]
    public function una_venta_genera_automaticamente_un_movimiento_de_salida(): void
    {
        $usuario = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $usuario->id]);
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 10]);
        $carrito = $this->crearCarritoConItem($usuario, $producto, 3, 50.00);

        $pedido = app(PedidoService::class)->crearDesdeCarrito($carrito, $direccion->id, 'contra_entrega', null, null, null);

        $this->assertDatabaseHas('movimientos_inventario', [
            'producto_id'          => $producto->id,
            'variante_producto_id' => null,
            'pedido_id'            => $pedido->id,
            'tipo'                 => 'salida',
            'cantidad'             => 3,
            'stock_antes'          => 10,
            'stock_despues'        => 7,
            'motivo'               => 'Venta - Pedido ' . $pedido->numero_pedido,
        ]);
    }

    #[Test]
    public function una_venta_con_variante_genera_el_movimiento_apuntando_a_la_variante(): void
    {
        $usuario = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $usuario->id]);
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 99]);
        $variante = VarianteProducto::factory()->create(['producto_id' => $producto->id, 'stock' => 5]);
        $carrito = $this->crearCarritoConItem($usuario, $producto, 2, 60.00, $variante->id);

        $pedido = app(PedidoService::class)->crearDesdeCarrito($carrito, $direccion->id, 'contra_entrega', null, null, null);

        $this->assertDatabaseHas('movimientos_inventario', [
            'producto_id'          => $producto->id,
            'variante_producto_id' => $variante->id,
            'pedido_id'            => $pedido->id,
            'tipo'                 => 'salida',
            'cantidad'             => 2,
            'stock_antes'          => 5,
            'stock_despues'        => 3,
        ]);
    }

    #[Test]
    public function el_movimiento_de_una_venta_queda_vinculado_al_usuario_que_compra(): void
    {
        $usuario = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $usuario->id]);
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 10]);
        $carrito = $this->crearCarritoConItem($usuario, $producto, 1, 50.00);

        $pedido = app(PedidoService::class)->crearDesdeCarrito($carrito, $direccion->id, 'contra_entrega', null, null, null);

        $movimiento = MovimientoInventario::where('pedido_id', $pedido->id)->first();
        $this->assertNotNull($movimiento);
        $this->assertSame($usuario->id, $movimiento->usuario_id);
    }

    #[Test]
    public function una_venta_por_el_checkout_http_descuenta_stock_y_registra_el_movimiento(): void
    {
        $usuario = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $usuario->id]);
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 5]);

        $carritoService = app(CarritoService::class);
        $carritoService->obtenerOCrearCarrito($usuario->id, null);
        $carritoService->agregarProducto($producto->id, null, 2, $usuario->id, null);

        $this->actingAs($usuario)
            ->withSession([
                'checkout_direccion_id' => $direccion->id,
                'checkout_metodo_pago'  => 'contra_entrega',
            ])
            ->post('/checkout/confirmacion', ['notas_cliente' => ''])
            ->assertRedirect()
            ->assertSessionHas('pedido_creado_animacion');

        $this->assertSame(3, $producto->fresh()->stock);

        $pedido = Pedido::where('usuario_id', $usuario->id)->first();
        $this->assertNotNull($pedido);
        $this->assertDatabaseHas('movimientos_inventario', [
            'producto_id'   => $producto->id,
            'pedido_id'     => $pedido->id,
            'tipo'          => 'salida',
            'cantidad'      => 2,
            'stock_despues' => 3,
            'usuario_id'    => $usuario->id,
        ]);
    }

    // =====================================================================
    //  HELPERS
    // =====================================================================

    /**
     * Crea un carrito con un item (producto o variante).
     */
    protected function crearCarritoConItem(
        Usuario $usuario,
        Producto $producto,
        int $cantidad,
        float $precio,
        ?int $varianteProductoId = null
    ): Carrito {
        $carrito = Carrito::factory()->create(['usuario_id' => $usuario->id]);

        ItemCarrito::factory()->create([
            'carrito_id'           => $carrito->id,
            'producto_id'          => $producto->id,
            'variante_producto_id' => $varianteProductoId,
            'cantidad'             => $cantidad,
            'precio_unitario'      => $precio,
        ]);

        return $carrito;
    }
}
