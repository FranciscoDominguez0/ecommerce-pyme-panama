<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
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

        if ($request->filled('q')) {
            $busqueda = $request->q;
            $query->where(function ($q) use ($busqueda) {
                $q->where('numero_pedido', 'ilike', "%{$busqueda}%")
                  ->orWhereHas('usuario', function ($uq) use ($busqueda) {
                      $uq->where('nombre', 'ilike', "%{$busqueda}%")
                         ->orWhere('apellido', 'ilike', "%{$busqueda}%");
                  });
            });
        }

        $pedidos = $query->orderByDesc('creado_en')->paginate(15)->withQueryString();

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

        $pedido = Pedido::with(['envio', 'ultimoEstado'])->findOrFail($id);
        
        // Un pedido reembolsado tiene su ciclo financiero y logístico cerrado permanentemente
        if ($pedido->ultimoEstado?->estado === 'reembolsado') {
            return back()->with('toast_error', 'Este pedido ya se encuentra reembolsado y su ciclo está cerrado. No se permiten más cambios de estado.');
        }

        $nuevoEstado = $request->estado;
        
        // Bloquear reembolsos directos desde el selector genérico de logística
        if ($nuevoEstado === 'reembolsado') {
            return back()->with('toast_error', 'Los reembolsos financieros no pueden procesarse desde el selector de estado. Deben ejecutarse formalmente mediante el botón de Reembolso con Stripe con su debida justificación.');
        }
        
        // Validar que exista información de envío antes de avanzar a estados exclusivos de logística
        if (in_array($nuevoEstado, ['en_transito', 'problema_entrega']) && !$pedido->envio) {
            return back()->with('toast_error', 'Debe configurar la Gestión de Envío (Método de Envío) antes de pasar a este estado.');
        }

        if ($request->comentario) {
            $comentario = $request->comentario;
        } else {
            // Auto-generar comentarios para estados de envío si no se provee uno
            $empresa = $pedido->envio?->empresa_mensajeria ?? 'nuestra logística';
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

    public function avanzarEstado(Request $request, $id)
    {
        $request->validate([
            'accion' => 'required|string|in:iniciar_preparacion,marcar_listo,marcar_transito,marcar_entregado'
        ]);

        $pedido = Pedido::with(['envio', 'ultimoEstado'])->findOrFail($id);
        
        if ($pedido->ultimoEstado?->estado === 'reembolsado') {
            return back()->with('toast_error', 'Este pedido ya se encuentra reembolsado y su ciclo está cerrado.');
        }
        
        $nuevoEstado = '';
        $comentario = '';

        switch ($request->accion) {
            case 'iniciar_preparacion':
                $nuevoEstado = 'en_preparacion';
                $comentario = 'El pedido ha comenzado a prepararse en bodega.';
                break;
            case 'marcar_listo':
                $nuevoEstado = 'listo_para_envio';
                $comentario = 'El pedido está empacado y listo para ser enviado.';
                break;
            case 'marcar_transito':
                $nuevoEstado = 'en_transito';
                $empresa = $pedido->envio->empresa_mensajeria ?? 'nuestra logística';
                $comentario = "El pedido se encuentra en ruta hacia su destino mediante {$empresa}.";
                break;
            case 'marcar_entregado':
                $nuevoEstado = 'entregado';
                $comentario = 'El pedido ha sido entregado exitosamente al destinatario.';
                break;
        }

        $this->pedidoService->cambiarEstado($pedido, $nuevoEstado, Auth::id(), $comentario);

        if ($nuevoEstado === 'entregado' && $pedido->envio) {
            $pedido->envio->update([
                'fecha_entrega_real' => now()
            ]);
        }

        return back()->with('toast_success', 'Estado del pedido actualizado a: ' . str_replace('_', ' ', strtoupper($nuevoEstado)));
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
        
        $resultado = $pagoService->reembolsarStripe(
            $pedido, 
            $montoSolicitado, 
            $request->motivo
        );

        if (!$resultado['exito']) {
            return back()->with('toast_error', $resultado['mensaje']);
        }

        $montoReembolsado = $resultado['monto'] ?: $montoSolicitado;
        $pedido->update(['monto_reembolsado' => $montoReembolsado]);

        $comentario = 'Reembolso de $' . number_format($montoReembolsado, 2) . ' procesado con Stripe.';
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
