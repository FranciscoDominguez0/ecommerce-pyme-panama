<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Factura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FacturaController extends Controller
{
    public function index()
    {
        $facturas = Factura::with('pedido')
            ->where('usuario_id', Auth::id())
            ->orderBy('creado_en', 'desc')
            ->paginate(10);

        return view('cliente.facturas', compact('facturas'));
    }

    public function descargarPdf(Factura $factura, Request $request, \App\Services\FacturaService $facturaService)
    {
        if ($factura->usuario_id !== Auth::id()) {
            abort(403, 'No tienes permiso para ver esta factura.');
        }

        if ($request->has('regenerar') || !$factura->pdf_ruta || !Storage::disk('local')->exists($factura->pdf_ruta)) {
            $facturaService->generarPdf($factura);
            $factura->refresh();
        }

        if ($factura->pdf_ruta && Storage::disk('local')->exists($factura->pdf_ruta)) {
            $path = Storage::disk('local')->path($factura->pdf_ruta);

            if ($request->get('modo') === 'inline' || $request->has('inline') || $request->has('imprimir')) {
                return response()->file($path, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="Factura_' . $factura->numero . '.pdf"',
                ]);
            }

            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = Storage::disk('local');
            return $disk->download($factura->pdf_ruta, 'Factura_' . $factura->numero . '.pdf');
        }

        abort(404, 'PDF no encontrado');
    }
}
