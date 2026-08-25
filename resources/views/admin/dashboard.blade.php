@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<style>
    /* Asegurar que los tooltips de ApexCharts se vean bien en modo claro */
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
        border: 1px solid #e2e8f0; /* slate-200 */
        border-radius: 12px;
        box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
    }
</style>

<div class="space-y-6">
    
    <!-- Page Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Resumen del Dashboard</h2>
            <p class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">Métricas de rendimiento en tiempo real.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-700 shadow-xs">
                <span class="material-symbols-outlined text-[16px] text-slate-400">calendar_today</span>
                <span>Últimos 7 días</span>
            </div>
            <a href="{{ url('/') }}" target="_blank" class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-slate-800 transition-colors shadow-xs">
                <span class="material-symbols-outlined text-[16px]">storefront</span>
                <span>Ver Tienda</span>
            </a>
        </div>
    </div>

    <!-- 4 Essential KPI Cards in 1 Clean Executive Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        
        <!-- Card 1: Ventas Totales -->
        <div class="card-saas p-4 flex flex-col justify-between relative overflow-hidden hover:border-slate-300 transition-all">
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Ventas Totales</span>
                    <div class="text-2xl font-extrabold text-slate-900 tracking-tight mt-1">${{ number_format(array_sum($ventas7Dias), 0) }}</div>
                </div>
                <span class="px-2 py-0.5 rounded {{ $crecimientoVentas >= 0 ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-red-50 text-red-600 border border-red-200' }} text-[10px] font-bold">
                    {{ $crecimientoVentas >= 0 ? '+' : '' }}{{ $crecimientoVentas }}%
                </span>
            </div>
            <div class="mt-2 h-14 relative z-0 w-full -mx-2" id="spark1"></div>
        </div>

        <!-- Card 2: Total Pedidos -->
        <div class="card-saas p-4 flex flex-col justify-between relative overflow-hidden hover:border-slate-300 transition-all">
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total de Pedidos</span>
                    <div class="text-2xl font-extrabold text-slate-900 tracking-tight mt-1">{{ number_format(array_sum($pedidos7Dias)) }}</div>
                </div>
                <span class="px-2 py-0.5 rounded {{ $crecimientoPedidos >= 0 ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-red-50 text-red-600 border border-red-200' }} text-[10px] font-bold">
                    {{ $crecimientoPedidos >= 0 ? '+' : '' }}{{ $crecimientoPedidos }}%
                </span>
            </div>
            <div class="mt-2 h-14 relative z-0 w-full -mx-2" id="spark2"></div>
        </div>

        <!-- Card 3: Clientes Registrados -->
        <div class="card-saas p-4 flex flex-col justify-between relative overflow-hidden hover:border-slate-300 transition-all">
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nuevos Clientes</span>
                    <div class="text-2xl font-extrabold text-slate-900 tracking-tight mt-1">{{ array_sum($clientes7Dias) }}</div>
                </div>
                <span class="px-2 py-0.5 rounded {{ $crecimientoClientes >= 0 ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-red-50 text-red-600 border border-red-200' }} text-[10px] font-bold">
                    {{ $crecimientoClientes >= 0 ? '+' : '' }}{{ $crecimientoClientes }}%
                </span>
            </div>
            <div class="mt-2 h-14 relative z-0 w-full -mx-2" id="spark3"></div>
        </div>

        <!-- Card 4: Ticket Promedio -->
        <div class="card-saas p-4 flex flex-col justify-between relative overflow-hidden hover:border-slate-300 transition-all">
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Ticket Promedio</span>
                    <div class="text-2xl font-extrabold text-slate-900 tracking-tight mt-1">
                        ${{ array_sum($pedidos7Dias) > 0 ? number_format(array_sum($ventas7Dias)/array_sum($pedidos7Dias), 2) : '0.00' }}
                    </div>
                </div>
                <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-600 border border-blue-200 text-[10px] font-bold">
                    Al día
                </span>
            </div>
            <div class="mt-2 h-14 relative z-0 w-full -mx-2" id="spark4"></div>
        </div>

    </div>

    <!-- Charts Layout (Rendimiento y Actividad) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        
        <!-- Revenue Chart (Ventas) -->
        <div class="lg:col-span-2 card-saas p-5 flex flex-col justify-between">
            <div class="flex justify-between items-center mb-2">
                <h3 class="text-sm sm:text-base font-bold text-slate-900">Resumen de Ventas</h3>
                <div class="flex items-center gap-2 px-2.5 py-1 bg-slate-50 border border-slate-200 rounded text-xs font-semibold text-slate-600">
                    <span class="material-symbols-outlined text-[14px]">calendar_today</span>
                    <span>Últimos 6 Meses</span>
                </div>
            </div>

            <!-- Totales del Gráfico -->
            <div class="flex gap-6 mb-2 mt-2">
                <div>
                    <div class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-500 mb-0.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Ingresos
                    </div>
                    <div class="font-bold text-slate-900 text-lg">${{ number_format(array_sum($ventasMeses) / 1000, 1) }}k</div>
                </div>
                <div>
                    <div class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-500 mb-0.5">
                        <span class="w-2 h-2 rounded-full bg-slate-400"></span> Pedidos
                    </div>
                    <div class="font-bold text-slate-900 text-lg">{{ number_format(array_sum($ordenesMeses)/1000, 1) }}k</div>
                </div>
            </div>

            <!-- Chart Visual Area -->
            <div class="w-full relative z-0">
                <div id="mainChart"></div>
            </div>
        </div>

        <!-- Activity Feed (1 Col) -->
        <div class="card-saas p-5 flex flex-col">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-sm sm:text-base font-bold text-slate-900">Actividad Reciente</h3>
            </div>

            <div class="flex flex-col gap-4 relative flex-1">
                @forelse($transaccionesRecientes as $pedido)
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                            <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-900 truncate">
                                Nuevo Pedido {{ $pedido->numero_pedido }}
                            </p>
                            <p class="text-[11px] text-slate-500 truncate mt-0.5">
                                {{ $pedido->usuario ? $pedido->usuario->nombre_completo : 'Cliente' }} - {{ $pedido->creado_en->format('d M') }}
                            </p>
                        </div>
                        <div class="text-xs font-extrabold text-slate-900">
                            ${{ number_format($pedido->total, 0) }}
                        </div>
                    </div>
                @empty
                    <div class="text-center text-slate-500 text-xs py-10">
                        No hay actividad reciente.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Bottom Layout: Order Management -->
    <div class="card-saas p-5 flex flex-col">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-sm sm:text-base font-bold text-slate-900">Gestión de Pedidos</h3>
            <a href="{{ url('/admin/pedidos') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition-colors flex items-center gap-1">
                <span>Ver todos</span>
                <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="pb-3 pl-1">Nº Pedido</th>
                        <th class="pb-3">Cliente</th>
                        <th class="pb-3">Fecha</th>
                        <th class="pb-3 text-center">Estado</th>
                        <th class="pb-3 text-right">Pago</th>
                        <th class="pb-3 text-right">Total</th>
                        <th class="pb-3 pr-1 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-slate-100">
                    @forelse($transaccionesRecientes as $pedido)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 pl-1 font-bold text-slate-900">
                                {{ $pedido->numero_pedido }}
                            </td>
                            <td class="py-3 text-slate-600 font-medium">
                                {{ $pedido->usuario ? $pedido->usuario->nombre_completo : 'Cliente' }}
                            </td>
                            <td class="py-3 text-slate-500">
                                {{ $pedido->creado_en->format('M d, h:i A') }}
                            </td>
                            <td class="py-3 text-center">
                                @php
                                    $estadoNombre = $pedido->ultimoEstado->estado ?? 'pendiente';
                                    $estadoColor = 'slate';
                                    
                                    if(in_array($estadoNombre, ['completado', 'entregado', 'pagado'])) $estadoColor = 'emerald';
                                    elseif(in_array($estadoNombre, ['cancelado', 'rechazado'])) $estadoColor = 'red';
                                    elseif(in_array($estadoNombre, ['en_proceso', 'enviado'])) $estadoColor = 'purple';
                                    else $estadoColor = 'amber';
                                @endphp
                                <span class="px-2 py-0.5 rounded-full bg-{{$estadoColor}}-50 text-{{$estadoColor}}-700 border border-{{$estadoColor}}-200 text-[10px] font-bold">
                                    {{ ucfirst($estadoNombre) }}
                                </span>
                            </td>
                            <td class="py-3 text-right font-medium text-slate-500">
                                <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200 text-[10px] font-semibold">
                                    {{ $pedido->metodo_pago ?? 'Tarjeta' }}
                                </span>
                            </td>
                            <td class="py-3 text-right font-extrabold text-slate-900">
                                ${{ number_format($pedido->total, 2) }}
                            </td>
                            <td class="py-3 pr-1 text-center">
                                <a href="{{ url('/admin/pedidos/'.$pedido->id) }}" class="text-blue-600 hover:text-blue-800 font-semibold transition-colors">
                                    Ver/Gst.
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500 text-xs">
                                No hay pedidos registrados todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bottom Layout: Top Selling Products -->
    <div class="card-saas p-5 flex flex-col mb-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-sm sm:text-base font-bold text-slate-900">Productos Más Vendidos</h3>
            <a href="{{ url('/admin/productos') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 transition-colors flex items-center gap-1">
                <span>Ir al catálogo</span>
                <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="pb-3 w-10"></th>
                        <th class="pb-3">Producto</th>
                        <th class="pb-3">Categoría</th>
                        <th class="pb-3">Precio</th>
                        <th class="pb-3 text-center">Stock</th>
                        <th class="pb-3 text-center">Ventas Totales</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-slate-100">
                    @forelse($topProductos as $prod)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3">
                                <div class="w-9 h-9 rounded-lg bg-white flex items-center justify-center shrink-0 border border-slate-200 overflow-hidden shadow-xs">
                                    <img src="{{ $prod->imagen_url }}" class="w-full h-full object-cover" alt="img">
                                </div>
                            </td>
                            <td class="py-3">
                                <div class="font-bold text-slate-900">{{ $prod->nombre }}</div>
                            </td>
                            <td class="py-3 text-slate-500 font-medium">
                                {{ $prod->categoria->nombre ?? 'Sin categoría' }}
                            </td>
                            <td class="py-3 font-semibold text-slate-700">
                                ${{ number_format($prod->precio, 2) }}
                            </td>
                            <td class="py-3 text-center text-slate-500">
                                {{ $prod->stock }} disp.
                            </td>
                            <td class="py-3 text-center font-extrabold text-emerald-600">
                                {{ $prod->ventas_totales }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500 text-xs">
                                Aún no hay ventas de productos para calcular esta métrica.
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
(function() {
    function renderDashboardCharts() {
        // Vite carga app.js como módulo (diferido). Esperamos hasta que ApexCharts esté disponible en window.
        if (typeof window.ApexCharts === 'undefined') {
            setTimeout(renderDashboardCharts, 50);
            return;
        }

        const spark1 = document.querySelector("#spark1");
        if (!spark1 || spark1.dataset.rendered === "1") return; // Evitar duplicados

        // Marcar como renderizados y limpiar contenedores por si acaso (para navegación SPA)
        document.querySelectorAll("#spark1, #spark2, #spark3, #spark4, #mainChart").forEach(el => {
            if (el) {
                el.dataset.rendered = "1";
                el.innerHTML = ''; 
            }
        });

        // Definimos los datos pasados desde el backend
        const diasLabels = @json($diasLabels);
        const ventas7Dias = @json($ventas7Dias);
        const pedidos7Dias = @json($pedidos7Dias);
        const clientes7Dias = @json($clientes7Dias);
        const aov7Dias = @json($aov7Dias);

        const mesesLabels = @json($mesesLabels);
        const ventasMeses = @json($ventasMeses);
        const ordenesMeses = @json($ordenesMeses);

        // Opciones base para los Sparklines
        const sparklineOptions = {
            chart: {
                type: 'area',
                height: 60,
                sparkline: { enabled: true },
                animations: { enabled: false },
                fontFamily: "'Plus Jakarta Sans', sans-serif"
            },
            stroke: { curve: 'smooth', width: 2 },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0,
                    stops: [0, 100]
                }
            },
            tooltip: {
                fixed: { enabled: false },
                x: { show: false },
                y: { title: { formatter: function (seriesName) { return '' } } },
                marker: { show: false }
            }
        };

        // 1. Sparkline - Sales (Emerald)
        new window.ApexCharts(document.querySelector("#spark1"), {
            ...sparklineOptions,
            colors: ['#059669'], // emerald-600
            series: [{ name: 'Ventas', data: ventas7Dias }]
        }).render();

        // 2. Sparkline - Orders (Blue)
        new window.ApexCharts(document.querySelector("#spark2"), {
            ...sparklineOptions,
            colors: ['#2563eb'], // blue-600
            series: [{ name: 'Pedidos', data: pedidos7Dias }]
        }).render();

        // 3. Sparkline - Customers (Purple)
        new window.ApexCharts(document.querySelector("#spark3"), {
            ...sparklineOptions,
            colors: ['#9333ea'], // purple-600
            series: [{ name: 'Clientes', data: clientes7Dias }]
        }).render();

        // 4. Sparkline - AOV (Orange)
        new window.ApexCharts(document.querySelector("#spark4"), {
            ...sparklineOptions,
            colors: ['#ea580c'], // orange-600
            series: [{ name: 'Ticket Promedio', data: aov7Dias }]
        }).render();

        // 5. Main Chart (Sales Overview)
        const mainChartOptions = {
            series: [
                { name: 'Ingresos', data: ventasMeses },
                { name: 'Pedidos', data: ordenesMeses }
            ],
            chart: {
                height: 300,
                type: 'area',
                toolbar: { show: false },
                fontFamily: "'Plus Jakarta Sans', sans-serif",
                background: 'transparent'
            },
            colors: ['#10b981', '#cbd5e1'], // emerald-500, slate-300
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0,
                    stops: [0, 100]
                }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            xaxis: {
                categories: mesesLabels,
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: {
                    style: { colors: '#64748b', fontSize: '11px', fontWeight: 600 } // slate-500
                }
            },
            yaxis: {
                labels: {
                    style: { colors: '#64748b', fontSize: '11px', fontWeight: 600 },
                    formatter: function(val, index) {
                        return val >= 1000 ? (val/1000).toFixed(1) + 'k' : val;
                    }
                }
            },
            grid: {
                borderColor: '#e2e8f0', // slate-200
                strokeDashArray: 4,
                xaxis: { lines: { show: true } },
                yaxis: { lines: { show: true } }
            },
            legend: { show: false },
            theme: { mode: 'light' }
        };

        new window.ApexCharts(document.querySelector("#mainChart"), mainChartOptions).render();
    }

    // Ejecutar inicialmente (esperará a que ApexCharts cargue)
    renderDashboardCharts();

    // Re-ejecutar si Livewire hace una navegación SPA (wire:navigate)
    document.addEventListener('livewire:navigated', function() {
        document.querySelectorAll("#spark1, #spark2, #spark3, #spark4, #mainChart").forEach(el => {
            if (el) el.dataset.rendered = "0"; // Reset para permitir redibujado
        });
        renderDashboardCharts();
    });
})();
</script>
@endpush