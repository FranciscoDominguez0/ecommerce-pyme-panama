<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cliente\GuardarValoracionRequest;
use App\Models\Pedido;
use App\Services\ValoracionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ValoracionController extends Controller
{
    public function __construct(
        protected ValoracionService $valoracionService
    ) {}

    /**
     * Guarda o actualiza la valoración de un producto asociado a un pedido entregado.
     */
    public function store(GuardarValoracionRequest $request, int $id): RedirectResponse|JsonResponse
    {
        $pedido = Pedido::with(['items', 'ultimoEstado'])
            ->where('usuario_id', Auth::id())
            ->findOrFail($id);

        $valoracion = $this->valoracionService->guardarValoracion(
            usuario: $request->user(),
            pedido: $pedido,
            productoId: (int) $request->validated('producto_id'),
            calificacion: (int) $request->validated('calificacion'),
            titulo: $request->validated('titulo'),
            comentario: $request->validated('comentario'),
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'mensaje' => '¡Muchas gracias! Tu opinión ha sido publicada exitosamente.',
                'valoracion' => $valoracion,
            ]);
        }

        return back()->with('toast_success', '¡Muchas gracias! Tu valoración ha sido registrada con éxito.');
    }
}
