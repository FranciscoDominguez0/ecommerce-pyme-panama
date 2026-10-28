<?php

namespace App\Jobs;

use App\Models\Factura;
use App\Services\FacturaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerarFacturaPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public bool $deleteWhenMissingModels = true;

    /**
     * Número máximo de reintentos.
     */
    public int $tries = 3;

    /**
     * Tiempo de espera antes de reintentar (segundos).
     */
    public int $backoff = 5;

    public function __construct(
        public Factura $factura
    ) {}

    /**
     * Ejecuta la compilación del archivo PDF de la factura en segundo plano.
     */
    public function handle(FacturaService $facturaService): void
    {
        try {
            $pdfRuta = $facturaService->generarPdf($this->factura);
            Log::info("GenerarFacturaPdfJob: Factura {$this->factura->numero} generada exitosamente en {$pdfRuta}");
        } catch (Throwable $e) {
            Log::error("GenerarFacturaPdfJob: Error al compilar PDF para factura {$this->factura->numero}: " . $e->getMessage());
            throw $e;
        }
    }
}
