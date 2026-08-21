@extends('layouts.admin')

@section('title', 'Reportes y Estadísticas')

@section('breadcrumbs')
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <span class="capitalize font-bold text-slate-900 truncate max-w-[90px] sm:max-w-none">Reportes</span>
@endsection

@push('styles')
    <style>
        .kpi-card {
            transition: all 0.3s ease;
        }
        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
    </style>
@endpush

@section('content')
<div class="space-y-6 max-w-7xl mx-auto">
    
    <!-- Header y Filtros -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
        <div>
            <h2 class="text-xl font-bold text-slate-800 tracking-tight">Reportes y Estadísticas</h2>
            <p class="text-sm text-slate-500 mt-1">Panel de control unificado con métricas en tiempo real.</p>
        </div>
        
        <form method="GET" action="{{ route('admin.reportes.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto" id="form-filtros">
            <div class="relative">
                <select name="tipo" class="pl-3 pr-10 py-2 bg-slate-50 border border-slate-200 text-slate-700 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 appearance-none shadow-sm cursor-pointer" onchange="document.getElementById('form-filtros').submit()">
                    <option value="mes" {{ $tipoFiltro === 'mes' ? 'selected' : '' }}>Este mes</option>
                    <option value="año" {{ $tipoFiltro === 'año' ? 'selected' : '' }}>Este año</option>
                    <option value="todos" {{ $tipoFiltro === 'todos' ? 'selected' : '' }}>Historico completo</option>
                    <option value="personalizado" {{ $tipoFiltro === 'personalizado' ? 'selected' : '' }} disabled>Personalizado...</option>
                </select>
                <span class="material-symbols-outlined absolute right-3 top-2.5 text-slate-400 text-[18px] pointer-events-none">expand_more</span>
            </div>

            <div class="flex gap-2 w-full md:w-auto">
                <button type="button" onclick="exportar('excel')" class="flex-1 md:flex-none flex items-center justify-center gap-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 px-4 py-2 rounded-xl text-sm font-semibold transition-colors border border-emerald-200 shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">table_view</span>
                    Excel
                </button>
                <button type="button" onclick="exportar('pdf')" class="flex-1 md:flex-none flex items-center justify-center gap-2 bg-rose-50 hover:bg-rose-100 text-rose-700 px-4 py-2 rounded-xl text-sm font-semibold transition-colors border border-rose-200 shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
                    PDF
                </button>
            </div>
        </form>
    </div>

    <!-- KPIs (Estilo idéntico al Dashboard) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
        <!-- KPI 1: Ventas Totales -->
        <div class="card-elevated rounded-xl p-5 hover:border-slate-300 transition-all flex flex-col justify-between bg-white border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between gap-3 mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Ingresos Totales</span>
                <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>
                </div>
            </div>
            <div class="my-1.5">
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    ${{ number_format($totalVentas, 2) }}
                </div>
            </div>
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="inline-flex items-center gap-1 font-semibold text-emerald-700">
                    <span class="material-symbols-outlined text-[14px]">trending_up</span>
                    <span>Facturado</span>
                </span>
                <span class="text-slate-400 font-medium">{{ ucfirst($tipoFiltro) }}</span>
            </div>
        </div>

        <!-- KPI 2: Total Pedidos -->
        <div class="card-elevated rounded-xl p-5 hover:border-slate-300 transition-all flex flex-col justify-between bg-white border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between gap-3 mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total de Pedidos</span>
                <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[18px]">shopping_cart</span>
                </div>
            </div>
            <div class="my-1.5">
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    {{ $numeroPedidos }}
                </div>
            </div>
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="inline-flex items-center gap-1 font-semibold text-slate-700">
                    <span class="material-symbols-outlined text-[14px] text-slate-500">done_all</span>
                    <span>Órdenes procesadas</span>
                </span>
                <span class="text-slate-400 font-medium">{{ ucfirst($tipoFiltro) }}</span>
            </div>
        </div>

        <!-- KPI 3: Ticket Promedio -->
        <div class="card-elevated rounded-xl p-5 hover:border-slate-300 transition-all flex flex-col justify-between bg-white border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between gap-3 mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Ticket Promedio</span>
                <div class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-700 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[18px]">receipt_long</span>
                </div>
            </div>
            <div class="my-1.5">
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    ${{ number_format($ticketPromedio, 2) }}
                </div>
            </div>
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="inline-flex items-center gap-1 font-semibold text-indigo-700">
                    <span class="material-symbols-outlined text-[14px]">sell</span>
                    <span>Por transacción</span>
                </span>
                <span class="text-slate-400 font-medium">{{ ucfirst($tipoFiltro) }}</span>
            </div>
        </div>
    </div>

    <!-- Dashboard: Todas las gráficas en una sola pantalla -->
    <div class="space-y-6">
        
        <!-- Fila 1: Gráfica Principal de Ventas -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-center gap-2 mb-6">
                <span class="material-symbols-outlined text-emerald-500">trending_up</span>
                <h3 class="text-lg font-bold text-slate-800">Evolución de Ventas</h3>
            </div>
            @if(count($ventasPorPeriodo) > 0)
                <div class="h-80 w-full relative">
                    <canvas id="ventasChart"></canvas>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <span class="material-symbols-outlined text-4xl text-slate-200 mb-2">analytics</span>
                    <p class="text-sm text-slate-500">No hay datos de ventas en este periodo.</p>
                </div>
            @endif
        </div>

        <!-- Fila 2: Categorías y Métodos de Pago -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <div class="flex items-center gap-2 mb-6">
                    <span class="material-symbols-outlined text-blue-500">category</span>
                    <h3 class="text-lg font-bold text-slate-800">Ventas por Categoría</h3>
                </div>
                @if(count($ventasPorCategoria) > 0)
                    <div class="h-64 relative">
                        <canvas id="categoriaChart"></canvas>
                    </div>
                @else
                    <p class="text-sm text-slate-500 text-center py-10">Sin datos de categorías.</p>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <div class="flex items-center gap-2 mb-6">
                    <span class="material-symbols-outlined text-indigo-500">account_balance_wallet</span>
                    <h3 class="text-lg font-bold text-slate-800">Métodos de Pago</h3>
                </div>
                @if(count($ventasPorMetodoPago) > 0)
                    <div class="h-64 relative">
                        <canvas id="metodoPagoChart"></canvas>
                    </div>
                @else
                    <p class="text-sm text-slate-500 text-center py-10">Sin datos de pagos.</p>
                @endif
            </div>

        </div>

        <!-- Fila 3: Productos y Clientes -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <div class="flex items-center gap-2 mb-6">
                    <span class="material-symbols-outlined text-amber-500">star</span>
                    <h3 class="text-lg font-bold text-slate-800">Productos Más Vendidos</h3>
                </div>
                @if($productosMasVendidos->count() > 0)
                    <div class="h-72 relative">
                        <canvas id="productosChart"></canvas>
                    </div>
                @else
                    <p class="text-sm text-slate-500 text-center py-10">Sin datos de productos.</p>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <div class="flex items-center gap-2 mb-6">
                    <span class="material-symbols-outlined text-violet-500">group</span>
                    <h3 class="text-lg font-bold text-slate-800">Clientes Frecuentes (Ingresos)</h3>
                </div>
                @if($clientesFrecuentes->count() > 0)
                    <div class="h-72 relative">
                        <canvas id="clientesChart"></canvas>
                    </div>
                @else
                    <p class="text-sm text-slate-500 text-center py-10">Sin datos de clientes.</p>
                @endif
            </div>

        </div>

        <!-- Fila 4: Stock Crítico -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <div class="flex items-center gap-2 mb-6">
                <span class="material-symbols-outlined text-rose-500">warning</span>
                <h3 class="text-lg font-bold text-slate-800">Stock Crítico vs Mínimo Permitido</h3>
            </div>
            @if($stockCritico->count() > 0)
                <div class="h-80 w-full relative">
                    <canvas id="stockChart"></canvas>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <span class="material-symbols-outlined text-4xl text-emerald-200 mb-2">check_circle</span>
                    <p class="text-sm text-slate-500">Todo el inventario está en niveles saludables.</p>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    Chart.defaults.font.family = "'Plus Jakarta Sans', 'Figtree', sans-serif";
    Chart.defaults.color = '#64748B';
    
    function exportar(formato) {
        const form = document.getElementById('form-filtros');
        const actionOriginal = form.action;
        
        // Exportamos siempre los datos completos ('ventas' usa la lógica original del controller para todo el periodo)
        // Ya no hay tabs, por lo que podemos crear un input hidden on the fly o usar la query principal
        const inputReporte = document.createElement('input');
        inputReporte.type = 'hidden';
        inputReporte.name = 'reporte';
        inputReporte.value = 'ventas'; // Fallback a general
        form.appendChild(inputReporte);
        
        if (formato === 'excel') {
            form.action = "{{ route('admin.reportes.exportar-excel') }}";
        } else if (formato === 'pdf') {
            form.action = "{{ route('admin.reportes.exportar-pdf') }}";
        }
        
        form.target = "_blank";
        form.submit();
        
        setTimeout(() => {
            form.action = actionOriginal;
            form.target = "_self";
            form.removeChild(inputReporte);
        }, 100);
    }

    document.addEventListener('DOMContentLoaded', () => {
        
        // 1. Gráfica de Ventas
        const ctxVentas = document.getElementById('ventasChart');
        if (ctxVentas) {
            new Chart(ctxVentas, {
                type: 'line',
                data: {
                    labels: @json(collect($ventasPorPeriodo)->pluck('etiqueta')),
                    datasets: [{
                        label: 'Ingresos ($)',
                        data: @json(collect($ventasPorPeriodo)->pluck('total')),
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 3,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#10B981',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, border: {display: false}, grid: {color: '#F1F5F9'} },
                        x: { border: {display: false}, grid: {display: false} }
                    }
                }
            });
        }

        // 2. Gráfica de Categorías (Bar)
        const ctxCategoria = document.getElementById('categoriaChart');
        if (ctxCategoria) {
            new Chart(ctxCategoria, {
                type: 'bar',
                data: {
                    labels: @json(collect($ventasPorCategoria)->pluck('categoria')),
                    datasets: [{
                        label: 'Ventas ($)',
                        data: @json(collect($ventasPorCategoria)->pluck('total_ventas')),
                        backgroundColor: '#3B82F6',
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, border: {display: false}, grid: {color: '#F1F5F9'} },
                        x: { border: {display: false}, grid: {display: false} }
                    }
                }
            });
        }

        // 3. Gráfica de Métodos de Pago (Doughnut)
        const ctxMetodo = document.getElementById('metodoPagoChart');
        if (ctxMetodo) {
            new Chart(ctxMetodo, {
                type: 'doughnut',
                data: {
                    labels: @json(collect($ventasPorMetodoPago)->pluck('metodo')),
                    datasets: [{
                        data: @json(collect($ventasPorMetodoPago)->pluck('total')),
                        backgroundColor: ['#10B981', '#3B82F6', '#F59E0B', '#6366F1', '#8B5CF6'],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false, cutout: '65%',
                    plugins: { legend: { position: 'right', labels: { usePointStyle: true, padding: 20 } } }
                }
            });
        }

        // 4. Gráfica de Productos Más Vendidos (Horizontal Bar)
        const ctxProductos = document.getElementById('productosChart');
        if (ctxProductos) {
            new Chart(ctxProductos, {
                type: 'bar',
                data: {
                    labels: @json($productosMasVendidos->pluck('nombre')),
                    datasets: [{
                        label: 'Ingresos Generados ($)',
                        data: @json($productosMasVendidos->pluck('ingresos_generados')),
                        backgroundColor: '#F59E0B',
                        borderRadius: 6,
                    }]
                },
                options: {
                    indexAxis: 'y', // Hace la barra horizontal
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { border: {display: false}, grid: {display: false} },
                        x: { beginAtZero: true, border: {display: false}, grid: {color: '#F1F5F9'} }
                    }
                }
            });
        }

        // 5. Gráfica de Clientes Frecuentes (Horizontal Bar o Vertical)
        const ctxClientes = document.getElementById('clientesChart');
        if (ctxClientes) {
            const nombresClientes = @json($clientesFrecuentes->map(fn($c) => $c->nombre . ' ' . $c->apellido));
            new Chart(ctxClientes, {
                type: 'bar',
                data: {
                    labels: nombresClientes,
                    datasets: [{
                        label: 'Total Gastado ($)',
                        data: @json($clientesFrecuentes->pluck('total_gastado')),
                        backgroundColor: '#8B5CF6',
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, border: {display: false}, grid: {color: '#F1F5F9'} },
                        x: { border: {display: false}, grid: {display: false} }
                    }
                }
            });
        }

        // 6. Gráfica de Stock Crítico (Bar Combinada)
        const ctxStock = document.getElementById('stockChart');
        if (ctxStock) {
            new Chart(ctxStock, {
                type: 'bar',
                data: {
                    labels: @json($stockCritico->pluck('nombre')),
                    datasets: [
                        {
                            label: 'Stock Actual',
                            data: @json($stockCritico->pluck('stock')),
                            backgroundColor: '#EF4444', // Red
                            borderRadius: 6,
                        },
                        {
                            label: 'Stock Mínimo Requerido',
                            data: @json($stockCritico->pluck('stock_minimo')),
                            backgroundColor: '#E2E8F0', // Slate 200 (referencia visual)
                            borderRadius: 6,
                        }
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'top', labels: { usePointStyle: true, padding: 20 } } },
                    scales: {
                        y: { beginAtZero: true, border: {display: false}, grid: {color: '#F1F5F9'} },
                        x: { border: {display: false}, grid: {display: false} }
                    }
                }
            });
        }

    });
</script>
@endpush
