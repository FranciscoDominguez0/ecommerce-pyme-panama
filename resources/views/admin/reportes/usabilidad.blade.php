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
        <div class="flex items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400 bg-white dark:bg-[#181a1b] border border-slate-200 dark:border-gray-700 px-3.5 py-2 rounded-xl shadow-xs self-start md:self-auto">
            <span class="material-symbols-outlined text-[18px] text-emerald-600">verified</span>
            <span>{{ $total_evaluaciones }} calificaciones recibidas</span>
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
                <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold mt-1">
                    @if($total_evaluaciones > 0)
                        {{ $respuestas_positivas ?? 0 }} de {{ $total_evaluaciones }} consideran el proceso Fácil o Muy Fácil
                    @else
                        Sin evaluaciones registradas aún
                    @endif
                </p>
            </div>
            <div class="pt-3 border-t border-slate-100 dark:border-gray-700 flex items-center justify-between text-xs mt-2">
                <span class="text-slate-400">Nivel de servicio</span>
                <span class="font-bold {{ $nivel_ces['color'] }}">{{ $nivel_ces['texto'] }}</span>
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
                        <span class="material-symbols-outlined text-[17px] {{ $total_evaluaciones > 0 && $i <= round($promedio_csat) ? 'text-amber-400' : 'text-slate-300 dark:text-slate-700' }}" style="font-variation-settings: 'FILL' 1;">star</span>
                    @endfor
                </div>
            </div>
            <div class="pt-3 border-t border-slate-100 dark:border-gray-700 flex items-center justify-between text-xs mt-2">
                <span class="text-slate-400">Promedio de compras</span>
                <span class="font-bold {{ $nivel_csat['color'] }}">{{ $nivel_csat['texto'] }}</span>
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
                <span class="font-bold {{ $nivel_tsr['color'] }}">{{ $nivel_tsr['texto'] }}</span>
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
                    @if($tiempo_promedio_checkout !== null)
                        <span>{{ $tiempo_promedio_checkout }}</span>
                        <span class="text-base text-slate-400 font-normal">segundos</span>
                    @else
                        <span class="text-2xl text-slate-400 font-semibold">N/D</span>
                    @endif
                </div>
                <p class="text-[11px] text-purple-600 dark:text-purple-400 font-semibold mt-1">
                    @if($tiempo_promedio_checkout !== null)
                        Tiempo medido en proceso de pago
                    @else
                        Pendiente de registros de pago
                    @endif
                </p>
            </div>
            <div class="pt-3 border-t border-slate-100 dark:border-gray-700 flex items-center justify-between text-xs mt-2">
                <span class="text-slate-400">Velocidad</span>
                <span class="font-bold {{ $nivel_tiempo['color'] }}">{{ $nivel_tiempo['texto'] }}</span>
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
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-100 dark:border-emerald-800/60">
                    <span class="material-symbols-outlined text-[18px]">bolt</span>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3 pt-1">
                <!-- Carga del Servidor -->
                <div class="p-4 rounded-xl bg-slate-50/80 dark:bg-[#202326] border border-slate-200/90 dark:border-gray-700 text-center">
                    <span class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Carga Servidor</span>
                    <div class="text-xl font-extrabold text-emerald-600 dark:text-emerald-400" id="metrica-carga-servidor">
                        {{ $rendimiento['tiempo_servidor_segundos'] }}s
                    </div>
                    <span class="text-[10px] text-emerald-600 dark:text-emerald-500 font-medium" id="calif-carga-servidor">
                        {{ $rendimiento['calificacion_servidor'] }}
                    </span>
                </div>

                <!-- Latencia de Base de Datos -->
                <div class="p-4 rounded-xl bg-slate-50/80 dark:bg-[#202326] border border-slate-200/90 dark:border-gray-700 text-center">
                    <span class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Respuesta BD</span>
                    <div class="text-xl font-extrabold text-emerald-600 dark:text-emerald-400">
                        {{ $rendimiento['latencia_db_ms'] }}ms
                    </div>
                    <span class="text-[10px] text-emerald-600 dark:text-emerald-500 font-medium">
                        {{ $rendimiento['calificacion_db'] }}
                    </span>
                </div>

                <!-- Estabilidad del Sistema -->
                <div class="p-4 rounded-xl bg-slate-50/80 dark:bg-[#202326] border border-slate-200/90 dark:border-gray-700 text-center">
                    <span class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Estabilidad</span>
                    <div class="text-xl font-extrabold text-emerald-600 dark:text-emerald-400">
                        {{ $rendimiento['estabilidad_porcentaje'] }}%
                    </div>
                    <span class="text-[10px] text-emerald-600 dark:text-emerald-500 font-medium">
                        {{ $rendimiento['calificacion_estabilidad'] }}
                    </span>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 dark:border-gray-700 text-xs text-slate-500 dark:text-slate-400 flex items-center justify-between">
                <span>Experiencia de navegación:</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ $rendimiento['experiencia_general'] }}</span>
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
            <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-100 dark:border-emerald-800/60">
                <span class="material-symbols-outlined text-[18px]">rate_review</span>
            </div>
        </div>

        @if($comentarios_recientes->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                @foreach($comentarios_recientes as $eval)
                    <div class="p-5 rounded-2xl border border-slate-200/80 dark:border-gray-800 bg-white dark:bg-[#1f2327] hover:border-slate-300 dark:hover:border-gray-700 transition-all shadow-sm flex flex-col justify-between">
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
                                        <div class="w-10 h-10 rounded-full bg-emerald-600 dark:bg-emerald-600 text-white hidden items-center justify-center text-sm font-bold shrink-0">
                                            {{ strtoupper(substr($eval->usuario?->nombre ?? 'C', 0, 1)) }}
                                        </div>
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-emerald-600 dark:bg-emerald-600 text-white flex items-center justify-center text-sm font-bold shrink-0">
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
                                “{{ $eval->comentario }}”
                            </p>
                        </div>

                        <!-- Pie: Pedido y dispositivo -->
                        <div class="pt-3 mt-2 border-t border-slate-100 dark:border-gray-800 flex items-center justify-between text-xs text-slate-400 dark:text-slate-400">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-gray-800/80 text-slate-600 dark:text-slate-300 font-semibold border border-slate-200/60 dark:border-gray-700/50">
                                <span class="material-symbols-outlined text-[15px] text-slate-400">receipt_long</span>
                                Pedido {{ str_starts_with($eval->pedido?->numero_pedido ?? '', '#') ? $eval->pedido?->numero_pedido : '#' . ($eval->pedido?->numero_pedido ?? 'S/N') }}
                            </span>

                            @if($eval->dispositivo)
                                <span class="inline-flex items-center gap-1 text-[11px] text-slate-400 dark:text-slate-500">
                                    <span class="material-symbols-outlined text-[13px]">{{ strtolower($eval->dispositivo) === 'mobile' ? 'smartphone' : 'computer' }}</span>
                                    {{ $eval->dispositivo }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="py-12 text-center space-y-3">
                <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-gray-800 text-slate-400 dark:text-slate-400 flex items-center justify-center mx-auto border border-slate-200 dark:border-gray-700">
                    <span class="material-symbols-outlined text-2xl">rate_review</span>
                </div>
                <p class="text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300">Sin comentarios adicionales</p>
                <p class="text-xs text-slate-400 dark:text-slate-400 max-w-sm mx-auto">Aún no se han registrado comentarios escritos en las evaluaciones de compra.</p>
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
    (function() {
        function actualizarRendimientoReal() {
            if (window.performance && window.performance.getEntriesByType) {
                var nav = window.performance.getEntriesByType('navigation')[0];
                if (nav && nav.duration > 0) {
                    var segundos = (nav.duration / 1000).toFixed(2);
                    var elCarga = document.getElementById('metrica-carga-servidor');
                    var elCalif = document.getElementById('calif-carga-servidor');
                    if (elCarga && segundos > 0) {
                        elCarga.textContent = segundos + 's';
                    }
                    if (elCalif) {
                        elCalif.textContent = nav.duration < 600 ? 'Ultra rápida' : (nav.duration < 1500 ? 'Rápida' : 'Aceptable');
                    }
                }
            }
        }
        if (document.readyState === 'complete') {
            setTimeout(actualizarRendimientoReal, 150);
        } else {
            window.addEventListener('load', function() {
                setTimeout(actualizarRendimientoReal, 150);
            });
        }
    })();
</script>
@endpush
@endsection
