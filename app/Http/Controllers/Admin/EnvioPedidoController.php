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


}
