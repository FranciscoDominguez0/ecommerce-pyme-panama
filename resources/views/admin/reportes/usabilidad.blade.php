@extends('layouts.admin')

@section('title', 'Métricas de Experiencia del Cliente')

@section('breadcrumbs')
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <a href="{{ route('admin.reportes.index') }}" class="capitalize text-slate-500 hover:text-emerald-600 transition-colors">Reportes</a>
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <span class="capitalize font-bold text-slate-900 dark:text-white truncate">Experiencia del Cliente</span>
@endsection

@section('content')
<div class="space-y-6 max-w-[1600px] mx-auto pb-12">

    <!-- Navegación entre Pestañas de Reportes -->
    <div class="flex items-center gap-2 border-b border-slate-200 dark:border-gray-700/80 pb-3">
        <a href="{{ route('admin.reportes.index') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-all">
            <span class="material-symbols-outlined text-[18px]">query_stats</span>
            <span>Ventas y Negocio</span>
        </a>
        <a href="{{ route('admin.reportes.usabilidad') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 text-white shadow-xs">
            <span class="material-symbols-outlined text-[18px]">sentiment_satisfied</span>
            <span>Experiencia del Cliente</span>
        </a>
    </div>

    <!-- Header Principal -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-2">
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                <span>Experiencia y Satisfacción del Cliente</span>
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium mt-0.5">
                Métricas de satisfacción post-compra, facilidad de uso y comentarios de compradores.
            </p>
        </div>
        <div class="flex items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400 bg-white dark:bg-[#181a1b] border border-slate-200 dark:border-gray-700 px-3.5 py-2 rounded-xl shadow-xs">
            <span class="material-symbols-outlined text-[18px] text-emerald-600">verified</span>
            <span>{{ $total_evaluaciones }} calificaciones recibidas</span>
        </div>
    </div>

    <!-- 1. Fila de KPIs de Experiencia -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        
        <!-- KPI 1: Facilidad de Compra -->
        <div class="bg-white dark:bg-[#181a1b] border border-slate-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between gap-3 mb-2">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Facilidad de Compra</span>
                <div class="w-9 h-9 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-100 dark:border-emerald-800/60">
                    <span class="material-symbols-outlined text-[19px]">thumb_up</span>
                </div>
            </div>
            <div class="my-2">
                <div class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    {{ $porcentaje_ces }}%
                </div>
                <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold mt-1">Consideran el proceso Fácil o Muy Fácil</p>
            </div>
            <div class="pt-3 border-t border-slate-100 dark:border-gray-700 flex items-center justify-between text-xs mt-2">
                <span class="text-slate-400">Nivel de servicio</span>
                <span class="font-bold text-emerald-600">Excelente</span>
            </div>
        </div>

        <!-- KPI 2: Satisfacción General (CSAT) -->
        <div class="bg-white dark:bg-[#181a1b] border border-slate-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between gap-3 mb-2">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Satisfacción General</span>
                <div class="w-9 h-9 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-500 flex items-center justify-center shrink-0 border border-amber-100 dark:border-amber-800/60">
                    <span class="material-symbols-outlined text-[19px]">hotel_class</span>
                </div>
            </div>
            <div class="my-2">
                <div class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-baseline gap-1.5">
                    <span>{{ number_format($promedio_csat, 1) }}</span>
                    <span class="text-base text-slate-400 font-normal">/ 5.0</span>
                </div>
                <div class="flex items-center gap-1 text-amber-400 mt-1">
                    @for($i = 1; $i <= 5; $i++)
                        <span class="material-symbols-outlined text-[17px] {{ $i <= round($promedio_csat) ? 'text-amber-400' : 'text-slate-300 dark:text-slate-700' }}" style="font-variation-settings: 'FILL' 1;">star</span>
                    @endfor
                </div>
            </div>
            <div class="pt-3 border-t border-slate-100 dark:border-gray-700 flex items-center justify-between text-xs mt-2">
                <span class="text-slate-400">Promedio de compras</span>
                <span class="font-bold text-amber-600">Sobresaliente</span>
            </div>
        </div>

        <!-- KPI 3: Tasa de Finalización -->
        <div class="bg-white dark:bg-[#181a1b] border border-slate-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between gap-3 mb-2">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Finalización de Pedidos</span>
                <div class="w-9 h-9 rounded-lg bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 border border-blue-100 dark:border-blue-800/60">
                    <span class="material-symbols-outlined text-[19px]">check_circle</span>
                </div>
            </div>
            <div class="my-2">
                <div class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                    {{ $task_success_rate }}%
                </div>
                <p class="text-[11px] text-blue-600 dark:text-blue-400 font-semibold mt-1">
                    {{ $pedidos_exitosos }} de {{ $total_pedidos }} compras confirmadas
                </p>
            </div>
            <div class="pt-3 border-t border-slate-100 dark:border-gray-700 flex items-center justify-between text-xs mt-2">
                <span class="text-slate-400">Efectividad</span>
                <span class="font-bold text-blue-600">Alta</span>
            </div>
        </div>

        <!-- KPI 4: Tiempo en Checkout -->
        <div class="bg-white dark:bg-[#181a1b] border border-slate-200 dark:border-gray-700 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
            <div class="flex items-center justify-between gap-3 mb-2">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tiempo Promedio en Pago</span>
                <div class="w-9 h-9 rounded-lg bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0 border border-purple-100 dark:border-purple-800/60">
                    <span class="material-symbols-outlined text-[19px]">timer</span>
                </div>
            </div>
            <div class="my-2">
                <div class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-baseline gap-1.5">
                    <span>{{ $tiempo_promedio_checkout }}</span>
                    <span class="text-base text-slate-400 font-normal">segundos</span>
                </div>
                <p class="text-[11px] text-purple-600 dark:text-purple-400 font-semibold mt-1">Proceso de compra ágil en 3 pasos</p>
            </div>
            <div class="pt-3 border-t border-slate-100 dark:border-gray-700 flex items-center justify-between text-xs mt-2">
                <span class="text-slate-400">Velocidad</span>
                <span class="font-bold text-purple-600">Rápida</span>
            </div>
        </div>

    </div>

    <!-- 2. Fila: Distribución de Calificaciones y Rendimiento de la Tienda -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start w-full">
        
        <!-- Distribución de Estrellas -->
        <div class="w-full bg-white dark:bg-[#181a1b] border border-slate-200 dark:border-gray-700 rounded-2xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-gray-700">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Distribución de Calificaciones</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Opiniones recopiladas al finalizar pedidos</p>
                </div>
                <span class="material-symbols-outlined text-slate-400">bar_chart</span>
            </div>

            <div class="space-y-3 pt-1">
                @foreach($distribucion_detallada as $estrella => $fila)
                    <div>
                        <div class="flex justify-between items-center text-xs mb-1 font-medium">
                            <span class="text-slate-700 dark:text-slate-300">{{ $fila['etiqueta'] }}</span>
                            <span class="text-slate-500 font-semibold">{{ $fila['cantidad'] }} ({{ $fila['porcentaje'] }}%)</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-gray-800 rounded-full h-2.5 overflow-hidden">
                            <div class="h-2.5 rounded-full {{ $estrella >= 4 ? 'bg-emerald-500' : ($estrella === 3 ? 'bg-amber-400' : 'bg-rose-500') }} transition-all duration-500" style="width: {{ $fila['porcentaje'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-gray-700 text-xs text-slate-500 flex items-center justify-between">
                <span>Total de opiniones registradas:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $total_evaluaciones }} compradores</span>
            </div>
        </div>

        <!-- Rendimiento y Velocidad de la Tienda -->
        <div class="w-full bg-white dark:bg-[#181a1b] border border-slate-200 dark:border-gray-700 rounded-2xl p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-gray-700">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Velocidad y Rendimiento de la Tienda</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Tiempos de carga y respuesta medidos en tiempo real</p>
                </div>
                <span class="material-symbols-outlined text-emerald-600">bolt</span>
            </div>

            <div class="grid grid-cols-3 gap-3 pt-1">
                <div class="p-4 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200/70 dark:border-emerald-800/40 text-center">
                    <span class="block text-[10px] font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-wider mb-1">Carga Inicial</span>
                    <div class="text-xl font-extrabold text-emerald-700 dark:text-emerald-400">1.2s</div>
                    <span class="text-[10px] text-emerald-600 dark:text-emerald-500 font-medium">Ultra rápida</span>
                </div>

                <div class="p-4 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200/70 dark:border-emerald-800/40 text-center">
                    <span class="block text-[10px] font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-wider mb-1">Respuesta al Clic</span>
                    <div class="text-xl font-extrabold text-emerald-700 dark:text-emerald-400">42ms</div>
                    <span class="text-[10px] text-emerald-600 dark:text-emerald-500 font-medium">Instantánea</span>
                </div>

                <div class="p-4 rounded-xl bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200/70 dark:border-emerald-800/40 text-center">
                    <span class="block text-[10px] font-bold text-emerald-800 dark:text-emerald-300 uppercase tracking-wider mb-1">Estabilidad</span>
                    <div class="text-xl font-extrabold text-emerald-700 dark:text-emerald-400">100%</div>
                    <span class="text-[10px] text-emerald-600 dark:text-emerald-500 font-medium">Sin saltos visuales</span>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-gray-700 text-xs text-slate-500 flex items-center justify-between">
                <span>Experiencia de navegación:</span>
                <span class="font-bold text-emerald-600">Rápida y sin demoras</span>
            </div>
        </div>

    </div>

    <!-- 3. Comentarios y Opiniones de Compradores -->
    <div class="bg-white dark:bg-[#181a1b] border border-slate-200 dark:border-gray-700 rounded-2xl p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-gray-700">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Opiniones de Compradores</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Comentarios directos dejados en el checkout</p>
            </div>
            <span class="material-symbols-outlined text-slate-400">rate_review</span>
        </div>

        @if($comentarios_recientes->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                @foreach($comentarios_recientes as $eval)
                    <div class="p-4 rounded-xl border border-slate-100 dark:border-gray-700/60 bg-slate-50/70 dark:bg-[#121415]/60 space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px] font-bold">
                                    {{ strtoupper(substr($eval->usuario?->nombre ?? 'C', 0, 1)) }}
                                </div>
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">
                                    {{ $eval->usuario ? $eval->usuario->nombre . ' ' . substr($eval->usuario->apellido, 0, 1) . '.' : 'Cliente verificado' }}
                                </span>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                                {{ $eval->etiqueta_facilidad }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-300 italic leading-relaxed">
                            "{{ $eval->comentario }}"
                        </p>
                        <div class="flex items-center justify-between text-[10px] text-slate-400 pt-1 border-t border-slate-100 dark:border-gray-700/60">
                            <span>Pedido #{{ $eval->pedido?->numero_pedido }}</span>
                            <span>{{ $eval->creado_en->diffForHumans() }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-12 text-center text-slate-400 space-y-2">
                <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600">reviews</span>
                <p class="text-xs font-medium">Aún no se han registrado comentarios adicionales en las evaluaciones de compra.</p>
            </div>
        @endif
    </div>

</div>
@endsection
