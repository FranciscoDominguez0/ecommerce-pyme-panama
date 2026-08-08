@extends('layouts.cliente')

@section('title', 'Mi Lista de Deseos')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
    
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-6" aria-label="Breadcrumb">
        <a href="{{ url('/') }}" class="hover:text-[#002349] transition-colors">Inicio</a>
        <span class="text-gray-300">/</span>
        <a href="{{ route('cliente.catalogo') }}" class="hover:text-[#002349] transition-colors">Catálogo</a>
        <span class="text-gray-300">/</span>
        <span class="text-[#002349]">Lista de Deseos</span>
    </nav>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#002349] tracking-tight flex items-center gap-2.5">
                <span class="material-symbols-outlined text-red-500 text-3xl">favorite</span>
                <span>Mi Lista de Deseos</span>
            </h1>
            <p class="text-sm text-gray-500 mt-1 font-medium">
                {{ $productos->count() }} {{ $productos->count() === 1 ? 'producto guardado' : 'productos guardados' }} para comprar más tarde
            </p>
        </div>

        @if($productos->isNotEmpty())
            <a href="{{ route('cliente.catalogo') }}" 
               class="inline-flex items-center gap-1.5 text-xs font-bold text-[#006148] hover:text-[#004f3b] transition-colors">
                <span class="material-symbols-outlined text-[16px]">storefront</span>
                <span>Explorar más productos</span>
            </a>
        @endif
    </div>

    @if($productos->isEmpty())
        <!-- Estado Vacío -->
        <section class="bg-white border border-gray-200/90 rounded-2xl p-8 sm:p-16 flex flex-col items-center justify-center text-center shadow-xs my-6">
            <div class="w-20 h-20 bg-red-50 text-red-400 rounded-full flex items-center justify-center mb-6 shadow-inner">
                <span class="material-symbols-outlined text-4xl">favorite_border</span>
            </div>

            <h2 class="text-xl sm:text-2xl font-extrabold text-[#002349] mb-2 tracking-tight">
                Tu lista de deseos está vacía
            </h2>

            <p class="text-sm text-gray-600 mb-8 max-w-md leading-relaxed">
                Guarda los productos tecnológicos que te interesen para revisarlos o comprarlos después cuando estés listo.
            </p>

            <a href="{{ route('cliente.catalogo') }}" 
               class="bg-[#006148] hover:bg-[#004f3b] text-white font-bold text-xs uppercase tracking-wider px-8 py-3.5 rounded-full shadow-sm hover:shadow-md transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">storefront</span>
                <span>Ir al catálogo</span>
            </a>
        </section>
    @else
        <!-- Grid de Productos -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($productos as $producto)
                @php
                    $imgRuta = $producto->imagenes->first() ? asset('storage/' . $producto->imagenes->first()->ruta) : asset('images/placeholder-product.png');
                @endphp
                <div class="bg-white border border-gray-200/90 rounded-2xl p-4 sm:p-5 flex flex-col group hover:shadow-md hover:border-gray-300 transition-all">
                    
                    <!-- Imagen con Badge de Descuento -->
                    <div class="w-full aspect-square bg-gray-50 rounded-xl overflow-hidden mb-4 relative border border-gray-100">
                        <img src="{{ $imgRuta }}" 
                             alt="{{ $producto->nombre }}" 
                             class="w-full h-full object-contain p-2 group-hover:scale-105 transition-transform duration-300"
                             onerror="this.onerror=null; this.src='https://placehold.co/200x200?text=Sin+Imagen';" />
                        
                        @if($producto->oferta_activa && $producto->precio_oferta && (float)$producto->precio > (float)$producto->precio_oferta)
                            @php
                                $descuentoPorc = round((((float)$producto->precio - (float)$producto->precio_oferta) / (float)$producto->precio) * 100);
                            @endphp
                            <span class="absolute top-2.5 right-2.5 bg-red-600 text-white font-bold text-[10px] px-2 py-0.5 rounded-full shadow-xs">
                                -{{ $descuentoPorc }}%
                            </span>
                        @endif

                        @if($producto->stock <= 0)
                            <div class="absolute inset-0 bg-white/70 backdrop-blur-2xs flex items-center justify-center">
                                <span class="bg-red-700 text-white text-xs font-bold px-3 py-1 rounded-full shadow-xs">
                                    Agotado
                                </span>
                            </div>
                        @endif
                    </div>

                    <!-- Marca y Nombre -->
                    @if($producto->brand)
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#006148] block mb-1">
                            {{ $producto->brand->name }}
                        </span>
                    @endif

                    <h3 class="text-sm font-bold text-gray-900 line-clamp-2 min-h-[40px] leading-snug hover:text-[#006148] transition-colors">
                        <a href="{{ route('cliente.producto.detalle', $producto->slug) }}">
                            {{ $producto->nombre }}
                        </a>
                    </h3>

                    <!-- Precio -->
                    <div class="mt-auto pt-3">
                        <div class="flex items-baseline gap-2 mb-4">
                            @if($producto->oferta_activa && $producto->precio_oferta)
                                <span class="text-lg font-extrabold text-[#002349] font-mono">
                                    ${{ number_format($producto->precio_oferta, 2) }}
                                </span>
                                <span class="text-xs text-gray-400 line-through font-mono">
                                    ${{ number_format($producto->precio, 2) }}
                                </span>
                            @else
                                <span class="text-lg font-extrabold text-[#002349] font-mono">
                                    ${{ number_format($producto->precio, 2) }}
                                </span>
                            @endif
                        </div>

                        <!-- Botones de Acción -->
                        <div class="flex items-center gap-2">
                            @if($producto->stock > 0)
                                <form method="POST" action="{{ route('cliente.lista-deseos.mover-al-carrito', $producto->id) }}" class="flex-1">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full bg-[#002349] hover:bg-[#001730] text-white text-xs font-bold py-2.5 px-3 rounded-xl transition-all flex items-center justify-center gap-1.5 shadow-2xs cursor-pointer">
                                        <span class="material-symbols-outlined text-[16px]">add_shopping_cart</span>
                                        <span>Mover al Carrito</span>
                                    </button>
                                </form>
                            @else
                                <button type="button" 
                                        disabled 
                                        class="flex-1 bg-gray-100 text-gray-400 text-xs font-bold py-2.5 px-3 rounded-xl cursor-not-allowed">
                                    Agotado
                                </button>
                            @endif

                            <form method="POST" action="{{ route('cliente.lista-deseos.eliminar', $producto->id) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="p-2 border border-gray-200 text-gray-400 hover:text-red-600 hover:bg-red-50 hover:border-red-200 rounded-xl transition-colors cursor-pointer" 
                                        title="Eliminar de la lista de deseos">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            @endforeach
        </div>
    @endif

</div>
@endsection
