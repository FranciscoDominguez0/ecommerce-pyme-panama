@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    
    <!-- Page Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200/80">
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Dashboard General</h2>
            <p class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">Métricas de rendimiento y operaciones en tiempo real.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-700 shadow-xs">
                <span class="material-symbols-outlined text-[16px] text-slate-400">calendar_today</span>
                <span>{{ \Carbon\Carbon::now()->locale('es')->isoFormat('MMMM YYYY') }}</span>
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
        <div class="card-elevated rounded-xl p-5 hover:border-slate-300 transition-all flex flex-col justify-between">
            <div class="flex items-center justify-between gap-3 mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Ventas Totales</span>
                <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[18px]">account_balance_wallet</span>
                </div>
            </div>
            <div class="my-1.5">
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    ${{ number_format($ventasTotales, 2) }}
                </div>
            </div>
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="inline-flex items-center gap-1 font-semibold text-emerald-700">
                    <span class="material-symbols-outlined text-[14px]">trending_up</span>
                    <span>Facturado</span>
                </span>
                <span class="text-slate-400 font-medium">Histórico</span>
            </div>
        </div>

        <!-- Card 2: Total Pedidos -->
        <div class="card-elevated rounded-xl p-5 hover:border-slate-300 transition-all flex flex-col justify-between">
            <div class="flex items-center justify-between gap-3 mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total de Pedidos</span>
                <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[18px]">shopping_cart</span>
                </div>
            </div>
            <div class="my-1.5">
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    {{ $totalPedidos }}
                </div>
            </div>
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="inline-flex items-center gap-1 font-semibold text-slate-700">
                    <span class="material-symbols-outlined text-[14px] text-slate-500">done_all</span>
                    <span>Órdenes procesadas</span>
                </span>
                <span class="text-slate-400 font-medium">Global</span>
            </div>
        </div>

        <!-- Card 3: Pedidos Pendientes -->
        <div class="card-elevated rounded-xl p-5 hover:border-slate-300 transition-all flex flex-col justify-between">
            <div class="flex items-center justify-between gap-3 mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pendientes</span>
                <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[18px]">pending_actions</span>
                </div>
            </div>
            <div class="my-1.5">
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    {{ $pedidosPendientes }}
                </div>
            </div>
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                @if($pedidosPendientes > 0)
                    <span class="inline-flex items-center gap-1.5 font-semibold text-amber-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 inline-block animate-pulse"></span>
                        <span>Requiere atención</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 font-semibold text-emerald-700">
                        <span class="material-symbols-outlined text-[14px]">check_circle</span>
                        <span>Al día</span>
                    </span>
                @endif
                <span class="text-slate-400 font-medium">Estado</span>
            </div>
        </div>

        <!-- Card 4: Clientes Registrados -->
        <div class="card-elevated rounded-xl p-5 hover:border-slate-300 transition-all flex flex-col justify-between">
            <div class="flex items-center justify-between gap-3 mb-2">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Clientes</span>
                <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[18px]">group</span>
                </div>
            </div>
            <div class="my-1.5">
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                    {{ $totalClientes }}
                </div>
            </div>
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="inline-flex items-center gap-1 font-semibold text-emerald-700">
                    <span class="material-symbols-outlined text-[14px]">trending_up</span>
                    <span>+{{ $clientesNuevosMes }} este mes</span>
                </span>
                <span class="text-slate-400 font-medium">Usuarios</span>
            </div>
        </div>

    </div>

    <!-- Charts Layout (Rendimiento y Crecimiento) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        
        <!-- Revenue Chart (Ventas) -->
        <div class="card-elevated rounded-xl p-5 sm:p-6 flex flex-col justify-between">
            <div class="flex justify-between items-center mb-5">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">Rendimiento de Facturación</h3>
                    <p class="text-xs text-slate-400">Histórico de ventas (Últimos 6 meses)</p>
                </div>
                <span class="px-2.5 py-1 bg-slate-100 text-slate-800 text-xs font-semibold rounded-md">
                    ${{ number_format($ventasTotales, 2) }}
                </span>
            </div>

            <!-- Chart Visual Area -->
            <div class="relative min-h-[200px] w-full pt-3 pr-2">
                <div class="absolute left-0 h-full flex flex-col justify-between text-xs font-medium text-slate-400 py-1">
                    <span>${{ number_format(max(max($ventasMeses ?? [0]), 100), 0) }}</span>
                    <span>${{ number_format(max(max($ventasMeses ?? [0]), 100) / 2, 0) }}</span>
                    <span>$0</span>
                </div>
                <div class="absolute inset-0 pl-14 flex flex-col justify-between pointer-events-none">
                    <div class="w-full border-t border-dashed border-slate-200"></div>
                    <div class="w-full border-t border-dashed border-slate-200"></div>
                    <div class="w-full border-t border-slate-200"></div>
                </div>
                
                <!-- SVG Line Chart (Charcoal Tone) -->
                <svg class="pl-14 h-40 w-full overflow-visible" preserveAspectRatio="none" viewBox="0 0 100 100">
                    <defs>
                        <linearGradient id="chartGradient1" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#0f172a" stop-opacity="0.10"></stop>
                            <stop offset="100%" stop-color="#0f172a" stop-opacity="0.0"></stop>
                        </linearGradient>
                    </defs>
                    @php
                        $maxV = max(max($ventasMeses ?? [0]), 1);
                        $points = [];
                        foreach ($ventasMeses as $idx => $val) {
                            $x = $idx * (100 / (count($ventasMeses) - 1 ?: 1));
                            $y = 90 - (($val / $maxV) * 80);
                            $points[] = "$x,$y";
                        }
                        $pointsStr = implode(" ", $points);
                    @endphp
                    <polygon points="0,95 {{ $pointsStr }} 100,95" fill="url(#chartGradient1)" />
                    <polyline fill="none" stroke="#0f172a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" points="{{ $pointsStr }}" />
                    @foreach($points as $pt)
                        @php [$px, $py] = explode(',', $pt); @endphp
                        <circle cx="{{ $px }}" cy="{{ $py }}" r="3" fill="#0f172a" stroke="#ffffff" stroke-width="2"></circle>
                    @endforeach
                </svg>
            </div>

            <!-- X Axis Labels -->
            <div class="flex justify-between text-xs font-medium text-slate-500 mt-4 pl-14">
                @foreach($mesesLabels as $label)
                    <span>{{ ucfirst($label) }}</span>
                @endforeach
            </div>
        </div>

        <!-- Customer Growth Chart (Clientes) -->
        <div class="card-elevated rounded-xl p-5 sm:p-6 flex flex-col justify-between">
            <div class="flex justify-between items-center mb-5">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">Crecimiento de Clientes</h3>
                    <p class="text-xs text-slate-400">Total acumulado de usuarios registrados</p>
                </div>
                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-800 text-xs font-semibold rounded-md border border-emerald-100">
                    {{ $totalClientes }} Clientes
                </span>
            </div>

            <!-- Chart Visual Area -->
            <div class="relative min-h-[200px] w-full pt-3 pr-2">
                <div class="absolute left-0 h-full flex flex-col justify-between text-xs font-medium text-slate-400 py-1">
                    <span>{{ max(max($clientesMeses ?? [0]), 10) }}</span>
                    <span>{{ round(max(max($clientesMeses ?? [0]), 10) / 2) }}</span>
                    <span>0</span>
                </div>
                <div class="absolute inset-0 pl-10 flex flex-col justify-between pointer-events-none">
                    <div class="w-full border-t border-dashed border-slate-200"></div>
                    <div class="w-full border-t border-dashed border-slate-200"></div>
                    <div class="w-full border-t border-slate-200"></div>
                </div>
                
                <!-- SVG Line Chart (Emerald Growth) -->
                <svg class="pl-10 h-40 w-full overflow-visible" preserveAspectRatio="none" viewBox="0 0 100 100">
                    <defs>
                        <linearGradient id="chartGradient2" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" stop-color="#059669" stop-opacity="0.10"></stop>
                            <stop offset="100%" stop-color="#059669" stop-opacity="0.0"></stop>
                        </linearGradient>
                    </defs>
                    @php
                        $maxC = max(max($clientesMeses ?? [0]), 1);
                        $cPoints = [];
                        foreach ($clientesMeses as $idx => $val) {
                            $x = $idx * (100 / (count($clientesMeses) - 1 ?: 1));
                            $y = 90 - (($val / $maxC) * 80);
                            $cPoints[] = "$x,$y";
                        }
                        $cPointsStr = implode(" ", $cPoints);
                    @endphp
                    <polygon points="0,95 {{ $cPointsStr }} 100,95" fill="url(#chartGradient2)" />
                    <polyline fill="none" stroke="#059669" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" points="{{ $cPointsStr }}" />
                    @foreach($cPoints as $pt)
                        @php [$px, $py] = explode(',', $pt); @endphp
                        <circle cx="{{ $px }}" cy="{{ $py }}" r="3" fill="#059669" stroke="#ffffff" stroke-width="2"></circle>
                    @endforeach
                </svg>
            </div>

            <!-- X Axis Labels -->
            <div class="flex justify-between text-xs font-medium text-slate-500 mt-4 pl-10">
                @foreach($mesesLabels as $label)
                    <span>{{ ucfirst($label) }}</span>
                @endforeach
            </div>
        </div>

    </div>

    <!-- Bottom Layout: Recent Transactions & Activity Feed -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        
        <!-- Recent Orders / Transactions Table (2 Cols) -->
        <div class="lg:col-span-2 card-elevated rounded-xl p-5 sm:p-6 flex flex-col">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">Transacciones Recientes</h3>
                    <p class="text-xs text-slate-400">Últimos pedidos registrados en el sistema</p>
                </div>
                <a href="{{ url('/admin/pedidos') }}" class="text-slate-900 hover:text-emerald-700 text-xs font-semibold flex items-center gap-1 transition-colors">
                    <span>Ver todas</span>
                    <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                </a>
            </div>

            <div class="flex-1 overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                            <th class="pb-2.5">Cliente / Pedido</th>
                            <th class="pb-2.5">Método</th>
                            <th class="pb-2.5 text-right">Monto</th>
                            <th class="pb-2.5 text-center">Estado</th>
                            <th class="pb-2.5 text-right">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs divide-y divide-slate-100">
                        @forelse($transaccionesRecientes as $pedido)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-2.5">
                                    <div class="font-semibold text-slate-900">
                                        {{ $pedido->usuario ? $pedido->usuario->nombre_completo : 'Cliente' }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 font-mono">
                                        #{{ $pedido->numero_pedido ?? 'PED-' . str_pad($pedido->id, 5, '0', STR_PAD_LEFT) }}
                                    </div>
                                </td>
                                <td class="py-2.5">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-medium text-[11px]">
                                        {{ $pedido->metodo_pago ?? 'Tarjeta/Yappy' }}
                                    </span>
                                </td>
                                <td class="py-2.5 text-right font-bold text-slate-900">
                                    ${{ number_format($pedido->total, 2) }}
                                </td>
                                <td class="py-2.5 text-center">
                                    @php
                                        $estadoNombre = $pedido->ultimoEstado->estado ?? 'pendiente';
                                    @endphp
                                    @if(in_array($estadoNombre, ['completado', 'entregado', 'pagado']))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">
                                            Completado
                                        </span>
                                    @elseif(in_array($estadoNombre, ['cancelado', 'rechazado']))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-red-50 text-red-700 border border-red-100">
                                            Cancelado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-100">
                                            {{ ucfirst($estadoNombre) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-2.5 text-right text-slate-400 font-medium text-[11px]">
                                    {{ $pedido->creado_en ? $pedido->creado_en->diffForHumans() : 'Reciente' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400">
                                    <span class="material-symbols-outlined text-3xl text-slate-300 block mb-1">shopping_bag</span>
                                    <p class="font-semibold text-slate-600 text-xs">No hay transacciones registradas todavía</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Los nuevos pedidos de tus clientes aparecerán aquí automáticamente.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Activity Feed (1 Col) -->
        <div class="card-elevated rounded-xl p-5 sm:p-6 flex flex-col">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">Actividad del Sistema</h3>
                    <p class="text-xs text-slate-400">Eventos de seguridad y accesos</p>
                </div>
            </div>

            <div class="flex flex-col gap-3.5 relative flex-1">
                <!-- Vertical Line -->
                <div class="absolute left-3 top-2 bottom-2 w-px bg-slate-200"></div>

                @forelse($actividadesRecientes as $actividad)
                    <div class="flex gap-3 relative z-10">
                        <div class="w-6 h-6 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center shrink-0 border border-slate-200">
                            <span class="material-symbols-outlined text-[13px]">security</span>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-slate-900 leading-tight">
                                {{ $actividad->accion }}: {{ $actividad->descripcion }}
                            </p>
                            <p class="text-[10px] text-slate-400 mt-0.5">
                                {{ $actividad->creado_en ? \Carbon\Carbon::parse($actividad->creado_en)->diffForHumans() : 'Hace un momento' }}
                            </p>
                        </div>
                    </div>
                @empty
                    <!-- Default System Events -->
                    <div class="flex gap-3 relative z-10">
                        <div class="w-6 h-6 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-200">
                            <span class="material-symbols-outlined text-[13px]">check_circle</span>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-slate-900 leading-tight">Sistema Iniciado</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Base de datos PostgreSQL conectada</p>
                        </div>
                    </div>

                    <div class="flex gap-3 relative z-10">
                        <div class="w-6 h-6 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center shrink-0 border border-slate-200">
                            <span class="material-symbols-outlined text-[13px]">verified_user</span>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-slate-900 leading-tight">Seguridad Spatie</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Roles y permisos activos</p>
                        </div>
                    </div>

                    <div class="flex gap-3 relative z-10">
                        <div class="w-6 h-6 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center shrink-0 border border-slate-200">
                            <span class="material-symbols-outlined text-[13px]">person</span>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-slate-900 leading-tight">Admin Autenticado</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">{{ Auth::user()->email ?? 'dominguezf225@gmail.com' }}</p>
                        </div>
                    </div>
                @endforelse
            </div>

            <a href="{{ url('/admin/auditoria') }}" class="mt-4 w-full py-2 border border-slate-200 rounded-lg text-slate-700 font-semibold text-xs hover:bg-slate-50 text-center transition-colors">
                Ver Registro de Auditoría
            </a>
        </div>

    </div>

</div>
@endsection
