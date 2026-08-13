<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Devolucion;
use App\Services\InventarioService;
use App\Services\PedidoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DevolucionController extends Controller
{
    public function index(Request $request)
    {
        $query = Devolucion::with(['pedido.items', 'usuario']);

        if ($request->filled('estado') && $request->estado !== 'todas') {
            $query->where('estado', $request->estado);
        }
        
        if ($request->filled('buscar')) {
            $buscar = $request->buscar;
            $query->whereHas('pedido', function($q) use ($buscar) {
                $q->where('numero_pedido', 'like', "%{$buscar}%");
            })->orWhereHas('usuario', function($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%")
                  ->orWhere('apellido', 'like', "%{$buscar}%")
                  ->orWhere('email', 'like', "%{$buscar}%");
            });
        }

        $devoluciones = $query->orderByDesc('creado_en')->paginate(15);

        return view('admin.devoluciones.index', compact('devoluciones'));
    }

    public function aprobar(Request $request, $id, InventarioService $inventarioService, PedidoService $pedidoService)
    {
        $request->validate([
            'comentario_admin' => 'nullable|string',
        ]);

        $devolucion = Devolucion::findOrFail($id);

        if ($devolucion->estado !== 'pendiente') {
            return back()->with('toast_error', 'Solo se pueden aprobar devoluciones en estado pendiente.');
        }

        DB::transaction(function () use ($devolucion, $request, $inventarioService, $pedidoService) {
            $devolucion->update([
                'estado' => 'aprobada',
                'comentario_admin' => $request->comentario_admin,
                'aprobado_en' => now(),
            ]);

            // Reintegrar inventario
            $inventarioService->registrarEntradaPorDevolucion($devolucion, Auth::id());
            
            // Opcional: Cambiar estado del pedido a algo como 'devolucion_aprobada' o similar si existe.
            // Para mantener compatibilidad con estados conocidos, agregamos nota en el historial.
            $pedidoService->cambiarEstado(
                $devolucion->pedido,
                'devolucion_aprobada', // Si tu servicio no lo soporta, caerá en exception, asumo que has validado los estados.
                Auth::id(),
                'Devolución #' . $devolucion->id . ' aprobada. ' . ($request->comentario_admin ?? '')
            );
        });

        return back()->with('toast_success', 'Devolución aprobada y stock reintegrado.');
    }

    public function rechazar(Request $request, $id, PedidoService $pedidoService)
    {
        $request->validate([
            'comentario_admin' => 'required|string',
        ]);

        $devolucion = Devolucion::findOrFail($id);

        if ($devolucion->estado !== 'pendiente') {
            return back()->with('toast_error', 'Solo se pueden rechazar devoluciones en estado pendiente.');
        }

        DB::transaction(function () use ($devolucion, $request, $pedidoService) {
            $devolucion->update([
                'estado' => 'rechazada',
                'comentario_admin' => $request->comentario_admin,
            ]);
            
            $pedidoService->cambiarEstado(
                $devolucion->pedido,
                'devolucion_rechazada', 
                Auth::id(),
                'Devolución #' . $devolucion->id . ' rechazada. Motivo: ' . $request->comentario_admin
            );
        });

        return back()->with('toast_success', 'Devolución rechazada correctamente.');
    }
}
