<?php

namespace Tests\Feature\Admin;

use App\Models\EstadoPedido;
use App\Models\Pedido;

/**
 * Pruebas del módulo ADMIN de Pedidos (FASE 12).
 *
 * Rutas cubiertas:
 *   GET  /admin/pedidos                          → index (listado filtrable)
 *   GET  /admin/pedidos/{id}                     → detalle
 *   POST /admin/pedidos/{id}/estado              → cambiarEstado
 *   POST /admin/pedidos/{id}/aprobar-pago        → aprobarPago
 *   POST /admin/pedidos/{id}/rechazar-pago       → rechazarPago
 *
 * Estados reales (CHECK estados_pedido_estado_check): pendiente, pago_confirmado,
 * pago_rechazado, en_preparacion, listo_para_envio, enviado, entregado, cancelado,
 * devolucion_solicitada, devolucion_aprobada, devolucion_rechazada.
 */
class PedidoTest extends BaseAdminTest
{
    // =====================================================================
    //  AUTORIZACIÓN — Solo administradores pueden acceder
    // =====================================================================

    public function test_el_acceso_a_la_gestion_de_pedidos_requiere_iniciar_sesion(): void
    {
        $this->get('/admin/pedidos')->assertRedirect('/login');
        $this->get('/admin/pedidos/1')->assertRedirect('/login');
    }

    public function test_un_cliente_no_puede_acceder_a_la_gestion_de_pedidos(): void
    {
        $cliente = $this->crearCliente();

        $this->actingAs($cliente)->get('/admin/pedidos')->assertForbidden();
    }

    public function test_un_administrador_puede_acceder_al_listado_de_pedidos(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)->get('/admin/pedidos')->assertOk();
    }

    // =====================================================================
    //  LISTADO — index (filtro por estado)
    // =====================================================================

    public function test_el_listado_muestra_los_pedidos_con_numero_cliente_y_total(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $pedido = Pedido::factory()->create([
            'usuario_id' => $cliente->id,
            'numero_pedido' => 'PM-ABC-123',
            'total' => 150.00,
        ]);

        $this->actingAs($admin)
            ->get('/admin/pedidos')
            ->assertOk()
            ->assertSee('PM-ABC-123')
            ->assertSee($cliente->nombre)
            ->assertSee('$150.00', false);
    }

    public function test_el_listado_tiene_filtro_por_estado(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $entregado = Pedido::factory()->create(['usuario_id' => $cliente->id]);
        $pendiente = Pedido::factory()->create(['usuario_id' => $cliente->id]);
        EstadoPedido::factory()->create(['pedido_id' => $entregado->id, 'estado' => 'entregado']);

        $this->actingAs($admin)
            ->get('/admin/pedidos?estado=entregado')
            ->assertOk()
            ->assertSee($entregado->numero_pedido)
            ->assertDontSee($pendiente->numero_pedido);
    }

    // =====================================================================
    //  DETALLE — acciones de aprobar/rechazar y cambio de estado
    // =====================================================================

    public function test_el_detalle_muestra_las_acciones_de_aprobar_y_rechazar_pago(): void
    {
        // Las acciones de aprobar/rechazar solo se muestran para pedidos de
        // "transferencia" en estado "pendiente" (requieren confirmación manual).
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $pedido = Pedido::factory()->create([
            'usuario_id' => $cliente->id,
            'metodo_pago' => 'transferencia',
        ]);

        $this->actingAs($admin)
            ->get('/admin/pedidos/' . $pedido->id)
            ->assertOk()
            ->assertSee('Aprobar Pago')
            ->assertSee('Rechazar')
            ->assertSee($pedido->numero_pedido);
    }

    public function test_un_administrador_puede_aprobar_el_pago_de_un_pedido(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $pedido = Pedido::factory()->create(['usuario_id' => $cliente->id]);

        $this->actingAs($admin)
            ->post('/admin/pedidos/' . $pedido->id . '/aprobar-pago')
            ->assertRedirect()
            ->assertSessionHas('toast_success');

        $this->assertDatabaseHas('estados_pedido', [
            'pedido_id' => $pedido->id,
            'estado' => 'pago_confirmado',
        ]);
    }

    public function test_un_administrador_puede_rechazar_el_pago_con_comentario(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $pedido = Pedido::factory()->create(['usuario_id' => $cliente->id]);

        $this->actingAs($admin)
            ->post('/admin/pedidos/' . $pedido->id . '/rechazar-pago', [
                'comentario' => 'Comprobante ilegible',
            ])
            ->assertRedirect()
            ->assertSessionHas('toast_success');

        $this->assertDatabaseHas('estados_pedido', [
            'pedido_id' => $pedido->id,
            'estado' => 'pago_rechazado',
            'comentario' => 'Pago rechazado: Comprobante ilegible',
        ]);
    }

    public function test_rechazar_el_pago_exige_un_comentario(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $pedido = Pedido::factory()->create(['usuario_id' => $cliente->id]);

        $this->actingAs($admin)
            ->post('/admin/pedidos/' . $pedido->id . '/rechazar-pago', ['comentario' => ''])
            ->assertSessionHasErrors('comentario');

        $this->assertDatabaseMissing('estados_pedido', [
            'pedido_id' => $pedido->id,
            'estado' => 'pago_rechazado',
        ]);
    }

    public function test_un_administrador_puede_cambiar_el_estado_de_un_pedido(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $pedido = Pedido::factory()->create(['usuario_id' => $cliente->id]);

        $this->actingAs($admin)
            ->post('/admin/pedidos/' . $pedido->id . '/estado', [
                'estado' => 'enviado',
                'comentario' => 'Paquete entregado a mensajería',
            ])
            ->assertRedirect()
            ->assertSessionHas('toast_success');

        $this->assertDatabaseHas('estados_pedido', [
            'pedido_id' => $pedido->id,
            'estado' => 'enviado',
            'comentario' => 'Paquete entregado a mensajería',
        ]);
    }

    public function test_el_historial_de_estados_no_se_sobrescribe_al_cambiar_estado(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $pedido = Pedido::factory()->create(['usuario_id' => $cliente->id]);

        $this->actingAs($admin)->post('/admin/pedidos/' . $pedido->id . '/estado', ['estado' => 'enviado']);
        $this->actingAs($admin)->post('/admin/pedidos/' . $pedido->id . '/estado', ['estado' => 'entregado']);

        $this->assertSame(1, EstadoPedido::where('pedido_id', $pedido->id)->where('estado', 'enviado')->count());
        $this->assertSame(1, EstadoPedido::where('pedido_id', $pedido->id)->where('estado', 'entregado')->count());
    }
}
