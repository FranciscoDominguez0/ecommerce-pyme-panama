<?php

namespace Tests\Feature;

use App\Mail\FacturaMail;
use App\Models\Factura;
use App\Models\Pedido;
use App\Services\FacturaService;
use App\Services\PedidoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Admin\BaseAdminTest;

/**
 * Pruebas del módulo de FACTURACIÓN (FASE 15) — FacturaService.
 *
 * Cubre: generación automática al confirmar el pago, numeración correlativa y
 * única (que nunca se reutiliza al anular), cálculo de ITBMS, totales copiados
 * del pedido, generación del PDF, reenvío por email y anti-duplicación.
 *
 * NOTAS DE IMPLEMENTACIÓN (para que las pruebas sean deterministas):
 *  1. `generarFactura()` difiere la generación del PDF y el envío del correo con
 *     `defer()`. En pruebas se ejecutan de inmediato con `$this->withoutDefer()`.
 *  2. El facade `Pdf` se mockea (`shouldReceive('loadView')`) para no renderizar
 *     DomPDF real; el stub devuelve contenido falso de PDF.
 *  3. El disco `public` se emula con `Storage::fake('public')` y el correo con
 *     `Mail::fake()`.
 *  4. El número de factura (F-YYYY-XXXX) sale del correlativo atómico de
 *     `configuracion` (clave `factura_correlativo_{año}`) y se reinicia en cada
 *     prueba gracias a RefreshDatabase.
 */
class FacturaServiceTest extends BaseAdminTest
{
    // =====================================================================
    //  GENERACIÓN AUTOMÁTICA — al confirmar el pago del pedido
    // =====================================================================

    public function test_genera_factura_automaticamente_al_confirmar_pago(): void
    {
        $this->prepararGeneracionDeFactura();

        $pedido = Pedido::factory()->create();
        $usuarioId = $pedido->usuario_id;

        app(PedidoService::class)->cambiarEstado($pedido, 'pago_confirmado', null, 'Pago confirmado.');

        // Se creó exactamente 1 factura para el pedido, en estado 'emitida'.
        $this->assertDatabaseHas('facturas', [
            'pedido_id' => $pedido->id,
            'usuario_id' => $usuarioId,
            'estado' => 'emitida',
            'numero' => 'F-' . date('Y') . '-0001',
        ]);
        $this->assertSame(1, Factura::where('pedido_id', $pedido->id)->count(), 'Debe existir una sola factura por pedido.');
        $this->assertSame('pago_confirmado', $pedido->ultimoEstado->estado);
    }

    // =====================================================================
    //  NUMERACIÓN — correlativo único y secuencial
    // =====================================================================

    public function test_numero_de_factura_es_correlativo_y_unico(): void
    {
        $this->prepararGeneracionDeFactura();

        $servicio = app(FacturaService::class);
        $numeros = [];

        for ($i = 1; $i <= 3; $i++) {
            $pedido = Pedido::factory()->create();
            $numeros[] = $servicio->generarFactura($pedido)->numero;
        }

        $this->assertSame(
            ['F-' . date('Y') . '-0001', 'F-' . date('Y') . '-0002', 'F-' . date('Y') . '-0003'],
            $numeros,
            'Las facturas consecutivas deben llevar números correlativos.'
        );
        $this->assertCount(3, array_unique($numeros), 'Los números de factura no deben repetirse.');
        $this->assertDatabaseHas('configuracion', [
            'clave' => 'factura_correlativo_' . date('Y'),
            'valor' => '3',
        ]);
    }

    public function test_numero_de_factura_nunca_se_reutiliza_al_anular(): void
    {
        $this->prepararGeneracionDeFactura();

        $servicio = app(FacturaService::class);
        $pedidoUno = Pedido::factory()->create();
        $facturaUno = $servicio->generarFactura($pedidoUno);

        $servicio->anularFactura($pedidoUno);

        $this->assertSame('anulada', $facturaUno->fresh()->estado, 'La factura debe quedar en estado anulada.');

        // Un segundo pedido NO puede reutilizar el número de la factura anulada.
        $pedidoDos = Pedido::factory()->create();
        $facturaDos = $servicio->generarFactura($pedidoDos);

        $this->assertNotSame(
            $facturaUno->numero,
            $facturaDos->numero,
            'El número de una factura anulada no debe reutilizarse.'
        );
        $this->assertSame('F-' . date('Y') . '-0002', $facturaDos->numero);
        $this->assertDatabaseHas('configuracion', [
            'clave' => 'factura_correlativo_' . date('Y'),
            'valor' => '2',
        ]);
    }

    // =====================================================================
    //  CÁLCULOS — ITBMS y totales copiados del pedido
    // =====================================================================

    public function test_calculo_correcto_de_itbms(): void
    {
        $this->prepararGeneracionDeFactura();

        $pedido = Pedido::factory()->create([
            'subtotal' => 250.00,
            'itbms_monto' => 17.50,
        ]);

        $factura = app(FacturaService::class)->generarFactura($pedido);

        // 17.50 = 250.00 * 7% (tasa por defecto).
        $this->assertSame('7.00', $factura->itbms_tasa, 'La tasa de ITBMS debe ser 7% por defecto.');
        $this->assertSame('17.50', $factura->itbms_monto, 'itbms_monto debe coincidir con subtotal * itbms_tasa.');
        $this->assertEqualsWithDelta(17.50, (float) $factura->subtotal * (float) $factura->itbms_tasa / 100, 0.01);
    }

