@extends('layouts.cliente')

@section('title', 'Catálogo de Productos - PayMe Panamá')

@section('content')
<div class="min-h-screen bg-slate-50 py-6 sm:py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-1.5 text-xs text-slate-500 font-medium" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}" wire:navigate class="hover:text-emerald-700 transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">home</span>
                <span>Inicio</span>
            </a>
            <span class="material-symbols-outlined text-[14px] text-slate-400">chevron_right</span>
            <span class="text-slate-900 font-bold">Catálogo de Productos</span>
        </nav>

        <!-- Barra Superior Secundaria (Solo PC) -->
        <div class="hidden md:block bg-white rounded-2xl border border-slate-200 shadow-xs p-2 mb-4">
            <!-- Menú Horizontal -->
            <div class="flex flex-wrap items-center gap-4 px-4 py-2 border-b border-slate-100 mb-3">

                <!-- Botón Productos: 2 columnas (categorías | subcategorías) -->
                <div x-data="{ open: false, activeCategory: null }" class="relative z-40" @click.away="open = false; activeCategory = null">
                    <button @click="open = !open"
                            class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg px-4 py-2 flex items-center justify-between sm:justify-start gap-2 text-sm font-bold cursor-pointer transition-colors">
                        <span class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                            </svg>
                            Productos
                        </span>
                        <span class="material-symbols-outlined text-[18px] transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
                    </button>

                    <!-- Panel de 2 columnas anclado directamente bajo el botón -->
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-1"
                         class="absolute top-full left-0 mt-2 bg-white rounded-xl shadow-2xl border border-slate-200 flex overflow-hidden"
                         style="display:none; min-width:560px; max-height:400px; z-index:50;">

                        <!-- Columna izquierda: lista de categorías padre -->
                        <div class="w-56 shrink-0 border-r border-slate-100 py-2 overflow-y-auto bg-white">
                            @foreach($categorias as $cat)
                                <div @mouseenter="activeCategory = {{ $cat->id }}"
                                     class="flex items-center justify-between px-4 py-2.5 cursor-pointer select-none transition-colors border-l-4"
                                     :class="activeCategory === {{ $cat->id }}
                                         ? 'bg-emerald-50 text-emerald-700 font-bold border-emerald-500'
                                         : 'text-slate-700 font-semibold border-transparent hover:bg-slate-50 hover:border-emerald-200'">
                                    <span class="flex items-center gap-3">
                                        @if($cat->imagen_ruta)
                                            <img src="{{ str_starts_with($cat->imagen_ruta, 'http') ? $cat->imagen_ruta : asset($cat->imagen_ruta) }}"
                                                 alt="" class="w-5 h-5 object-contain shrink-0">
                                        @else
                                            <span class="material-symbols-outlined text-[18px] text-emerald-500 shrink-0">category</span>
                                        @endif
                                        <span class="text-sm">{{ $cat->nombre }}</span>
                                    </span>
                                    @if($cat->hijas->count() > 0)
                                        <span class="material-symbols-outlined text-[16px] text-slate-400 shrink-0">chevron_right</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <!-- Columna derecha: subcategorías de la categoría en hover -->
                        <div class="flex-1 overflow-y-auto bg-slate-50/60">

                            <!-- Placeholder cuando ninguna categoría está activa -->
                            <div x-show="activeCategory === null"
                                 class="flex flex-col items-center justify-center h-full py-10 text-slate-400"
                                 style="display:flex;">
                                <span class="material-symbols-outlined text-[40px] mb-2">category</span>
                                <p class="text-xs font-medium text-center">Pasa el cursor sobre<br>una categoría</p>
                            </div>

                            <!-- Panel de subcategorías por categoría -->
                            @foreach($categorias as $cat)
                                <div x-show="activeCategory === {{ $cat->id }}"
                                     class="px-5 py-4"
                                     style="display:none;">
                                    <h3 class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 pb-2 border-b border-slate-200">
                                        {{ $cat->nombre }}
                                    </h3>
                                    @if($cat->hijas->count() > 0)
                                        <div class="space-y-0.5">
                                            <a href="{{ route('cliente.catalogo', ['categoria' => $cat->slug]) }}" wire:navigate
                                               class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-bold text-emerald-700 hover:bg-emerald-50 transition-colors">
                                                <span class="material-symbols-outlined text-[14px]">apps</span>
                                                Ver todo en {{ $cat->nombre }}
                                            </a>
                                            @foreach($cat->hijas as $hija)
                                                <a href="{{ route('cliente.catalogo', ['categoria' => $hija->slug]) }}" wire:navigate
                                                   class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0"></span>
                                                    {{ $hija->nombre }}
                                                </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <a href="{{ route('cliente.catalogo', ['categoria' => $cat->slug]) }}" wire:navigate
                                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-bold text-emerald-700 hover:bg-emerald-50 transition-colors">
                                            <span class="material-symbols-outlined text-[16px]">open_in_new</span>
                                            Ver todos los productos
                                        </a>
                                    @endif
                                </div>
                            @endforeach

                        </div>
                    </div>
                </div>

                <nav class="flex flex-wrap items-center gap-4 text-sm font-semibold">

                    <!-- Categorías Dropdown -->
                    <div x-data="{ open: false }" class="relative z-30" @click.away="open = false">
                        <button @click="open = !open" class="text-slate-900 hover:text-emerald-700 transition-colors flex items-center gap-1">
                            Categorías <span class="material-symbols-outlined text-[16px] transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
                        </button>
                        <div x-show="open" x-transition.opacity class="absolute top-full left-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-slate-100 py-2" style="display: none;">
                            @foreach($categorias as $cat)
                                <a href="{{ route('cliente.catalogo', ['categoria' => $cat->slug]) }}" wire:navigate class="block px-4 py-2 text-sm text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 font-semibold">
                                    {{ $cat->nombre }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <a href="{{ route('cliente.catalogo') }}" wire:navigate class="text-slate-600 hover:text-emerald-700 transition-colors">Todos los productos</a>

                    <!-- Lo más vendido -->
                    <div x-data="{ open: false }" class="relative z-30" @click.away="open = false">
                        <button @click="open = !open" class="text-slate-600 hover:text-emerald-700 transition-colors flex items-center gap-1">
                            Lo más vendido <span class="material-symbols-outlined text-[16px] transition-transform" :class="open ? 'rotate-180' : ''">expand_more</span>
                        </button>
                        <div x-show="open" x-transition.opacity class="absolute top-full left-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-slate-100 py-2" style="display: none;">
                            <a href="{{ route('cliente.catalogo', ['orden' => 'relevancia']) }}" wire:navigate class="block px-4 py-2 text-sm text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 font-semibold">Top Ventas Global</a>
                        </div>
                    </div>
                </nav>
            </div>

            <div class="px-4 pb-2">
                <h2 class="text-base font-bold text-slate-900 mb-3">Categorías</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($categoriasPills as $cat)
                        <a href="{{ route('cliente.catalogo', ['categoria' => $cat->slug]) }}" wire:navigate
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
                    <a href="{{ route('cliente.catalogo') }}" wire:navigate class="text-[11px] font-semibold text-emerald-700 hover:underline">
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
                    <div class="space-y-2 border-t border-slate-100 pt-4 hidden lg:block" x-data="{ verMas: false }">
                        <h4 class="text-xs font-bold text-slate-700 tracking-wider mb-2 flex items-center justify-between">
                            Categorías
                            <span class="material-symbols-outlined text-[16px]">expand_more</span>
                        </h4>
                        <div class="space-y-1 text-xs">
                            <a href="{{ route('cliente.catalogo', array_merge(request()->query(), ['categoria' => 'all'])) }}" wire:navigate
                               class="flex items-center justify-between px-2.5 py-1.5 rounded-lg {{ $categoriaSlug === 'all' ? 'font-bold text-slate-900' : 'text-slate-600 hover:bg-slate-50' }} transition-colors">
                                <span class="flex items-center gap-2">
                                    <input type="radio" {{ $categoriaSlug === 'all' ? 'checked' : '' }} class="text-blue-600 border-slate-300 focus:ring-blue-500">
                                    Todos los productos
                                </span>
                            </a>
                            @foreach($categorias as $index => $cat)
                                <a href="{{ route('cliente.catalogo', array_merge(request()->query(), ['categoria' => $cat->slug])) }}" wire:navigate
                                   x-show="verMas || {{ $index }} < 5"
                                   {!! $index >= 5 ? 'style="display:none;"' : '' !!}
                                   class="flex items-center justify-between px-2.5 py-1.5 rounded-lg {{ $categoriaSlug === $cat->slug ? 'font-bold text-slate-900' : 'text-slate-600 hover:bg-slate-50' }} transition-colors">
                                    <span class="flex items-center gap-2">
                                        <input type="radio" {{ $categoriaSlug === $cat->slug ? 'checked' : '' }} class="text-blue-600 border-slate-300 focus:ring-blue-500">
                                        {{ $cat->nombre }} <span class="text-slate-400 font-normal">({{ $cat->productos_count }})</span>
                                    </span>
                                </a>
                            @endforeach
                            @if(count($categorias) > 5)
                                <button type="button" @click="verMas = !verMas" class="text-emerald-700 hover:underline font-semibold mt-2 px-2.5 py-1 text-[11px] block text-left">
                                    <span x-text="verMas ? '- Ver menos' : '+ Ver más'"></span>
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- 3. Marcas -->
                    <div x-data="{ open: {{ count(request('marca', [])) > 0 ? 'true' : 'false' }}, searchBrand: '' }" class="space-y-3 border-t border-slate-100 pt-4 hidden lg:block">
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
                                        <input type="checkbox" class="peer sr-only" name="marca[]" value="{{ $marca->id }}" {{ in_array($marca->id, request('marca', [])) ? 'checked' : '' }}>
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

                    <!-- 4. Rango de Precio -->
                    <div class="space-y-2 border-t border-slate-100 pt-4 hidden lg:block">
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
                        <x-producto-card :prod="$prod" />
                    @empty
                        <div class="col-span-full py-16 text-center text-slate-500 bg-white rounded-2xl border border-slate-200">
                            <span class="material-symbols-outlined text-[48px] text-slate-300 mx-auto">search_off</span>
                            <h3 class="text-sm font-bold text-slate-700 mt-2">No encontramos productos con estos filtros</h3>
                            <p class="text-xs text-slate-400 mt-1">Intenta con otros términos de búsqueda o categorías.</p>
                            <a href="{{ route('cliente.catalogo') }}" wire:navigate class="inline-block mt-4 text-xs font-semibold text-emerald-700 hover:underline">
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
