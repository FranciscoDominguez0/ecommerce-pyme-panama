<?php

namespace App\Services;

use App\Models\Factura;
use App\Models\Pedido;
use App\Models\ReenvioFactura;
use App\Mail\FacturaMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;

class FacturaService
{
    /**
     * Genera una factura a partir de un pedido aprobado.
     */
    public function generarFactura(Pedido $pedido): ?Factura
    {
        // Evitar generar la factura si ya existe
        if (Factura::where('pedido_id', $pedido->id)->exists()) {
            return Factura::where('pedido_id', $pedido->id)->first();
        }

        return DB::transaction(function () use ($pedido) {
            $numeroFactura = $this->generarNumeroFactura();

            $itbmsTasa = 7.00; // Podría venir de configuración

            $factura = Factura::create([
                'pedido_id' => $pedido->id,
                'usuario_id' => $pedido->usuario_id,
                'numero' => $numeroFactura,
                'metodo_pago' => $pedido->metodo_pago,
                'referencia_pago_externo' => $pedido->comprobante_pago_ruta,
                'subtotal' => $pedido->subtotal,
                'descuento' => $pedido->descuento,
                'costo_envio' => $pedido->costo_envio,
                'itbms_tasa' => $itbmsTasa,
                'itbms_monto' => $pedido->itbms_monto,
                'total' => $pedido->total,
                'estado' => 'emitida',
                'emitida_en' => now(),
            ]);

            // Cargar relaciones necesarias para el PDF
            $factura->load(['pedido.items.producto', 'usuario']);

            // Generar PDF
            $pdf = Pdf::loadView('admin.facturacion.factura-pdf', [
                'factura' => $factura,
            ]);

            $pdfRuta = 'facturas/' . $numeroFactura . '.pdf';
            Storage::disk('public')->put($pdfRuta, $pdf->output());

            $factura->update(['pdf_ruta' => $pdfRuta]);

            // Enviar correo automático
            Mail::to($factura->usuario->email)->send(new FacturaMail($factura));

            return $factura;
        });
    }

    /**
     * Reenvía una factura por correo electrónico.
     */
    public function reenviarFactura(Factura $factura, string $emailDestino, ?string $mensajePersonalizado = null): ReenvioFactura
    {
        $reenvio = ReenvioFactura::create([
            'factura_id' => $factura->id,
            'usuario_id' => auth()->id(),
            'email_destino' => $emailDestino,
            'mensaje_personalizado' => $mensajePersonalizado,
            'enviado_en' => now(),
        ]);

        Mail::to($emailDestino)->send(new FacturaMail($factura, $mensajePersonalizado));

        return $reenvio;
    }

    /**
     * Anula la factura asociada a un pedido.
     */
    public function anularFactura(Pedido $pedido): void
    {
        $factura = Factura::where('pedido_id', $pedido->id)->first();
        
        if ($factura && $factura->estado !== 'anulada') {
            $factura->update(['estado' => 'anulada']);
            
            // Regenerar el PDF para que muestre el estado "ANULADA"
            $factura->load(['pedido.items.producto', 'usuario']);
            $pdf = Pdf::loadView('admin.facturacion.factura-pdf', [
                'factura' => $factura,
            ]);
            
            if ($factura->pdf_ruta) {
                Storage::disk('public')->put($factura->pdf_ruta, $pdf->output());
            }
        }
    }

    /**
     * Genera un número correlativo atómico y secuencial para la factura (ej: F-2024-0001).
     */
    protected function generarNumeroFactura(): string
    {
        $anio = date('Y');
        $clave = 'factura_correlativo_' . $anio;

        DB::table('configuracion')->insertOrIgnore([
            'clave' => $clave,
            'valor' => '0',
            'grupo' => 'general',
            'descripcion' => 'Correlativo de facturas para el año ' . $anio,
            'actualizado_en' => now(),
        ]);

        $fila = DB::table('configuracion')
            ->where('clave', $clave)
            ->lockForUpdate()
            ->first();

        $correlativo = $fila ? ((int) $fila->valor) + 1 : 1;

        if ($fila) {
            DB::table('configuracion')
                ->where('clave', $clave)
                ->update(['valor' => (string) $correlativo, 'actualizado_en' => now()]);
        } else {
            DB::table('configuracion')->insert([
                'clave' => $clave,
                'valor' => (string) $correlativo,
                'grupo' => 'general',
                'descripcion' => 'Correlativo de facturas para el año ' . $anio,
                'actualizado_en' => now(),
            ]);
        }

        // Formato: F-2024-0001
        return 'F-' . $anio . '-' . str_pad((string)$correlativo, 4, '0', STR_PAD_LEFT);
    }
}
