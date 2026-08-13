<?php

namespace Tests\Feature\Envios;

use App\Models\EnvioPedido;
use App\Models\EstadoPedido;
use App\Models\Pedido;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Admin\BaseAdminTest;

/**
 * Pruebas del registro de ENVÍOS (FASE 13).
 *
 * Ruta cubierta: PUT /admin/pedidos/{id}/envio (EnvioPedidoController::update)
 *
 * Comportamiento real implementado:
 *   - Validación: metodo_envio requerido; empresa_mensajeria, numero_guia y
 *     fecha_estimada_entrega opcionales (fecha válida).
 *   - EnvioPedido::updateOrCreate por pedido (un solo envío por pedido).
 *   - empresa_mensajeria se guarda como "metodo_envio - empresa_mensajeria".
 *   - Si el envío es NUEVO, el pedido pasa automáticamente al estado 'enviado'
 *     (PedidoService::cambiarEstado, comentario con el método de envío).
 */
class EnvioPedidoTest extends BaseAdminTest
{
    // =====================================================================
    //  REGISTRO DE ENVÍO
    // =====================================================================

    #[Test]
    public function un_administrador_puede_registrar_un_envio_con_metodo_mensajeria_y_guia(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $pedido = Pedido::factory()->create(['usuario_id' => $cliente->id]);

        $this->actingAs($admin)
            ->put(route('admin.pedidos.envio.update', $pedido->id), [
                'metodo_envio'           => 'Company Delivery',
                'empresa_mensajeria'     => 'UnoExpress',
                'numero_guia'            => '1Z999AA10123456784',
                'fecha_estimada_entrega' => now()->addDays(3)->toDateString(),
            ])
            ->assertRedirect(route('admin.pedidos.detalle', $pedido->id))
            ->assertSessionHas('toast_success');

        $this->assertDatabaseHas('envios_pedido', [
            'pedido_id'          => $pedido->id,
            'empresa_mensajeria' => 'Company Delivery - UnoExpress',
            'numero_guia'        => '1Z999AA10123456784',
        ]);
    }

    #[Test]
    public function el_envio_queda_asociado_al_pedido(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $pedido = Pedido::factory()->create(['usuario_id' => $cliente->id]);

        $this->actingAs($admin)
            ->put(route('admin.pedidos.envio.update', $pedido->id), [
                'metodo_envio' => 'Store Pickup',
            ])
            ->assertRedirect(route('admin.pedidos.detalle', $pedido->id));

        $envio = EnvioPedido::where('pedido_id', $pedido->id)->first();
        $this->assertNotNull($envio);
        $this->assertSame($pedido->id, $envio->pedido_id);
        $this->assertSame($envio->id, $pedido->fresh()->envio->id);
    }

    #[Test]
    public function el_envio_combina_el_metodo_y_la_empresa_de_mensajeria(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $pedido = Pedido::factory()->create(['usuario_id' => $cliente->id]);

        $this->actingAs($admin)
            ->put(route('admin.pedidos.envio.update', $pedido->id), [
                'metodo_envio'       => 'Company Delivery',
                'empresa_mensajeria' => 'Fletes Chavale',
            ]);

        $this->assertSame(
            'Company Delivery - Fletes Chavale',
            $pedido->fresh()->envio->empresa_mensajeria
        );
    }

    #[Test]
    public function la_fecha_estimada_de_entrega_se_guarda_en_el_envio(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $pedido = Pedido::factory()->create(['usuario_id' => $cliente->id]);
        $fechaEstimada = now()->addDays(5)->toDateString();

        $this->actingAs($admin)
            ->put(route('admin.pedidos.envio.update', $pedido->id), [
                'metodo_envio'           => 'Company Delivery',
                'fecha_estimada_entrega' => $fechaEstimada,
            ]);

        $envio = $pedido->fresh()->envio;
        $this->assertNotNull($envio->fecha_estimada_entrega);
        $this->assertSame($fechaEstimada, $envio->fecha_estimada_entrega->toDateString());
    }

    // =====================================================================
    //  ESTADO AUTOMÁTICO DEL PEDIDO
    // =====================================================================

