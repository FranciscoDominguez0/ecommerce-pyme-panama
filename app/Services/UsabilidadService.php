<?php

namespace App\Services;

use App\Models\EvaluacionUsabilidad;
use App\Models\Pedido;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class UsabilidadService
{
    /**
     * Guarda la evaluación de usabilidad post-compra para un pedido.
     */
    public function registrarEvaluacion(Pedido $pedido, ?Usuario $usuario, array $datos): EvaluacionUsabilidad
    {
        // Si ya fue evaluado, retornar la evaluación existente
        $existente = EvaluacionUsabilidad::where('pedido_id', $pedido->id)->first();
        if ($existente) {
            return $existente;
        }

        return EvaluacionUsabilidad::create([
            'pedido_id' => $pedido->id,
            'usuario_id' => $usuario?->id ?? $pedido->usuario_id,
            'puntuacion_facilidad' => (int) ($datos['puntuacion_facilidad'] ?? 5),
            'puntuacion_general' => isset($datos['puntuacion_general']) ? (int) $datos['puntuacion_general'] : (int) ($datos['puntuacion_facilidad'] ?? 5),
            'tiempo_checkout_segundos' => isset($datos['tiempo_checkout_segundos']) ? (int) $datos['tiempo_checkout_segundos'] : null,
            'comentario' => !empty($datos['comentario']) ? trim((string) $datos['comentario']) : null,
            'dispositivo' => $datos['dispositivo'] ?? null,
            'navegador' => $datos['navegador'] ?? null,
        ]);
    }

    /**
     * Calcula los KPIs científicos de Usabilidad y Experiencia (ISO 9241-11, CES, CSAT, TSR).
     */
    public function obtenerMetricas(): array
    {
        $evaluaciones = EvaluacionUsabilidad::with(['usuario', 'pedido'])->orderByDesc('creado_en')->get();
        $totalEvaluaciones = $evaluaciones->count();

        // 1. Customer Effort Score (CES): % de respuestas positivas (4 o 5 = Fácil / Muy Fácil)
        $respuestasPositivas = $evaluaciones->where('puntuacion_facilidad', '>=', 4)->count();
        $porcentajeCES = $totalEvaluaciones > 0 ? round(($respuestasPositivas / $totalEvaluaciones) * 100, 1) : 100.0;

        // 2. CSAT Promedio (Calificación de 1 a 5)
        $promedioCSAT = $totalEvaluaciones > 0 ? round($evaluaciones->avg('puntuacion_facilidad'), 2) : 5.0;

        // 3. Distribución por Estrellas (1 a 5)
        $etiquetas = [
            5 => '5 ★ (Excelente)',
            4 => '4 ★ (Muy bueno)',
            3 => '3 ★ (Bueno)',
            2 => '2 ★ (Regular)',
            1 => '1 ★ (Malo)',
        ];

        $distribucion = [];
        $distribucionDetallada = [];

        foreach ([5, 4, 3, 2, 1] as $estrella) {
            $cantidad = $evaluaciones->where('puntuacion_facilidad', $estrella)->count();
            $porcentaje = $totalEvaluaciones > 0 ? round(($cantidad / $totalEvaluaciones) * 100) : ($estrella >= 4 ? 50 : 0);

            $distribucion[$estrella] = $cantidad;
            $distribucionDetallada[$estrella] = [
                'etiqueta'   => $etiquetas[$estrella],
                'cantidad'   => $cantidad,
                'porcentaje' => $porcentaje,
            ];
        }

        // 4. Eficacia: Task Success Rate (TSR) de acuerdo a ISO 9241-11
        // Porcentaje de pedidos completados con éxito frente al total de pedidos creados
        $totalPedidos = Pedido::count();
        $pedidosExitosos = Pedido::whereHas('estados', function ($q) {
            $q->whereIn('estado', ['pagado', 'pago_confirmado', 'preparando', 'en_camino', 'entregado']);
        })->count();
        $taskSuccessRate = $totalPedidos > 0 ? round(($pedidosExitosos / $totalPedidos) * 100, 1) : 100.0;

        // 5. Eficiencia: Tiempo promedio de Checkout
        $tiempoPromedioCheckout = $evaluaciones->whereNotNull('tiempo_checkout_segundos')->avg('tiempo_checkout_segundos');
        $tiempoPromedioCheckout = $tiempoPromedioCheckout ? round($tiempoPromedioCheckout) : 45; // 45 segundos por defecto

        // 6. Comentarios cualitativos recientes
        $comentariosRecientes = $evaluaciones->whereNotNull('comentario')->take(10);

        return [
            'total_evaluaciones' => $totalEvaluaciones,
            'porcentaje_ces' => $porcentajeCES,
            'promedio_csat' => $promedioCSAT,
            'distribucion_estrellas' => $distribucion,
            'distribucion_detallada' => $distribucionDetallada,
            'task_success_rate' => $taskSuccessRate,
            'tiempo_promedio_checkout' => $tiempoPromedioCheckout,
            'comentarios_recientes' => $comentariosRecientes,
            'total_pedidos' => $totalPedidos,
            'pedidos_exitosos' => $pedidosExitosos,
        ];
    }
}
