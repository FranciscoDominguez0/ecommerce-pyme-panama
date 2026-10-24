<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Services\UsabilidadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsabilidadController extends Controller
{
    protected UsabilidadService $usabilidadService;

    public function __construct(UsabilidadService $usabilidadService)
    {
        $this->usabilidadService = $usabilidadService;
    }

    /**
     * Guarda la evaluación de usabilidad post-compra del cliente.
     */
    public function store(Request $request, $id): JsonResponse
    {
        $usuario = Auth::user();

        // Buscar el pedido y asegurar pertenencia
        $pedido = Pedido::where('id', $id)
            ->where('usuario_id', $usuario->id)
            ->firstOrFail();

        $validados = $request->validate([
            'puntuacion_facilidad' => 'required|integer|min:1|max:5',
            'comentario' => 'nullable|string|max:500',
            'tiempo_checkout_segundos' => 'nullable|integer',
            'dispositivo' => 'nullable|string|max:40',
        ]);

        $evaluacion = $this->usabilidadService->registrarEvaluacion($pedido, $usuario, [
            'puntuacion_facilidad' => $validados['puntuacion_facilidad'],
            'comentario' => $validados['comentario'] ?? null,
            'tiempo_checkout_segundos' => $validados['tiempo_checkout_segundos'] ?? null,
            'dispositivo' => $validados['dispositivo'] ?? ($request->header('User-Agent') ? 'Web' : null),
            'navegador' => substr((string)$request->header('User-Agent'), 0, 60),
        ]);

        return response()->json([
            'success' => true,
            'mensaje' => '¡Muchas gracias! Tu opinión nos ayuda a mejorar la experiencia de compra.',
            'evaluacion' => [
                'puntuacion_facilidad' => $evaluacion->puntuacion_facilidad,
                'etiqueta' => $evaluacion->etiqueta_facilidad,
            ],
        ]);
    }
}
