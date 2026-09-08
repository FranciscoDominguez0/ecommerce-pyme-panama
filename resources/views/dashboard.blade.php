@extends('layouts.cliente')

@section('title', 'Dashboard')

@section('content')
<div class="min-h-screen bg-[#F4F6F8] pb-12">
    
    <!-- 1. Hero Banner -->
    <div class="relative w-full overflow-hidden text-white" style="background: linear-gradient(135deg, #060d18 0%, #0b1628 40%, #091a10 100%);">
        
        <!-- Glow blobs estilo Welcome -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="absolute top-[-80px] right-[-60px] w-[500px] h-[500px] rounded-full opacity-20"
                style="background: radial-gradient(circle, #22c55e 0%, transparent 65%);"></div>
            <div class="absolute bottom-[-60px] left-[-40px] w-[300px] h-[300px] rounded-full opacity-10"
                style="background: radial-gradient(circle, #3b82f6 0%, transparent 65%);"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14 relative z-10">
            <div class="flex flex-col md:flex-row items-center justify-between gap-8">
                <!-- Columna Texto -->
                <div class="w-full md:w-3/5">
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight mb-4 text-white drop-shadow-md">
                        ¡Hola, <span class="text-emerald-400">{{ Auth::check() ? Auth::user()->nombre : 'Bienvenido' }}</span>!
                    </h1>
                    <p class="text-base md:text-lg text-slate-300 mb-8 max-w-lg leading-relaxed drop-shadow-sm">
                        Es el momento perfecto para renovar tu setup. Descubre nuestro catálogo de tecnología con envío a todo Panamá y garantía directa.
                    </p>
                    <div class="flex items-center gap-4">
                        <a href="{{ route('cliente.catalogo', ['ofertas' => 1]) }}" wire:navigate class="inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-400 text-slate-900 px-6 py-3 rounded-xl font-black transition-colors shadow-lg shadow-emerald-500/20">
                            Ver Ofertas Especiales
                            <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                        </a>
                    </div>
                </div>
                
                <!-- Columna Showcase (Productos Reales con Glassmorphism Oscuro) -->
                <div class="w-full md:w-2/5 hidden md:block">
                    <div class="grid grid-cols-2 gap-4">
                        @foreach($productos->take(2) as $heroProd)
                        <a href="{{ route('cliente.producto.detalle', $heroProd->slug) }}" wire:navigate class="bg-white/5 backdrop-blur-xl border border-white/10 shadow-2xl rounded-2xl p-5 hover:bg-white/10 hover:border-white/20 transition-all duration-300 group relative flex flex-col items-center text-center overflow-hidden">
                            <!-- Efecto de brillo interior hover -->
                            <div class="absolute inset-0 bg-gradient-to-tr from-white/0 via-white/0 to-emerald-400/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            
                            <div class="w-28 h-28 mb-4 flex items-center justify-center p-3 bg-black/40 backdrop-blur-md border border-white/5 rounded-2xl shadow-inner relative z-10 overflow-hidden">
                                @if($heroProd->imagenPrincipal())
                                    <img src="{{ str_starts_with($heroProd->imagenPrincipal()->ruta, 'http') ? $heroProd->imagenPrincipal()->ruta : asset(ltrim($heroProd->imagenPrincipal()->ruta, '/')) }}" alt="{{ $heroProd->nombre }}" class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-500 relative z-10 drop-shadow-lg">
                                @else
                                    <span class="material-symbols-outlined text-[40px] text-white/30 group-hover:text-white/50 transition-colors">image</span>
                                @endif
                            </div>
                            <h4 class="text-sm font-bold text-slate-200 mb-1.5 truncate w-full relative z-10 drop-shadow-sm">{{ $heroProd->nombre }}</h4>
                            <span class="text-emerald-400 font-black text-base relative z-10 drop-shadow-sm">${{ number_format($heroProd->precioFinalPromocional(), 2) }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 relative z-20 space-y-8">

        <!-- 3. Categorías Rápidas -->
        @if(isset($categoriasPrincipales) && $categoriasPrincipales->count() > 0)
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-extrabold text-[#002349]">Explorar Categorías</h2>
            </div>
            <div class="flex overflow-x-auto pb-4 gap-4 sm:gap-6 hide-scrollbar snap-x md:justify-center">
                @foreach($categoriasPrincipales as $cat)
                <a href="{{ route('cliente.catalogo', ['categoria' => $cat->slug]) }}" wire:navigate class="snap-start shrink-0 w-24 flex flex-col items-center gap-3 group">
                    <div class="w-16 h-16 rounded-full bg-white border border-slate-200 shadow-sm flex items-center justify-center group-hover:shadow-md group-hover:border-emerald-400 group-hover:-translate-y-1 transition-all">
                        <!-- Icono genérico basado en slug si no tiene imagen -->
                        @php
                            $icon = 'devices';
                            if(str_contains($cat->slug, 'laptop')) $icon = 'laptop_mac';
                            if(str_contains($cat->slug, 'gamer') || str_contains($cat->slug, 'consola')) $icon = 'sports_esports';
                            if(str_contains($cat->slug, 'smartphones') || str_contains($cat->slug, 'celular')) $icon = 'smartphone';
                            if(str_contains($cat->slug, 'monitor')) $icon = 'monitor';
                            if(str_contains($cat->slug, 'teclado') || str_contains($cat->slug, 'mouse')) $icon = 'keyboard';
                            if(str_contains($cat->slug, 'red') || str_contains($cat->slug, 'router')) $icon = 'router';
                            if(str_contains($cat->slug, 'audio') || str_contains($cat->slug, 'audifonos')) $icon = 'headphones';
                        @endphp
                        <span class="material-symbols-outlined text-[28px] text-slate-700 group-hover:text-emerald-600 transition-colors">{{ $icon }}</span>
                    </div>
                    <span class="text-xs font-semibold text-slate-700 text-center leading-tight group-hover:text-emerald-700">{{ $cat->nombre }}</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif

        <!-- 4. Banner Promocional Secundario a 2 Columnas -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Oferta Destacada -->
            <div class="lg:col-span-2 bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl overflow-hidden relative shadow-md flex items-center min-h-[220px]">
                <div class="absolute right-0 top-0 h-full w-1/2 opacity-30 pointer-events-none" style="background: radial-gradient(circle at 100% 50%, #006148 0%, transparent 70%);"></div>
                <div class="p-8 md:p-10 relative z-10 w-full sm:w-2/3">
                    <div class="inline-block px-3 py-1 bg-rose-600 text-white text-[10px] font-black uppercase tracking-wider rounded-full mb-3">
                        Venta Flash
                    </div>
                    <h2 class="text-2xl md:text-3xl font-black text-white mb-2 leading-tight">Hasta 40% OFF en Laptops Seleccionadas</h2>
                    <p class="text-slate-300 text-sm mb-6">Equípate con lo mejor para el trabajo y el gaming.</p>
                    <a href="{{ route('cliente.catalogo', ['ofertas' => 1]) }}" wire:navigate class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-slate-900 hover:bg-slate-100 rounded-xl font-bold text-sm transition-colors">
                        Comprar Ahora
                        <span class="material-symbols-outlined text-[18px]">shopping_cart_checkout</span>
                    </a>
                </div>
            </div>

            <!-- Continúa Viendo (Mini Widget) -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between">
                <div>
                    <h3 class="text-base font-bold text-[#002349] mb-4">Recomendado para ti</h3>
                    <div class="space-y-4">
                        @foreach($productos->take(2) as $miniProd)
                        <a href="{{ route('cliente.producto.detalle', $miniProd->slug) }}" wire:navigate class="flex items-center gap-4 group">
                            <div class="w-16 h-16 rounded-lg bg-slate-50 border border-slate-100 flex-shrink-0 overflow-hidden flex items-center justify-center p-1">
                                @if($miniProd->imagenPrincipal())
                                    <img src="{{ str_starts_with($miniProd->imagenPrincipal()->ruta, 'http') ? $miniProd->imagenPrincipal()->ruta : asset(ltrim($miniProd->imagenPrincipal()->ruta, '/')) }}" alt="{{ $miniProd->nombre }}" class="w-full h-full object-contain group-hover:scale-110 transition-transform">
                                @else
                                    <span class="material-symbols-outlined text-gray-300 text-2xl">image</span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-xs font-bold text-slate-800 truncate group-hover:text-emerald-700 transition-colors">{{ $miniProd->nombre }}</h4>
                                <div class="text-emerald-600 font-bold text-sm mt-0.5">${{ number_format($miniProd->precioFinalPromocional(), 2) }}</div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                <a href="{{ route('cliente.catalogo') }}" wire:navigate class="text-xs font-semibold text-blue-600 hover:text-blue-800 mt-4 inline-block">Ver más recomendaciones &rarr;</a>
            </div>
        </div>

        <!-- 4.5. Banner Promocional Full Width (Entretenimiento) -->
        <a href="{{ route('cliente.catalogo') }}" wire:navigate class="block w-full rounded-2xl overflow-hidden relative shadow-sm hover:shadow-md transition-shadow group">
            <img src="{{ asset('images/Banners/Home-banner-entretenimiento-01.webp') }}" alt="Equipos de Entretenimiento" class="w-full h-auto object-cover group-hover:scale-[1.02] transition-transform duration-500">
        </a>

        <!-- 5. Grid de Productos -->
        <div>
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-extrabold text-[#002349]">Ofertas Top & Tendencias</h2>
                <a href="{{ route('cliente.catalogo') }}" wire:navigate class="text-sm font-semibold text-emerald-700 hover:underline hidden sm:block">Ver todo el catálogo</a>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                @forelse($productos as $prod)
                    <x-producto-card :prod="$prod" />
                @empty
                    <div class="col-span-full text-center py-16 bg-white rounded-2xl border border-slate-200 shadow-sm">
                        <span class="material-symbols-outlined text-5xl text-slate-300 block mb-3">inventory_2</span>
                        <p class="text-base font-bold text-slate-600">No hay productos disponibles en este momento</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- 6. Marcas Destacadas -->
        @if(isset($marcas) && $marcas->count() > 0)
            <x-marcas-carousel :marcas="$marcas" />
        @endif

    </div>
</div>
@endsection
