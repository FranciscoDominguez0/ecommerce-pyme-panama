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

    public function descargarPdf(Factura $factura)
    {
        if ($factura->usuario_id !== Auth::id()) {
            abort(403, 'No tienes permiso para ver esta factura.');
        }

        if ($factura->pdf_ruta && Storage::disk('public')->exists($factura->pdf_ruta)) {
            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = Storage::disk('public');
            return $disk->download($factura->pdf_ruta, 'Factura_' . $factura->numero . '.pdf');
        }

        abort(404, 'PDF no encontrado');
    }
}
