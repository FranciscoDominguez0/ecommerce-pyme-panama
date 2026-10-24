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
        $totalPedidos = Pedido::count();
        $pedidosExitosos = Pedido::whereHas('estados', function ($q) {
            $q->whereIn('estado', ['pagado', 'pago_confirmado', 'preparando', 'en_camino', 'entregado']);
        })->count();
        $taskSuccessRate = $totalPedidos > 0 ? round(($pedidosExitosos / $totalPedidos) * 100, 1) : 0.0;

        $nivelTSR = match (true) {
            $totalPedidos === 0 => ['texto' => 'Sin datos', 'color' => 'text-slate-400 dark:text-slate-500'],
            $taskSuccessRate >= 80 => ['texto' => 'Alta', 'color' => 'text-blue-600 dark:text-blue-400'],
            $taskSuccessRate >= 50 => ['texto' => 'Media', 'color' => 'text-amber-600 dark:text-amber-400'],
            default => ['texto' => 'Baja', 'color' => 'text-rose-600 dark:text-rose-400'],
        };

        // 5. Eficiencia: Tiempo promedio de Checkout
        $evaluacionesConTiempo = $evaluaciones->whereNotNull('tiempo_checkout_segundos');
        $tiempoPromedioCheckout = $evaluacionesConTiempo->count() > 0 
            ? round($evaluacionesConTiempo->avg('tiempo_checkout_segundos')) 
            : null;

        $nivelTiempo = match (true) {
            $tiempoPromedioCheckout === null => ['texto' => 'Sin datos', 'color' => 'text-slate-400 dark:text-slate-500'],
            $tiempoPromedioCheckout <= 45 => ['texto' => 'Ultra rápida', 'color' => 'text-purple-600 dark:text-purple-400'],
            $tiempoPromedioCheckout <= 90 => ['texto' => 'Rápida', 'color' => 'text-purple-600 dark:text-purple-400'],
            $tiempoPromedioCheckout <= 180 => ['texto' => 'Moderada', 'color' => 'text-amber-600 dark:text-amber-400'],
            default => ['texto' => 'Lenta', 'color' => 'text-rose-600 dark:text-rose-400'],
        };

        // 6. Rendimiento y Velocidad Técnica en Tiempo Real
        // Benchmark real de base de datos
        $inicioDb = microtime(true);
        DB::select('SELECT 1');
        $latenciaDbMs = round((microtime(true) - $inicioDb) * 1000, 1);
        $calificacionDb = match (true) {
            $latenciaDbMs < 10 => 'Instantánea',
            $latenciaDbMs < 30 => 'Muy rápida',
            $latenciaDbMs < 80 => 'Rápida',
            default => 'Moderada',
        };

        // Tiempo real de procesamiento del servidor (Backend execution)
        $tiempoServidorSegundos = defined('LARAVEL_START') 
            ? round(max(0.02, microtime(true) - LARAVEL_START), 2) 
            : 0.12;
        $calificacionServidor = match (true) {
            $tiempoServidorSegundos < 0.5 => 'Ultra rápida',
            $tiempoServidorSegundos < 1.2 => 'Rápida',
            $tiempoServidorSegundos < 2.5 => 'Aceptable',
            default => 'Lenta',
        };

        // Estabilidad: tasa de pedidos confirmados sin fallos ni cancelaciones
        $pedidosCancelados = Pedido::whereHas('estados', function ($q) {
            $q->whereIn('estado', ['cancelado', 'reembolsado']);
        })->count();
        $tasaEstabilidad = $totalPedidos > 0 
            ? round((($totalPedidos - $pedidosCancelados) / $totalPedidos) * 100, 1) 
            : 100.0;
        $calificacionEstabilidad = match (true) {
            $tasaEstabilidad >= 98 => 'Sin incidencias',
            $tasaEstabilidad >= 90 => 'Alta fiabilidad',
            default => 'Requiere atención',
        };

        $experienciaGeneral = ($tiempoServidorSegundos < 1.2 && $latenciaDbMs < 60 && $tasaEstabilidad >= 90)
            ? 'Rápida y sin demoras'
            : 'Estable con margen de optimización';

        $rendimiento = [
            'tiempo_servidor_segundos' => $tiempoServidorSegundos,
            'calificacion_servidor' => $calificacionServidor,
            'latencia_db_ms' => $latenciaDbMs,
            'calificacion_db' => $calificacionDb,
            'estabilidad_porcentaje' => $tasaEstabilidad,
            'calificacion_estabilidad' => $calificacionEstabilidad,
            'experiencia_general' => $experienciaGeneral,
        ];

        // 7. Comentarios cualitativos recientes
        $comentariosRecientes = $evaluaciones->whereNotNull('comentario')->take(10);

        return [
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
            'rendimiento' => $rendimiento,
            'comentarios_recientes' => $comentariosRecientes,
            'total_pedidos' => $totalPedidos,
            'pedidos_exitosos' => $pedidosExitosos,
        ];
    }
}
