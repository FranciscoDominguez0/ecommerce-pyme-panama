<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Support\Facades\Auth;

class PedidoController extends Controller
{
    public function index()
    {
        $pedidos = Pedido::where('usuario_id', Auth::id())
            ->orderByDesc('creado_en')
            ->paginate(10);
            
        return view('cliente.pedidos.index', compact('pedidos'));
    }

    public function detalle($id)
    {
        $pedido = Pedido::with(['items.producto.imagenes', 'items.variante.opciones', 'estados', 'ultimoEstado', 'direccion', 'zonaEnvio'])
            ->where('usuario_id', Auth::id())
            ->findOrFail($id);
            
        return view('cliente.pedidos.detalle', compact('pedido'));
    }
}
