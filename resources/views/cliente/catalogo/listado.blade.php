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

        <!-- Banner de Cabecera del Catálogo -->
        <div class="rounded-3xl bg-gradient-to-r from-slate-900 via-primary to-slate-900 text-white p-6 sm:p-10 relative overflow-hidden shadow-lg">
            <div class="relative z-10 max-w-2xl space-y-3">
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                    <span class="material-symbols-outlined text-[14px]">local_shipping</span>
                    <span>Envíos a todo Panamá</span>
                </span>
                <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight">
                    Catálogo Oficial de Productos
                </h1>
                <p class="text-xs sm:text-sm text-slate-300">
                    Explora nuestra selección de tecnología, periféricos y accesorios con garantía oficial, facturación fiscal y pago directo en Panamá.
                </p>
            </div>
            
            <!-- Decoración de Fondo -->
            <div class="absolute right-0 top-0 bottom-0 w-1/3 bg-radial from-emerald-500/10 to-transparent pointer-events-none"></div>
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
                        <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Categorías
                        </h4>
                        <div class="space-y-1 text-xs">
                            <a href="{{ route('cliente.catalogo', array_merge(request()->query(), ['categoria' => 'all'])) }}" 
                               class="flex items-center justify-between px-2.5 py-1.5 rounded-lg {{ $categoriaSlug === 'all' ? 'font-bold bg-emerald-50 text-emerald-800' : 'text-slate-600 hover:bg-slate-50' }} transition-colors">
                                <span>Todas las categorías</span>
                                <span class="text-[10px] {{ $categoriaSlug === 'all' ? 'bg-emerald-200/60 font-bold' : 'text-slate-400' }} px-1.5 py-0.5 rounded-full">
                                    {{ $categorias->sum('productos_count') }}
                                </span>
                            </a>
                            @foreach($categorias as $cat)
                                <a href="{{ route('cliente.catalogo', array_merge(request()->query(), ['categoria' => $cat->slug])) }}" 
                                   class="flex items-center justify-between px-2.5 py-1.5 rounded-lg {{ $categoriaSlug === $cat->slug ? 'font-bold bg-emerald-50 text-emerald-800' : 'text-slate-600 hover:bg-slate-50' }} transition-colors">
                                    <span>{{ $cat->nombre }}</span>
                                    <span class="text-[10px] {{ $categoriaSlug === $cat->slug ? 'bg-emerald-200/60 font-bold' : 'text-slate-400' }} px-1.5 py-0.5 rounded-full">
                                        {{ $cat->productos_count }}
                                    </span>
                                </a>
                            @endforeach
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
                        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs hover:shadow-md hover:border-emerald-200 transition-all flex flex-col group">
                            
                            <!-- Imagen y Badges -->
                            <div class="relative h-48 bg-slate-50 p-4 flex items-center justify-center overflow-hidden">
                                @if($prod->tieneOfertaValida())
                                    <div class="absolute top-3 left-3 z-10">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-500 text-white shadow-xs">
                                            -{{ round((($prod->precio - $prod->precio_oferta) / $prod->precio) * 100) }}% OFF
                                        </span>
                                    </div>
                                @elseif($prod->destacado)
                                    <div class="absolute top-3 left-3 z-10">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500 text-white shadow-xs flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[12px]">star</span>
                                            <span>Destacado</span>
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

                            <!-- Contenido de la Tarjeta -->
                            <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
                                <div class="space-y-1">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">
                                        {{ $prod->categoria?->nombre ?? 'Catálogo' }}
                                    </span>
                                    <h3 class="text-sm font-bold text-slate-900 group-hover:text-emerald-700 transition-colors line-clamp-2">
                                        <a href="{{ route('cliente.producto.detalle', $prod->slug) }}">
                                            {{ $prod->nombre }}
                                        </a>
                                    </h3>
                                    <p class="text-xs text-slate-500 line-clamp-2">
                                        {{ $prod->descripcion_corta ?? $prod->descripcion }}
                                    </p>
                                </div>

                                <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                                    <div>
                                        @if($prod->tieneOfertaValida())
                                            <div class="text-base font-extrabold text-emerald-700">${{ number_format($prod->precio_oferta, 2) }}</div>
                                            <div class="text-[10px] text-slate-400 line-through">${{ number_format($prod->precio, 2) }}</div>
                                        @else
                                            <div class="text-base font-extrabold text-slate-900">${{ number_format($prod->precio, 2) }}</div>
                                            <div class="text-[10px] text-slate-400">USD + ITBMS</div>
                                        @endif
                                    </div>

                                    <a href="{{ route('cliente.producto.detalle', $prod->slug) }}" 
                                       class="inline-flex items-center justify-center p-2 rounded-xl bg-slate-100 hover:bg-emerald-600 hover:text-white text-slate-700 transition-colors">
                                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                                    </a>
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
