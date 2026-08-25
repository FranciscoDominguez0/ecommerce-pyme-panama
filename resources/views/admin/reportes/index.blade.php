@extends('layouts.admin')

@section('title', 'Reportes y Estadísticas')

@section('breadcrumbs')
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <span class="capitalize font-bold text-slate-900 truncate max-w-[90px] sm:max-w-none">Reportes</span>
@endsection

@push('styles')
    <style>
        .apexcharts-tooltip {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1) !important;
            border: 1px solid #e2e8f0 !important;
        }

        .apexcharts-tooltip-title {
            font-weight: 600 !important;
            border-bottom: 1px solid #e2e8f0 !important;
            background: #f8fafc !important;
        }

        .card-saas {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        }
    </style>
@endpush

@section('content')
    <div class="space-y-6 max-w-[1600px] mx-auto">

        <!-- Header y Filtros -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200/80">
            <div>
                <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Reportes Avanzados</h2>
                <p class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">Métricas clave e inteligencia de negocio.
                </p>
            </div>

            <form method="GET" action="{{ route('admin.reportes.index') }}"
                class="flex flex-wrap items-center gap-3 w-full sm:w-auto" id="form-filtros">
                <div class="relative">
                    <select name="tipo"
                        class="pl-8 pr-8 py-1.5 bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-lg focus:ring-emerald-500 focus:border-emerald-500 appearance-none shadow-xs cursor-pointer"
                        onchange="document.getElementById('form-filtros').submit()">
                        <option value="mes" {{ $tipoFiltro === 'mes' ? 'selected' : '' }}>Este mes</option>
                        <option value="año" {{ $tipoFiltro === 'año' ? 'selected' : '' }}>Este año</option>
                        <option value="todos" {{ $tipoFiltro === 'todos' ? 'selected' : '' }}>Histórico completo</option>
                    </select>
                    <span
                        class="material-symbols-outlined absolute left-2.5 top-1.5 text-slate-400 text-[16px] pointer-events-none">calendar_today</span>
                    <span
                        class="material-symbols-outlined absolute right-2.5 top-1.5 text-slate-400 text-[16px] pointer-events-none">expand_more</span>
                </div>

                <div class="flex gap-2 w-full sm:w-auto">
                    <x-btn-exportar excel-onclick="exportar('excel')" pdf-onclick="exportar('pdf')" />
                </div>
            </form>
        </div>

        <!-- KPIs Principales -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
            <!-- KPI 1: Ventas Totales -->
            <div class="card-saas p-5 hover:border-slate-300 transition-all flex flex-col justify-between">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Ingresos Totales</span>
                    <div
                        class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                        <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>
                    </div>
                </div>
                <div class="my-1.5">
                    <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        ${{ number_format($totalVentas, 2) }}
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs mt-2">
                    <span class="inline-flex items-center gap-1 font-bold text-emerald-600">
                        <span class="material-symbols-outlined text-[14px]">trending_up</span>
                        <span>Facturado</span>
                    </span>
                    <span class="text-slate-400 font-semibold">{{ ucfirst($tipoFiltro) }}</span>
                </div>
            </div>

            <!-- KPI 2: Total Pedidos -->
            <div class="card-saas p-5 hover:border-slate-300 transition-all flex flex-col justify-between">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total de Pedidos</span>
                    <div
                        class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100">
                        <span class="material-symbols-outlined text-[18px]">shopping_cart</span>
                    </div>
                </div>
                <div class="my-1.5">
                    <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        {{ $numeroPedidos }}
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs mt-2">
                    <span class="inline-flex items-center gap-1 font-bold text-blue-600">
                        <span class="material-symbols-outlined text-[14px]">done_all</span>
                        <span>Órdenes procesadas</span>
                    </span>
                    <span class="text-slate-400 font-semibold">{{ ucfirst($tipoFiltro) }}</span>
                </div>
            </div>

            <!-- KPI 3: Ticket Promedio -->
            <div class="card-saas p-5 hover:border-slate-300 transition-all flex flex-col justify-between">
                <div class="flex items-center justify-between gap-3 mb-2">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Ticket Promedio</span>
                    <div
                        class="w-9 h-9 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 border border-purple-100">
                        <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                    </div>
                </div>
                <div class="my-1.5">
                    <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        ${{ number_format($ticketPromedio, 2) }}
                    </div>
                </div>
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs mt-2">
                    <span class="inline-flex items-center gap-1 font-bold text-purple-600">
                        <span class="material-symbols-outlined text-[14px]">sell</span>
                        <span>Por transacción</span>
                    </span>
                    <span class="text-slate-400 font-semibold">{{ ucfirst($tipoFiltro) }}</span>
                </div>
            </div>
        </div>

        <!-- 3 Gráficos Principales (Estilo SaaS Referencia) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- 1. Revenue Overview (Área) -->
            <div class="card-saas p-6 flex flex-col">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-base font-bold text-slate-900">Resumen de Ingresos</h3>
                    <span
                        class="px-2 py-0.5 rounded bg-emerald-50 text-emerald-600 border border-emerald-200 text-xs font-bold flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">arrow_upward</span>
                        ${{ number_format($totalVentas, 0) }}
                    </span>
                </div>
                @if(count($ventasPorPeriodo) > 0)
                    <div class="w-full relative mt-auto">
                        <div id="revenueChart"></div>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-10 text-center flex-1">
                        <span class="material-symbols-outlined text-4xl text-slate-200 mb-2">analytics</span>
                        <p class="text-sm font-semibold text-slate-500">Sin datos.</p>
                    </div>
                @endif
            </div>

            <!-- 2. Expense By (Ingresos vs Descuentos - Barras Agrupadas) -->
            <div class="card-saas p-6 flex flex-col">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-base font-bold text-slate-900">Ingresos vs Descuentos</h3>
                    <span
                        class="px-2 py-0.5 rounded bg-slate-50 text-slate-600 border border-slate-200 text-xs font-bold flex items-center gap-1">
                        {{ $numeroPedidos }} Órdenes
                    </span>
                </div>
                @if(count($ventasPorPeriodo) > 0)
                    <div class="w-full relative mt-auto">
                        <div id="expenseChart"></div>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-10 text-center flex-1">
                        <span class="material-symbols-outlined text-4xl text-slate-200 mb-2">bar_chart</span>
                        <p class="text-sm font-semibold text-slate-500">Sin datos.</p>
                    </div>
                @endif
            </div>

            <!-- 3. Income Sources (Categorías - Donut) -->
            <div class="card-saas p-6 flex flex-col">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="text-base font-bold text-slate-900">Fuentes de Ingreso</h3>
                </div>
                @if(count($ventasPorCategoria) > 0)
                    <div class="w-full relative mt-auto flex justify-center items-center">
                        <div id="sourcesChart"></div>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-10 text-center flex-1">
                        <span class="material-symbols-outlined text-4xl text-slate-200 mb-2">pie_chart</span>
                        <p class="text-sm font-semibold text-slate-500">Sin datos.</p>
                    </div>
                @endif
            </div>

        </div>

        <!-- Tablas Secundarias -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Productos Más Vendidos -->
            <div class="card-saas p-6">
                <div class="flex items-center gap-2 mb-4">
                    <div
                        class="w-8 h-8 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center border border-orange-100">
                        <span class="material-symbols-outlined text-[16px]">star</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Top Productos</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="pb-3 pl-1">Producto</th>
                                <th class="pb-3 text-center">Cant.</th>
                                <th class="pb-3 text-right">Ingresos</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs divide-y divide-slate-100">
                            @forelse($productosMasVendidos->take(5) as $prod)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-3 pl-1">
                                        <div class="font-bold text-slate-900">{{ $prod->nombre }}</div>
                                        <div class="text-[10px] text-slate-500 mt-0.5">SKU: {{ $prod->sku }}</div>
                                    </td>
                                    <td class="py-3 text-center font-medium text-slate-600">
                                        {{ $prod->total_vendido }}
                                    </td>
                                    <td class="py-3 text-right font-extrabold text-emerald-600">
                                        ${{ number_format($prod->ingresos_generados, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-6 text-center text-slate-500 text-xs">Sin datos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Clientes Top -->
            <div class="card-saas p-6">
                <div class="flex items-center gap-2 mb-4">
                    <div
                        class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center border border-purple-100">
                        <span class="material-symbols-outlined text-[16px]">group</span>
                    </div>
                    <h3 class="text-base font-bold text-slate-900">Mejores Clientes</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="pb-3 pl-1">Cliente</th>
                                <th class="pb-3 text-center">Pedidos</th>
                                <th class="pb-3 text-right">Gastado</th>
                            </tr>
                        </thead>
                        <tbody class="text-xs divide-y divide-slate-100">
                            @forelse($clientesFrecuentes->take(5) as $cli)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-3 pl-1">
                                        <div class="font-bold text-slate-900">{{ $cli->nombre }} {{ $cli->apellido }}</div>
                                        <div class="text-[10px] text-slate-500 mt-0.5">{{ $cli->email }}</div>
                                    </td>
                                    <td class="py-3 text-center font-medium text-slate-600">
                                        {{ $cli->total_pedidos }}
                                    </td>
                                    <td class="py-3 text-right font-extrabold text-purple-600">
                                        ${{ number_format($cli->total_gastado, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-6 text-center text-slate-500 text-xs">Sin datos.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Stock Crítico -->
        <div class="card-saas p-6 mb-4">
            <div class="flex items-center gap-2 mb-4">
                <div
                    class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center border border-rose-100">
                    <span class="material-symbols-outlined text-[16px]">warning</span>
                </div>
                <h3 class="text-base font-bold text-slate-900">Alerta de Stock Crítico</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="pb-3 pl-1">Producto</th>
                            <th class="pb-3 text-center">Stock Actual</th>
                            <th class="pb-3 text-center">Mínimo Requerido</th>
                            <th class="pb-3 text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y divide-slate-100">
                        @forelse($stockCritico->take(5) as $stock)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 pl-1">
                                    <div class="font-bold text-slate-900">{{ $stock->nombre }}</div>
                                </td>
                                <td class="py-3 text-center font-extrabold text-rose-600">
                                    {{ $stock->stock }}
                                </td>
                                <td class="py-3 text-center font-bold text-slate-500">
                                    {{ $stock->stock_minimo }}
                                </td>
                                <td class="py-3 text-center">
                                    <span
                                        class="px-2 py-0.5 rounded-full bg-rose-50 text-rose-600 border border-rose-200 text-[10px] font-bold">
                                        Crítico
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-500 text-xs">
                                    <span
                                        class="material-symbols-outlined text-3xl text-emerald-300 mb-2 block">check_circle</span>
                                    Todo el inventario está en niveles saludables.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        function exportar(formato) {
            const form = document.getElementById('form-filtros');
            const actionOriginal = form.action;
            const inputReporte = document.createElement('input');
            inputReporte.type = 'hidden';
            inputReporte.name = 'reporte';
            inputReporte.value = 'completo';
            form.appendChild(inputReporte);

            if (formato === 'excel') { form.action = "{{ route('admin.reportes.exportar-excel') }}"; }
            else if (formato === 'pdf') { form.action = "{{ route('admin.reportes.exportar-pdf') }}"; }

            form.target = "_blank";
            form.submit();

            setTimeout(() => { form.action = actionOriginal; form.target = "_self"; form.removeChild(inputReporte); }, 100);
        }

        document.addEventListener('DOMContentLoaded', () => {

            const fontConfig = { fontFamily: "'Plus Jakarta Sans', sans-serif" };

            // 1. Revenue Overview (Línea suave con marcadores)
            @if(count($ventasPorPeriodo) > 0)
                new ApexCharts(document.querySelector("#revenueChart"), {
                    series: [{
                        name: 'Ingresos',
                        data: @json(collect($ventasPorPeriodo)->pluck('total'))
                    }],
                    chart: { type: 'area', height: 260, toolbar: { show: false }, ...fontConfig },
                    colors: ['#059669'], // emerald-600 (color verde corporativo)
                    fill: {
                        type: 'gradient',
                        gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0, stops: [0, 100] }
                    },
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 3 },
                    markers: {
                        size: 5,
                        colors: ['#ffffff'],
                        strokeColors: '#059669',
                        strokeWidth: 2,
                        hover: { size: 7 }
                    },
                    xaxis: {
                        categories: @json(collect($ventasPorPeriodo)->pluck('etiqueta')),
                        axisBorder: { show: false }, axisTicks: { show: false },
                        labels: { style: { colors: '#94a3b8', fontSize: '10px', fontWeight: 600 } } // slate-400
                    },
                    yaxis: {
                        labels: {
                            style: { colors: '#94a3b8', fontSize: '10px', fontWeight: 600 },
                            formatter: function (val) { return val >= 1000 ? (val / 1000).toFixed(1) + 'k' : val; }
                        }
                    },
                    grid: { borderColor: '#f1f5f9', strokeDashArray: 0, xaxis: { lines: { show: false } }, yaxis: { lines: { show: true } } },
                    theme: { mode: 'light' }
                }).render();
            @endif

            // 2. Expense By (Ingresos vs Descuentos - Barras Agrupadas)
            @if(count($ventasPorPeriodo) > 0)
                new ApexCharts(document.querySelector("#expenseChart"), {
                    series: [
                        {
                            name: 'Ingresos',
                            data: @json(collect($ventasPorPeriodo)->pluck('total'))
                        },
                        {
                            name: 'Descuentos',
                            data: @json(collect($ventasPorPeriodo)->pluck('descuentos'))
                        }
                    ],
                    chart: { type: 'bar', height: 260, toolbar: { show: false }, ...fontConfig },
                    colors: ['#059669', '#cbd5e1'], // emerald-600 y slate-300
                    plotOptions: { bar: { borderRadius: 3, columnWidth: '45%' } },
                    dataLabels: { enabled: false },
                    xaxis: {
                        categories: @json(collect($ventasPorPeriodo)->pluck('etiqueta')),
                        axisBorder: { show: false }, axisTicks: { show: false },
                        labels: { style: { colors: '#94a3b8', fontSize: '10px', fontWeight: 600 } }
                    },
                    yaxis: { show: false }, // Ocultar Y axis para limpiar el diseño como en la imagen
                    legend: { show: false }, // Ocultar leyenda
                    grid: { borderColor: '#f1f5f9', strokeDashArray: 0, xaxis: { lines: { show: false } }, yaxis: { lines: { show: true } } },
                    theme: { mode: 'light' }
                }).render();
            @endif

            // 3. Income Sources (Fuentes por Categoría - Pastel)
            @if(count($ventasPorCategoria) > 0)
                new ApexCharts(document.querySelector("#sourcesChart"), {
                    series: @json(collect($ventasPorCategoria)->pluck('total_ventas')),
                    labels: @json(collect($ventasPorCategoria)->pluck('categoria')),
                    chart: { type: 'pie', height: 260, ...fontConfig },
                    colors: ['#059669', '#cbd5e1', '#94a3b8', '#64748b', '#475569'], // Tonos de verde y grises (estilo imagen)
                    dataLabels: { enabled: false }, // Limpio
                    legend: { show: false }, // Ocultamos la leyenda estándar para que se vea más limpio
                    theme: { mode: 'light' },
                    stroke: { show: true, colors: '#ffffff', width: 3 }
                }).render();
            @endif
        });
    </script>
@endpush