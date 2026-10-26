<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\AuditoriaService;
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
        $pedidos = $this->pedidoService->obtenerPedidosPaginadosAdmin(
            $request->input('estado') ?? 'todos',
            $request->input('q') ?? ''
        );

        return view('admin.pedidos.index', compact('pedidos'));
    }

    public function detalle($id)
    {
        $pedido = Pedido::with([
            'usuario', 'vendedor', 'items.producto', 'items.variante', 'estados.usuario', 'direccion', 'zonaEnvio'
        ])->findOrFail($id);

        // Al abrir el pedido por primera vez un administrador, se marca como leído para quitar el badge de Nuevo
        if (is_null($pedido->leido_en)) {
            $pedido->update(['leido_en' => now()]);
        }

        if (Auth::user()) {
            Auth::user()->unreadNotifications()
                ->where(function ($q) use ($pedido) {
                    $q->where('data', 'like', '%"pedido_id":' . $pedido->id . '%')
                      ->orWhere('data', 'like', '%"pedido_id": ' . $pedido->id . '%');
                })
                ->update(['read_at' => now()]);
        }

        // Preparamos los datos de entrega y contacto extraídos de notas internas
        $notasInternas = json_decode($pedido->notas_internas, true) ?? [];
        $metodoEntrega = $notasInternas['metodo_entrega'] ?? 'desconocido';
        $contacto = $notasInternas['contacto'] ?? null;
        $courierSucursalId = $notasInternas['courier_sucursal_id'] ?? null;
        $sucursal = $courierSucursalId ? \App\Models\CourierSucursal::find($courierSucursalId) : null;

        return view('admin.pedidos.detalle', compact('pedido', 'metodoEntrega', 'contacto', 'sucursal'));
    }

    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|string',
            'comentario' => 'nullable|string',
        ]);

        $pedido = Pedido::with(['envio', 'ultimoEstado'])->findOrFail($id);

        try {
            $this->pedidoService->cambiarEstado($pedido, $request->estado, Auth::id(), $request->comentario);
            return back()->with('toast_success', 'Estado del pedido actualizado correctamente.');
        } catch (\Exception $e) {
            return back()->with('toast_error', $e->getMessage());
        }
    }

    public function avanzarEstado(Request $request, $id)
    {
        $request->validate([
            'accion' => 'required|string|in:iniciar_preparacion,marcar_listo,marcar_transito,marcar_entregado,marcar_enviado'
        ]);

        $pedido = Pedido::with(['envio', 'ultimoEstado'])->findOrFail($id);
        
        $nuevoEstado = match ($request->accion) {
            'iniciar_preparacion' => 'en_preparacion',
            'marcar_listo'        => 'listo_para_envio',
            'marcar_enviado'      => 'enviado',
            'marcar_transito'     => 'en_transito',
            'marcar_entregado'    => 'entregado',
        };

        try {
            $this->pedidoService->cambiarEstado($pedido, $nuevoEstado, Auth::id());
            return back()->with('toast_success', 'Estado avanzado correctamente a: ' . str_replace('_', ' ', strtoupper($nuevoEstado)));
        } catch (\Exception $e) {
            return back()->with('toast_error', $e->getMessage());
        }
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

    public function reembolsar(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || (!$user->hasAnyRole(['Admin', 'super_admin']) && !$user->can('admin.pedidos.reembolsar'))) {
            abort(403, 'No tienes autorización para procesar reembolsos financieros.');
        }

        $pedido = Pedido::with('ultimoEstado')->findOrFail($id);

        if ($pedido->metodo_pago !== 'stripe') {
            return back()->with('toast_error', 'El reembolso automatizado solo está disponible para pedidos procesados mediante Stripe.');
        }

        if ($pedido->ultimoEstado?->estado === 'reembolsado') {
            return back()->with('toast_error', 'Este pedido ya se encuentra reembolsado.');
        }

        $request->validate([
            'monto' => 'nullable|numeric|min:0.01|max:' . $pedido->total,
            'motivo' => 'required|string|min:5|max:255',
        ], [
            'motivo.required' => 'Es obligatorio ingresar un motivo o justificación para procesar el reembolso.',
            'motivo.min' => 'El motivo debe tener al menos 5 caracteres descriptivos para el registro de auditoría.',
        ]);

        $pagoService = app(\App\Services\PagoService::class);
        $montoSolicitado = $request->filled('monto') ? (float) $request->monto : (float) $pedido->total;
        
        $resultado = $pagoService->reembolsar(
            $pedido, 
            $montoSolicitado, 
            $request->motivo
        );

        if (!$resultado['exito']) {
            return back()->with('toast_error', $resultado['mensaje']);
        }

        $montoReembolsado = $resultado['monto'] ?: $montoSolicitado;
        $pedido->update(['monto_reembolsado' => $montoReembolsado]);

        $nombreMetodo = ucfirst(str_replace('_', ' ', $pedido->metodo_pago));
        $comentario = 'Reembolso de $' . number_format($montoReembolsado, 2) . ' procesado con ' . $nombreMetodo . '.';
        if ($request->filled('motivo')) {
            $comentario .= ' Motivo: ' . $request->motivo;
        }

        $this->pedidoService->cambiarEstado($pedido, 'reembolsado', Auth::id(), $comentario);

        // Registro de auditoría para control de fraude y supervisión administrativa
        AuditoriaService::registrar(
            'pedidos',
            'reembolso_stripe',
            "Reembolso emitido por \${$montoReembolsado} en pedido #{$pedido->numero_pedido} por {$user->nombre} {$user->apellido}. Motivo: {$request->motivo}",
            null,
            [
                'pedido_id' => $pedido->id,
                'numero_pedido' => $pedido->numero_pedido,
                'monto_reembolsado' => $montoReembolsado,
                'motivo' => $request->motivo,
                'refund_id' => $resultado['refund_id'] ?? null,
                'autorizado_por_id' => $user->id,
                'autorizado_por_email' => $user->email,
            ]
        );

        return back()->with('toast_success', $resultado['mensaje']);
    }
}
