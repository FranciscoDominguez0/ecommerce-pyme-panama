@extends('layouts.admin')

@section('title', 'Productos & Variantes')

@section('breadcrumbs')
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <span class="font-bold text-slate-900 truncate">Productos</span>
@endsection

@section('content')
<div class="space-y-6 w-full min-w-0 max-w-full">

    <!-- Page Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200/80">
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Productos & Variantes</h2>
            <p class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">Administra el catálogo de artículos, control de stock multivariante, precios y visibilidad.</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" 
                    onclick="alert('Exportando catálogo completo a CSV / Excel...')" 
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 hover:border-slate-300 transition-all shadow-xs">
                <span class="material-symbols-outlined text-[18px] text-slate-500">file_download</span>
                <span class="hidden sm:inline">Exportar</span>
            </button>
            <a href="{{ route('admin.productos.create') }}" 
               class="flex items-center gap-1.5 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-bold transition-all shadow-sm">
                <span class="material-symbols-outlined text-[18px]">add</span>
                <span>Nuevo Producto</span>
            </a>
        </div>
    </div>

    <!-- Tarjetas KPI / Métricas Rápidas desde la Base de Datos -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- KPI 1: Total Productos -->
        <div class="card-elevated p-4 rounded-xl relative overflow-hidden group hover:border-emerald-200 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Catálogo</p>
                    <h3 class="text-2xl font-extrabold text-slate-900 mt-1">{{ $kpiTotal }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center group-hover:bg-emerald-50 group-hover:text-emerald-600 transition-colors">
                    <span class="material-symbols-outlined text-[22px]">category</span>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-emerald-600 font-medium">
                <span class="material-symbols-outlined text-[14px]">trending_up</span>
                <span>Catálogo en base de datos</span>
            </div>
        </div>

        <!-- KPI 2: En Stock -->
        <div class="card-elevated p-4 rounded-xl relative overflow-hidden group hover:border-emerald-200 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">En Stock</p>
                    <h3 class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $kpiEnStock }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[22px]">check_circle</span>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-500">
                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                <span>{{ $kpiTotal > 0 ? number_format(($kpiEnStock / $kpiTotal) * 100, 1) : 0 }}% disponible</span>
            </div>
        </div>

        <!-- KPI 3: Stock Bajo / Alertas -->
        <div class="card-elevated p-4 rounded-xl relative overflow-hidden group hover:border-amber-200 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Stock Bajo / Crítico</p>
                    <h3 class="text-2xl font-extrabold text-amber-600 mt-1">{{ $kpiStockBajo }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[22px]">warning</span>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-amber-600 font-medium">
                <span class="material-symbols-outlined text-[14px]">priority_high</span>
                <span>Requieren reposición</span>
            </div>
        </div>

        <!-- KPI 4: Variantes Creadas -->
        <div class="card-elevated p-4 rounded-xl relative overflow-hidden group hover:border-purple-200 transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Variantes Creadas</p>
                    <h3 class="text-2xl font-extrabold text-purple-600 mt-1">{{ $kpiVariantes }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[22px]">style</span>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-500">
                <span>Combinaciones SKU activas</span>
            </div>
        </div>
    </div>

    <!-- Barra de Búsqueda y Filtros con Formularios Reales -->
    <form method="GET" action="{{ route('admin.productos.index') }}" class="card-elevated p-4 rounded-xl">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
            
            <!-- Búsqueda por Nombre -->
            <div class="lg:col-span-4">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Buscar por nombre</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <span class="material-symbols-outlined text-[18px]">search</span>
                    </span>
                    <input type="text" 
                           name="buscar" 
                           value="{{ $buscar }}" 
                           placeholder="Ej. MacBook Air, Teclado, Impresora..." 
                           class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                </div>
            </div>

            <!-- Búsqueda por SKU -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">SKU / Código</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                        <span class="material-symbols-outlined text-[16px]">qr_code</span>
                    </span>
                    <input type="text" 
                           name="sku" 
                           value="{{ $buscarSku }}" 
                           placeholder="PROD-001..." 
                           class="w-full pl-8 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-mono placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                </div>
            </div>

            <!-- Filtro por Categoría -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Categoría</label>
                <select name="categoria_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                    <option value="all">Todas las categorías</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat->id }}" @selected($categoriaId == $cat->id)>{{ $cat->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro por Estado -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Estado</label>
                <select name="estado" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                    <option value="all" @selected($filtroEstado == 'all')>Todos los estados</option>
                    <option value="activo" @selected($filtroEstado == 'activo')>Activo (Visible)</option>
                    <option value="inactivo" @selected($filtroEstado == 'inactivo')>Inactivo (Borrador)</option>
                </select>
            </div>

            <!-- Filtro por Stock -->
            <div class="lg:col-span-2 flex items-end gap-2">
                <div class="flex-1">
                    <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Stock</label>
                    <select name="stock" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all">
                        <option value="all" @selected($filtroStock == 'all')>Todos</option>
                        <option value="en_stock" @selected($filtroStock == 'en_stock')>En Stock (>5)</option>
                        <option value="bajo_stock" @selected($filtroStock == 'bajo_stock')>Bajo Stock (1-5)</option>
                        <option value="agotado" @selected($filtroStock == 'agotado')>Agotado (0)</option>
                    </select>
                </div>
                <button type="submit" class="p-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl shadow-xs transition-colors" title="Aplicar filtros">
                    <span class="material-symbols-outlined text-[20px]">filter_alt</span>
                </button>
            </div>

        </div>
    </form>

    <!-- Tabla Principal de Productos -->
    <div class="card-elevated rounded-2xl overflow-hidden border border-slate-200/80 shadow-xs">
        
        <!-- Header de la Tabla -->
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-2">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Catálogo de Productos</h3>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                    {{ $productos->total() }} productos registrados
                </span>
            </div>
        </div>

        <!-- Tabla con Scroll Horizontal Suave -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[850px]">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-semibold text-slate-400 uppercase tracking-wider bg-slate-50/50">
                        <th class="py-3.5 px-4 w-10 text-center"></th>
                        <th class="py-3.5 px-4">Producto & SKU</th>
                        <th class="py-3.5 px-4">Precio (USD/PAB)</th>
                        <th class="py-3.5 px-4">Inventario & Variantes</th>
                        <th class="py-3.5 px-4">Estado</th>
                        <th class="py-3.5 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    
                    @forelse($productos as $prod)
                        @php
                            $imgPrincipal = $prod->imagenPrincipal();
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            
                            <!-- Miniatura / Ícono del Producto -->
                            <td class="py-3 px-4 text-center w-12">
                                <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-600 overflow-hidden shrink-0">
                                    @if($imgPrincipal && (str_starts_with($imgPrincipal->ruta, 'http') || str_starts_with($imgPrincipal->ruta, '/storage') || str_starts_with($imgPrincipal->ruta, 'data:image') || str_starts_with($imgPrincipal->ruta, 'storage/')))
                                        <img src="{{ str_starts_with($imgPrincipal->ruta, 'storage/') ? asset($imgPrincipal->ruta) : $imgPrincipal->ruta }}" alt="{{ $prod->nombre }}" class="w-full h-full object-cover">
                                    @elseif($imgPrincipal && (str_starts_with($imgPrincipal->ruta, '<svg') || str_contains($imgPrincipal->ruta, '</svg>')))
                                        <div class="w-7 h-7 flex items-center justify-center svg-container">{!! $imgPrincipal->ruta !!}</div>
                                    @elseif($imgPrincipal && !empty($imgPrincipal->ruta))
                                        <span class="material-symbols-outlined text-[24px] text-slate-700">{{ $imgPrincipal->ruta }}</span>
                                    @else
                                        <span class="material-symbols-outlined text-[24px] text-slate-400">image</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Nombre, SKU y Categoría -->
                            <td class="py-3 px-4">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-1.5">
                                        <a href="{{ route('admin.productos.edit', $prod->id) }}" class="font-bold text-slate-900 text-sm hover:text-emerald-700 transition-colors">
                                            {{ $prod->nombre }}
                                        </a>
                                        @if($prod->destacado)
                                            <span class="material-symbols-outlined text-[15px] text-amber-500" title="Producto Destacado">star</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2 text-[11px] flex-wrap">
                                        <span class="font-mono font-semibold text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">SKU: {{ $prod->sku ?? 'N/A' }}</span>
                                        
                                        @if($prod->categoria)
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                                                <span class="material-symbols-outlined text-[11px]">folder</span>
                                                <span>{{ $prod->categoria->nombre }}</span>
                                            </span>
                                        @else
                                            <span class="text-slate-400 italic text-[10px]">Sin categoría</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Precio y Oferta -->
                            <td class="py-3 px-4">
                                <div>
                                    @if($prod->tieneOfertaValida())
                                        <div class="font-extrabold text-emerald-700">${{ number_format($prod->precio_oferta, 2) }}</div>
                                        <div class="text-[10px] text-slate-400 line-through">${{ number_format($prod->precio, 2) }}</div>
                                    @else
                                        <div class="font-bold text-slate-900">${{ number_format($prod->precio, 2) }}</div>
                                        @if($prod->aplica_itbms)
                                            <span class="text-[10px] text-slate-400">+ ITBMS (7%)</span>
                                        @endif
                                    @endif
                                </div>
                            </td>

                            <!-- Inventario y Variantes -->
                            <td class="py-3 px-4">
                                <div class="space-y-1.5">
                                    <div class="flex items-center gap-2">
                                        @if($prod->stock > ($prod->stock_minimo ?? 5))
                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                <span>{{ $prod->stock }} en stock</span>
                                            </span>
                                        @elseif($prod->stock > 0)
                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                <span>{{ $prod->stock }} bajo stock</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-rose-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                <span>Agotado</span>
                                            </span>
                                        @endif
                                        
                                        @if($prod->variantes_count > 0)
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200" title="Este producto tiene {{ $prod->variantes_count }} combinaciones">
                                                <span class="material-symbols-outlined text-[11px]">style</span>
                                                <span>{{ $prod->variantes_count }} var</span>
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="w-24 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full rounded-full {{ $prod->stock > ($prod->stock_minimo ?? 5) ? 'bg-emerald-500' : ($prod->stock > 0 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ min(100, max(5, ($prod->stock / max(1, $prod->stock_minimo ?? 5)) * 50)) }}%"></div>
                                    </div>
                                </div>
                            </td>

                            <!-- Estado (Activo / Inactivo) -->
                            <td class="py-3 px-4">
                                @if($prod->activo)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Activo</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500">
                                        <span>Inactivo</span>
                                    </span>
                                @endif
                            </td>


                            <!-- Acciones Rápidas -->
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('cliente.producto.detalle', $prod->slug) }}" 
                                       target="_blank" 
                                       class="p-1.5 text-slate-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" 
                                       title="Ver en la tienda pública">
                                        <span class="material-symbols-outlined text-[17px]">visibility</span>
                                    </a>
                                    <a href="{{ route('admin.productos.edit', $prod->id) }}" 
                                       class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" 
                                       title="Editar producto">
                                        <span class="material-symbols-outlined text-[17px]">edit</span>
                                    </a>
                                    <button type="button" 
                                            onclick="window.ModalEliminar.abrir('{{ route('admin.productos.destroy', $prod->id) }}', '{{ addslashes($prod->nombre) }}')" 
                                            class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer" 
                                            title="Eliminar producto">
                                        <span class="material-symbols-outlined text-[17px]">delete</span>
                                    </button>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-500">
                                <div class="max-w-xs mx-auto space-y-3">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                                        <span class="material-symbols-outlined text-[28px]">inventory_2</span>
                                    </div>
                                    <p class="text-xs font-bold text-slate-700">No hay productos registrados con estos filtros.</p>
                                    <a href="{{ route('admin.productos.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-slate-800 transition-colors shadow-xs">
                                        <span class="material-symbols-outlined text-[16px]">add</span>
                                        <span>Agregar Primer Producto</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        <!-- Paginación Dinámica de Laravel -->
        <div class="px-5 py-4 border-t border-slate-100 flex items-center justify-between flex-wrap gap-3">
            <span class="text-xs text-slate-500">
                Mostrando <strong>{{ $productos->firstItem() ?? 0 }}</strong> a <strong>{{ $productos->lastItem() ?? 0 }}</strong> de <strong>{{ $productos->total() }}</strong> productos en total
            </span>

            @if($productos->hasPages())
                <div class="flex items-center gap-1">
                    {{ $productos->links() }}
                </div>
            @endif
        </div>

    </div>

</div>
@endsection
