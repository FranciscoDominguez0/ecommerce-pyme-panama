@extends('layouts.admin')

@section('title', 'Gestión de Cupones — PayMe Panamá')

@section('breadcrumbs')
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <span class="text-slate-600">Promociones</span>
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <span class="font-bold text-slate-900 truncate">Cupones</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-600 text-[24px]">local_offer</span>
                Gestión de Cupones
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Administra códigos de descuento, vigencias y límites de uso en la tienda.</p>
        </div>

        <a href="{{ route('admin.promociones.cupones.crear') }}" 
           class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold rounded-xl transition-all shadow-xs flex items-center justify-center gap-2 group shrink-0">
            <span class="material-symbols-outlined text-[18px] group-hover:scale-110 transition-transform">add</span>
            <span>Nuevo Cupón</span>
        </a>
    </div>

    <!-- Metrics Bento (3 KPI Cards) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Card 1: Total Cupones -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-emerald-100/50 rounded-full group-hover:scale-110 transition-transform duration-300"></div>
            <div class="flex items-center justify-between mb-3 relative z-10">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Cupones</span>
                <div class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-2xs">
                    <span class="material-symbols-outlined text-[18px]">local_offer</span>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 relative z-10">{{ number_format($totalCupones) }}</div>
            <div class="text-[11px] text-slate-500 font-medium mt-1 relative z-10">Registrados en el sistema</div>
        </div>

        <!-- Card 2: Cupones Activos -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-emerald-100/60 rounded-full group-hover:scale-110 transition-transform duration-300"></div>
            <div class="flex items-center justify-between mb-3 relative z-10">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Cupones Activos</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shadow-2xs">
                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 relative z-10">{{ number_format($cuponesActivosCount) }}</div>
            <div class="text-[11px] text-emerald-700 font-medium mt-1 relative z-10 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                Generando conversión en tienda
            </div>
        </div>

        <!-- Card 3: Descuentos Aplicados -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-2xs relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-amber-100/40 rounded-full group-hover:scale-110 transition-transform duration-300"></div>
            <div class="flex items-center justify-between mb-3 relative z-10">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Descuentos Ahorrados</span>
                <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shadow-2xs">
                    <span class="material-symbols-outlined text-[18px]">savings</span>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 relative z-10">${{ number_format($totalDescuentosMonto, 2) }}</div>
            <div class="text-[11px] text-slate-500 font-medium mt-1 relative z-10">Acumulado en ventas realizadas</div>
        </div>
    </div>

    <!-- Data Table & Filter Toolbar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        
        <!-- Table Toolbar -->
        <div class="p-4 border-b border-slate-100 flex flex-col md:flex-row gap-3 items-center justify-between bg-slate-50/50">
            <!-- Filter Pills -->
            <div class="flex items-center gap-1.5 overflow-x-auto w-full md:w-auto pb-1 md:pb-0">
                <a href="{{ route('admin.promociones.cupones', array_merge(request()->except('tipo', 'page'), ['tipo' => 'all'])) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ $filtroTipo === 'all' ? 'bg-slate-900 text-white shadow-2xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100' }}">
                    Todos los Cupones
                </a>
                <a href="{{ route('admin.promociones.cupones', array_merge(request()->except('tipo', 'page'), ['tipo' => 'porcentaje'])) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ $filtroTipo === 'porcentaje' ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100' }}">
                    Porcentaje (%)
                </a>
                <a href="{{ route('admin.promociones.cupones', array_merge(request()->except('tipo', 'page'), ['tipo' => 'monto_fijo'])) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ $filtroTipo === 'monto_fijo' ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100' }}">
                    Monto Fijo ($)
                </a>
                <a href="{{ route('admin.promociones.cupones', array_merge(request()->except('tipo', 'page'), ['tipo' => 'envio_gratis'])) }}"
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all {{ $filtroTipo === 'envio_gratis' ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100' }}">
                    Envío Gratis
                </a>
            </div>

            <!-- Search input -->
            <form action="{{ route('admin.promociones.cupones') }}" method="GET" class="relative w-full md:w-72">
                @if($filtroTipo !== 'all')
                    <input type="hidden" name="tipo" value="{{ $filtroTipo }}">
                @endif
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
                <input type="text" 
                       name="buscar" 
                       value="{{ $busqueda }}" 
                       placeholder="Buscar por código..." 
                       class="w-full pl-9 pr-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none">
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-4">Código</th>
                        <th class="py-3 px-4">Tipo & Valor</th>
                        <th class="py-3 px-4">Aplicación</th>
                        <th class="py-3 px-4">Vigencia</th>
                        <th class="py-3 px-4">Usos</th>
                        <th class="py-3 px-4">Estado</th>
                        <th class="py-3 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    @forelse($cupones as $cupon)
                        <tr class="hover:bg-slate-50/60 transition-colors group">
                            <!-- Código -->
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                <div class="inline-flex items-center gap-2 px-2.5 py-1 bg-slate-100 border border-slate-200 rounded-lg text-xs font-mono font-bold tracking-wide">
                                    <span>{{ $cupon->codigo }}</span>
                                    <button type="button" 
                                            onclick="copiarCodigo('{{ $cupon->codigo }}')" 
                                            class="text-slate-400 hover:text-emerald-600 transition-colors cursor-pointer"
                                            title="Copiar código">
                                        <span class="material-symbols-outlined text-[15px]">content_copy</span>
                                    </button>
                                </div>
                            </td>

                            <!-- Tipo & Valor -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2">
                                    @if($cupon->tipo === 'porcentaje')
                                        <span class="material-symbols-outlined text-emerald-600 text-[18px]">percent</span>
                                        <span class="font-bold text-slate-900">{{ number_format($cupon->valor, 0) }}% OFF</span>
                                    @elseif($cupon->tipo === 'monto_fijo')
                                        <span class="material-symbols-outlined text-emerald-600 text-[18px]">attach_money</span>
                                        <span class="font-bold text-slate-900">${{ number_format($cupon->valor, 2) }} OFF</span>
                                    @else
                                        <span class="material-symbols-outlined text-emerald-600 text-[18px]">local_shipping</span>
                                        <span class="font-bold text-slate-900">Envío Gratis</span>
                                    @endif
                                </div>
                                @if($cupon->monto_minimo)
                                    <span class="text-[10px] text-slate-400 block mt-0.5">Min. ${{ number_format($cupon->monto_minimo, 2) }}</span>
                                @endif
                            </td>

                            <!-- Aplicación -->
                            <td class="py-3.5 px-4 text-slate-600">
                                @if($cupon->aplica_a === 'catalogo' || $cupon->aplica_a === 'todo')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px] font-semibold">
                                        <span class="material-symbols-outlined text-[14px]">storefront</span>
                                        Todo el catálogo
                                    </span>
                                @elseif($cupon->aplica_a === 'categoria')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px] font-semibold truncate max-w-[180px]">
                                        <span class="material-symbols-outlined text-[14px]">category</span>
                                        {{ $cupon->categoria ? $cupon->categoria->nombre : 'Categoría' }}
                                    </span>
                                @elseif($cupon->aplica_a === 'producto')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px] font-semibold truncate max-w-[180px]">
                                        <span class="material-symbols-outlined text-[14px]">inventory_2</span>
                                        {{ $cupon->producto ? $cupon->producto->nombre : 'Producto' }}
                                    </span>
                                @endif
                            </td>

                            <!-- Vigencia -->
                            <td class="py-3.5 px-4">
                                <div class="text-[11px] font-medium text-slate-800">
                                    {{ $cupon->inicio_en ? $cupon->inicio_en->format('d/m/Y') : 'Inmediata' }}
                                    <span class="text-slate-400 mx-0.5">&rarr;</span>
                                    {{ $cupon->fin_en ? $cupon->fin_en->format('d/m/Y') : 'Indefinida' }}
                                </div>
                                @if($cupon->estaVencido())
                                    <span class="text-[10px] font-bold text-rose-500 block">Expiró</span>
                                @endif
                            </td>

                            <!-- Usos -->
                            <td class="py-3.5 px-4">
                                @php
                                    $maxUsos = $cupon->maximo_usos_total;
                                    $usosAct = $cupon->usos_actuales;
                                    $pctUso = ($maxUsos && $maxUsos > 0) ? min(100, round(($usosAct / $maxUsos) * 100)) : 0;
                                @endphp
                                <div class="flex items-center gap-2">
                                    @if($maxUsos && $maxUsos > 0)
                                        <div class="w-16 h-1.5 bg-slate-100 rounded-full overflow-hidden shrink-0">
                                            <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $pctUso }}%;"></div>
                                        </div>
                                        <span class="text-xs font-mono font-bold text-slate-800">{{ $usosAct }}/{{ $maxUsos }}</span>
                                    @else
                                        <span class="text-xs font-mono font-bold text-slate-800">{{ $usosAct }}</span>
                                        <span class="text-[10px] text-slate-400">ilimitado</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Estado -->
                            <td class="py-3.5 px-4">
                                @php $estado = $cupon->obtenerEstadoTexto(); @endphp
                                @if($estado === 'Activo')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Activo
                                    </span>
                                @elseif($estado === 'Inactivo')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Inactivo
                                    </span>
                                @elseif($estado === 'Vencido')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Vencido
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Agotado
                                    </span>
                                @endif
                            </td>

                            <!-- Acciones -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <!-- Editar -->
                                    <a href="{{ route('admin.promociones.cupones.editar', $cupon->id) }}" 
                                       class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors"
                                       title="Editar cupón">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </a>

                                    <!-- Toggle Estado -->
                                    <form action="{{ route('admin.promociones.cupones.toggle', $cupon->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="p-1.5 {{ $cupon->activo ? 'text-emerald-600 hover:bg-emerald-50' : 'text-slate-400 hover:bg-slate-100' }} rounded-lg transition-colors"
                                                title="{{ $cupon->activo ? 'Desactivar' : 'Activar' }}">
                                            <span class="material-symbols-outlined text-[18px]">{{ $cupon->activo ? 'toggle_on' : 'toggle_off' }}</span>
                                        </button>
                                    </form>

                                    <!-- Eliminar -->
                                    <form action="{{ route('admin.promociones.cupones.eliminar', $cupon->id) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('¿Estás seguro de eliminar este cupón?');" 
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"
                                                title="Eliminar cupón">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    <span class="material-symbols-outlined text-[24px]">local_offer</span>
                                </div>
                                <p class="text-sm font-semibold text-slate-700 mb-1">No se encontraron cupones</p>
                                <p class="text-xs text-slate-400 mb-4">Crea tu primer código promocional para incentivar ventas.</p>
                                <a href="{{ route('admin.promociones.cupones.crear') }}" class="px-4 py-2 bg-emerald-600 text-white text-xs font-bold rounded-xl hover:bg-emerald-700 transition-colors inline-flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px]">add</span>
                                    Crear Cupón
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($cupones->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
                {{ $cupones->links() }}
            </div>
        @endif
    </div>

</div>

@push('scripts')
<script>
    function copiarCodigo(texto) {
        navigator.clipboard.writeText(texto).then(() => {
            if (window.showToast) {
                window.showToast(`Código "${texto}" copiado al portapapeles.`, 'success');
            } else {
                alert(`Código "${texto}" copiado.`);
            }
        });
    }
</script>
@endpush
@endsection
