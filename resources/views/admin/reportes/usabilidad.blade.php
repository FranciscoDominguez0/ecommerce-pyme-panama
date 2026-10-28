@extends('layouts.admin')

@section('title', 'Métricas de Experiencia del Cliente')

@section('breadcrumbs')
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <a href="{{ route('admin.reportes.index') }}" class="capitalize text-slate-500 hover:text-emerald-600 transition-colors">Reportes</a>
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <span class="capitalize font-bold text-slate-900 dark:text-white truncate">Experiencia del Cliente</span>
@endsection

@section('content')
<div class="space-y-6 max-w-[1600px] mx-auto pb-12 font-sans">

    <!-- Header Principal con Filtros Dinámicos -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                <span>Experiencia y Satisfacción del Cliente</span>
            </h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium mt-0.5">
                Métricas de satisfacción post-compra, facilidad de uso y opiniones reales de compradores.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <form method="GET" action="{{ route('admin.reportes.usabilidad') }}" class="flex items-center gap-2" id="form-filtros-usabilidad">
                @if(request('estrella'))
                    <input type="hidden" name="estrella" value="{{ request('estrella') }}">
                @endif
                <div class="relative">
                    <select name="tipo"
                        class="pl-8 pr-8 py-2 bg-white dark:bg-[#121415] border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-slate-300 text-xs font-semibold rounded-xl focus:ring-emerald-500 focus:border-emerald-500 appearance-none shadow-xs cursor-pointer"
                        onchange="document.getElementById('form-filtros-usabilidad').submit()">
                        <option value="mes" {{ $tipoFiltro === 'mes' ? 'selected' : '' }}>Este mes</option>
                        <option value="año" {{ $tipoFiltro === 'año' ? 'selected' : '' }}>Este año</option>
                        <option value="todo" {{ $tipoFiltro === 'todo' ? 'selected' : '' }}>Todo el histórico</option>
                    </select>
                    <span class="material-symbols-outlined absolute left-2.5 top-2.5 text-slate-400 text-[16px] pointer-events-none">calendar_today</span>
                    <span class="material-symbols-outlined absolute right-2.5 top-2.5 text-slate-400 text-[16px] pointer-events-none">expand_more</span>
                </div>
            </form>

            <div class="flex items-center gap-2 text-xs font-bold text-slate-700 dark:text-slate-300 bg-white dark:bg-[#181a1b] border border-slate-200 dark:border-gray-700 px-3.5 py-2 rounded-xl shadow-xs">
                <span class="material-symbols-outlined text-[18px] text-emerald-600">verified</span>
                <span>{{ $total_evaluaciones }} calificaciones</span>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-slate-200 dark:border-gray-700 gap-6">
        <a href="{{ route('admin.reportes.index') }}" 
           class="pb-3 text-xs font-bold border-b-2 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">query_stats</span>
            <span>Ventas y Negocio</span>
        </a>
        <a href="{{ route('admin.reportes.usabilidad') }}" 
           class="pb-3 text-xs font-bold border-b-2 border-emerald-600 text-emerald-700 dark:text-emerald-400 flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">sentiment_satisfied</span>
            <span>Experiencia del Cliente</span>
        </a>
    </div>

    <!-- 1. Fila de KPIs de Experiencia (100% dinámicos) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        
        <!-- KPI 1: Facilidad de Compra (Customer Effort Score) -->
        <div class="bg-white dark:bg-[#181a1b] border border-slate-200 dark:border-gray-700 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
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
                <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold mt-1">
                    @if($total_evaluaciones > 0)
                        {{ $respuestas_positivas }} de {{ $total_evaluaciones }} calificaron 4 o 5 estrellas
                    @else
                        Sin evaluaciones en este período
                    @endif
                </p>
            </div>
            <div class="pt-3 border-t border-slate-100 dark:border-gray-700 flex items-center justify-between text-xs mt-2">
                <span class="text-slate-400">Nivel de esfuerzo</span>
                <span class="font-bold {{ $nivel_ces['color'] }}">{{ $nivel_ces['texto'] }}</span>
            </div>
        </div>

        <!-- KPI 2: Satisfacción General (CSAT) -->
        <div class="bg-white dark:bg-[#181a1b] border border-slate-200 dark:border-gray-700 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
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
                        <span class="material-symbols-outlined text-[17px] {{ $total_evaluaciones > 0 && $i <= round($promedio_csat) ? 'text-amber-400' : 'text-slate-300 dark:text-slate-700' }}" style="font-variation-settings: 'FILL' 1;">star</span>
                    @endfor
                </div>
            </div>
            <div class="pt-3 border-t border-slate-100 dark:border-gray-700 flex items-center justify-between text-xs mt-2">
                <span class="text-slate-400">Puntuación promedio</span>
                <span class="font-bold {{ $nivel_csat['color'] }}">{{ $nivel_csat['texto'] }}</span>
            </div>
        </div>

        <!-- KPI 3: Tasa de Finalización de Pedidos (TSR) -->
        <div class="bg-white dark:bg-[#181a1b] border border-slate-200 dark:border-gray-700 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
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
                <span class="text-slate-400">Tasa de éxito</span>
                <span class="font-bold {{ $nivel_tsr['color'] }}">{{ $nivel_tsr['texto'] }}</span>
            </div>
        </div>

        <!-- KPI 4: Tiempo Promedio en Checkout -->
        <div class="bg-white dark:bg-[#181a1b] border border-slate-200 dark:border-gray-700 rounded-2xl p-5 shadow-xs flex flex-col justify-between">
            <div class="flex items-center justify-between gap-3 mb-2">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tiempo en Checkout</span>
                <div class="w-9 h-9 rounded-lg bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center shrink-0 border border-purple-100 dark:border-purple-800/60">
                    <span class="material-symbols-outlined text-[19px]">timer</span>
                </div>
            </div>
            <div class="my-2">
                <div class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-baseline gap-1.5">
                    @if($tiempo_promedio_checkout !== null)
                        <span>{{ $tiempo_promedio_checkout }}</span>
                        <span class="text-base text-slate-400 font-normal">seg</span>
                    @else
                        <span class="text-2xl text-slate-400 font-semibold">N/D</span>
                    @endif
                </div>
                <p class="text-[11px] text-purple-600 dark:text-purple-400 font-semibold mt-1">
                    @if($tiempo_promedio_checkout !== null)
                        Mín: {{ $tiempos_detalle['minimo'] }}s &bull; Máx: {{ $tiempos_detalle['maximo'] }}s
                    @else
                        Sin registros de tiempo
                    @endif
                </p>
            </div>
            <div class="pt-3 border-t border-slate-100 dark:border-gray-700 flex items-center justify-between text-xs mt-2">
                <span class="text-slate-400">Velocidad cliente</span>
                <span class="font-bold {{ $nivel_tiempo['color'] }}">{{ $nivel_tiempo['texto'] }}</span>
            </div>
        </div>

    </div>

    <!-- 2. Fila: Distribución de Calificaciones y Desglose por Dispositivos / Tiempos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start w-full">
        
        <!-- Distribución de Estrellas -->
        <div class="w-full bg-white dark:bg-[#181a1b] border border-slate-200 dark:border-gray-700 rounded-2xl p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-gray-700">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Distribución de Calificaciones</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Opiniones recopiladas al finalizar pedidos en la tienda</p>
                </div>
                <span class="material-symbols-outlined text-slate-400">bar_chart</span>
            </div>

            <div class="space-y-3 pt-1">
                @foreach($distribucion_detallada as $estrella => $fila)
                    <div>
                        <div class="flex justify-between items-center text-xs mb-1 font-medium">
                            <span class="text-slate-700 dark:text-slate-300 font-semibold">{{ $fila['etiqueta'] }}</span>
                            <span class="text-slate-500 font-semibold">{{ $fila['cantidad'] }} ({{ $fila['porcentaje'] }}%)</span>
                        </div>
                        <div class="w-full bg-slate-100 dark:bg-gray-800 rounded-full h-2.5 overflow-hidden">
                            <div class="h-2.5 rounded-full {{ $estrella >= 4 ? 'bg-emerald-500' : ($estrella === 3 ? 'bg-amber-400' : 'bg-rose-500') }} transition-all duration-500" style="width: {{ $fila['porcentaje'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-gray-700 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Total de evaluaciones registradas:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $total_evaluaciones }} compradores</span>
            </div>
        </div>

        <!-- Métricas de Experiencia por Dispositivo y Duración -->
        <div class="w-full bg-white dark:bg-[#181a1b] border border-slate-200 dark:border-gray-700 rounded-2xl p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-gray-700">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Experiencia por Dispositivo y Tiempos</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Comportamiento medido directamente desde la compra</p>
                </div>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-100 dark:border-emerald-800/60">
                    <span class="material-symbols-outlined text-[18px]">devices</span>
                </div>
            </div>

            <!-- Dispositivos móviles vs escritorio -->
            <div class="grid grid-cols-2 gap-3 pt-1">
                <!-- Vista para móviles -->
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#202326] border border-slate-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] text-slate-500">smartphone</span>
                            Móvil
                        </span>
                        <span class="text-xs font-mono font-bold text-slate-500">{{ $metricas_dispositivos['mobile']['porcentaje'] }}%</span>
                    </div>
                    <div class="text-2xl font-extrabold text-slate-900 dark:text-white">
                        {{ $metricas_dispositivos['mobile']['cantidad'] }}
                    </div>
                    <span class="text-[11px] text-amber-500 font-semibold flex items-center gap-1 mt-1">
                        <span class="material-symbols-outlined text-[14px]">star</span>
                        {{ number_format($metricas_dispositivos['mobile']['csat'], 1) }} CSAT Promedio
                    </span>
                </div>

                <!-- Vista para escritorio -->
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-[#202326] border border-slate-200 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] text-slate-500">computer</span>
                            Escritorio
                        </span>
                        <span class="text-xs font-mono font-bold text-slate-500">{{ $metricas_dispositivos['desktop']['porcentaje'] }}%</span>
                    </div>
                    <div class="text-2xl font-extrabold text-slate-900 dark:text-white">
                        {{ $metricas_dispositivos['desktop']['cantidad'] }}
                    </div>
                    <span class="text-[11px] text-amber-500 font-semibold flex items-center gap-1 mt-1">
                        <span class="material-symbols-outlined text-[14px]">star</span>
                        {{ number_format($metricas_dispositivos['desktop']['csat'], 1) }} CSAT Promedio
                    </span>
                </div>
            </div>

            <!-- Rangos de duración del Checkout -->
            <div class="pt-2">
                <span class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-2">Duración del Checkout por Comprador:</span>
                <div class="grid grid-cols-3 gap-2 text-center text-xs">
                    <div class="p-2.5 rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-[#181a1b]">
                        <span class="text-[10px] text-slate-400 uppercase font-bold block">&le; 1 minuto</span>
                        <span class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400">{{ $tiempos_detalle['menos_60']['cantidad'] }}</span>
                        <span class="text-[10px] text-slate-400 block font-mono">({{ $tiempos_detalle['menos_60']['porcentaje'] }}%)</span>
                    </div>
                    <div class="p-2.5 rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-[#181a1b]">
                        <span class="text-[10px] text-slate-400 uppercase font-bold block">1 a 3 minutos</span>
                        <span class="text-sm font-extrabold text-amber-600 dark:text-amber-400">{{ $tiempos_detalle['entre_60_180']['cantidad'] }}</span>
                        <span class="text-[10px] text-slate-400 block font-mono">({{ $tiempos_detalle['entre_60_180']['porcentaje'] }}%)</span>
                    </div>
                    <div class="p-2.5 rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-[#181a1b]">
                        <span class="text-[10px] text-slate-400 uppercase font-bold block">&gt; 3 minutos</span>
                        <span class="text-sm font-extrabold text-purple-600 dark:text-purple-400">{{ $tiempos_detalle['mas_180']['cantidad'] }}</span>
                        <span class="text-[10px] text-slate-400 block font-mono">({{ $tiempos_detalle['mas_180']['porcentaje'] }}%)</span>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-gray-700 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Tiempo promedio general:</span>
                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $tiempo_promedio_checkout ? $tiempo_promedio_checkout . ' segundos' : 'N/D' }}</span>
            </div>
        </div>

    </div>

    <!-- 3. Comentarios y Opiniones Reales de Compradores -->
    <div class="bg-white dark:bg-[#181a1b] border border-slate-200 dark:border-gray-700 rounded-2xl p-6 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-100 dark:border-gray-700 gap-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Opiniones y Comentarios de Clientes</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Comentarios directos dejados en el checkout por compradores reales</p>
            </div>

            <!-- Filtro de estrellas para comentarios -->
            <div class="flex items-center gap-1.5 flex-wrap">
                <a href="{{ route('admin.reportes.usabilidad', ['tipo' => $tipoFiltro]) }}" 
                   class="px-2.5 py-1 text-xs rounded-lg font-bold border transition-colors {{ empty($filtroEstrella) ? 'bg-slate-900 text-white border-slate-900 dark:bg-emerald-600 dark:border-emerald-600' : 'bg-white dark:bg-[#181a1b] text-slate-600 dark:text-slate-300 border-slate-200 dark:border-gray-700 hover:bg-slate-50' }}">
                   Todas
                </a>
                @foreach([5, 4, 3, 2, 1] as $est)
                    <a href="{{ route('admin.reportes.usabilidad', ['tipo' => $tipoFiltro, 'estrella' => $est]) }}" 
                       class="px-2.5 py-1 text-xs rounded-lg font-bold border transition-colors flex items-center gap-1 {{ (string)$filtroEstrella === (string)$est ? 'bg-amber-500 text-white border-amber-500' : 'bg-white dark:bg-[#181a1b] text-slate-600 dark:text-slate-300 border-slate-200 dark:border-gray-700 hover:bg-slate-50' }}">
                       <span>{{ $est }}</span>
                       <span class="material-symbols-outlined text-[13px] {{ (string)$filtroEstrella === (string)$est ? 'text-white' : 'text-amber-400' }}" style="font-variation-settings: 'FILL' 1;">star</span>
                    </a>
                @endforeach
            </div>
        </div>

        @if($comentarios_recientes->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                @foreach($comentarios_recientes as $eval)
                    <div class="p-5 rounded-2xl border border-slate-200/80 dark:border-gray-800 bg-white dark:bg-[#1f2327] hover:border-slate-300 dark:hover:border-gray-700 transition-all shadow-xs flex flex-col justify-between">
                        <div>
                            <!-- Cabecera: Avatar, Nombre, Estrellas y Tiempo -->
                            <div class="flex items-center justify-between gap-3 mb-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    @if($eval->usuario?->foto_perfil_ruta)
                                        <img src="{{ $eval->usuario->foto_perfil_ruta }}" 
                                             alt="{{ $eval->usuario->nombre }}" 
                                             referrerpolicy="no-referrer"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                             class="w-10 h-10 rounded-full object-cover border border-slate-200 dark:border-gray-700 shrink-0">
                                        <div class="w-10 h-10 rounded-full bg-emerald-600 text-white hidden items-center justify-center text-sm font-bold shrink-0">
                                            {{ strtoupper(substr($eval->usuario?->nombre ?? 'C', 0, 1)) }}
                                        </div>
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-emerald-600 text-white flex items-center justify-center text-sm font-bold shrink-0">
                                            {{ strtoupper(substr($eval->usuario?->nombre ?? 'C', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <h4 class="text-sm font-bold text-slate-900 dark:text-white capitalize truncate">
                                            {{ $eval->usuario ? $eval->usuario->nombre . ' ' . substr($eval->usuario->apellido, 0, 1) . '.' : 'Cliente verificado' }}
                                        </h4>
                                        <div class="flex items-center text-amber-400 mt-0.5">
                                            @for($i = 1; $i <= 5; $i++)
                                                <span class="material-symbols-outlined text-[15px] {{ $i <= $eval->puntuacion_facilidad ? 'text-amber-400' : 'text-slate-300 dark:text-gray-700' }}" style="font-variation-settings: 'FILL' 1;">star</span>
                                            @endfor
                                        </div>
                                    </div>
                                </div>

                                <span class="text-xs text-slate-400 dark:text-slate-500 whitespace-nowrap shrink-0 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">schedule</span>
                                    {{ $eval->creado_en->diffForHumans() }}
                                </span>
                            </div>

                            <!-- Comentario del cliente -->
                            <p class="text-sm text-slate-700 dark:text-slate-200 leading-relaxed font-normal my-2 italic">
                                &ldquo;{{ $eval->comentario }}&rdquo;
                            </p>
                        </div>

                        <!-- Pie: Pedido y dispositivo -->
                        <div class="pt-3 mt-2 border-t border-slate-100 dark:border-gray-800 flex items-center justify-between text-xs text-slate-400 dark:text-slate-400">
                            @if($eval->pedido)
                                <a href="{{ route('admin.pedidos.detalle', $eval->pedido->id) }}" 
                                   title="Ver detalle del pedido"
                                   class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 hover:text-emerald-800 dark:hover:text-emerald-300 font-bold border border-emerald-200/80 dark:border-emerald-800/60 transition-colors shadow-2xs group">
                                    <span class="material-symbols-outlined text-[15px] text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition-transform">receipt_long</span>
                                    <span>Pedido {{ str_starts_with($eval->pedido->numero_pedido ?? '', '#') ? $eval->pedido->numero_pedido : '#' . ($eval->pedido->numero_pedido ?? 'S/N') }}</span>
                                    <span class="material-symbols-outlined text-[13px] opacity-70 group-hover:opacity-100 transition-opacity">open_in_new</span>
                                </a>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-gray-800/80 text-slate-600 dark:text-slate-300 font-semibold border border-slate-200/60 dark:border-gray-700/50">
                                    <span class="material-symbols-outlined text-[15px] text-slate-400">receipt_long</span>
                                    Pedido S/N
                                </span>
                            @endif

                            <div class="flex items-center gap-3">
                                @if($eval->tiempo_checkout_segundos)
                                    <span class="inline-flex items-center gap-1 text-[11px] text-slate-500 font-medium">
                                        <span class="material-symbols-outlined text-[13px]">timer</span>
                                        {{ $eval->tiempo_checkout_segundos }}s
                                    </span>
                                @endif

                                @if($eval->dispositivo)
                                    <span class="inline-flex items-center gap-1 text-[11px] text-slate-500 font-medium">
                                        <span class="material-symbols-outlined text-[13px]">{{ strtolower($eval->dispositivo) === 'mobile' ? 'smartphone' : 'computer' }}</span>
                                        {{ $eval->dispositivo }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-12 text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-gray-800 text-slate-400 dark:text-slate-400 flex items-center justify-center mx-auto border border-slate-200 dark:border-gray-700">
                    <span class="material-symbols-outlined text-2xl">rate_review</span>
                </div>
                <p class="text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300">
                    {{ $filtroEstrella ? 'No hay opiniones con ' . $filtroEstrella . ' estrellas' : 'Sin comentarios adicionales' }}
                </p>
                <p class="text-xs text-slate-400 dark:text-slate-400 max-w-sm mx-auto">
                    {{ $filtroEstrella ? 'Prueba seleccionando otra calificación o el filtro "Todas".' : 'Aún no se han registrado comentarios escritos en las evaluaciones de compra de este período.' }}
                </p>
            </div>
        @endif
    </div>

</div>
@endsection
