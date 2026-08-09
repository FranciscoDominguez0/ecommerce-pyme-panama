@extends('layouts.cliente')

@section('title', 'Catálogo de Productos - PayMe Panamá')

@section('content')
<div class="min-h-screen bg-slate-50 py-6 sm:py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-1.5 text-xs text-slate-500 font-medium" aria-label="Breadcrumb">
            <a href="{{ route('inicio') }}" class="hover:text-emerald-700 transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">home</span>
                <span>Inicio</span>
            </a>
            <span class="material-symbols-outlined text-[14px] text-slate-400">chevron_right</span>
            <span class="text-slate-900 font-bold">Catálogo de Productos</span>
        </nav>

        <!-- Barra Superior Secundaria y Subcategorías -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-2 mb-4">
            <!-- Menú Horizontal (Mockup basado en imagen) -->
            <div class="flex flex-wrap items-center gap-4 px-4 py-2 border-b border-slate-100 mb-3">
                <div class="bg-blue-700 text-white rounded-lg px-4 py-2 flex items-center gap-2 text-sm font-bold cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">accessibility_new</span>
                    Productos
                    <span class="material-symbols-outlined text-[18px]">expand_more</span>
                </div>
                <nav class="flex flex-wrap items-center gap-4 text-sm font-semibold">
                    <a href="{{ route('inicio') }}" class="text-slate-900 hover:text-blue-700 transition-colors">Inicio</a>
                    <a href="#" class="text-slate-900 hover:text-blue-700 transition-colors flex items-center gap-1">Categorías <span class="material-symbols-outlined text-[16px]">expand_more</span></a>
                    <a href="{{ route('cliente.catalogo') }}" class="text-slate-600 hover:text-blue-700 transition-colors">Todos los productos</a>
                    <a href="#" class="text-slate-600 hover:text-blue-700 transition-colors flex items-center gap-1">Lo mas vendido <span class="material-symbols-outlined text-[16px]">expand_more</span></a>
                    <a href="#" class="text-slate-600 hover:text-blue-700 transition-colors">+</a>
                </nav>
            </div>

            <div class="px-4 pb-2">
                <h2 class="text-base font-bold text-slate-900 mb-3">Categorías</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($categorias as $cat)
                        <a href="{{ route('cliente.catalogo', ['categoria' => $cat->slug]) }}" 
                           class="flex items-center gap-2 px-4 py-2 rounded-lg border {{ $categoriaSlug === $cat->slug ? 'border-emerald-500 bg-emerald-50 text-emerald-800' : 'border-slate-200 bg-white text-slate-600 hover:border-emerald-300 hover:shadow-xs' }} transition-all text-xs font-semibold">
                            @if($cat->imagen_ruta)
                                <img src="{{ str_starts_with($cat->imagen_ruta, 'http') ? $cat->imagen_ruta : asset($cat->imagen_ruta) }}" alt="{{ $cat->nombre }}" class="w-5 h-5 object-contain">
                            @else
                                <span class="material-symbols-outlined text-[18px] text-emerald-500">category</span>
                            @endif
                            <span>{{ $cat->nombre }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Grid Principal: Sidebar de Filtros + Grid de Productos -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Sidebar de Filtros (3 columnas en LG) -->
            <aside class="lg:col-span-3 space-y-5 bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                
                <!-- Encabezado de Filtros -->
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px] text-emerald-600">tune</span>
                        <span>Filtrar Resultados</span>
                    </h3>
                    <a href="{{ route('cliente.catalogo') }}" class="text-[11px] font-semibold text-emerald-700 hover:underline">
                        Limpiar todo
                    </a>
                </div>

                <form method="GET" action="{{ route('cliente.catalogo') }}" class="space-y-5">
                    <!-- 1. Búsqueda de Texto -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Buscar
                        </label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
                            <input type="text" 
                                   name="buscar"
                                   placeholder="Nombre, SKU..." 
                                   value="{{ $buscar }}" 
                                   class="pl-9 text-xs w-full rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 py-2">
                        </div>
                    </div>

                    <!-- 2. Categorías -->
                    <div class="space-y-2 border-t border-slate-100 pt-4">
                        <h4 class="text-xs font-bold text-slate-700 tracking-wider mb-2 flex items-center justify-between">
                            Categorías
                            <span class="material-symbols-outlined text-[16px]">expand_more</span>
                        </h4>
                        <div class="space-y-1 text-xs">
                            <a href="{{ route('cliente.catalogo', array_merge(request()->query(), ['categoria' => 'all'])) }}" 
                               class="flex items-center justify-between px-2.5 py-1.5 rounded-lg {{ $categoriaSlug === 'all' ? 'font-bold text-slate-900' : 'text-slate-600 hover:bg-slate-50' }} transition-colors">
                                <span class="flex items-center gap-2">
                                    <input type="radio" {{ $categoriaSlug === 'all' ? 'checked' : '' }} class="text-blue-600 border-slate-300 focus:ring-blue-500">
                                    Todas los productos
                                </span>
                            </a>
                            @foreach($categorias as $cat)
                                <a href="{{ route('cliente.catalogo', array_merge(request()->query(), ['categoria' => $cat->slug])) }}" 
                                   class="flex items-center justify-between px-2.5 py-1.5 rounded-lg {{ $categoriaSlug === $cat->slug ? 'font-bold text-slate-900' : 'text-slate-600 hover:bg-slate-50' }} transition-colors">
                                    <span class="flex items-center gap-2">
                                        <input type="radio" {{ $categoriaSlug === $cat->slug ? 'checked' : '' }} class="text-blue-600 border-slate-300 focus:ring-blue-500">
                                        {{ $cat->nombre }} <span class="text-slate-400 font-normal">({{ $cat->productos_count }})</span>
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- 3. Marcas (Basado en imagen) -->
                    <div x-data="{ open: false, searchBrand: '' }" class="space-y-3 border-t border-slate-100 pt-4">
                        <h4 @click="open = !open" class="text-[14px] font-bold text-slate-900 mb-2 flex items-center justify-between cursor-pointer">
                            Marcas
                            <span class="material-symbols-outlined text-[20px] transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
                        </h4>
                        
                        <div x-show="open" x-collapse>
                            <div class="relative mb-4">
                                <input type="text" x-model="searchBrand" placeholder="Search Brands" class="w-full border-0 border-b border-slate-200 text-sm py-2 pl-1 pr-8 focus:ring-0 focus:border-slate-400 placeholder-slate-300">
                                <span class="material-symbols-outlined absolute right-1 top-1/2 -translate-y-1/2 text-slate-300 text-[20px]">search</span>
                            </div>

                            <div class="grid grid-cols-2 lg:grid-cols-3 gap-2 max-h-64 overflow-y-auto pr-1 custom-scrollbar">
                                @foreach($marcas as $marca)
                                    <label x-show="searchBrand === '' || '{{ strtolower($marca->name) }}'.includes(searchBrand.toLowerCase())" class="relative group cursor-pointer">
                                        <input type="checkbox" class="peer sr-only" name="marca[]" value="{{ $marca->id }}">
                                        <div class="h-12 border border-slate-200 rounded p-1.5 flex items-center justify-center peer-checked:border-emerald-600 peer-checked:shadow-sm transition-all bg-white hover:border-slate-300">
                                            @if($marca->logo_url)
                                                <img src="{{ $marca->logo_url }}" alt="{{ $marca->name }}" class="max-h-full max-w-full object-contain transition-all">
                                            @else
                                                <span class="text-[9px] font-bold text-slate-400 text-center uppercase truncate w-full">{{ $marca->name }}</span>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- 3. Rango de Precio -->
                    <div class="space-y-2 border-t border-slate-100 pt-4">
                        <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Precio (USD)
                        </h4>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <span class="text-[10px] text-slate-400">Mínimo</span>
                                <input type="number" name="min_precio" value="{{ $precioMin > 0 ? $precioMin : '' }}" placeholder="$0" class="text-xs w-full rounded-xl border-slate-200 py-1.5 px-2">
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400">Máximo</span>
                                <input type="number" name="max_precio" value="{{ $precioMax < 2000 ? $precioMax : '' }}" placeholder="$2000" class="text-xs w-full rounded-xl border-slate-200 py-1.5 px-2">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs transition-colors">
                        Aplicar Filtros
                    </button>
                </form>
            </aside>

            <!-- Grid de Productos (9 columnas en LG) -->
            <main class="lg:col-span-9 space-y-6">
                
                <!-- Barra Superior del Listado: Total y Ordenamiento -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
                    <div class="text-xs text-slate-500">
                        Mostrando <strong class="text-slate-900">{{ $productos->count() }}</strong> de <strong class="text-slate-900">{{ $productos->total() }}</strong> productos
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs text-slate-400">Ordenar por:</span>
                        <select onchange="window.location.href = this.value" class="text-xs rounded-xl border-slate-200 py-1.5 px-3 bg-slate-50 font-medium text-slate-700">
                            <option value="{{ route('cliente.catalogo', array_merge(request()->query(), ['orden' => 'relevancia'])) }}" @selected($orden === 'relevancia')>Relevancia / Destacados</option>
                            <option value="{{ route('cliente.catalogo', array_merge(request()->query(), ['orden' => 'precio_asc'])) }}" @selected($orden === 'precio_asc')>Menor Precio</option>
                            <option value="{{ route('cliente.catalogo', array_merge(request()->query(), ['orden' => 'precio_desc'])) }}" @selected($orden === 'precio_desc')>Mayor Precio</option>
                            <option value="{{ route('cliente.catalogo', array_merge(request()->query(), ['orden' => 'nombre_asc'])) }}" @selected($orden === 'nombre_asc')>Nombre A-Z</option>
                        </select>
                    </div>
                </div>

                <!-- Tarjetas de Productos en Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    @forelse($productos as $prod)
                        @php
                            $img = $prod->imagenPrincipal();
                        @endphp
                        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs hover:shadow-md transition-all flex flex-col group relative">
                            
                            <!-- Indicador de Stock (Punto Verde) -->
                            @if($prod->stock > 0)
                                <div class="absolute top-4 right-4 z-10 flex items-center justify-center w-6 h-6 rounded-full bg-white shadow-xs">
                                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                                </div>
                            @else
                                <div class="absolute top-4 right-4 z-10 flex items-center justify-center w-6 h-6 rounded-full bg-white shadow-xs">
                                    <div class="w-2 h-2 rounded-full bg-rose-500"></div>
                                </div>
                            @endif

                            <!-- Imagen y Badges de Oferta -->
                            <div class="relative h-48 bg-white p-4 flex items-center justify-center">
                                @if($prod->tieneOfertaValida())
                                    <div class="absolute top-4 left-4 z-10">
                                        <span class="px-2 py-1 rounded bg-rose-500 text-white text-[10px] font-bold">
                                            -{{ round((($prod->precio - $prod->precio_oferta) / $prod->precio) * 100) }}%
                                        </span>
                                    </div>
                                @endif

                                @if($img && (str_starts_with($img->ruta, 'http') || str_starts_with($img->ruta, '/storage') || str_starts_with($img->ruta, 'data:image') || str_starts_with($img->ruta, 'storage/')))
                                    <img src="{{ str_starts_with($img->ruta, 'storage/') ? asset($img->ruta) : $img->ruta }}" alt="{{ $prod->nombre }}" class="h-full object-contain group-hover:scale-105 transition-transform duration-300">
                                @elseif($img && (str_starts_with($img->ruta, '<svg') || str_contains($img->ruta, '</svg>')))
                                    <div class="h-full flex items-center justify-center svg-container group-hover:scale-105 transition-transform duration-300">{!! $img->ruta !!}</div>
                                @elseif($img && !empty($img->ruta))
                                    <span class="material-symbols-outlined text-[64px] text-slate-600 group-hover:scale-105 transition-transform duration-300">{{ $img->ruta }}</span>
                                @else
                                    <span class="material-symbols-outlined text-[64px] text-slate-300">image</span>
                                @endif
                            </div>

                            <!-- 4 Botones de Acción (Flotantes o justo debajo de la imagen) -->
                            <div class="flex items-center justify-center gap-3 -mt-6 z-20 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <!-- Botón Carrito -->
                                <button type="button" 
                                        class="w-10 h-10 rounded-full bg-white shadow-md border border-slate-100 flex items-center justify-center text-slate-600 hover:text-white hover:bg-emerald-600 transition-colors tooltip-trigger"
                                        title="Añadir al carrito">
                                    <span class="material-symbols-outlined text-[18px]">shopping_cart</span>
                                </button>
                                <!-- Botón Deseos -->
                                <button type="button" 
                                        class="w-10 h-10 rounded-full bg-white shadow-md border border-slate-100 flex items-center justify-center text-slate-600 hover:text-white hover:bg-rose-500 transition-colors tooltip-trigger"
                                        title="Añadir a deseos">
                                    <span class="material-symbols-outlined text-[18px]">favorite</span>
                                </button>
                                <!-- Botón Comparar -->
                                <button type="button" 
                                        class="w-10 h-10 rounded-full bg-white shadow-md border border-slate-100 flex items-center justify-center text-slate-600 hover:text-white hover:bg-blue-600 transition-colors tooltip-trigger"
                                        title="Comparar">
                                    <span class="material-symbols-outlined text-[18px]">compare_arrows</span>
                                </button>
                                <!-- Botón Ver Detalle -->
                                <a href="{{ route('cliente.producto.detalle', $prod->slug) }}" 
                                   class="w-10 h-10 rounded-full bg-white shadow-md border border-slate-100 flex items-center justify-center text-slate-600 hover:text-white hover:bg-slate-800 transition-colors tooltip-trigger"
                                   title="Vista rápida">
                                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                                </a>
                            </div>

                            <!-- Contenido de la Tarjeta -->
                            <div class="p-5 flex-1 flex flex-col justify-end space-y-2 text-center mt-2">
                                <h3 class="text-[13px] font-bold text-slate-900 group-hover:text-emerald-700 transition-colors line-clamp-2 leading-tight">
                                    <a href="{{ route('cliente.producto.detalle', $prod->slug) }}">
                                        {{ $prod->nombre }}
                                    </a>
                                </h3>
                                
                                <span class="text-[11px] font-medium text-slate-400 block">
                                    SKU: {{ $prod->sku }}
                                </span>

                                <div class="pt-2">
                                    @if($prod->tienePromocionOPrecioOferta())
                                        <div class="text-lg font-extrabold text-emerald-700">${{ number_format($prod->precioFinalPromocional(), 2) }}</div>
                                        <div class="text-[11px] text-slate-400 line-through">${{ number_format($prod->precio, 2) }}</div>
                                    @else
                                        <div class="text-lg font-extrabold text-emerald-700">${{ number_format($prod->precio, 2) }}</div>
                                    @endif
                                </div>
                            </div>

                        </div>
                    @empty
                        <div class="col-span-full py-16 text-center text-slate-500 bg-white rounded-2xl border border-slate-200">
                            <span class="material-symbols-outlined text-[48px] text-slate-300 mx-auto">search_off</span>
                            <h3 class="text-sm font-bold text-slate-700 mt-2">No encontramos productos con estos filtros</h3>
                            <p class="text-xs text-slate-400 mt-1">Intenta con otros términos de búsqueda o categorías.</p>
                            <a href="{{ route('cliente.catalogo') }}" class="inline-block mt-4 text-xs font-semibold text-emerald-700 hover:underline">
                                Ver todo el catálogo
                            </a>
                        </div>
                    @endforelse

                </div>

                <!-- Paginación -->
                @if($productos->hasPages())
                    <div class="pt-4 flex items-center justify-center">
                        {{ $productos->links() }}
                    </div>
                @endif

            </main>

        </div>

    </div>
</div>
@endsection
