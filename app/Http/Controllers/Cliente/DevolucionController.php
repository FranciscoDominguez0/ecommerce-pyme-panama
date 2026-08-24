<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Devolucion;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Usuario;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NuevaDevolucionNotification;

class DevolucionController extends Controller
{
    public function create($pedidoId)
    {
        $pedido = Pedido::with(['items.producto', 'items.variante'])
            ->where('usuario_id', Auth::id())
            ->findOrFail($pedidoId);
            
        // Validar que no haya ya una devolución pendiente o aprobada para este pedido
        $devolucionExistente = Devolucion::where('pedido_id', $pedido->id)->first();
        if ($devolucionExistente) {
            return redirect()->route('cliente.perfil.pedidos.detalle', $pedido->id)
                ->with('toast_error', 'Este pedido ya tiene una solicitud de devolución registrada.');
        }

        // Se requiere que el pedido esté entregado para solicitar devolución?
        // Depende de la lógica de negocio, asumiremos que no está restringido estrictamente aquí, 
        // pero típicamente un pedido debe estar 'entregado' para ser devuelto.

        return view('cliente.pedidos.devolucion', compact('pedido'));
    }

    public function store(Request $request, $pedidoId)
    {
        $pedido = Pedido::where('usuario_id', Auth::id())->findOrFail($pedidoId);

        $request->validate([
            'motivo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'foto_evidencia' => 'nullable|image|max:10240', // Hasta 10MB
        ]);

        $devolucionExistente = Devolucion::where('pedido_id', $pedido->id)->first();
        if ($devolucionExistente) {
            return redirect()->route('cliente.perfil.pedidos.detalle', $pedido->id)
                ->with('toast_error', 'Este pedido ya tiene una solicitud de devolución registrada.');
        }

        $fotoRuta = null;
        if ($request->hasFile('foto_evidencia')) {
            $fotoRuta = $request->file('foto_evidencia')->store('devoluciones', 'public');
        }

        $devolucion = Devolucion::create([
            'pedido_id' => $pedido->id,
            'usuario_id' => Auth::id(),
            'motivo' => $request->motivo,
            'descripcion' => $request->descripcion,
            'foto_evidencia_ruta' => $fotoRuta,
            'estado' => 'pendiente',
        ]);

        // Enviar notificación a los administradores
        $admins = Usuario::role('super_admin')->get();
        Notification::send($admins, new NuevaDevolucionNotification($devolucion));

        return redirect()->route('cliente.perfil.pedidos.detalle', $pedido->id)
            ->with('toast_success', 'Solicitud de devolución enviada correctamente.');
    }
}
