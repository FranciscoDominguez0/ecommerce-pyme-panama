<?php

namespace Tests\Feature\Cliente;

use App\Models\Carrito;
use App\Models\Direccion;
use App\Models\ItemCarrito;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\PagoService;
use Tests\Feature\Admin\BaseAdminTest;

/**
 * Pruebas del flujo HTTP de CHECKOUT (FASE 12).
 *
 * Rutas cubiertas:
 *   GET  /checkout/direccion, /checkout/pago, /checkout/confirmacion
 *   POST /checkout/pago (guardar-pago), /checkout/confirmacion (procesar)
 *   GET  /mi-cuenta/mis-pedidos, /mi-cuenta/mis-pedidos/{id}
 *
 * El pago se procesa ANTES de crear el pedido: si falla, no hay pedido, no se
 * descuenta stock y el carrito queda intacto. Stripe/Yappy son simulaciones; el
 * fallo se fuerza con un mock de PagoService (nunca se llama a APIs reales).
 */
class CheckoutTest extends BaseAdminTest
{
    // =====================================================================
    //  ACCESO — invitados
    // =====================================================================

    public function test_un_invitado_es_redirigido_al_login_en_el_checkout(): void
    {
        $this->get('/checkout/direccion')->assertRedirect(route('login'));
        $this->get('/checkout/pago')->assertRedirect(route('login'));
        $this->get('/checkout/confirmacion')->assertRedirect(route('login'));
        $this->post('/checkout/confirmacion', [])->assertRedirect(route('login'));
    }

    // =====================================================================
    //  PASOS DEL CHECKOUT — renderizado
    // =====================================================================

    public function test_el_paso_de_direccion_muestra_las_direcciones_y_la_opcion_de_agregar_nueva(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['stock' => 10]);
        $this->crearCarritoConItem($usuario, $producto, 1, 50.00);
        Direccion::factory()->create(['usuario_id' => $usuario->id, 'alias' => 'Casa']);

