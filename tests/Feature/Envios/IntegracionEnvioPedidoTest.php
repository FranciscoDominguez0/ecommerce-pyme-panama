<?php

namespace Tests\Feature\Envios;

use App\Models\Carrito;
use App\Models\Direccion;
use App\Models\EnvioPedido;
use App\Models\EstadoPedido;
use App\Models\ItemCarrito;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\PedidoService;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Admin\BaseAdminTest;

/**
 * Pruebas de INTEGRACIÓN del flujo de ENVÍOS (FASE 13).
 *
 * Flujo real cubierto:
 *   1. El cliente crea un pedido completo (PedidoService::crearDesdeCarrito).
 *   2. El administrador registra el envío (PUT /admin/pedidos/{id}/envio).
 *   3. El pedido pasa automáticamente a estado 'enviado'.
 *   4. La relación Pedido ↔ EnvioPedido resuelve ambos lados.
 *   5. Los estados logísticos avanzados ('en_transito', 'problema_entrega')
 *      exigen que el pedido tenga envío configurado (Admin\PedidoController).
 */
class IntegracionEnvioPedidoTest extends BaseAdminTest
{
    #[Test]
    public function un_pedido_completo_pasa_a_enviado_al_registrar_su_envio(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $cliente->id]);
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 10]);

        $pedido = $this->crearPedidoReal($cliente, $direccion, $producto);
        $this->assertSame(
            1,
            EstadoPedido::where('pedido_id', $pedido->id)->where('estado', 'pendiente')->count()
        );

        $this->actingAs($admin)
            ->put(route('admin.pedidos.envio.update', $pedido->id), [
                'metodo_envio'       => 'Company Delivery',
                'empresa_mensajeria' => 'UnoExpress',
                'numero_guia'        => 'GUIA-INTEGRACION-001',
            ])
            ->assertRedirect(route('admin.pedidos.detalle', $pedido->id));

        // Historial: 'pendiente' (al crearse) + 'enviado' (al registrar el envío).
        $estados = EstadoPedido::where('pedido_id', $pedido->id)->orderBy('id')->pluck('estado')->all();
        $this->assertSame(['pendiente', 'enviado'], $estados);

        $ultimo = EstadoPedido::where('pedido_id', $pedido->id)->orderByDesc('id')->first();
        $this->assertSame('enviado', $ultimo->estado);
        $this->assertSame($admin->id, $ultimo->usuario_id);
    }

    #[Test]
    public function la_relacion_pedido_envio_resuelve_el_pedido_y_su_usuario(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $cliente->id]);
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 10]);
        $pedido = $this->crearPedidoReal($cliente, $direccion, $producto);

        $this->actingAs($admin)
            ->put(route('admin.pedidos.envio.update', $pedido->id), [
                'metodo_envio' => 'Company Delivery',
                'numero_guia'  => 'GUIA-REL-001',
            ]);

        // EnvioPedido → pedido → usuario (integridad de relaciones).
        $envio = EnvioPedido::with('pedido.usuario')->where('pedido_id', $pedido->id)->first();
        $this->assertNotNull($envio);
        $this->assertSame($pedido->id, $envio->pedido->id);
        $this->assertSame($cliente->id, $envio->pedido->usuario->id);
        $this->assertSame($envio->id, $pedido->fresh()->envio->id);
    }

    #[Test]
    public function el_detalle_del_pedido_en_admin_muestra_la_informacion_del_envio(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $cliente->id]);
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 10]);
        $pedido = $this->crearPedidoReal($cliente, $direccion, $producto);

        $this->actingAs($admin)
            ->put(route('admin.pedidos.envio.update', $pedido->id), [
                'metodo_envio'       => 'Company Delivery',
                'empresa_mensajeria' => 'Fletes Chavale',
                'numero_guia'        => 'GUIA-DETALLE-777',
            ]);

        $this->actingAs($admin)
            ->get(route('admin.pedidos.detalle', $pedido->id))
            ->assertOk()
            ->assertSee('GUIA-DETALLE-777')
            ->assertSee('Company Delivery');
    }

    #[Test]
    public function los_estados_logisticos_avanzados_exigen_envio_configurado(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $direccion = Direccion::factory()->create(['usuario_id' => $cliente->id]);
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 10]);
        $pedido = $this->crearPedidoReal($cliente, $direccion, $producto);

        // Sin envío registrado: 'en_transito' es rechazado con aviso.
        $this->actingAs($admin)
            ->from(route('admin.pedidos.detalle', $pedido->id))
            ->post(route('admin.pedidos.estado', $pedido->id), ['estado' => 'en_transito'])
            ->assertRedirect(route('admin.pedidos.detalle', $pedido->id))
            ->assertSessionHas('toast_error');

        $this->assertSame(
            0,
            EstadoPedido::where('pedido_id', $pedido->id)->where('estado', 'en_transito')->count()
        );

        // Con envío registrado: 'en_transito' es aceptado.
        $this->actingAs($admin)
            ->put(route('admin.pedidos.envio.update', $pedido->id), [
                'metodo_envio' => 'Company Delivery',
                'numero_guia'  => 'GUIA-TRANSITO-001',
            ]);

        $this->actingAs($admin)
            ->post(route('admin.pedidos.estado', $pedido->id), ['estado' => 'en_transito']);

        $this->assertSame(
            1,
            EstadoPedido::where('pedido_id', $pedido->id)->where('estado', 'en_transito')->count()
        );
    }

    // =====================================================================
    //  HELPERS
    // =====================================================================

    /**
     * Crea un pedido real a través de PedidoService::crearDesdeCarrito
     * (flujo completo de la aplicación, no datos fabricados).
     */
    protected function crearPedidoReal(Usuario $cliente, Direccion $direccion, Producto $producto): Pedido
    {
        $carrito = Carrito::factory()->create(['usuario_id' => $cliente->id]);

        ItemCarrito::factory()->create([
            'carrito_id'      => $carrito->id,
            'producto_id'     => $producto->id,
            'cantidad'        => 2,
            'precio_unitario' => 50.00,
        ]);

        return app(PedidoService::class)->crearDesdeCarrito(
            $carrito,
            $direccion->id,
            'contra_entrega',
            null,
            null,
            null
        );
    }
}
