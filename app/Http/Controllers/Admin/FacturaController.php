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

        $facturas = $query->orderBy('creado_en', 'desc')->paginate(15);

        return view('admin.facturacion.index', compact('facturas'));
    }

    public function show(Factura $factura)
    {
        $factura->load(['pedido.items.producto', 'usuario', 'reenvios.usuario', 'pedido.direccion']);
        return view('admin.facturacion.show', compact('factura'));
    }

    public function descargarPdf(Factura $factura)
    {
        if ($factura->pdf_ruta && Storage::disk('local')->exists($factura->pdf_ruta)) {
            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = Storage::disk('local');
            return $disk->download($factura->pdf_ruta, 'Factura_' . $factura->numero . '.pdf');
        }

        abort(404, 'PDF no encontrado');
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