        $this->actingAs($usuario)
            ->get('/checkout/direccion')
            ->assertOk()
            ->assertSee('Seleccione su dirección de envío')
            ->assertSee('Casa')
            ->assertSee('Ingresar Nueva');
    }

    public function test_el_paso_de_pago_muestra_los_cuatro_metodos_de_pago(): void
    {
        $usuario = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $usuario->id]);

        $this->actingAs($usuario)
            ->withSession(['checkout_direccion_id' => $direccion->id])
            ->get('/checkout/pago')
            ->assertOk()
            ->assertSee('name="metodo_pago"', false)
            ->assertSee('value="stripe"', false)
            ->assertSee('value="yappy"', false)
            ->assertSee('value="transferencia"', false)
            ->assertSee('value="contra_entrega"', false)
            ->assertSee('Tarjeta de Crédito / Débito')
            ->assertSee('Pago Contra Entrega');
    }

    public function test_el_paso_de_pago_sin_direccion_seleccionada_redirige_a_direccion(): void
    {
        $usuario = $this->crearCliente();

        $this->actingAs($usuario)
            ->get('/checkout/pago')
            ->assertRedirect(route('cliente.checkout.direccion'))
            ->assertSessionHas('warning');
    }

    public function test_el_paso_de_confirmacion_muestra_el_resumen_y_el_boton_confirmar(): void
    {
        $usuario = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $usuario->id, 'alias' => 'Casa']);
        $producto = Producto::factory()->create(['precio' => 100.00, 'stock' => 10]);
        $this->crearCarritoConItem($usuario, $producto, 2, 100.00);

        $this->actingAs($usuario)
            ->withSession([
                'checkout_direccion_id' => $direccion->id,
                'checkout_metodo_pago' => 'contra_entrega',
            ])
            ->get('/checkout/confirmacion')
            ->assertOk()
            ->assertSee('Resumen de tu Pedido')
            ->assertSee($producto->nombre)
            ->assertSee('Confirmar Pedido')
            ->assertSee('Envío a');
    }

    public function test_la_confirmacion_sin_metodo_de_pago_redirige_a_pago(): void
    {
        $usuario = $this->crearCliente();

        $this->actingAs($usuario)
            ->get('/checkout/confirmacion')
            ->assertRedirect(route('cliente.checkout.pago'))
            ->assertSessionHas('warning');
    }

    // =====================================================================
    //  CONFIRMACIÓN DEL PEDIDO
    // =====================================================================

    public function test_confirmar_un_pedido_contra_entrega_crea_el_pedido_y_descuenta_stock(): void
    {
        $usuario = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $usuario->id]);
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 5]);
        $this->crearCarritoConItem($usuario, $producto, 2, 50.00);

        $this->actingAs($usuario)
            ->withSession([
                'checkout_direccion_id' => $direccion->id,
                'checkout_metodo_pago' => 'contra_entrega',
            ])
            ->post('/checkout/confirmacion', ['notas_cliente' => 'Entregar en portería'])
            ->assertRedirect()
            ->assertSessionHas('pedido_creado_animacion');

        $pedido = Pedido::where('usuario_id', $usuario->id)->first();
        $this->assertNotNull($pedido);
        $this->assertSame($direccion->id, $pedido->direccion_id);
        $this->assertSame('contra_entrega', $pedido->metodo_pago);
        $this->assertSame('100.00', $pedido->subtotal);
        $this->assertSame('7.00', $pedido->itbms_monto); // 7% de 100
        $this->assertMatchesRegularExpression('/^#PM-\d+$/', $pedido->numero_pedido);
        $this->assertSame(3, $producto->fresh()->stock);
        $this->assertDatabaseHas('items_pedido', ['pedido_id' => $pedido->id, 'producto_id' => $producto->id, 'cantidad' => 2]);
        $this->assertDatabaseHas('estados_pedido', ['pedido_id' => $pedido->id, 'estado' => 'pendiente']);

        // La sesión del checkout se limpia.
        $this->assertNull(session('checkout_direccion_id'));
        $this->assertNull(session('checkout_metodo_pago'));
    }

    public function test_no_se_puede_confirmar_un_pedido_con_el_carrito_vacio(): void
    {
        $usuario = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $usuario->id]);

        $this->actingAs($usuario)
            ->withSession([
                'checkout_direccion_id' => $direccion->id,
                'checkout_metodo_pago' => 'contra_entrega',
            ])
            ->post('/checkout/confirmacion', [])
            ->assertRedirect(route('cliente.carrito'))
            ->assertSessionHas('warning');

        $this->assertSame(0, Pedido::count());
    }

    public function test_no_se_puede_confirmar_sin_direccion_seleccionada(): void
    {
        $usuario = $this->crearCliente();
        $producto = Producto::factory()->create(['stock' => 10]);
        $this->crearCarritoConItem($usuario, $producto, 1, 50.00);

        $this->actingAs($usuario)
            ->withSession(['checkout_metodo_pago' => 'contra_entrega'])
            ->post('/checkout/confirmacion', [])
            ->assertRedirect(route('cliente.checkout.direccion'))
            ->assertSessionHas('error');

        $this->assertSame(0, Pedido::count());
    }

    public function test_no_se_acepta_un_metodo_de_pago_invalido(): void
    {
        $usuario = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $usuario->id]);

        $this->actingAs($usuario)
            ->withSession(['checkout_direccion_id' => $direccion->id])
            ->post('/checkout/pago', ['metodo_pago' => 'bitcoin'])
            ->assertSessionHasErrors('metodo_pago');

        $this->assertNull(session('checkout_metodo_pago'));
    }

    // =====================================================================
    //  FALLO DE PAGO — no se crea pedido, no se descuenta stock, carrito intacto
    // =====================================================================

    public function test_si_el_pago_con_stripe_falla_no_se_crea_el_pedido(): void
    {
        $this->mock(PagoService::class, function ($mock) {
            $mock->shouldReceive('procesarStripe')->once()->andReturn(false);
        });

        $usuario = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $usuario->id]);
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 5]);
        $carrito = $this->crearCarritoConItem($usuario, $producto, 2, 50.00);

        $this->actingAs($usuario)
            ->withSession([
                'checkout_direccion_id' => $direccion->id,
                'checkout_metodo_pago' => 'stripe',
            ])
            ->post('/checkout/confirmacion', [])
            ->assertRedirect(route('cliente.checkout.pago'))
            ->assertSessionHas('error');

        $this->assertSame(0, Pedido::count());
        $this->assertSame(5, $producto->fresh()->stock);
        $this->assertSame(1, $carrito->items()->count()); // carrito intacto
    }

    public function test_si_el_pago_con_yappy_falla_no_se_crea_el_pedido(): void
    {
        $this->mock(PagoService::class, function ($mock) {
            $mock->shouldReceive('procesarYappy')->once()->andReturn(false);
        });

        $usuario = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $usuario->id]);
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 5]);
        $carrito = $this->crearCarritoConItem($usuario, $producto, 1, 50.00);

        $this->actingAs($usuario)
            ->withSession([
                'checkout_direccion_id' => $direccion->id,
                'checkout_metodo_pago' => 'yappy',
            ])
            ->post('/checkout/confirmacion', [])
            ->assertRedirect(route('cliente.checkout.pago'))
            ->assertSessionHas('error');

        $this->assertSame(0, Pedido::count());
        $this->assertSame(5, $producto->fresh()->stock);
        $this->assertSame(1, $carrito->items()->count());
    }

    public function test_la_transferencia_sin_comprobante_impide_crear_el_pedido(): void
    {
        // HALLAZGO: "transferencia" requiere comprobante; sin él PagoService retorna
        // false y NO se crea el pedido (queda pendiente de confirmación manual).
        $usuario = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $usuario->id]);
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 5]);
        $this->crearCarritoConItem($usuario, $producto, 1, 50.00);

        $this->actingAs($usuario)
            ->withSession([
                'checkout_direccion_id' => $direccion->id,
                'checkout_metodo_pago' => 'transferencia',
            ])
            ->post('/checkout/confirmacion', [])
            ->assertRedirect(route('cliente.checkout.pago'))
            ->assertSessionHas('error');

        $this->assertSame(0, Pedido::count());
        $this->assertSame(5, $producto->fresh()->stock);
    }

    // =====================================================================
    //  MIS PEDIDOS — aislamiento por usuario
    // =====================================================================

    public function test_mis_pedidos_muestra_solo_los_pedidos_del_usuario_autenticado(): void
    {
        $usuarioA = $this->crearCliente();
        $usuarioB = $this->crearCliente();
        $pedidoA = Pedido::factory()->create(['usuario_id' => $usuarioA->id]);
        $pedidoB = Pedido::factory()->create(['usuario_id' => $usuarioB->id]);

        $this->actingAs($usuarioA)
            ->get('/mi-cuenta/mis-pedidos')
            ->assertOk()
            ->assertSee($pedidoA->numero_pedido)
            ->assertDontSee($pedidoB->numero_pedido);
    }

    public function test_un_cliente_no_puede_ver_el_detalle_de_un_pedido_de_otro_usuario(): void
    {
        $usuarioA = $this->crearCliente();
        $usuarioB = $this->crearCliente();
        $pedidoB = Pedido::factory()->create(['usuario_id' => $usuarioB->id]);

        $this->actingAs($usuarioA)
            ->get('/mi-cuenta/mis-pedidos/' . $pedidoB->id)
            ->assertNotFound();
    }

    public function test_mis_pedidos_muestra_la_insignia_con_el_estado_actual(): void
    {
        $usuario = $this->crearCliente();
        $pedido = Pedido::factory()->create(['usuario_id' => $usuario->id]);
        $estadoCancelado = \App\Models\EstadoPedido::factory()->create(['pedido_id' => $pedido->id, 'estado' => 'cancelado']);
        // Asegura que sea el estado más reciente (creado_en tiene precisión de segundos).
        $estadoCancelado->forceFill(['creado_en' => now()->addSecond()])->save();

        $this->actingAs($usuario)
            ->get('/mi-cuenta/mis-pedidos')
            ->assertOk()
            ->assertSee($pedido->numero_pedido)
            // La insignia refleja el último estado real (ucfirst de "cancelado").
            ->assertSee('Cancelado');
    }

    // =====================================================================
    //  HELPERS
    // =====================================================================

    /**
     * Crea un carrito con un item para el usuario indicado.
     */
    protected function crearCarritoConItem(Usuario $usuario, Producto $producto, int $cantidad, float $precio): Carrito
    {
        $carrito = Carrito::factory()->create(['usuario_id' => $usuario->id]);

        ItemCarrito::factory()->create([
            'carrito_id' => $carrito->id,
            'producto_id' => $producto->id,
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
        ]);

        return $carrito;
    }
}
