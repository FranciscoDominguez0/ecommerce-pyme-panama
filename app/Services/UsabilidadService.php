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
     * Calcula los KPIs de Usabilidad y Experiencia (ISO 9241-11, CES, CSAT, TSR).
     * Todos los datos se extraen dinámicamente de la base de datos sin valores simulados.
     */
    public function obtenerMetricas(?\Illuminate\Http\Request $request = null): array
    {
        $tipoFiltro = $request ? $request->query('tipo', 'todo') : 'todo';
        $fechaInicioStr = $request ? $request->query('fecha_inicio') : null;
        $fechaFinStr = $request ? $request->query('fecha_fin') : null;
        $filtroEstrella = $request ? $request->query('estrella') : null;

        $fechaInicio = null;
        $fechaFin = null;

        if ($fechaInicioStr && $fechaFinStr) {
            $fechaInicio = \Carbon\Carbon::parse($fechaInicioStr)->startOfDay();
            $fechaFin = \Carbon\Carbon::parse($fechaFinStr)->endOfDay();
        } elseif ($tipoFiltro === 'mes') {
            $fechaInicio = \Carbon\Carbon::now()->startOfMonth();
            $fechaFin = \Carbon\Carbon::now()->endOfMonth();
        } elseif ($tipoFiltro === 'año') {
            $fechaInicio = \Carbon\Carbon::now()->startOfYear();
            $fechaFin = \Carbon\Carbon::now()->endOfYear();
        }

        $queryEvaluaciones = EvaluacionUsabilidad::with(['usuario', 'pedido'])->orderByDesc('creado_en');
        $queryPedidos = Pedido::query();

        if ($fechaInicio && $fechaFin) {
            $queryEvaluaciones->whereBetween('creado_en', [$fechaInicio, $fechaFin]);
            $queryPedidos->whereBetween('creado_en', [$fechaInicio, $fechaFin]);
        }

        $evaluaciones = $queryEvaluaciones->get();
        $totalEvaluaciones = $evaluaciones->count();

        // 1. Customer Effort Score (CES): % de respuestas positivas (4 o 5 = Fácil / Muy Fácil)
        $respuestasPositivas = $evaluaciones->where('puntuacion_facilidad', '>=', 4)->count();
        $porcentajeCES = $totalEvaluaciones > 0 ? round(($respuestasPositivas / $totalEvaluaciones) * 100, 1) : 0.0;

        $nivelCES = match (true) {
            $totalEvaluaciones === 0 => ['texto' => 'Sin datos', 'color' => 'text-slate-400 dark:text-slate-500'],
            $porcentajeCES >= 85 => ['texto' => 'Excelente', 'color' => 'text-emerald-600 dark:text-emerald-400'],
            $porcentajeCES >= 70 => ['texto' => 'Bueno', 'color' => 'text-emerald-600 dark:text-emerald-400'],
            $porcentajeCES >= 50 => ['texto' => 'Aceptable', 'color' => 'text-amber-600 dark:text-amber-400'],
            default => ['texto' => 'Por mejorar', 'color' => 'text-rose-600 dark:text-rose-400'],
        };

        // 2. CSAT Promedio (Calificación de 1 a 5)
        $promedioCSAT = $totalEvaluaciones > 0 ? round($evaluaciones->avg('puntuacion_facilidad'), 1) : 0.0;
        $nivelCSAT = match (true) {
            $totalEvaluaciones === 0 => ['texto' => 'Sin datos', 'color' => 'text-slate-400 dark:text-slate-500'],
            $promedioCSAT >= 4.5 => ['texto' => 'Sobresaliente', 'color' => 'text-amber-600 dark:text-amber-400'],
            $promedioCSAT >= 4.0 => ['texto' => 'Muy Bueno', 'color' => 'text-amber-600 dark:text-amber-400'],
            $promedioCSAT >= 3.0 => ['texto' => 'Aceptable', 'color' => 'text-amber-600 dark:text-amber-400'],
            default => ['texto' => 'Deficiente', 'color' => 'text-rose-600 dark:text-rose-400'],
        };

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
            $porcentaje = $totalEvaluaciones > 0 ? (int) round(($cantidad / $totalEvaluaciones) * 100) : 0;

            $distribucion[$estrella] = $cantidad;
            $distribucionDetallada[$estrella] = [
                'etiqueta'   => $etiquetas[$estrella],
                'cantidad'   => $cantidad,
                'porcentaje' => $porcentaje,
            ];
        }

        // 4. Eficacia: Task Success Rate (TSR) de acuerdo a ISO 9241-11
        $totalPedidos = $queryPedidos->count();
        $pedidosExitosos = (clone $queryPedidos)->whereHas('estados', function ($q) {
            $q->whereIn('estado', ['pagado', 'pago_confirmado', 'preparando', 'en_camino', 'entregado']);
        })->count();
        $taskSuccessRate = $totalPedidos > 0 ? round(($pedidosExitosos / $totalPedidos) * 100, 1) : 0.0;

        $nivelTSR = match (true) {
            $totalPedidos === 0 => ['texto' => 'Sin datos', 'color' => 'text-slate-400 dark:text-slate-500'],
            $taskSuccessRate >= 80 => ['texto' => 'Alta', 'color' => 'text-blue-600 dark:text-blue-400'],
            $taskSuccessRate >= 50 => ['texto' => 'Media', 'color' => 'text-amber-600 dark:text-amber-400'],
            default => ['texto' => 'Baja', 'color' => 'text-rose-600 dark:text-rose-400'],
        };

        // 5. Eficiencia: Tiempo promedio y extremos de Checkout
        $evaluacionesConTiempo = $evaluaciones->whereNotNull('tiempo_checkout_segundos')->where('tiempo_checkout_segundos', '>', 0);
        $totalConTiempo = $evaluacionesConTiempo->count();
        $tiempoPromedioCheckout = $totalConTiempo > 0 
            ? round($evaluacionesConTiempo->avg('tiempo_checkout_segundos')) 
            : null;
        $tiempoMinCheckout = $totalConTiempo > 0 ? (int) $evaluacionesConTiempo->min('tiempo_checkout_segundos') : null;
        $tiempoMaxCheckout = $totalConTiempo > 0 ? (int) $evaluacionesConTiempo->max('tiempo_checkout_segundos') : null;

        $nivelTiempo = match (true) {
            $tiempoPromedioCheckout === null => ['texto' => 'Sin datos', 'color' => 'text-slate-400 dark:text-slate-500'],
            $tiempoPromedioCheckout <= 45 => ['texto' => 'Ultra rápida', 'color' => 'text-purple-600 dark:text-purple-400'],
            $tiempoPromedioCheckout <= 90 => ['texto' => 'Rápida', 'color' => 'text-purple-600 dark:text-purple-400'],
            $tiempoPromedioCheckout <= 180 => ['texto' => 'Moderada', 'color' => 'text-amber-600 dark:text-amber-400'],
            default => ['texto' => 'Lenta', 'color' => 'text-rose-600 dark:text-rose-400'],
        };

        // Rangos de tiempo de compra
        $tiempoMenos60 = $evaluacionesConTiempo->where('tiempo_checkout_segundos', '<=', 60)->count();
        $tiempoEntre60Y180 = $evaluacionesConTiempo->where('tiempo_checkout_segundos', '>', 60)->where('tiempo_checkout_segundos', '<=', 180)->count();
        $tiempoMas180 = $evaluacionesConTiempo->where('tiempo_checkout_segundos', '>', 180)->count();

        $tiemposDetalle = [
            'promedio' => $tiempoPromedioCheckout,
            'minimo' => $tiempoMinCheckout,
            'maximo' => $tiempoMaxCheckout,
            'menos_60' => [
                'cantidad' => $tiempoMenos60,
                'porcentaje' => $totalConTiempo > 0 ? round(($tiempoMenos60 / $totalConTiempo) * 100) : 0,
            ],
            'entre_60_180' => [
                'cantidad' => $tiempoEntre60Y180,
                'porcentaje' => $totalConTiempo > 0 ? round(($tiempoEntre60Y180 / $totalConTiempo) * 100) : 0,
            ],
            'mas_180' => [
                'cantidad' => $tiempoMas180,
                'porcentaje' => $totalConTiempo > 0 ? round(($tiempoMas180 / $totalConTiempo) * 100) : 0,
            ],
        ];

        // 6. Métricas por Dispositivo (Mobile vs Desktop)
        $evaluacionesMobile = $evaluaciones->filter(fn($e) => strtolower((string)$e->dispositivo) === 'mobile');
        $evaluacionesDesktop = $evaluaciones->filter(fn($e) => strtolower((string)$e->dispositivo) !== 'mobile');

        $cantMobile = $evaluacionesMobile->count();
        $cantDesktop = $evaluacionesDesktop->count();

        $metricasDispositivos = [
            'mobile' => [
                'cantidad' => $cantMobile,
                'porcentaje' => $totalEvaluaciones > 0 ? round(($cantMobile / $totalEvaluaciones) * 100) : 0,
                'csat' => $cantMobile > 0 ? round($evaluacionesMobile->avg('puntuacion_facilidad'), 1) : 0.0,
            ],
            'desktop' => [
                'cantidad' => $cantDesktop,
                'porcentaje' => $totalEvaluaciones > 0 ? round(($cantDesktop / $totalEvaluaciones) * 100) : 0,
                'csat' => $cantDesktop > 0 ? round($evaluacionesDesktop->avg('puntuacion_facilidad'), 1) : 0.0,
            ],
        ];

        // 7. Comentarios de compradores (con filtro opcional por estrellas)
        $comentariosQuery = $evaluaciones->whereNotNull('comentario');
        if ($filtroEstrella && in_array((int) $filtroEstrella, [1, 2, 3, 4, 5])) {
            $comentariosQuery = $comentariosQuery->where('puntuacion_facilidad', (int) $filtroEstrella);
        }

        $comentariosRecientes = $comentariosQuery->values();

        return [
            'tipoFiltro' => $tipoFiltro,
            'filtroEstrella' => $filtroEstrella,
            'total_evaluaciones' => $totalEvaluaciones,
            'respuestas_positivas' => $respuestasPositivas,
            'porcentaje_ces' => $porcentajeCES,
            'nivel_ces' => $nivelCES,
            'promedio_csat' => $promedioCSAT,
            'nivel_csat' => $nivelCSAT,
            'distribucion_estrellas' => $distribucion,
            'distribucion_detallada' => $distribucionDetallada,
            'task_success_rate' => $taskSuccessRate,
            'nivel_tsr' => $nivelTSR,
            'tiempo_promedio_checkout' => $tiempoPromedioCheckout,
            'nivel_tiempo' => $nivelTiempo,
            'tiempos_detalle' => $tiemposDetalle,
            'metricas_dispositivos' => $metricasDispositivos,
            'comentarios_recientes' => $comentariosRecientes,
            'total_pedidos' => $totalPedidos,
            'pedidos_exitosos' => $pedidosExitosos,
        ];
    }
}
