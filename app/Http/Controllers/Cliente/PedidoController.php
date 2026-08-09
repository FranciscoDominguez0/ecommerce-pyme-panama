<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $query = Pedido::where('usuario_id', Auth::id())
            ->with(['items', 'ultimoEstado'])
            ->orderByDesc('creado_en');

        if ($request->estado === 'pendiente') {
            $query->whereHas('ultimoEstado', fn ($q) => $q->whereIn('estado', [
                'pendiente', 'pago_confirmado', 'en_preparacion', 'listo_para_envio', 'enviado',
            ]));
        } elseif ($request->estado === 'entregado') {
            $query->whereHas('ultimoEstado', fn ($q) => $q->where('estado', 'entregado'));
        }

        $pedidos = $query->paginate(9)->withQueryString();

        return view('cliente.pedidos.index', compact('pedidos'));
    }

    public function detalle($id)
    {
        $pedido = Pedido::with(['items.producto.imagenes', 'items.variante.opciones.tipo', 'estados', 'ultimoEstado', 'direccion', 'zonaEnvio'])
            ->where('usuario_id', Auth::id())
            ->findOrFail($id);

        $estadosOrdenados = $pedido->estados->sortBy('creado_en')->values();
        $totalArticulos = $pedido->items->sum('cantidad');

        return view('cliente.pedidos.detalle', compact('pedido', 'estadosOrdenados', 'totalArticulos'));
    }
}
