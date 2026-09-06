<?php

namespace Tests\Feature\Admin\Pedidos;

use App\Models\Direccion;
use App\Models\EstadoPedido;
use App\Models\LogAuditoria;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use App\Models\ZonaEnvio;
use App\Services\PedidoService;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Admin\BaseAdminTest;

class ControlReembolsosTest extends BaseAdminTest
{
    private function crearPedido(Usuario $cliente, string $metodoPago = 'stripe'): Pedido
    {
        $zona = ZonaEnvio::factory()->create();
        $direccion = Direccion::factory()->create(['usuario_id' => $cliente->id]);
        $producto = Producto::factory()->create(['precio' => 100.00, 'stock' => 10]);

        $pedido = Pedido::create([
            'numero_pedido' => 'PM-' . rand(100000, 999999),
            'usuario_id' => $cliente->id,
            'direccion_id' => $direccion->id,
            'zona_envio_id' => $zona->id,
            'subtotal' => 100.00,
            'itbms' => 7.00,
            'costo_envio' => 5.00,
            'total' => 112.00,
            'metodo_pago' => $metodoPago,
            'stripe_payment_intent_id' => 'pi_simulacion_test_123',
        ]);

        EstadoPedido::create([
            'pedido_id' => $pedido->id,
            'usuario_id' => $cliente->id,
            'estado' => 'pago_confirmado',
            'comentario' => 'Pago confirmado de prueba.',
        ]);

        return $pedido;
    }

    #[Test]
    public function no_se_puede_establecer_estado_reembolsado_desde_el_selector_generico_de_estado(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $pedido = $this->crearPedido($cliente);

        $response = $this->actingAs($admin)
            ->post(route('admin.pedidos.estado', $pedido->id), [
                'estado' => 'reembolsado',
                'comentario' => 'Intento de reembolso manual sin pasarela',
            ]);

        $response->assertSessionHas('toast_error');
        $this->assertNotEquals('reembolsado', $pedido->fresh()->ultimoEstado->estado);
    }

    #[Test]
    public function un_pedido_ya_reembolsado_bloquea_posteriores_cambios_de_estado(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $pedido = $this->crearPedido($cliente);

        EstadoPedido::create([
            'pedido_id' => $pedido->id,
            'usuario_id' => $admin->id,
            'estado' => 'reembolsado',
            'comentario' => 'Reembolsado previamente.',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.pedidos.estado', $pedido->id), [
                'estado' => 'enviado',
                'comentario' => 'Intentando despachar un pedido ya reembolsado',
            ]);

        $response->assertSessionHas('toast_error');
        $this->assertEquals('reembolsado', $pedido->fresh()->ultimoEstado->estado);
    }

    #[Test]
    public function un_usuario_sin_permiso_de_reembolso_es_rechazado(): void
    {
        // Usuario empleado con rol o permisos pero SIN 'admin.pedidos.reembolsar'
        $empleado = Usuario::create([
            'nombre' => 'Operario',
            'apellido' => 'Bodega',
            'email' => 'operario.' . uniqid() . '@example.com',
            'password_hash' => bcrypt('secret123'),
            'telefono' => '60000001',
        ]);
        // Solo permiso de ver pedidos
        $empleado->givePermissionTo('admin.pedidos.ver');

        $cliente = $this->crearCliente();
        $pedido = $this->crearPedido($cliente);

        $response = $this->actingAs($empleado)
            ->post(route('admin.pedidos.reembolsar', $pedido->id), [
                'monto' => 112.00,
                'motivo' => 'Devolución no autorizada',
            ]);

        $response->assertForbidden();
    }

    #[Test]
    public function el_reembolso_exige_un_motivo_o_justificacion_minimo(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $pedido = $this->crearPedido($cliente);

        $response = $this->actingAs($admin)
            ->post(route('admin.pedidos.reembolsar', $pedido->id), [
                'monto' => 112.00,
                'motivo' => '', // Vacío
            ]);

        $response->assertSessionHasErrors('motivo');
        $this->assertNotEquals('reembolsado', $pedido->fresh()->ultimoEstado->estado);
    }

    #[Test]
    public function el_reembolso_autorizado_procesa_stripe_actualiza_monto_y_registra_auditoria(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $pedido = $this->crearPedido($cliente);

        $response = $this->actingAs($admin)
            ->post(route('admin.pedidos.reembolsar', $pedido->id), [
                'monto' => 112.00,
                'motivo' => 'Devolución acordada con el cliente por producto defectuoso',
            ]);

        $response->assertSessionHas('toast_success');
        
        $pedidoActualizado = $pedido->fresh();
        $this->assertEquals('reembolsado', $pedidoActualizado->ultimoEstado->estado);
        $this->assertEquals(112.00, (float) $pedidoActualizado->monto_reembolsado);

        // Verificar log de auditoría
        $log = LogAuditoria::where('modulo', 'pedidos')
            ->where('accion', 'reembolso_stripe')
            ->where('usuario_id', $admin->id)
            ->first();

        $this->assertNotNull($log, 'Debe existir un registro de auditoría del reembolso');
        $this->assertStringContainsString('Devolución acordada', $log->descripcion);
    }

    #[Test]
    public function pedido_con_metodo_distinto_a_stripe_no_puede_reembolsarse_por_este_proceso(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $pedido = $this->crearPedido($cliente, 'transferencia');

        $response = $this->actingAs($admin)
            ->post(route('admin.pedidos.reembolsar', $pedido->id), [
                'monto' => 112.00,
                'motivo' => 'Intento de reembolso automático a pedido manual',
            ]);

        $response->assertSessionHas('toast_error');
        $this->assertNotEquals('reembolsado', $pedido->fresh()->ultimoEstado->estado);
    }
}
