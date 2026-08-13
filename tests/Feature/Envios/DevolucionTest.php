<?php

namespace Tests\Feature\Envios;

use App\Models\Devolucion;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Admin\BaseAdminTest;

/**
 * Pruebas de SOLICITUDES DE DEVOLUCIÓN del cliente (FASE 13).
 *
 * Rutas cubiertas:
 *   GET  /mi-cuenta/mis-pedidos/{id}/devolucion → create (formulario)
 *   POST /mi-cuenta/mis-pedidos/{id}/devolucion → store (guardar solicitud)
 *
 * Comportamiento real implementado (Cliente\DevolucionController):
 *   - Solo el dueño del pedido puede solicitar (404 para pedidos ajenos).
 *   - Validación: motivo y descripcion requeridos; foto_evidencia opcional (imagen ≤ 10MB).
 *   - Una sola devolución por pedido (la segunda solicitud se rechaza con toast_error).
 *   - La solicitud se crea en estado 'pendiente' con pedido_id y usuario_id.
 */
class DevolucionTest extends BaseAdminTest
{
    // =====================================================================
    //  FORMULARIO
    // =====================================================================

    #[Test]
    public function el_formulario_de_devolucion_muestra_motivo_descripcion_y_foto(): void
    {
        $cliente = $this->crearCliente();
        $pedido = $this->crearPedidoDelCliente($cliente);

        $this->actingAs($cliente)
            ->get(route('cliente.perfil.pedidos.devolucion.create', $pedido->id))
            ->assertOk()
            ->assertSee('name="motivo"', false)
            ->assertSee('name="descripcion"', false)
            ->assertSee('name="foto_evidencia"', false)
            ->assertSee('Motivo de devolución');
    }

    #[Test]
    public function el_formulario_de_devolucion_de_un_pedido_ajeno_devuelve_404(): void
    {
        $cliente = $this->crearCliente();
        $otroCliente = $this->crearCliente();
        $pedidoAjeno = $this->crearPedidoDelCliente($otroCliente);

        $this->actingAs($cliente)
            ->get(route('cliente.perfil.pedidos.devolucion.create', $pedidoAjeno->id))
            ->assertNotFound();
    }

    // =====================================================================
    //  SOLICITUD DE DEVOLUCIÓN
    // =====================================================================

    #[Test]
    public function un_cliente_puede_solicitar_una_devolucion_de_su_pedido(): void
    {
        $cliente = $this->crearCliente();
        $pedido = $this->crearPedidoDelCliente($cliente);

        $this->actingAs($cliente)
            ->post(route('cliente.perfil.pedidos.devolucion.store', $pedido->id), [
                'motivo'      => 'defectuoso',
                'descripcion' => 'El producto llegó dañado y no funciona.',
            ])
            ->assertRedirect(route('cliente.perfil.pedidos.detalle', $pedido->id))
            ->assertSessionHas('toast_success');

        $this->assertDatabaseHas('devoluciones', [
            'pedido_id'   => $pedido->id,
            'usuario_id'  => $cliente->id,
            'motivo'      => 'defectuoso',
            'descripcion' => 'El producto llegó dañado y no funciona.',
            'estado'      => 'pendiente',
        ]);
    }

    #[Test]
    public function la_devolucion_queda_asociada_al_pedido_y_al_usuario_correctos(): void
    {
        $cliente = $this->crearCliente();
        $pedido = $this->crearPedidoDelCliente($cliente);

        $this->actingAs($cliente)
            ->post(route('cliente.perfil.pedidos.devolucion.store', $pedido->id), [
                'motivo'      => 'incorrecto',
                'descripcion' => 'Recibí otro modelo.',
            ]);

        $devolucion = Devolucion::where('pedido_id', $pedido->id)->first();
        $this->assertNotNull($devolucion);
        $this->assertSame($pedido->id, $devolucion->pedido_id);
        $this->assertSame($cliente->id, $devolucion->usuario_id);
        $this->assertSame($pedido->id, $devolucion->pedido->id);
        $this->assertSame($cliente->id, $devolucion->usuario->id);
    }

    #[Test]
    public function la_solicitud_de_devolucion_requiere_motivo(): void
    {
        $cliente = $this->crearCliente();
        $pedido = $this->crearPedidoDelCliente($cliente);

        $this->actingAs($cliente)
            ->post(route('cliente.perfil.pedidos.devolucion.store', $pedido->id), [
                'descripcion' => 'Sin motivo definido.',
            ])
            ->assertSessionHasErrors('motivo');

        $this->assertSame(0, Devolucion::count());
    }

    #[Test]
    public function la_solicitud_de_devolucion_requiere_descripcion(): void
    {
        $cliente = $this->crearCliente();
        $pedido = $this->crearPedidoDelCliente($cliente);

        $this->actingAs($cliente)
            ->post(route('cliente.perfil.pedidos.devolucion.store', $pedido->id), [
                'motivo' => 'defectuoso',
            ])
            ->assertSessionHasErrors('descripcion');

        $this->assertSame(0, Devolucion::count());
    }

