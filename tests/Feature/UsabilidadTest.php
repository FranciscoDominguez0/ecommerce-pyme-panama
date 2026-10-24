<?php

namespace Tests\Feature;

use App\Models\EvaluacionUsabilidad;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\Producto;
use App\Services\UsabilidadService;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Admin\BaseAdminTest;

class UsabilidadTest extends BaseAdminTest
{
    #[Test]
    public function un_cliente_puede_evaluar_la_usabilidad_de_su_pedido(): void
    {
        $cliente = $this->crearCliente();
        $pedido = $this->crearPedidoParaCliente($cliente);

        $response = $this->actingAs($cliente)->postJson(route('cliente.perfil.pedidos.usabilidad', $pedido->id), [
            'puntuacion_facilidad' => 5,
            'comentario' => 'La compra fue súper rápida y la interfaz es genial.',
            'tiempo_checkout_segundos' => 38,
            'dispositivo' => 'Desktop',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'evaluacion' => [
                    'puntuacion_facilidad' => 5,
                    'etiqueta' => 'Muy fácil',
                ],
            ]);

        $this->assertDatabaseHas('evaluaciones_usabilidad', [
            'pedido_id' => $pedido->id,
            'usuario_id' => $cliente->id,
            'puntuacion_facilidad' => 5,
            'comentario' => 'La compra fue súper rápida y la interfaz es genial.',
        ]);
    }

    #[Test]
    public function la_puntuacion_de_usabilidad_debe_ser_valida(): void
    {
        $cliente = $this->crearCliente();
        $pedido = $this->crearPedidoParaCliente($cliente);

        // Menor a 1
        $this->actingAs($cliente)
            ->postJson(route('cliente.perfil.pedidos.usabilidad', $pedido->id), [
                'puntuacion_facilidad' => 0,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('puntuacion_facilidad');

        // Mayor a 5
        $this->actingAs($cliente)
            ->postJson(route('cliente.perfil.pedidos.usabilidad', $pedido->id), [
                'puntuacion_facilidad' => 6,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('puntuacion_facilidad');
    }

    #[Test]
    public function un_cliente_no_puede_evaluar_un_pedido_que_no_le_pertenece(): void
    {
        $cliente = $this->crearCliente();
        $otroCliente = $this->crearCliente();
        $pedidoAjeno = $this->crearPedidoParaCliente($otroCliente);

        $this->actingAs($cliente)
            ->postJson(route('cliente.perfil.pedidos.usabilidad', $pedidoAjeno->id), [
                'puntuacion_facilidad' => 5,
            ])
            ->assertNotFound();
    }

    #[Test]
    public function no_se_duplican_evaluaciones_para_el_mismo_pedido(): void
    {
        $cliente = $this->crearCliente();
        $pedido = $this->crearPedidoParaCliente($cliente);

        // Primera evaluación
        $this->actingAs($cliente)->postJson(route('cliente.perfil.pedidos.usabilidad', $pedido->id), [
            'puntuacion_facilidad' => 4,
            'comentario' => 'Primera calificación',
        ])->assertOk();

        // Segunda evaluación sobre el mismo pedido
        $this->actingAs($cliente)->postJson(route('cliente.perfil.pedidos.usabilidad', $pedido->id), [
            'puntuacion_facilidad' => 5,
            'comentario' => 'Segunda calificación',
        ])->assertOk();

        // Debe mantenerse la original y existir exactamente 1 registro
        $this->assertSame(1, EvaluacionUsabilidad::where('pedido_id', $pedido->id)->count());
        $this->assertDatabaseHas('evaluaciones_usabilidad', [
            'pedido_id' => $pedido->id,
            'puntuacion_facilidad' => 4,
        ]);
    }

    #[Test]
    public function el_servicio_de_usabilidad_calcula_ces_csat_y_tsr_correctamente(): void
    {
        $cliente = $this->crearCliente();
        $pedido1 = $this->crearPedidoParaCliente($cliente);
        $pedido2 = $this->crearPedidoParaCliente($cliente);

        EvaluacionUsabilidad::create([
            'pedido_id' => $pedido1->id,
            'usuario_id' => $cliente->id,
            'puntuacion_facilidad' => 5,
            'comentario' => 'Increíble servicio',
            'tiempo_checkout_segundos' => 30,
        ]);

        EvaluacionUsabilidad::create([
            'pedido_id' => $pedido2->id,
            'usuario_id' => $cliente->id,
            'puntuacion_facilidad' => 4,
            'comentario' => 'Todo bien',
            'tiempo_checkout_segundos' => 40,
        ]);

        $servicio = app(UsabilidadService::class);
        $metricas = $servicio->obtenerMetricas();

        $this->assertSame(2, $metricas['total_evaluaciones']);
        $this->assertEquals(100.0, $metricas['porcentaje_ces']); // Ambas fueron >= 4
        $this->assertEquals(4.5, $metricas['promedio_csat']); // (5 + 4) / 2
        $this->assertEquals(35, $metricas['tiempo_promedio_checkout']); // (30 + 40) / 2
        $this->assertCount(2, $metricas['comentarios_recientes']);
    }

    #[Test]
    public function un_administrador_puede_acceder_al_reporte_de_usabilidad(): void
    {
        $admin = $this->crearSuperAdmin();

        $this->actingAs($admin)
            ->get(route('admin.reportes.usabilidad'))
            ->assertOk()
            ->assertSee('Experiencia y Satisfacción del Cliente')
            ->assertSee('Facilidad de Compra')
            ->assertSee('Satisfacción General')
            ->assertSee('Finalización de Pedidos');
    }

    protected function crearPedidoParaCliente($cliente): Pedido
    {
        $producto = Producto::factory()->create(['precio' => 100.00, 'stock' => 10]);

        $pedido = Pedido::factory()->create([
            'usuario_id' => $cliente->id,
            'numero_pedido' => '#PM-' . uniqid(),
            'total' => 107.00,
        ]);

        ItemPedido::factory()->create([
            'pedido_id' => $pedido->id,
            'producto_id' => $producto->id,
            'cantidad' => 1,
            'precio_unitario' => 100.00,
            'subtotal' => 100.00,
        ]);

        return $pedido;
    }
}
