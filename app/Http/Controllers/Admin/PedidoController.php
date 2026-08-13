<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Services\PedidoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PedidoController extends Controller
{
    protected PedidoService $pedidoService;

    public function __construct(PedidoService $pedidoService)
    {
        $this->pedidoService = $pedidoService;
    }

    public function index(Request $request)
    {
        $query = Pedido::with(['usuario', 'ultimoEstado']);

        if ($request->has('estado') && $request->estado !== 'todos') {
            $estado = $request->estado;
            $query->whereHas('estados', function ($q) use ($estado) {
                $q->where('estado', $estado)
                  ->whereIn('id', function ($sub) {
                      $sub->selectRaw('MAX(id)')
                          ->from('estados_pedido')
                          ->groupBy('pedido_id');
                  });
            });
        }

        $pedidos = $query->orderByDesc('creado_en')->paginate(15);

        return view('admin.pedidos.index', compact('pedidos'));
    }

    public function detalle($id)
    {
        $pedido = Pedido::with([
            'usuario', 'items.producto', 'items.variante', 'estados.usuario', 'direccion', 'zonaEnvio'
        ])->findOrFail($id);

        return view('admin.pedidos.detalle', compact('pedido'));
    }

    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|string',
            'comentario' => 'nullable|string',
        ]);

        $pedido = Pedido::with('envio')->findOrFail($id);
        
        $nuevoEstado = $request->estado;
        
        if ($request->comentario) {
            $comentario = $request->comentario;
        } else {
            // Auto-generar comentarios para estados de envío si no se provee uno
            $empresa = $pedido->envio->empresa_mensajeria ?? 'nuestra logística';
            $guia = ($pedido->envio && $pedido->envio->numero_guia) ? " (Referencia: {$pedido->envio->numero_guia})" : "";
            
            switch ($nuevoEstado) {
                case 'enviado':
                    $comentario = "El pedido ha sido despachado a través de {$empresa}{$guia}.";
                    break;
                case 'en_transito':
                    $comentario = "El pedido se encuentra en ruta hacia su destino mediante {$empresa}.";
                    break;
                case 'entregado':
                    $comentario = "El pedido ha sido entregado exitosamente al destinatario.";
                    break;
                case 'problema_entrega':
                    $comentario = "Se ha reportado un inconveniente durante el proceso de entrega. Estamos revisando el caso.";
                    break;
                default:
                    $comentario = 'Estado actualizado a ' . str_replace('_', ' ', $nuevoEstado);
            }
        }

        $this->pedidoService->cambiarEstado($pedido, $nuevoEstado, Auth::id(), $comentario);

        if ($nuevoEstado === 'entregado' && $pedido->envio) {
            $pedido->envio->update([
                'fecha_entrega_real' => now()
            ]);
        }

        return back()->with('toast_success', 'Estado del pedido actualizado correctamente.');
    }

    public function aprobarPago(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);
        $this->pedidoService->cambiarEstado($pedido, 'pago_confirmado', Auth::id(), 'Pago aprobado por el administrador.');
        
        return back()->with('toast_success', 'Pago aprobado.');
    }

    public function rechazarPago(Request $request, $id)
    {
        $request->validate([
            'comentario' => 'required|string',
        ]);

        $pedido = Pedido::findOrFail($id);
        $this->pedidoService->cambiarEstado($pedido, 'pago_rechazado', Auth::id(), 'Pago rechazado: ' . $request->comentario);
        
        return back()->with('toast_success', 'Pago rechazado.');
    }
}