    #[Test]
    public function una_solicitud_con_datos_vacios_es_rechazada(): void
    {
        $cliente = $this->crearCliente();
        $pedido = $this->crearPedidoDelCliente($cliente);

        $this->actingAs($cliente)
            ->post(route('cliente.perfil.pedidos.devolucion.store', $pedido->id), [])
            ->assertSessionHasErrors(['motivo', 'descripcion']);

        $this->assertSame(0, Devolucion::count());
    }

    #[Test]
    public function un_cliente_no_puede_solicitar_una_devolucion_para_un_pedido_ajeno(): void
    {
        $cliente = $this->crearCliente();
        $otroCliente = $this->crearCliente();
        $pedidoAjeno = $this->crearPedidoDelCliente($otroCliente);

        $this->actingAs($cliente)
            ->post(route('cliente.perfil.pedidos.devolucion.store', $pedidoAjeno->id), [
                'motivo'      => 'defectuoso',
                'descripcion' => 'Intento de devolución de un pedido ajeno.',
            ])
            ->assertNotFound();

        $this->assertSame(0, Devolucion::count());
    }

    #[Test]
    public function no_se_pueden_registrar_dos_devoluciones_para_el_mismo_pedido(): void
    {
        $cliente = $this->crearCliente();
        $pedido = $this->crearPedidoDelCliente($cliente);

        $this->actingAs($cliente)
            ->post(route('cliente.perfil.pedidos.devolucion.store', $pedido->id), [
                'motivo'      => 'defectuoso',
                'descripcion' => 'Primera solicitud.',
            ]);

        $this->actingAs($cliente)
            ->post(route('cliente.perfil.pedidos.devolucion.store', $pedido->id), [
                'motivo'      => 'incorrecto',
                'descripcion' => 'Segunda solicitud.',
            ])
            ->assertRedirect(route('cliente.perfil.pedidos.detalle', $pedido->id))
            ->assertSessionHas('toast_error');

        $this->assertSame(1, Devolucion::where('pedido_id', $pedido->id)->count());
    }

    // =====================================================================
    //  EVIDENCIA FOTOGRÁFICA
    // =====================================================================

    #[Test]
    public function el_cliente_puede_adjuntar_una_foto_de_evidencia(): void
    {
        Storage::fake('public');

        $cliente = $this->crearCliente();
        $pedido = $this->crearPedidoDelCliente($cliente);

        $this->actingAs($cliente)
            ->post(route('cliente.perfil.pedidos.devolucion.store', $pedido->id), [
                'motivo'         => 'defectuoso',
                'descripcion'    => 'Se adjunta foto de la falla.',
                'foto_evidencia' => UploadedFile::fake()->image('evidencia.jpg'),
            ])
            ->assertRedirect(route('cliente.perfil.pedidos.detalle', $pedido->id))
            ->assertSessionHas('toast_success');

        $devolucion = Devolucion::where('pedido_id', $pedido->id)->first();
        $this->assertNotNull($devolucion->foto_evidencia_ruta);
        Storage::disk('public')->assertExists($devolucion->foto_evidencia_ruta);
    }

    #[Test]
    public function un_archivo_que_no_es_imagen_es_rechazado_como_evidencia(): void
    {
        Storage::fake('public');

        $cliente = $this->crearCliente();
        $pedido = $this->crearPedidoDelCliente($cliente);

        $this->actingAs($cliente)
            ->post(route('cliente.perfil.pedidos.devolucion.store', $pedido->id), [
                'motivo'         => 'defectuoso',
                'descripcion'    => 'Adjunto un documento de texto.',
                'foto_evidencia' => UploadedFile::fake()->create('documento.txt', 100),
            ])
            ->assertSessionHasErrors('foto_evidencia');

        $this->assertSame(0, Devolucion::count());
    }

    // =====================================================================
    //  AUTORIZACIÓN
    // =====================================================================

    #[Test]
    public function un_usuario_no_autenticado_no_puede_solicitar_una_devolucion(): void
    {
        $cliente = $this->crearCliente();
        $pedido = $this->crearPedidoDelCliente($cliente);

        $this->post(route('cliente.perfil.pedidos.devolucion.store', $pedido->id), [
                'motivo'      => 'defectuoso',
                'descripcion' => 'Sin sesión iniciada.',
            ])
            ->assertRedirect('/login');

        $this->assertSame(0, Devolucion::count());
    }

    // =====================================================================
    //  HELPERS
    // =====================================================================

    protected function crearPedidoDelCliente($cliente): Pedido
    {
        $producto = Producto::factory()->create(['precio' => 50.00, 'stock' => 10]);

        $pedido = Pedido::factory()->create([
            'usuario_id' => $cliente->id,
            'numero_pedido' => '#PM-' . uniqid(),
        ]);

        ItemPedido::factory()->create([
            'pedido_id'       => $pedido->id,
            'producto_id'     => $producto->id,
            'cantidad'        => 1,
            'precio_unitario' => 50.00,
            'subtotal'        => 50.00,
        ]);

        return $pedido;
    }
}
