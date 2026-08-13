<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EnvioPedido;
use App\Models\Pedido;
use App\Services\PedidoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnvioPedidoController extends Controller
{
    protected PedidoService $pedidoService;

    public function __construct(PedidoService $pedidoService)
    {
        $this->pedidoService = $pedidoService;
    }

    public function edit($pedidoId)
    {
        $pedido = Pedido::with(['usuario', 'items.producto', 'items.variante', 'direccion', 'envio'])->findOrFail($pedidoId);
        
        return view('admin.pedidos.envio', compact('pedido'));
    }

    public function update(Request $request, $pedidoId)
    {
        $request->validate([
            'metodo_envio' => 'required|string|max:100',
            'empresa_mensajeria' => 'nullable|string|max:155',
            'numero_guia' => 'nullable|string|max:255',
            'fecha_estimada_entrega' => 'nullable|date',
        ]);

        $pedido = Pedido::with('envio')->findOrFail($pedidoId);
        
        $esNuevoEnvio = !$pedido->envio;

        $empresaFinal = $request->metodo_envio;
        if (!empty($request->empresa_mensajeria)) {
            $empresaFinal .= ' - ' . $request->empresa_mensajeria;
        }

        $envio = EnvioPedido::updateOrCreate(
            ['pedido_id' => $pedido->id],
            [
                'empresa_mensajeria' => $empresaFinal,
                'numero_guia' => $request->numero_guia,
                'fecha_estimada_entrega' => $request->fecha_estimada_entrega,
            ]
        );

        if ($esNuevoEnvio) {
            $this->pedidoService->cambiarEstado(
                $pedido,
                'enviado',
                Auth::id(),
                'Pedido preparado para envío: ' . $empresaFinal
            );
        }

        return redirect()->route('admin.pedidos.detalle', $pedido->id)
            ->with('toast_success', 'Información de envío actualizada correctamente.');
    }

    public function updateStatus(Request $request, $pedidoId)
    {
        $request->validate([
            'nuevo_estado' => 'required|string|in:enviado,en_transito,entregado,problema_entrega',
            'comentario' => 'nullable|string|max:255',
        ]);

        $pedido = Pedido::with('envio')->findOrFail($pedidoId);

        $nuevoEstado = $request->nuevo_estado;
        
        if ($request->comentario) {
            $comentario = $request->comentario;
        } else {
            $empresa = $pedido->envio->empresa_mensajeria ?? 'nuestra logística';
            $guia = $pedido->envio->numero_guia ? " (Referencia: {$pedido->envio->numero_guia})" : "";
            
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

        $this->pedidoService->cambiarEstado(
            $pedido,
            $nuevoEstado,
            Auth::id(),
            $comentario
        );

        if ($nuevoEstado === 'entregado' && $pedido->envio) {
            $pedido->envio->update([
                'fecha_entrega_real' => now()
            ]);
        }

        return back()->with('toast_success', 'Estado del envío actualizado a ' . strtoupper(str_replace('_', ' ', $nuevoEstado)));
    }
}
