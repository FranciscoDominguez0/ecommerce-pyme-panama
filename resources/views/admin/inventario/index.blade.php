@extends('layouts.admin')

@section('title', isset($vista) && $vista === 'stock' ? 'Inventario — Stock Actual' : 'Inventario — Historial de Movimientos')

@section('breadcrumbs')
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <a href="{{ route('admin.inventario.index') }}" class="font-medium text-slate-500 hover:text-slate-700 truncate">Inventario</a>
    @if(isset($vista) && $vista === 'stock')
        <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
        <span class="font-bold text-slate-900 truncate">Stock Actual</span>
    @else
        <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
        <span class="font-bold text-slate-900 truncate">Historial</span>
    @endif
@endsection

@section('content')
<div class="space-y-6 w-full min-w-0 max-w-full">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200/80">
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Gestión de Inventario</h2>
            <p class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">Controla el stock, registra movimientos y consulta el historial de cambios.</p>
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
                     class="absolute right-0 mt-2 w-52 bg-white border border-slate-200 rounded-xl shadow-lg z-50 overflow-hidden">
                    <a href="{{ route('admin.inventario.entrada.form') }}"
                       class="flex items-center gap-3 px-4 py-3 text-xs font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
                        <span class="material-symbols-outlined text-emerald-600 text-[18px]">south_east</span>
                        Registrar Entrada
                    </a>
                    <a href="{{ route('admin.inventario.salida.form') }}"
                       class="flex items-center gap-3 px-4 py-3 text-xs font-semibold text-slate-700 hover:bg-red-50 hover:text-red-700 transition-colors">
                        <span class="material-symbols-outlined text-red-500 text-[18px]">north_east</span>
                        Registrar Salida
                    </a>
                    <div class="border-t border-slate-100"></div>
                    <a href="{{ route('admin.inventario.ajuste.form') }}"
                       class="flex items-center gap-3 px-4 py-3 text-xs font-semibold text-slate-700 hover:bg-amber-50 hover:text-amber-700 transition-colors">
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
                    <h3 class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($kpis['stockTotal']) }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">inventory_2</span>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-500 font-medium">
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
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-500 font-medium">
                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                <span>Unidades recibidas</span>
            </div>
        </div>

        <div class="card-elevated p-4 rounded-xl flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Salidas (7 días)</p>
                    <h3 class="text-2xl font-extrabold text-slate-700 mt-1">-{{ number_format($kpis['salidasSiete']) }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">north_east</span>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-500 font-medium">
                <span class="w-2 h-2 rounded-full bg-slate-400 inline-block"></span>
                <span>Unidades despachadas</span>
            </div>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="flex items-center gap-1 border-b border-slate-200">
        <a href="{{ route('admin.inventario.index') }}"
           class="px-4 py-2.5 text-xs font-bold transition-all border-b-2 {{ !isset($vista) || $vista !== 'stock' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
            <span class="flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px]">history</span>
                Historial de Movimientos
            </span>
        </a>
        <a href="{{ route('admin.inventario.stock') }}"
           class="px-4 py-2.5 text-xs font-bold transition-all border-b-2 {{ isset($vista) && $vista === 'stock' ? 'border-slate-900 text-slate-900' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
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
            <div class="p-4 sm:p-5 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
                <h3 class="text-sm font-extrabold text-slate-800">Historial de Movimientos</h3>
                <form method="GET" action="{{ route('admin.inventario.index') }}" class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                    {{-- Search --}}
                    <div class="flex items-center bg-white border border-slate-200 rounded-lg px-3 py-1.5 focus-within:border-slate-400 w-full sm:w-52">
                        <span class="material-symbols-outlined text-slate-400 text-[16px] mr-1.5">search</span>
                        <input type="text" name="q" value="{{ request('q') }}"
                               placeholder="Buscar producto o motivo…"
                               class="bg-transparent border-none focus:ring-0 text-xs text-slate-700 w-full p-0 placeholder-slate-400">
                    </div>
                    {{-- Tipo --}}
                    <select name="tipo"
                            class="text-xs border border-slate-200 rounded-lg px-3 py-1.5 bg-white text-slate-700 focus:border-slate-400 focus:ring-0 outline-none cursor-pointer">
                        <option value="">Todos los tipos</option>
                        <option value="entrada" {{ request('tipo') === 'entrada' ? 'selected' : '' }}>Entradas</option>
                        <option value="salida"  {{ request('tipo') === 'salida'  ? 'selected' : '' }}>Salidas</option>
                        <option value="ajuste"  {{ request('tipo') === 'ajuste'  ? 'selected' : '' }}>Ajustes</option>
                    </select>
                    {{-- Fecha desde --}}
                    <input type="date" name="desde" value="{{ request('desde') }}"
                           class="text-xs border border-slate-200 rounded-lg px-3 py-1.5 bg-white text-slate-700 focus:border-slate-400 focus:ring-0 outline-none">
                    {{-- Fecha hasta --}}
                    <input type="date" name="hasta" value="{{ request('hasta') }}"
                           class="text-xs border border-slate-200 rounded-lg px-3 py-1.5 bg-white text-slate-700 focus:border-slate-400 focus:ring-0 outline-none">
                    <button type="submit"
                            class="px-3 py-1.5 bg-slate-900 text-white rounded-lg text-xs font-bold hover:bg-slate-800 transition-colors">
                        Filtrar
                    </button>
                    @if(request()->hasAny(['q', 'tipo', 'desde', 'hasta']))
                        <a href="{{ route('admin.inventario.index') }}" class="px-3 py-1.5 text-xs text-slate-500 hover:text-slate-700 font-medium">Limpiar</a>
                    @endif
                </form>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left min-w-[860px]">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-5 py-3.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Producto / Variante</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Cantidad</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Stock</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Motivo</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Responsable</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-xs">
                        @forelse($movimientos as $mov)
                            <tr class="hover:bg-slate-50/70 transition-colors group">
                                {{-- Fecha --}}
                                <td class="px-5 py-3.5 text-slate-500 whitespace-nowrap font-mono text-[11px]">
                                    {{ $mov->creado_en->format('d/m/Y') }}<br>
                                    <span class="text-slate-400">{{ $mov->creado_en->format('H:i') }}</span>
                                </td>
                                {{-- Producto --}}
                                <td class="px-5 py-3.5">
                                    <div class="font-semibold text-slate-800">{{ $mov->producto?->nombre ?? '—' }}</div>
                                    @if($mov->variante)
                                        <div class="text-[11px] text-slate-400 mt-0.5">
                                            {{ $mov->variante->opciones->map(fn($o) => $o->tipo?->nombre . ': ' . $o->valor)->join(' / ') }}
                                            @if($mov->variante->sku)
                                                · <span class="font-mono">{{ $mov->variante->sku }}</span>
                                            @endif
                                        </div>
                                    @elseif($mov->producto?->sku)
                                        <div class="text-[11px] text-slate-400 font-mono mt-0.5">{{ $mov->producto->sku }}</div>
                                    @endif
                                </td>
                                {{-- Tipo badge --}}
                                <td class="px-5 py-3.5">
                                    @if($mov->tipo === 'entrada')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 uppercase tracking-wide">
                                            <span class="material-symbols-outlined text-[12px]">south_east</span> Entrada
                                        </span>
                                    @elseif($mov->tipo === 'salida')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-red-50 text-red-600 uppercase tracking-wide">
                                            <span class="material-symbols-outlined text-[12px]">north_east</span> Salida
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-50 text-amber-700 uppercase tracking-wide">
                                            <span class="material-symbols-outlined text-[12px]">sync_alt</span> Ajuste
                                        </span>
                                    @endif
                                </td>
                                {{-- Cantidad --}}
                                <td class="px-5 py-3.5 font-bold tabular-nums {{ $mov->tipo === 'entrada' ? 'text-emerald-600' : ($mov->tipo === 'salida' ? 'text-red-600' : 'text-amber-600') }}">
                                    {{ $mov->tipo === 'entrada' ? '+' : ($mov->tipo === 'salida' ? '-' : '±') }}{{ $mov->cantidad }}
                                </td>
                                {{-- Stock antes → después --}}
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-1.5 text-[11px] tabular-nums">
                                        <span class="text-slate-500">{{ $mov->stock_antes }}</span>
                                        <span class="material-symbols-outlined text-slate-300 text-[14px]">arrow_forward</span>
                                        <span class="font-bold text-slate-800">{{ $mov->stock_despues }}</span>
                                    </div>
                                </td>
                                {{-- Motivo --}}
                                <td class="px-5 py-3.5 text-slate-600 max-w-[200px]">
                                    <div class="truncate" title="{{ $mov->motivo }}">{{ $mov->motivo }}</div>
                                    @if($mov->pedido_id)
                                        <a href="{{ route('admin.pedidos.detalle', $mov->pedido_id) }}"
                                           class="text-[11px] text-slate-400 hover:text-slate-600 font-mono">
                                            {{ $mov->pedido?->numero_pedido }}
                                        </a>
                                    @endif
                                </td>
                                {{-- Responsable --}}
                                <td class="px-5 py-3.5 text-slate-500">
                                    {{ $mov->usuario ? $mov->usuario->nombre_completo : 'Sistema' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center">
                                            <span class="material-symbols-outlined text-[28px] text-slate-400">history</span>
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-600">Sin movimientos registrados</p>
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
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/40">
                    {{ $movimientos->links('vendor.pagination.admin-tailwind') }}
                </div>
            @endif
        </div>

    @else
        {{-- ═══════════ STOCK ACTUAL ═══════════ --}}
        <div class="card-elevated rounded-xl overflow-hidden">
            {{-- Filter bar --}}
            <div class="p-4 sm:p-5 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
                <h3 class="text-sm font-extrabold text-slate-800">Stock Actual</h3>
                <form method="GET" action="{{ route('admin.inventario.stock') }}" class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                    <div class="flex items-center bg-white border border-slate-200 rounded-lg px-3 py-1.5 focus-within:border-slate-400 w-full sm:w-56">
                        <span class="material-symbols-outlined text-slate-400 text-[16px] mr-1.5">search</span>
                        <input type="text" name="q" value="{{ request('q') }}"
                               placeholder="Buscar SKU o nombre…"
                               class="bg-transparent border-none focus:ring-0 text-xs text-slate-700 w-full p-0 placeholder-slate-400">
                    </div>
                    <select name="categoria"
                            class="text-xs border border-slate-200 rounded-lg px-3 py-1.5 bg-white text-slate-700 focus:border-slate-400 focus:ring-0 outline-none cursor-pointer">
                        <option value="">Todas las categorías</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" {{ request('categoria') == $cat->id ? 'selected' : '' }}>{{ $cat->nombre }}</option>
                        @endforeach
                    </select>
                    <label class="flex items-center gap-1.5 text-xs font-medium text-slate-600 cursor-pointer px-2 py-1.5 border border-slate-200 rounded-lg bg-white hover:bg-slate-50 transition-colors">
                        <input type="checkbox" name="stock_bajo" value="1" {{ request('stock_bajo') ? 'checked' : '' }}
                               class="rounded text-slate-900 focus:ring-slate-900 border-slate-300">
                        Solo stock bajo
                    </label>
                    <button type="submit"
                            class="px-3 py-1.5 bg-slate-900 text-white rounded-lg text-xs font-bold hover:bg-slate-800 transition-colors">
                        Filtrar
                    </button>
                    @if(request()->hasAny(['q', 'categoria', 'stock_bajo']))
                        <a href="{{ route('admin.inventario.stock') }}" class="px-3 py-1.5 text-xs text-slate-500 hover:text-slate-700 font-medium">Limpiar</a>
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
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-5 py-3.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Producto</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">SKU</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Variante</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Stock Actual</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Mínimo</th>
                            <th class="px-5 py-3.5 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 text-xs">
                        {{-- Productos sin variantes --}}
                        @foreach($productos as $producto)
                            @php
                                $stockBajo  = $producto->stock <= $producto->stock_minimo;
                                $sinStock   = $producto->stock === 0;
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors group">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-lg border border-slate-200 overflow-hidden flex-shrink-0 bg-slate-100 flex items-center justify-center shadow-sm">
                                            @if($producto->imagenes->isNotEmpty())
                                                <img src="{{ $producto->imagen_url }}" alt="{{ $producto->nombre }}" class="w-full h-full object-cover">
                                            @else
                                                <span class="material-symbols-outlined text-slate-400 text-[20px]">inventory_2</span>
                                            @endif
                                        </div>
                                        <span class="font-semibold text-slate-800">{{ $producto->nombre }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 font-mono text-slate-500 text-[11px]">{{ $producto->sku ?? '—' }}</td>
                                <td class="px-5 py-3.5 text-slate-400">—</td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold tabular-nums {{ $sinStock ? 'text-red-600' : ($stockBajo ? 'text-amber-600' : 'text-slate-800') }}">
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
                                <td class="px-5 py-3.5 text-slate-500 tabular-nums">{{ $producto->stock_minimo }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="{{ route('admin.inventario.entrada.form') }}?producto_id={{ $producto->id }}"
                                           title="Registrar entrada"
                                           class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50 transition-colors">
                                            <span class="material-symbols-outlined text-[16px]">south_east</span>
                                        </a>
                                        <a href="{{ route('admin.inventario.ajuste.form') }}?producto_id={{ $producto->id }}"
                                           title="Ajustar stock"
                                           class="p-1.5 rounded-lg text-slate-600 hover:bg-slate-100 transition-colors">
                                            <span class="material-symbols-outlined text-[16px]">edit</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        {{-- Variantes --}}
                        @foreach($variantes as $variante)
                            @php
                                $stockBajoV  = $variante->stock <= $variante->producto->stock_minimo;
                                $sinStockV   = $variante->stock === 0;
                                $labelV = $variante->opciones->map(fn($o) => ($o->tipo?->nombre ?? '') . ': ' . $o->valor)->join(' / ');
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors group">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-lg border border-slate-200 overflow-hidden flex-shrink-0 bg-slate-100 flex items-center justify-center shadow-sm">
                                            @if($variante->imagen_ruta)
                                                <img src="{{ asset('storage/' . $variante->imagen_ruta) }}" alt="{{ $variante->producto->nombre }}" class="w-full h-full object-cover">
                                            @elseif($variante->producto->imagenes->isNotEmpty())
                                                <img src="{{ $variante->producto->imagen_url }}" alt="{{ $variante->producto->nombre }}" class="w-full h-full object-cover">
                                            @else
                                                <span class="material-symbols-outlined text-slate-400 text-[20px]">inventory_2</span>
                                            @endif
                                        </div>
                                        <span class="font-semibold text-slate-800">{{ $variante->producto->nombre }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 font-mono text-slate-500 text-[11px]">{{ $variante->sku ?? '—' }}</td>
                                <td class="px-5 py-3.5 text-slate-500 text-[11px]">{{ $labelV ?: '—' }}</td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold tabular-nums {{ $sinStockV ? 'text-red-600' : ($stockBajoV ? 'text-amber-600' : 'text-slate-800') }}">
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
                                <td class="px-5 py-3.5 text-slate-500 tabular-nums">{{ $variante->producto->stock_minimo }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="{{ route('admin.inventario.entrada.form') }}?producto_id={{ $variante->producto_id }}&variante_id={{ $variante->id }}"
                                           title="Registrar entrada"
                                           class="p-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50 transition-colors">
                                            <span class="material-symbols-outlined text-[16px]">south_east</span>
                                        </a>
                                        <a href="{{ route('admin.inventario.ajuste.form') }}?producto_id={{ $variante->producto_id }}&variante_id={{ $variante->id }}"
                                           title="Ajustar stock"
                                           class="p-1.5 rounded-lg text-slate-600 hover:bg-slate-100 transition-colors">
                                            <span class="material-symbols-outlined text-[16px]">edit</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        @if($productos->isEmpty() && $variantes->isEmpty())
                            <tr>
                                <td colspan="6" class="px-5 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center">
                                            <span class="material-symbols-outlined text-[28px] text-slate-400">inventory_2</span>
                                        </div>
                                        <p class="text-sm font-bold text-slate-600">Sin productos en inventario</p>
                                        <p class="text-xs text-slate-400">Agrega productos al catálogo para gestionar su stock.</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($productos->total() > 0 || $variantes->total() > 0)
                <div class="flex flex-col border-t border-slate-100 bg-slate-50/40 divide-y divide-slate-100">
                    @if($productos->total() > 0)
                        <div class="px-6 py-4">
                            <div class="text-xs text-slate-500 font-bold mb-2">Paginación de Productos (Sin Variantes)</div>
                            {{ $productos->links('vendor.pagination.admin-tailwind') }}
                        </div>
                    @endif
                    
                    @if($variantes->total() > 0)
                        <div class="px-6 py-4">
                            <div class="text-xs text-slate-500 font-bold mb-2">Paginación de Variantes</div>
                            {{ $variantes->links('vendor.pagination.admin-tailwind') }}
                        </div>
                    @endif
                </div>
            @endif
        </div>
    @endif

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
</script>
@endpush
