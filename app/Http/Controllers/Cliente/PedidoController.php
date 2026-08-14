<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Services\PedidoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PedidoController extends Controller
{
    protected $pedidoService;

    public function __construct(PedidoService $pedidoService)
    {
        $this->pedidoService = $pedidoService;
    }
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
        $pedido = Pedido::with(['items.producto.imagenes', 'items.variante.opciones.tipo', 'estados', 'ultimoEstado', 'direccion', 'zonaEnvio', 'factura'])
            ->where('usuario_id', Auth::id())
            ->findOrFail($id);

        $estadosOrdenados = $pedido->estados->sortBy('creado_en')->values();
        $totalArticulos = $pedido->items->sum('cantidad');

        return view('cliente.pedidos.detalle', compact('pedido', 'estadosOrdenados', 'totalArticulos'));
    }

    public function confirmarRecepcion($id)
    {
        $pedido = Pedido::with('ultimoEstado')
            ->where('usuario_id', Auth::id())
            ->findOrFail($id);

        $ultimoEstado = $pedido->ultimoEstado?->estado;

        if (in_array($ultimoEstado, ['enviado', 'en_transito'])) {
            $this->pedidoService->cambiarEstado(
                $pedido, 
                'entregado', 
                Auth::id(),
                'Entrega confirmada por el cliente desde su panel.'
            );
            
            return back()->with('toast_success', '¡Gracias por confirmar la recepción! Disfruta tu compra.');
        }

        return back()->with('toast_error', 'Este pedido no se encuentra en ruta.');
    }
}