    #[Test]
    public function registrar_un_envio_marca_el_pedido_como_enviado_automaticamente(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $pedido = Pedido::factory()->create(['usuario_id' => $cliente->id]);

        $this->actingAs($admin)
            ->put(route('admin.pedidos.envio.update', $pedido->id), [
                'metodo_envio'       => 'Company Delivery',
                'empresa_mensajeria' => 'UnoExpress',
                'numero_guia'        => '1Z999AA10123456784',
            ]);

        $this->assertDatabaseHas('estados_pedido', [
            'pedido_id' => $pedido->id,
            'usuario_id' => $admin->id,
            'estado' => 'enviado',
            'comentario' => 'Pedido preparado para envío: Company Delivery - UnoExpress',
        ]);
    }

    #[Test]
    public function actualizar_un_envio_existente_no_duplica_el_envio_ni_el_estado_enviado(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $pedido = Pedido::factory()->create(['usuario_id' => $cliente->id]);

        // Primer registro: crea el envío y el estado 'enviado'.
        $this->actingAs($admin)
            ->put(route('admin.pedidos.envio.update', $pedido->id), [
                'metodo_envio' => 'Company Delivery',
                'numero_guia'  => 'GUIA-001',
            ]);

        // Actualización del mismo envío (cambia la guía): ni duplica ni re-registra el estado.
        $this->actingAs($admin)
            ->put(route('admin.pedidos.envio.update', $pedido->id), [
                'metodo_envio' => 'Company Delivery',
                'numero_guia'  => 'GUIA-002',
            ])
            ->assertRedirect(route('admin.pedidos.detalle', $pedido->id));

        $this->assertSame(1, EnvioPedido::where('pedido_id', $pedido->id)->count());
        $this->assertSame('GUIA-002', $pedido->fresh()->envio->numero_guia);
        $this->assertSame(
            1,
            EstadoPedido::where('pedido_id', $pedido->id)->where('estado', 'enviado')->count()
        );
    }

    // =====================================================================
    //  VALIDACIONES
    // =====================================================================

    #[Test]
    public function un_envio_sin_metodo_de_envio_es_rechazado(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $pedido = Pedido::factory()->create(['usuario_id' => $cliente->id]);

        $this->actingAs($admin)
            ->put(route('admin.pedidos.envio.update', $pedido->id), [
                'empresa_mensajeria' => 'UnoExpress',
                'numero_guia'        => '1Z999AA10123456784',
            ])
            ->assertSessionHasErrors('metodo_envio');

        $this->assertDatabaseMissing('envios_pedido', ['pedido_id' => $pedido->id]);
    }

    #[Test]
    public function una_fecha_estimada_invalida_es_rechazada(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $pedido = Pedido::factory()->create(['usuario_id' => $cliente->id]);

        $this->actingAs($admin)
            ->put(route('admin.pedidos.envio.update', $pedido->id), [
                'metodo_envio'           => 'Company Delivery',
                'fecha_estimada_entrega' => 'no-es-una-fecha',
            ])
            ->assertSessionHasErrors('fecha_estimada_entrega');

        $this->assertDatabaseMissing('envios_pedido', ['pedido_id' => $pedido->id]);
    }

    #[Test]
    public function registrar_un_envio_de_un_pedido_inexistente_devuelve_404(): void
    {
        $admin = $this->crearAdmin();

        $this->actingAs($admin)
            ->put(route('admin.pedidos.envio.update', 999999), [
                'metodo_envio' => 'Company Delivery',
            ])
            ->assertNotFound();
    }

    // =====================================================================
    //  AUTORIZACIÓN
    // =====================================================================

    #[Test]
    public function un_cliente_no_puede_registrar_envios(): void
    {
        $cliente = $this->crearCliente();
        $pedido = Pedido::factory()->create(['usuario_id' => $cliente->id]);

        $this->actingAs($cliente)
            ->put(route('admin.pedidos.envio.update', $pedido->id), [
                'metodo_envio' => 'Company Delivery',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('envios_pedido', ['pedido_id' => $pedido->id]);
    }

    #[Test]
    public function un_usuario_no_autenticado_no_puede_registrar_envios(): void
    {
        $cliente = $this->crearCliente();
        $pedido = Pedido::factory()->create(['usuario_id' => $cliente->id]);

        $this->put(route('admin.pedidos.envio.update', $pedido->id), [
                'metodo_envio' => 'Company Delivery',
            ])
            ->assertRedirect('/login');
    }
}