    public function test_totales_de_factura_coinciden_con_el_pedido(): void
    {
        $this->prepararGeneracionDeFactura();

        $pedido = Pedido::factory()->create([
            'subtotal' => 250.00,
            'descuento' => 20.00,
            'costo_envio' => 10.00,
            'itbms_monto' => 16.10,
            'total' => 256.10,
        ]);

        $factura = app(FacturaService::class)->generarFactura($pedido);

        $this->assertSame('250.00', $factura->subtotal, 'El subtotal de la factura debe copiarse del pedido.');
        $this->assertSame('20.00', $factura->descuento, 'El descuento de la factura debe copiarse del pedido.');
        $this->assertSame('10.00', $factura->costo_envio, 'El costo de envío de la factura debe copiarse del pedido.');
        $this->assertSame('16.10', $factura->itbms_monto, 'El ITBMS de la factura debe copiarse del pedido.');
        $this->assertSame('256.10', $factura->total, 'El total de la factura debe copiarse del pedido.');
        $this->assertSame($pedido->metodo_pago, $factura->metodo_pago);
        $this->assertSame($pedido->usuario_id, $factura->usuario_id);
    }

    // =====================================================================
    //  PDF — generación y ruta guardada
    // =====================================================================

    public function test_genera_pdf_y_guarda_ruta(): void
    {
        $this->prepararGeneracionDeFactura();
        Storage::fake('public');

        $pedido = Pedido::factory()->create();
        $factura = app(FacturaService::class)->generarFactura($pedido);

        $pdfRuta = 'facturas/' . $factura->numero . '.pdf';

        $this->assertSame($pdfRuta, $factura->fresh()->pdf_ruta, 'pdf_ruta debe guardarse en storage/app/public/facturas/.');
        Storage::disk('public')->assertExists($pdfRuta);
        $this->assertSame('%PDF-1.4 TEST', Storage::disk('public')->get($pdfRuta));

        // El correo automático se envía al usuario dueño del pedido.
        Mail::assertSent(FacturaMail::class, fn (FacturaMail $mail) => $mail->hasTo($pedido->usuario->email));
    }

    // =====================================================================
    //  REENVÍO — registro por email
    // =====================================================================

    public function test_registra_reenvio_de_factura_por_email(): void
    {
        $admin = $this->crearAdmin();
        $this->actingAs($admin);
        Mail::fake();

        $factura = Factura::factory()->create();

        $reenvio = app(FacturaService::class)->reenviarFactura(
            $factura,
            'destinatario@example.com',
            'Adjuntamos su factura actualizada.'
        );

        $this->assertDatabaseHas('reenvios_factura', [
            'factura_id' => $factura->id,
            'usuario_id' => $admin->id,
            'email_destino' => 'destinatario@example.com',
            'mensaje_personalizado' => 'Adjuntamos su factura actualizada.',
        ]);
        $this->assertNotNull($reenvio->enviado_en, 'enviado_en debe quedar poblado con la fecha del reenvío.');
        $this->assertInstanceOf(Factura::class, $reenvio->factura);

        Mail::assertSent(FacturaMail::class, fn (FacturaMail $mail) => $mail->hasTo('destinatario@example.com'));
    }

    // =====================================================================
    //  ANTI-DUPLICACIÓN — un pedido no puede tener dos facturas
    // =====================================================================

    public function test_no_se_genera_factura_duplicada_para_el_mismo_pedido(): void
    {
        $this->prepararGeneracionDeFactura();

        $pedido = Pedido::factory()->create();
        $servicio = app(PedidoService::class);

        // Confirmar el pago dos veces (p. ej. doble clic del admin).
        $servicio->cambiarEstado($pedido, 'pago_confirmado');
        $servicio->cambiarEstado($pedido, 'pago_confirmado');

        $this->assertSame(1, Factura::count(), 'No debe crearse una segunda factura para el mismo pedido.');
        $this->assertSame(
            1,
            Factura::where('pedido_id', $pedido->id)->count(),
            'El pedido debe conservar su única factura original.'
        );
        $this->assertDatabaseCount('estados_pedido', 2);
    }

    // =====================================================================
    //  HELPERS
    // =====================================================================

    /**
     * Prepara el entorno para ejecutar la generación de facturas de forma
     * determinista: ejecuta los `defer()` de inmediato, mockea el PDF y el correo.
     */
    protected function prepararGeneracionDeFactura(): void
    {
        $this->withoutDefer();
        Mail::fake();

        // El mock DEBE ser una instancia real de Barryvdh\DomPDF\PDF porque
        // loadView() tiene tipo de retorno `self`.
        $pdfStub = \Mockery::mock(\Barryvdh\DomPDF\PDF::class);
        $pdfStub->shouldReceive('output')->andReturn('%PDF-1.4 TEST');

        Pdf::shouldReceive('loadView')->andReturn($pdfStub);
    }
}
