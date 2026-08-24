<?php

namespace Tests\Feature\Admin;

use App\Models\Factura;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\ReenvioFactura;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Admin\BaseAdminTest;

/**
 * Pruebas del módulo ADMIN de FACTURACIÓN (FASE 15).
 *
 * Rutas cubiertas:
 *   GET  /admin/facturas                    → index (listado filtrable)
 *   GET  /admin/facturas/{factura}          → show (detalle con items del pedido)
 *   GET  /admin/facturas/{factura}/pdf      → descargarPdf
 *   POST /admin/facturas/{factura}/reenviar → reenviar (email)
 *
 * Middleware: auth + role:admin|super_admin|Admin (403 para clientes).
 */
class FacturaControllerAdminTest extends BaseAdminTest
{
    // =====================================================================
    //  AUTORIZACIÓN — solo administradores pueden acceder
    // =====================================================================

    public function test_el_acceso_a_la_gestion_de_facturas_requiere_iniciar_sesion(): void
    {
        $this->get('/admin/facturas')->assertRedirect('/login');
        $this->get('/admin/facturas/1')->assertRedirect('/login');
    }

    public function test_usuario_no_admin_no_puede_acceder_a_facturacion_admin(): void
    {
        $cliente = $this->crearCliente();
        $factura = Factura::factory()->create();

        $this->actingAs($cliente)
            ->get(route('admin.facturas.index'))
            ->assertForbidden();

        $this->actingAs($cliente)
            ->get(route('admin.facturas.show', $factura))
            ->assertForbidden();
    }

    // =====================================================================
    //  LISTADO — index y filtros
    // =====================================================================

    public function test_admin_puede_ver_listado_de_facturas(): void
    {
        $admin = $this->crearAdmin();
        $factura = Factura::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.facturas.index'))
            ->assertOk()
            ->assertSee($factura->numero)
            ->assertSee($factura->usuario->nombre)
            ->assertSee('$' . number_format($factura->total, 2), false);
    }

    public function test_listado_se_puede_filtrar_por_estado(): void
    {
        $admin = $this->crearAdmin();
        $emitida = Factura::factory()->create(['numero' => 'F-' . date('Y') . '-1001']);
        $anulada = Factura::factory()->anulada()->create(['numero' => 'F-' . date('Y') . '-1002']);

        $this->actingAs($admin)
            ->get(route('admin.facturas.index', ['estado' => 'emitida']))
            ->assertOk()
            ->assertSee($emitida->numero)
            ->assertDontSee($anulada->numero);

        $this->actingAs($admin)
            ->get(route('admin.facturas.index', ['estado' => 'anulada']))
            ->assertOk()
            ->assertSee($anulada->numero)
            ->assertDontSee($emitida->numero);
    }

    public function test_listado_se_puede_filtrar_por_rango_de_fechas(): void
    {
        $admin = $this->crearAdmin();
        $hoy = now()->startOfDay();
        $dentroDelRango = Factura::factory()->create([
            'numero' => 'F-' . date('Y') . '-2001',
            'emitida_en' => $hoy->copy()->subDay(),
        ]);
        $fueraDelRango = Factura::factory()->create([
            'numero' => 'F-' . date('Y') . '-2002',
            'emitida_en' => $hoy->copy()->subDays(10),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.facturas.index', [
                'emitida_desde' => $hoy->copy()->subDays(2)->toDateString(),
                'emitida_hasta' => $hoy->copy()->addDay()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee($dentroDelRango->numero)
            ->assertDontSee($fueraDelRango->numero);
    }

    // =====================================================================
    //  DETALLE — show
    // =====================================================================

    public function test_admin_puede_ver_detalle_de_una_factura(): void
    {
        $admin = $this->crearAdmin();
        $cliente = $this->crearCliente();
        $pedido = Pedido::factory()->create(['usuario_id' => $cliente->id]);
        $item = ItemPedido::factory()->create(['pedido_id' => $pedido->id]);
        $factura = Factura::factory()->create([
            'pedido_id' => $pedido->id,
            'usuario_id' => $cliente->id,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.facturas.show', $factura))
            ->assertOk()
            ->assertSee($factura->numero)
            ->assertSee($pedido->numero_pedido)
            ->assertSee($item->producto->nombre)
            ->assertSee('$' . number_format($factura->total, 2), false);
    }

    // =====================================================================
    //  PDF — descarga
    // =====================================================================

    public function test_admin_puede_descargar_pdf_de_factura(): void
    {
        Storage::fake('public');

        $admin = $this->crearAdmin();
        $factura = Factura::factory()->create([
            'pdf_ruta' => 'facturas/ejemplo.pdf',
        ]);
        Storage::disk('local')->put('facturas/ejemplo.pdf', '%PDF-1.4 TEST');

        $this->actingAs($admin)
            ->get(route('admin.facturas.pdf', $factura))
            ->assertOk()
            ->assertDownload('Factura_' . $factura->numero . '.pdf');
    }

    public function test_descargar_pdf_de_factura_sin_pdf_devuelve_404(): void
    {
        $admin = $this->crearAdmin();
        $factura = Factura::factory()->create(['pdf_ruta' => null]);

        $this->actingAs($admin)
            ->get(route('admin.facturas.pdf', $factura))
            ->assertNotFound();
    }

    // =====================================================================
    //  REENVÍO — email
    // =====================================================================

    public function test_admin_puede_reenviar_factura_por_email(): void
    {
        Mail::fake();

        $admin = $this->crearAdmin();
        $factura = Factura::factory()->create();

        $this->actingAs($admin)
            ->from(route('admin.facturas.index'))
            ->post(route('admin.facturas.reenviar', $factura), [
                'email_destino' => 'destinatario@example.com',
                'mensaje' => 'Adjuntamos su factura.',
            ])
            ->assertRedirect(route('admin.facturas.index'))
            ->assertSessionHas('success', 'Factura reenviada exitosamente.');

        $this->assertDatabaseHas('reenvios_factura', [
            'factura_id' => $factura->id,
            'usuario_id' => $admin->id,
            'email_destino' => 'destinatario@example.com',
            'mensaje_personalizado' => 'Adjuntamos su factura.',
        ]);
        $this->assertNotNull(ReenvioFactura::where('factura_id', $factura->id)->first()->enviado_en);

        Mail::assertSent(\App\Mail\FacturaMail::class, fn ($mail) => $mail->hasTo('destinatario@example.com'));
    }

    public function test_no_se_puede_reenviar_factura_con_email_invalido(): void
    {
        $admin = $this->crearAdmin();
        $factura = Factura::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.facturas.reenviar', $factura), [
                'email_destino' => 'correo-invalido',
            ])
            ->assertSessionHasErrors('email_destino');

        $this->assertDatabaseCount('reenvios_factura', 0);
    }
}
