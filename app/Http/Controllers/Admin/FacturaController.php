<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Factura;
use App\Services\FacturaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FacturaController extends Controller
{
    protected FacturaService $facturaService;

    public function __construct(FacturaService $facturaService)
    {
        $this->facturaService = $facturaService;
    }

    public function index(Request $request)
    {
        $query = Factura::with(['pedido', 'usuario']);

        if ($request->filled('numero')) {
            $query->where('numero', 'like', '%' . $request->numero . '%');
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('emitida_desde')) {
            $query->whereDate('emitida_en', '>=', $request->emitida_desde);
        }

        if ($request->filled('emitida_hasta')) {
            $query->whereDate('emitida_en', '<=', $request->emitida_hasta);
        }

        if ($request->filled('cliente')) {
            $query->whereHas('usuario', function($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->cliente . '%')
                  ->orWhere('apellido', 'like', '%' . $request->cliente . '%')
                  ->orWhere('email', 'like', '%' . $request->cliente . '%');
            });
        }

        $facturas = $query->orderBy('creado_en', 'desc')->paginate(15)->withQueryString();

        return view('admin.facturacion.index', compact('facturas'));
    }

    public function show(Factura $factura)
    {
        $factura->load(['pedido.items.producto', 'usuario', 'reenvios.usuario', 'pedido.direccion']);
        return view('admin.facturacion.show', compact('factura'));
    }

    public function descargarPdf(Factura $factura, Request $request)
    {
        if ($request->has('regenerar')) {
            $this->facturaService->generarPdf($factura);
            $factura->refresh();
        }


        if ($factura->pdf_ruta && Storage::disk('local')->exists($factura->pdf_ruta)) {
            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = Storage::disk('local');
            return $disk->download($factura->pdf_ruta, 'Factura_' . $factura->numero . '.pdf');
        }

        abort(404, 'PDF no encontrado');
    }

    public function verPdf(Factura $factura)
    {
        if (!$factura->pdf_ruta || !Storage::disk('local')->exists($factura->pdf_ruta)) {
            app(\App\Services\FacturaService::class)->generarPdf($factura);
            $factura->refresh();
        }

        if ($factura->pdf_ruta && Storage::disk('local')->exists($factura->pdf_ruta)) {
            $path = Storage::disk('local')->path($factura->pdf_ruta);
            return response()->file($path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="Factura_' . $factura->numero . '.pdf"'
            ]);
        }

        abort(404, 'PDF no encontrado');
    }

    /**
     * Transmite el PDF del Ticket configurado en formato 80mm para impresora térmica.
     */
    /**
     * Transmite el PDF del Ticket configurado en formato 80mm para impresora térmica.
     */
    public function ticket(Factura $factura)
    {
        $factura->load(['pedido.items.producto', 'pedido.items.variante', 'pedido.vendedor', 'usuario', 'pedido.direccion']);
        $datosEmpresa = $this->prepararDatosEmpresaTicket();

        // Ancho estándar para impresora térmica de 80mm (226.77 pt)
        $anchoPt = 80 * 2.83465;

        // Cálculo de altura según contenido para bobina continua
        $cantItems = $factura->pedido ? $factura->pedido->items->count() : 1;
        $altoMm = 145 + ($cantItems * 14);
        if (!empty($datosEmpresa['logo_src'])) {
            $altoMm += 20;
        }
        if (!empty($factura->pedido?->notas_cliente) || !empty($factura->pedido?->notas_internas)) {
            $altoMm += 20;
        }
        $altoMm = max($altoMm, 160);
        $altoPt = $altoMm * 2.83465;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.facturacion.ticket-pdf', compact('factura', 'datosEmpresa'))
            ->setPaper([0, 0, $anchoPt, $altoPt], 'portrait');

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Ticket_' . $factura->numero . '.pdf"',
        ]);
    }

    /**
     * Descarga el PDF del Ticket en formato 80mm para impresora térmica.
     */
    public function ticketPdf(Factura $factura)
    {
        $factura->load(['pedido.items.producto', 'pedido.items.variante', 'pedido.vendedor', 'usuario', 'pedido.direccion']);
        $datosEmpresa = $this->prepararDatosEmpresaTicket();

        // Ancho estándar para impresora térmica de 80mm (226.77 pt)
        $anchoPt = 80 * 2.83465;

        $cantItems = $factura->pedido ? $factura->pedido->items->count() : 1;
        $altoMm = 145 + ($cantItems * 14);
        if (!empty($datosEmpresa['logo_src'])) {
            $altoMm += 20;
        }
        if (!empty($factura->pedido?->notas_cliente) || !empty($factura->pedido?->notas_internas)) {
            $altoMm += 20;
        }
        $altoMm = max($altoMm, 160);
        $altoPt = $altoMm * 2.83465;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.facturacion.ticket-pdf', compact('factura', 'datosEmpresa'))
            ->setPaper([0, 0, $anchoPt, $altoPt], 'portrait');

        return $pdf->download('Ticket_' . $factura->numero . '.pdf');
    }

    /**
     * Prepara los datos de la empresa garantizando que el logo sea monocromático para tickets térmicos.
     */
    protected function prepararDatosEmpresaTicket(): array
    {
        $datos = $this->facturaService->prepararDatosEmpresaParaPdf();

        $rutaLogoConf = \App\Models\Configuracion::obtener('empresa.logo_ruta');
        $logoPath = $rutaLogoConf ? storage_path('app/public/' . $rutaLogoConf) : public_path('images/logo.png');

        if (file_exists($logoPath) && function_exists('imagecreatefromstring')) {
            $content = file_get_contents($logoPath);
            $im = @imagecreatefromstring($content);
            if ($im) {
                imagefilter($im, IMG_FILTER_GRAYSCALE);
                ob_start();
                imagepng($im);
                $datos['logo_src'] = 'data:image/png;base64,' . base64_encode(ob_get_clean());
                imagedestroy($im);
            }
        }

        return $datos;
    }

    public function reenviar(Request $request, Factura $factura)
    {
        $request->validate([
            'email_destino' => 'required|email',
            'mensaje' => 'nullable|string',
        ]);

        $this->facturaService->reenviarFactura($factura, $request->email_destino, $request->mensaje);

        return back()->with('success', 'Factura reenviada exitosamente.');
    }
}
