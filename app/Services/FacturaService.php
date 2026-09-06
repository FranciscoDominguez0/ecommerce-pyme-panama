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

            // Generar PDF y almacenarlo con imágenes incrustadas en Base64
            $pdfRuta = $this->generarPdf($factura);

            // Enviar correo automático en segundo plano usando defer() de Laravel 11+
            // Esto asegura que la transacción de BD se haya cerrado y el usuario reciba
            // respuesta inmediata, evitando que el tiempo de conexión SMTP bloquee el sistema.
            defer(function () use ($factura) {
                try {
                    Mail::to($factura->usuario->email)->send(new FacturaMail($factura));
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Error al enviar factura por correo (defer): ' . $e->getMessage());
                }
            });

            return $factura;
        });
    }

    /**
     * Genera o regenera el archivo PDF de la factura garantizando
     * que todas las imágenes (locales o remotas por URL) se incrusten en Base64.
     */
    public function generarPdf(Factura $factura): string
    {
        $factura->load(['pedido.items.producto.imagenes', 'pedido.items.variante', 'usuario', 'pedido.direccion']);
        $itemsFactura = $this->prepararItemsParaPdf($factura);

        $pdf = Pdf::loadView('admin.facturacion.factura-pdf', [
            'factura' => $factura,
            'itemsFactura' => $itemsFactura,
        ]);

        $pdfRuta = 'facturas/' . $factura->numero . '.pdf';
        Storage::disk('local')->put($pdfRuta, $pdf->output());

        $factura->update(['pdf_ruta' => $pdfRuta]);

        return $pdfRuta;
    }

    /**
     * Prepara los items del pedido resolviendo sus imágenes a Base64 para el PDF.
     */
    public function prepararItemsParaPdf(Factura $factura): array
    {
        $items = [];
        $itemsPedido = $factura->pedido ? $factura->pedido->items : collect();

        foreach ($itemsPedido as $item) {
            $imgRuta = null;
            if ($item->variante && !empty($item->variante->imagen_ruta)) {
                $imgRuta = $item->variante->imagen_ruta;
            } elseif ($item->producto) {
                $imgPrinc = $item->producto->imagenPrincipal();
                $imgRuta = $imgPrinc ? $imgPrinc->ruta : null;
            }

            $items[] = [
                'cantidad' => $item->cantidad,
                'nombre' => $item->producto->nombre ?? 'Producto Eliminado',
                'sku' => $item->variante ? $item->variante->sku : ($item->producto ? $item->producto->sku : 'N/A'),
                'precio_unitario' => (float) $item->precio_unitario,
                'subtotal' => (float) $item->subtotal,
                'imagen_base64' => $this->resolverImagenBase64($imgRuta),
            ];
        }

        return $items;
    }

    /**
     * Convierte una ruta o enlace de imagen (incluyendo URLs remotas https://...)
     * en Data URI Base64 compatible con DomPDF.
     */
    public function resolverImagenBase64(?string $ruta): string
    {
        $placeholderPath = public_path('images/placeholder-product.png');
        $fallback = '';
        if (file_exists($placeholderPath)) {
            $fallback = 'data:image/png;base64,' . base64_encode(file_get_contents($placeholderPath));
        }

        if (empty($ruta)) {
            return $fallback;
        }

        // 1. Data URI existente
        if (str_starts_with($ruta, 'data:image')) {
            return $ruta;
        }

        // 2. URL externa (http:// o https://)
        if (str_starts_with($ruta, 'http://') || str_starts_with($ruta, 'https://')) {
            try {
                $ctx = stream_context_create([
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ],
                    'http' => [
                        'timeout' => 5,
                        'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\nAccept: image/*\r\n"
                    ]
                ]);

                $contenido = @file_get_contents($ruta, false, $ctx);
                if ($contenido) {
                    $pathUrl = parse_url($ruta, PHP_URL_PATH);
                    $ext = strtolower(pathinfo($pathUrl, PATHINFO_EXTENSION));

                    $mime = match ($ext) {
                        'png' => 'image/png',
                        'gif' => 'image/gif',
                        'webp' => 'image/webp',
                        'svg' => 'image/svg+xml',
                        default => 'image/jpeg',
                    };

                    // Si es WebP, convertir a JPEG para compatibilidad nativa con DomPDF si GD está disponible
                    if ($ext === 'webp' && function_exists('imagecreatefromstring')) {
                        $gdImg = @imagecreatefromstring($contenido);
                        if ($gdImg) {
                            ob_start();
                            imagejpeg($gdImg, null, 90);
                            $contenido = ob_get_clean();
                            imagedestroy($gdImg);
                            $mime = 'image/jpeg';
                        }
                    }

                    return 'data:' . $mime . ';base64,' . base64_encode($contenido);
                }
            } catch (\Throwable $e) {
                // Silencioso, usará fallback
            }

            return $fallback;
        }

        // 3. Almacenamiento local (storage/ o public)
        $cleanRoute = preg_replace('/^\/?(storage\/)?/', '', $ruta);
        $localPath = storage_path('app/public/' . $cleanRoute);
        if (file_exists($localPath)) {
            $ext = strtolower(pathinfo($localPath, PATHINFO_EXTENSION));
            $mime = match ($ext) {
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'svg' => 'image/svg+xml',
                default => 'image/jpeg',
            };
            return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($localPath));
        }

        $publicPath = public_path(ltrim($ruta, '/'));
        if (file_exists($publicPath)) {
            $ext = strtolower(pathinfo($publicPath, PATHINFO_EXTENSION));
            $mime = match ($ext) {
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                default => 'image/jpeg',
            };
            return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($publicPath));
        }

        return $fallback;
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
            $this->generarPdf($factura);
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
