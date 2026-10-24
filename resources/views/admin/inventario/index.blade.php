@extends('layouts.admin')

@section('title', isset($vista) && $vista === 'stock' ? 'Inventario — Stock Actual' : 'Inventario — Historial de Movimientos')

@section('breadcrumbs')
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <a href="{{ route('admin.inventario.index') }}" class="font-medium text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:text-slate-300 truncate">Inventario</a>
    @if(isset($vista) && $vista === 'stock')
        <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
        <span class="font-bold text-slate-900 dark:text-white truncate">Stock Actual</span>
    @else
        <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
        <span class="font-bold text-slate-900 dark:text-white truncate">Historial</span>
    @endif
@endsection

@section('content')
<div class="space-y-6 w-full min-w-0 max-w-full">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200 dark:border-gray-700/80">
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Gestión de Inventario</h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium mt-0.5">Controla el stock, registra movimientos y consulta el historial de cambios.</p>
        </div>
        <div class="flex items-center gap-2">
            {{-- Dropdown: Registrar movimiento --}}
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button @click="open = !open"
                        class="flex items-center gap-1.5 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-bold transition-all shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    <span>Registrar movimiento</span>
                    <span class="material-symbols-outlined text-[16px]" :class="open ? 'rotate-180' : ''" style="transition: transform 0.15s">expand_more</span>
                </button>
                <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                     class="absolute right-0 mt-2 w-52 bg-white dark:bg-[#181a1b] border border-slate-200 dark:border-gray-700 rounded-xl shadow-lg z-50 overflow-hidden">
                    <a href="{{ route('admin.inventario.entrada.form') }}"
                       class="flex items-center gap-3 px-4 py-3 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-emerald-50 hover:text-emerald-700 dark:hover:bg-emerald-900/30 dark:hover:text-emerald-400 transition-colors">
                        <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-500 text-[18px]">south_east</span>
                        Registrar Entrada
                    </a>
                    <a href="{{ route('admin.inventario.salida.form') }}"
                       class="flex items-center gap-3 px-4 py-3 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-900/30 dark:hover:text-red-400 transition-colors">
                        <span class="material-symbols-outlined text-red-500 text-[18px]">north_east</span>
                        Registrar Salida
                    </a>
                    <div class="border-t border-slate-100 dark:border-gray-700"></div>
                    <a href="{{ route('admin.inventario.ajuste.form') }}"
                       class="flex items-center gap-3 px-4 py-3 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-amber-50 hover:text-amber-700 dark:hover:bg-amber-900/30 dark:hover:text-amber-400 transition-colors">
                        <span class="material-symbols-outlined text-amber-500 text-[18px]">sync_alt</span>
                        Ajuste Manual
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="card-elevated p-4 rounded-xl flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Stock Total</p>
                    <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">{{ number_format($kpis['stockTotal']) }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-transparent text-slate-600 dark:text-slate-400 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">inventory_2</span>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                <span class="w-2 h-2 rounded-full bg-slate-400 inline-block"></span>
                <span>Unidades en catálogo</span>
            </div>
        </div>

        <div class="card-elevated p-4 rounded-xl flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Stock Bajo / Crítico</p>
                    <h3 class="text-2xl font-extrabold {{ $kpis['stockBajo'] > 0 ? 'text-red-600' : 'text-emerald-600' }} mt-1">{{ $kpis['stockBajo'] }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl {{ $kpis['stockBajo'] > 0 ? 'bg-red-50 text-red-500' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">{{ $kpis['stockBajo'] > 0 ? 'warning' : 'check_circle' }}</span>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] {{ $kpis['stockBajo'] > 0 ? 'text-red-500' : 'text-emerald-600' }} font-medium">
                @if($kpis['stockBajo'] > 0)
                    <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse inline-block"></span>
                    <span>Requieren atención</span>
                @else
                    <span class="material-symbols-outlined text-[14px]">check</span>
                    <span>Todo en stock correcto</span>
                @endif
            </div>
        </div>

        <div class="card-elevated p-4 rounded-xl flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Entradas (7 días)</p>
                    <h3 class="text-2xl font-extrabold text-emerald-600 mt-1">+{{ number_format($kpis['entradasSiete']) }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">south_east</span>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                <span>Unidades recibidas</span>
            </div>
        </div>

        <div class="card-elevated p-4 rounded-xl flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Salidas (7 días)</p>
                    <h3 class="text-2xl font-extrabold text-slate-700 dark:text-slate-300 mt-1">-{{ number_format($kpis['salidasSiete']) }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-transparent text-slate-500 dark:text-slate-400 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">north_east</span>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                <span class="w-2 h-2 rounded-full bg-slate-400 inline-block"></span>
                <span>Unidades despachadas</span>
            </div>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="flex items-center gap-1 border-b border-slate-200 dark:border-gray-700">
        <a href="{{ route('admin.inventario.index') }}"
           class="px-4 py-2.5 text-xs font-bold transition-all border-b-2 {{ !isset($vista) || $vista !== 'stock' ? 'border-slate-900 text-slate-900 dark:text-white' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:text-slate-300' }}">
            <span class="flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">history</span>
                Historial de Movimientos
            </span>
        </a>
        <a href="{{ route('admin.inventario.stock') }}"
           class="px-4 py-2.5 text-xs font-bold transition-all border-b-2 {{ isset($vista) && $vista === 'stock' ? 'border-slate-900 text-slate-900 dark:text-white' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:text-slate-300' }}">
            <span class="flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">inventory_2</span>
                Stock Actual
            </span>
        </a>
    </div>

    @if(!isset($vista) || $vista !== 'stock')
        {{-- ═══════════ HISTORIAL DE MOVIMIENTOS ═══════════ --}}
        <div class="card-elevated rounded-xl overflow-hidden">
            {{-- Filter bar --}}
            <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-gray-700 bg-slate-50 dark:bg-transparent/50 flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
                <h3 class="text-sm font-extrabold text-slate-800 dark:text-gray-100">Historial de Movimientos</h3>
                <form method="GET" action="{{ route('admin.inventario.index') }}" class="flex flex-wrap items-center gap-2 w-full sm:w-auto" x-data="adminAutoFilter">
                    {{-- Search --}}
                    <div class="flex items-center bg-white dark:bg-[#181a1b] border border-slate-200 dark:border-gray-700 rounded-lg px-3 py-1.5 focus-within:border-slate-400 w-full sm:w-52">
                        <span class="material-symbols-outlined text-slate-400 text-[16px] mr-1.5">search</span>
                        <input type="text" name="q" value="{{ request('q') }}" x-on:input="autoSubmit()"
                               placeholder="Buscar producto o motivo…"
                               class="bg-transparent border-none focus:ring-0 text-xs text-slate-700 dark:text-slate-300 w-full p-0 placeholder-slate-400 dark:placeholder-gray-500">
                    </div>
                    {{-- Tipo --}}
                    <select name="tipo" x-on:change="autoSubmit()"
                            class="text-xs border border-slate-200 dark:border-gray-700 rounded-lg px-3 py-1.5 bg-white dark:bg-[#181a1b] text-slate-700 dark:text-slate-200 focus:border-slate-400 focus:ring-0 outline-none cursor-pointer">
                        <option value="">Todos los tipos</option>
                        <option value="entrada" {{ request('tipo') === 'entrada' ? 'selected' : '' }}>Entradas</option>
                        <option value="salida"  {{ request('tipo') === 'salida'  ? 'selected' : '' }}>Salidas</option>
                        <option value="ajuste"  {{ request('tipo') === 'ajuste'  ? 'selected' : '' }}>Ajustes</option>
                    </select>
                    {{-- Fecha desde --}}
                    <input type="date" name="desde" value="{{ request('desde') }}" x-on:change="autoSubmit()"
                           class="text-xs border border-slate-200 dark:border-gray-700 rounded-lg px-3 py-1.5 bg-white dark:bg-[#121415] text-slate-700 dark:text-slate-300 focus:border-slate-400 focus:ring-0 outline-none">
                    {{-- Fecha hasta --}}
                    <input type="date" name="hasta" value="{{ request('hasta') }}" x-on:change="autoSubmit()"
                           class="text-xs border border-slate-200 dark:border-gray-700 rounded-lg px-3 py-1.5 bg-white dark:bg-[#121415] text-slate-700 dark:text-slate-300 focus:border-slate-400 focus:ring-0 outline-none">
                    @if(request()->hasAny(['q', 'tipo', 'desde', 'hasta']))
                        <a href="{{ route('admin.inventario.index') }}" class="px-3 py-1.5 text-xs text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:text-slate-300 font-medium">Limpiar</a>
                    @endif
                </form>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left min-w-[760px]">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-gray-700 text-[11px] font-semibold text-slate-400 uppercase tracking-wider bg-slate-50 dark:bg-transparent/50">
                            <th class="px-5 py-3.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Fecha</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Producto</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tipo</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Movimiento</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Motivo</th>
                            <th class="px-4 py-3.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center w-16">Detalle</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-gray-700/50 text-xs">
                        @forelse($movimientos as $mov)
                            <tr class="hover:bg-slate-50/80 dark:bg-transparent dark:hover:bg-gray-700/30 transition-colors group">
                                {{-- Fecha --}}
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="font-medium text-slate-700 dark:text-slate-300 text-xs">{{ $mov->creado_en->format('d/m/Y') }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $mov->creado_en->format('h:i A') }}</div>
                                </td>

                                {{-- Producto --}}
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg border border-slate-200 dark:border-gray-700 overflow-hidden flex-shrink-0 bg-slate-100 dark:bg-slate-800 flex items-center justify-center shadow-2xs">
                                            @if($mov->producto?->imagen_url)
                                                <img src="{{ $mov->producto->imagen_url }}" alt="{{ $mov->producto->nombre }}" class="w-full h-full object-cover">
                                            @else
                                                <span class="material-symbols-outlined text-slate-400 text-[18px]">inventory_2</span>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-semibold text-slate-800 dark:text-gray-100 truncate max-w-[240px]" title="{{ $mov->producto?->nombre }}">
                                                {{ $mov->producto?->nombre ?? '—' }}
                                            </div>
                                            @if($mov->texto_variante)
                                                <div class="text-[11px] text-slate-400 mt-0.5 truncate max-w-[240px]">
                                                    {{ $mov->texto_variante }}
                                                </div>
                                            @elseif($mov->producto?->sku)
                                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $mov->producto->sku }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Tipo badge --}}
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    @if($mov->tipo === 'entrada')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800/60 uppercase tracking-wide">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Entrada
                                        </span>
                                    @elseif($mov->tipo === 'salida')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-100 dark:border-rose-800/60 uppercase tracking-wide">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Salida
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-100 dark:border-amber-800/60 uppercase tracking-wide">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Ajuste
                                        </span>
                                    @endif
                                </td>

                                {{-- Movimiento (Cantidad + Stock resultante) --}}
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="font-extrabold text-xs tabular-nums {{ $mov->tipo === 'entrada' ? 'text-emerald-600 dark:text-emerald-400' : ($mov->tipo === 'salida' ? 'text-rose-600 dark:text-rose-400' : 'text-amber-600 dark:text-amber-400') }}">
                                            {{ $mov->tipo === 'entrada' ? '+' : ($mov->tipo === 'salida' ? '-' : '±') }}{{ $mov->cantidad }}
                                        </span>
                                        <span class="text-slate-300 dark:text-gray-600">·</span>
                                        <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400 tabular-nums">
                                            Stock: <strong class="text-slate-700 dark:text-slate-300 font-bold">{{ $mov->stock_despues }}</strong>
                                        </span>
                                    </div>
                                </td>

                                {{-- Motivo --}}
                                <td class="px-5 py-3.5 text-slate-600 dark:text-slate-400">
                                    <div class="truncate max-w-[220px] font-medium" title="{{ $mov->motivo }}">{{ $mov->motivo }}</div>
                                </td>

                                {{-- Detalle (Botón ojito) --}}
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    <button type="button"
                                            data-movimiento='@json($mov->payload_modal)'
                                            onclick="window.abrirModalDetalleMovimiento(JSON.parse(this.dataset.movimiento))"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                                            title="Ver detalles del movimiento">
                                        <span class="material-symbols-outlined text-[19px]">visibility</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-transparent flex items-center justify-center">
                                            <span class="material-symbols-outlined text-[28px] text-slate-400">history</span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-600 dark:text-slate-400">Sin movimientos registrados</p>
                                            <p class="text-xs text-slate-400 mt-0.5">Aquí aparecerán las entradas, salidas y ajustes de inventario.</p>
                                        </div>
                                        <a href="{{ route('admin.inventario.entrada.form') }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-900 text-white rounded-lg text-xs font-bold hover:bg-slate-800 transition-colors mt-1">
                                            <span class="material-symbols-outlined text-[16px]">add</span>
                                            Registrar primera entrada
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($movimientos->total() > 0)
                <div class="px-6 py-4 border-t border-slate-100 dark:border-gray-700 bg-slate-50 dark:bg-transparent/40">
                    {{ $movimientos->links('vendor.pagination.admin-tailwind') }}
                </div>
            @endif
        </div>

    @else
        {{-- ═══════════ STOCK ACTUAL ═══════════ --}}
        <div class="card-elevated rounded-xl overflow-hidden">
            {{-- Filter bar --}}
            <div class="p-4 sm:p-5 border-b border-slate-100 dark:border-gray-700 bg-slate-50 dark:bg-transparent/50 flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
                <h3 class="text-sm font-extrabold text-slate-800 dark:text-gray-100">Stock Actual</h3>
                <form method="GET" action="{{ route('admin.inventario.stock') }}" class="flex flex-wrap items-center gap-2 w-full sm:w-auto" x-data="adminAutoFilter">
                    <div class="flex items-center bg-white dark:bg-[#181a1b] border border-slate-200 dark:border-gray-700 rounded-lg px-3 py-1.5 focus-within:border-slate-400 w-full sm:w-56">
                        <span class="material-symbols-outlined text-slate-400 text-[16px] mr-1.5">search</span>
                        <input type="text" name="q" value="{{ request('q') }}" x-on:input="autoSubmit()"
                               placeholder="Buscar SKU o nombre…"
                               class="bg-transparent border-none focus:ring-0 text-xs text-slate-700 dark:text-slate-300 w-full p-0 placeholder-slate-400 dark:placeholder-gray-500">
                    </div>
                    <select name="categoria" x-on:change="autoSubmit()"
                            class="text-xs border border-slate-200 dark:border-gray-700 rounded-lg px-3 py-1.5 bg-white dark:bg-[#181a1b] text-slate-700 dark:text-slate-200 focus:border-slate-400 focus:ring-0 outline-none cursor-pointer">
                        <option value="">Todas las categorías</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" {{ request('categoria') == $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                    <label class="flex items-center gap-1.5 text-xs font-medium text-slate-600 dark:text-slate-400 cursor-pointer px-2 py-1.5 border border-slate-200 dark:border-gray-700 rounded-lg bg-white dark:bg-[#181a1b] hover:bg-slate-50 dark:bg-transparent dark:hover:bg-gray-700/30 transition-colors">
                        <input type="checkbox" name="stock_bajo" value="1" {{ request('stock_bajo') ? 'checked' : '' }} x-on:change="autoSubmit()"
                               class="rounded text-slate-900 dark:text-white focus:ring-slate-900 border-slate-300 dark:border-gray-700">
                        Solo stock bajo
                    </label>
                    @if(request()->hasAny(['q', 'categoria', 'stock_bajo']))
                        <a href="{{ route('admin.inventario.stock') }}" class="px-3 py-1.5 text-xs text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:text-slate-300 font-medium">Limpiar</a>
                    @endif
                    <div class="ml-auto">
                        <x-btn-exportar excel-onclick="exportar('excel')" pdf-onclick="exportar('pdf')" />
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left min-w-[760px]">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-gray-700 text-[11px] font-semibold text-slate-400 uppercase tracking-wider bg-slate-50 dark:bg-transparent/50">
                            <th class="px-5 py-3.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Producto</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">SKU</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Variante</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Stock Actual</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Mínimo</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-gray-700/50 text-xs">
                        @forelse($productos as $producto)
                            @if($producto->variantes->isEmpty())
                                @php
                                    $stockBajo  = $producto->stock <= $producto->stock_minimo;
                                    $sinStock   = $producto->stock === 0;
                                @endphp
                                <tr class="hover:bg-slate-50 dark:bg-transparent dark:hover:bg-gray-700/30 transition-colors group">
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-12 rounded-lg border border-slate-200 dark:border-gray-700 overflow-hidden flex-shrink-0 bg-slate-100 dark:bg-transparent flex items-center justify-center shadow-sm">
                                                @if($producto->imagenes->isNotEmpty())
                                                    <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}" class="w-full h-full object-cover">
                                                @else
                                                    <span class="material-symbols-outlined text-slate-400 text-[20px]">inventory_2</span>
                                                @endif
                                            </div>
                                            <span class="font-semibold text-slate-800 dark:text-gray-100">{{ $producto->nombre }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 font-mono text-slate-500 dark:text-slate-400 text-[11px]">{{ $producto->sku ?? '—' }}</td>
                                    <td class="px-5 py-3.5 text-slate-400">—</td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold tabular-nums {{ $sinStock ? 'text-red-600' : ($stockBajo ? 'text-amber-600' : 'text-slate-800 dark:text-gray-100') }}">
                                                {{ $producto->stock }}
                                            </span>
                                            @if($sinStock)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-50 text-red-600 uppercase tracking-wide">Sin Stock</span>
                                            @elseif($stockBajo)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 uppercase tracking-wide">Stock Bajo</span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 uppercase tracking-wide">Disponible</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400 tabular-nums">{{ $producto->stock_minimo }}</td>
                                    <td class="px-5 py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <a href="{{ route('admin.inventario.entrada.form') }}?producto_id={{ $producto->id }}"
                                               title="Registrar entrada"
                                               class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50 transition-colors">
                                                <span class="material-symbols-outlined text-[16px]">south_east</span>
                                            </a>
                                            <a href="{{ route('admin.inventario.ajuste.form') }}?producto_id={{ $producto->id }}"
                                               title="Ajustar stock"
                                               class="p-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:bg-transparent transition-colors">
                                                <span class="material-symbols-outlined text-[16px]">edit</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @else
                                @foreach($producto->variantes as $variante)
                                    @php
                                        $stockBajoV  = $variante->stock <= $producto->stock_minimo;
                                        $sinStockV   = $variante->stock === 0;
                                        $labelV = $variante->opciones->map(fn($o) => ($o->tipo?->nombre ?? '') . ': ' . $o->valor)->join(' / ');
                                    @endphp
                                    <tr class="hover:bg-slate-50 dark:bg-transparent dark:hover:bg-gray-700/30 transition-colors group">
                                        <td class="px-5 py-3.5">
                                            <div class="flex items-center gap-3">
                                                <div class="w-12 h-12 rounded-lg border border-slate-200 dark:border-gray-700 overflow-hidden flex-shrink-0 bg-slate-100 dark:bg-transparent flex items-center justify-center shadow-sm">
                                                    @if($variante->imagen_ruta)
                                                        <img src="{{ asset('storage/' . $variante->imagen_ruta) }}" alt="{{ $producto->nombre }}" class="w-full h-full object-cover">
                                                    @elseif($producto->imagenes->isNotEmpty())
                                                        <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}" class="w-full h-full object-cover">
                                                    @else
                                                        <span class="material-symbols-outlined text-slate-400 text-[20px]">inventory_2</span>
                                                    @endif
                                                </div>
                                                <span class="font-semibold text-slate-800 dark:text-gray-100">{{ $producto->nombre }}</span>
                                            </div>
                                        </td>
                                        <td class="px-5 py-3.5 font-mono text-slate-500 dark:text-slate-400 text-[11px]">{{ $variante->sku ?? '—' }}</td>
                                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400 text-[11px]">{{ $labelV ?: '—' }}</td>
                                        <td class="px-5 py-3.5">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold tabular-nums {{ $sinStockV ? 'text-red-600' : ($stockBajoV ? 'text-amber-600' : 'text-slate-800 dark:text-gray-100') }}">
                                                    {{ $variante->stock }}
                                                </span>
                                                @if($sinStockV)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-50 text-red-600 uppercase tracking-wide">Sin Stock</span>
                                                @elseif($stockBajoV)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 uppercase tracking-wide">Stock Bajo</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 uppercase tracking-wide">Disponible</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-5 py-3.5 text-slate-500 dark:text-slate-400 tabular-nums">{{ $producto->stock_minimo }}</td>
                                        <td class="px-5 py-3.5 text-right">
                                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <a href="{{ route('admin.inventario.entrada.form') }}?producto_id={{ $producto->id }}&variante_id={{ $variante->id }}"
                                                   title="Registrar entrada"
                                                   class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50 transition-colors">
                                                    <span class="material-symbols-outlined text-[16px]">south_east</span>
                                                </a>
                                                <a href="{{ route('admin.inventario.ajuste.form') }}?producto_id={{ $producto->id }}&variante_id={{ $variante->id }}"
                                                   title="Ajustar stock"
                                                   class="p-1.5 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:bg-transparent transition-colors">
                                                    <span class="material-symbols-outlined text-[16px]">edit</span>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-transparent flex items-center justify-center">
                                            <span class="material-symbols-outlined text-[28px] text-slate-400">inventory_2</span>
                                        </div>
                                        <p class="text-sm font-bold text-slate-600 dark:text-slate-400">Sin productos en inventario</p>
                                        <p class="text-xs text-slate-400">Agrega productos al catálogo para gestionar su stock.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($productos->total() > 0)
                <div class="px-6 py-4 border-t border-slate-100 dark:border-gray-700 bg-slate-50 dark:bg-transparent/40">
                    {{ $productos->links('vendor.pagination.admin-tailwind') }}
                </div>
            @endif
        </div>
    @endif

    {{-- Modal Detalle de Movimiento --}}
    <div id="modal-detalle-movimiento" 
         class="fixed inset-0 top-0 left-0 right-0 bottom-0 z-[99999] hidden items-center justify-center p-4 overflow-y-auto select-none" 
         style="background-color: rgba(15, 23, 42, 0.75); backdrop-filter: blur(8px);"
         onclick="if(event.target === this) window.cerrarModalDetalleMovimiento();"
         role="dialog"
         aria-modal="true">
        <div class="bg-white dark:bg-[#181a1b] rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 dark:border-gray-700/80 space-y-5 animate-in fade-in zoom-in-95 duration-150 relative z-10 text-left my-auto">
            {{-- Header --}}
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div id="mdm-icono-container" class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0">
                        <span id="mdm-icono" class="material-symbols-outlined text-[24px]"></span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Detalle de Movimiento</h3>
                            <span id="mdm-tipo-badge" class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider"></span>
                        </div>
                        <p id="mdm-fecha" class="text-xs text-slate-400 font-medium mt-0.5"></p>
                    </div>
                </div>
                <button type="button" 
                        onclick="window.cerrarModalDetalleMovimiento()" 
                        class="p-1.5 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-gray-800 transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            {{-- Tarjeta Producto --}}
            <div class="flex items-center gap-3.5 p-3.5 bg-slate-50 dark:bg-slate-900/40 rounded-2xl border border-slate-100 dark:border-gray-800">
                <div id="mdm-img-container" class="w-12 h-12 rounded-xl border border-slate-200 dark:border-gray-700 overflow-hidden shrink-0 bg-white dark:bg-[#121415] flex items-center justify-center">
                    <img id="mdm-img" src="" alt="" class="w-full h-full object-cover hidden">
                    <span id="mdm-placeholder-img" class="material-symbols-outlined text-slate-400 text-[22px]">inventory_2</span>
                </div>
                <div class="min-w-0 flex-1">
                    <h4 id="mdm-producto-nombre" class="text-sm font-bold text-slate-900 dark:text-white truncate"></h4>
                    <div class="flex items-center gap-2 mt-0.5 text-xs text-slate-400">
                        <span id="mdm-producto-sku" class="font-mono"></span>
                        <span id="mdm-variante-badge" class="hidden px-2 py-0.5 rounded bg-slate-200 dark:bg-gray-800 text-slate-700 dark:text-slate-300 text-[11px] truncate"></span>
                    </div>
                </div>
            </div>

            {{-- Métricas de Transición de Stock --}}
            <div class="grid grid-cols-3 gap-2.5 p-3.5 bg-slate-50 dark:bg-slate-900/40 rounded-2xl border border-slate-100 dark:border-gray-800 text-center">
                <div class="space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Stock Inicial</span>
                    <p id="mdm-stock-antes" class="text-lg font-extrabold text-slate-700 dark:text-slate-300 tabular-nums">—</p>
                </div>
                <div class="space-y-1 border-x border-slate-200 dark:border-gray-700/60">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Variación</span>
                    <p id="mdm-cantidad" class="text-lg font-extrabold tabular-nums">—</p>
                </div>
                <div class="space-y-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Stock Final</span>
                    <p id="mdm-stock-despues" class="text-lg font-extrabold text-slate-900 dark:text-white tabular-nums">—</p>
                </div>
            </div>

            {{-- Tarjeta Responsable y Origen del Movimiento --}}
            <div class="p-3.5 bg-slate-50 dark:bg-slate-900/40 rounded-2xl border border-slate-100 dark:border-gray-800 space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Responsable / Origen</span>
                    <span id="mdm-responsable-badge" class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider"></span>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-slate-200 dark:bg-gray-700 flex items-center justify-center shrink-0 text-slate-600 dark:text-slate-300 font-bold text-xs">
                            <span class="material-symbols-outlined text-[18px]">person</span>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5">
                                <a id="mdm-responsable-link" href="#" target="_blank" class="font-bold text-xs text-slate-900 dark:text-white hover:text-emerald-600 dark:hover:text-emerald-400 hover:underline flex items-center gap-1 truncate group" title="Ver perfil del usuario en el sistema">
                                    <span id="mdm-responsable-nombre"></span>
                                    <span class="material-symbols-outlined text-[13px] text-slate-400 group-hover:text-emerald-600">open_in_new</span>
                                </a>
                                <span id="mdm-responsable-nombre-static" class="hidden font-bold text-xs text-slate-900 dark:text-white truncate"></span>
                            </div>
                            <p id="mdm-responsable-email" class="text-[11px] text-slate-400 font-mono truncate"></p>
                        </div>
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-200/60 dark:border-gray-800 text-[11px] flex items-start gap-1.5 text-slate-600 dark:text-slate-300">
                    <span class="material-symbols-outlined text-[15px] shrink-0 mt-0.5 text-emerald-600">info</span>
                    <span id="mdm-responsable-detalle" class="leading-relaxed"></span>
                </div>
            </div>

            {{-- Información operativa --}}
            <div class="space-y-2.5 text-xs">
                <div class="flex items-center justify-between py-1.5 border-b border-slate-100 dark:border-gray-800">
                    <span class="text-slate-400 font-medium">Motivo:</span>
                    <span id="mdm-motivo" class="font-semibold text-slate-800 dark:text-slate-200 text-right"></span>
                </div>
                <div id="mdm-pedido-row" class="hidden flex items-center justify-between py-1.5 border-b border-slate-100 dark:border-gray-800">
                    <span class="text-slate-400 font-medium">Pedido asociado:</span>
                    <a id="mdm-pedido-link" href="#" class="inline-flex items-center gap-1 font-mono font-bold text-emerald-600 hover:text-emerald-700 hover:underline">
                        <span id="mdm-pedido-numero"></span>
                        <span class="material-symbols-outlined text-[13px]">open_in_new</span>
                    </a>
                </div>
                <div id="mdm-proveedor-row" class="hidden flex items-center justify-between py-1.5 border-b border-slate-100 dark:border-gray-800">
                    <span class="text-slate-400 font-medium">Proveedor:</span>
                    <span id="mdm-proveedor" class="font-semibold text-slate-800 dark:text-slate-200"></span>
                </div>
                <div id="mdm-factura-row" class="hidden flex items-center justify-between py-1.5 border-b border-slate-100 dark:border-gray-800">
                    <span class="text-slate-400 font-medium">Factura / Remito:</span>
                    <span id="mdm-factura" class="font-mono font-semibold text-slate-800 dark:text-slate-200"></span>
                </div>
                <div id="mdm-notas-row" class="hidden pt-1">
                    <span class="text-slate-400 font-medium block mb-1">Notas / Observaciones:</span>
                    <div id="mdm-notas" class="p-2.5 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-100 dark:border-gray-800 text-slate-600 dark:text-slate-300 italic text-[11px] leading-relaxed"></div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="pt-2 flex justify-end">
                <button type="button" 
                        onclick="window.cerrarModalDetalleMovimiento()" 
                        class="w-full sm:w-auto px-5 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-gray-800 hover:bg-slate-200 dark:hover:bg-gray-700 rounded-xl transition-all cursor-pointer">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function exportar(formato) {
        // Encontramos el formulario de filtros del stock
        const forms = document.getElementsByTagName('form');
        let form = null;
        for (let i = 0; i < forms.length; i++) {
            if (forms[i].action.includes('admin/inventario/stock')) {
                form = forms[i];
                break;
            }
        }
        
        if (!form) return;

        const actionOriginal = form.action;
        
        if (formato === 'excel') { form.action = "{{ route('admin.inventario.stock.exportar-excel') }}"; } 
        else if (formato === 'pdf') { form.action = "{{ route('admin.inventario.stock.exportar-pdf') }}"; }
        
        form.target = "_blank";
        form.submit();
        
        setTimeout(() => { form.action = actionOriginal; form.target = "_self"; }, 100);
    }

    // Modal de detalle de movimiento de inventario
    window.abrirModalDetalleMovimiento = function(m) {
        let modal = document.getElementById('modal-detalle-movimiento');
        if (!modal) return;

        // Aseguramos que el modal esté directamente en el <body> para que el backdrop cubra el 100% del viewport
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }

        // Fecha
        document.getElementById('mdm-fecha').textContent = m.fecha;

        // Tipo, icono y badge
        const icnContainer = document.getElementById('mdm-icono-container');
        const icn = document.getElementById('mdm-icono');
        const tipoBadge = document.getElementById('mdm-tipo-badge');

        if (m.tipo === 'entrada') {
            icnContainer.className = 'w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/80';
            icn.textContent = 'south_east';
            tipoBadge.className = 'px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300';
            tipoBadge.textContent = 'Entrada';
        } else if (m.tipo === 'salida') {
            icnContainer.className = 'w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400 border border-rose-200 dark:border-rose-800/80';
            icn.textContent = 'north_east';
            tipoBadge.className = 'px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-rose-100 text-rose-800 dark:bg-rose-900/60 dark:text-rose-300';
            tipoBadge.textContent = 'Salida';
        } else {
            icnContainer.className = 'w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 bg-amber-50 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400 border border-amber-200 dark:border-amber-800/80';
            icn.textContent = 'sync_alt';
            tipoBadge.className = 'px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300';
            tipoBadge.textContent = 'Ajuste';
        }

        // Producto
        document.getElementById('mdm-producto-nombre').textContent = m.producto_nombre;
        document.getElementById('mdm-producto-sku').textContent = m.producto_sku ? 'SKU: ' + m.producto_sku : '';

        const imgEl = document.getElementById('mdm-img');
        const placeholderImg = document.getElementById('mdm-placeholder-img');
        if (m.producto_imagen) {
            imgEl.src = m.producto_imagen;
            imgEl.classList.remove('hidden');
            placeholderImg.classList.add('hidden');
        } else {
            imgEl.src = '';
            imgEl.classList.add('hidden');
            placeholderImg.classList.remove('hidden');
        }

        const varBadge = document.getElementById('mdm-variante-badge');
        if (m.variante) {
            varBadge.textContent = m.variante;
            varBadge.classList.remove('hidden');
        } else {
            varBadge.classList.add('hidden');
        }

        // Métricas de stock
        document.getElementById('mdm-stock-antes').textContent = m.stock_antes;
        const cantEl = document.getElementById('mdm-cantidad');
        const signo = m.tipo === 'entrada' ? '+' : (m.tipo === 'salida' ? '-' : '±');
        cantEl.textContent = signo + m.cantidad;
        cantEl.className = 'text-lg font-extrabold tabular-nums ' + (m.tipo === 'entrada' ? 'text-emerald-600 dark:text-emerald-400' : (m.tipo === 'salida' ? 'text-rose-600 dark:text-rose-400' : 'text-amber-600 dark:text-amber-400'));
        document.getElementById('mdm-stock-despues').textContent = m.stock_despues;

        // Responsable completo
        const resp = m.responsable || {};
        const respLink = document.getElementById('mdm-responsable-link');
        const respStatic = document.getElementById('mdm-responsable-nombre-static');
        const respBadge = document.getElementById('mdm-responsable-badge');

        if (resp.url) {
            respLink.href = resp.url;
            document.getElementById('mdm-responsable-nombre').textContent = resp.nombre;
            respLink.classList.remove('hidden');
            respStatic.classList.add('hidden');
        } else {
            respStatic.textContent = resp.nombre;
            respStatic.classList.remove('hidden');
            respLink.classList.add('hidden');
        }

        document.getElementById('mdm-responsable-email').textContent = resp.email ? resp.email : '';
        document.getElementById('mdm-responsable-detalle').textContent = resp.detalle || '';
        respBadge.textContent = resp.badge || 'Sistema';
        respBadge.className = 'px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider ' + (resp.badge_class || 'bg-slate-100 text-slate-700');

        // Datos operativos
        document.getElementById('mdm-motivo').textContent = m.motivo || '—';

        // Pedido
        const pedRow = document.getElementById('mdm-pedido-row');
        if (m.pedido_numero && m.pedido_url) {
            document.getElementById('mdm-pedido-numero').textContent = m.pedido_numero;
            document.getElementById('mdm-pedido-link').href = m.pedido_url;
            pedRow.classList.remove('hidden');
        } else {
            pedRow.classList.add('hidden');
        }

        // Proveedor
        const provRow = document.getElementById('mdm-proveedor-row');
        if (m.proveedor) {
            document.getElementById('mdm-proveedor').textContent = m.proveedor;
            provRow.classList.remove('hidden');
        } else {
            provRow.classList.add('hidden');
        }

        // Factura
        const factRow = document.getElementById('mdm-factura-row');
        if (m.factura_proveedor) {
            document.getElementById('mdm-factura').textContent = m.factura_proveedor;
            factRow.classList.remove('hidden');
        } else {
            factRow.classList.add('hidden');
        }

        // Notas
        const notasRow = document.getElementById('mdm-notas-row');
        if (m.notas) {
            document.getElementById('mdm-notas').textContent = m.notas;
            notasRow.classList.remove('hidden');
        } else {
            notasRow.classList.add('hidden');
        }

        // Mostrar modal
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    };

    window.cerrarModalDetalleMovimiento = function() {
        const modal = document.getElementById('modal-detalle-movimiento');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    };

    if (!window._mdmEscapeListenerAttached) {
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                window.cerrarModalDetalleMovimiento();
            }
        });
        window._mdmEscapeListenerAttached = true;
    }
</script>
@endpush




