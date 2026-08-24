<?php

namespace Tests\Feature\Cliente;

use App\Models\Factura;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Admin\BaseAdminTest;

/**
 * Pruebas del módulo CLIENTE de FACTURACIÓN (FASE 15).
 *
 * Rutas cubiertas:
 *   GET /mis-facturas              → cliente.facturas.index (solo facturas propias)
 *   GET /mis-facturas/{factura}/pdf → cliente.facturas.pdf (descarga con autorización)
 *
 * Comportamiento real implementado (Cliente\FacturaController):
 *   - El listado filtra por usuario autenticado (Auth::id()).
 *   - La descarga del PDF valida la propiedad: 403 para facturas ajenas.
 */
class Cliente_FacturaControllerTest extends BaseAdminTest
{
    // =====================================================================
    //  AUTORIZACIÓN — acceso al módulo
    // =====================================================================

    public function test_el_acceso_a_mis_facturas_requiere_iniciar_sesion(): void
    {
        $this->get(route('cliente.facturas.index'))->assertRedirect('/login');
    }

    // =====================================================================
    //  LISTADO — solo facturas del usuario autenticado
    // =====================================================================

    public function test_cliente_puede_ver_solo_sus_propias_facturas(): void
    {
        $cliente = $this->crearCliente();
        $otroCliente = $this->crearCliente();

        $facturaPropia = Factura::factory()->create([
            'usuario_id' => $cliente->id,
            'numero' => 'F-' . date('Y') . '-3001',
        ]);
        $facturaAjena = Factura::factory()->create([
            'usuario_id' => $otroCliente->id,
            'numero' => 'F-' . date('Y') . '-3002',
        ]);

        $this->actingAs($cliente)
            ->get(route('cliente.facturas.index'))
            ->assertOk()
            ->assertSee($facturaPropia->numero)
            ->assertDontSee($facturaAjena->numero);
    }

    // =====================================================================
    //  AISLAMIENTO — acceso a facturas ajenas
    // =====================================================================

    public function test_cliente_no_puede_ver_facturas_de_otro_usuario(): void
    {
        $cliente = $this->crearCliente();
        $otroCliente = $this->crearCliente();
        $facturaAjena = Factura::factory()->create([
            'usuario_id' => $otroCliente->id,
        ]);

        $this->actingAs($cliente)
            ->get(route('cliente.facturas.pdf', $facturaAjena))
            ->assertForbidden();
    }

    // =====================================================================
    //  PDF — descarga de la propia factura
    // =====================================================================

    public function test_cliente_puede_descargar_pdf_de_su_factura(): void
    {
        Storage::fake('public');

        $cliente = $this->crearCliente();
        $factura = Factura::factory()->create([
            'usuario_id' => $cliente->id,
            'pdf_ruta' => 'facturas/cliente-ejemplo.pdf',
        ]);
        Storage::disk('local')->put('facturas/cliente-ejemplo.pdf', '%PDF-1.4 TEST');

        $this->actingAs($cliente)
            ->get(route('cliente.facturas.pdf', $factura))
            ->assertOk()
            ->assertDownload('Factura_' . $factura->numero . '.pdf');
    }

    // =====================================================================
    //  ESTADO VACÍO — sin facturas
    // =====================================================================

    public function test_cliente_ve_mensaje_de_estado_vacio_sin_facturas(): void
    {
        $cliente = $this->crearCliente();

        $this->actingAs($cliente)
            ->get(route('cliente.facturas.index'))
            ->assertOk()
            ->assertSee('Aún no tienes facturas');
    }
}
