@extends('layouts.cliente')

@section('title', ($producto->nombre ?? 'Detalle del Producto') . ' - PayMe Panamá')

@section('content')
<div class="min-h-screen bg-slate-50 py-6 sm:py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <!-- Breadcrumbs Navegables -->
        <nav class="flex flex-wrap items-center gap-1.5 text-xs text-slate-500 font-medium" aria-label="Breadcrumb">
            <a href="{{ route('inicio') }}" wire:navigate class="hover:text-emerald-700 transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">home</span>
                <span>Inicio</span>
            </a>
            <span class="material-symbols-outlined text-[14px] text-slate-400">chevron_right</span>
            <a href="{{ route('cliente.catalogo') }}" wire:navigate class="hover:text-emerald-700 transition-colors">Catálogo</a>
            @if($producto->categoria)
                <span class="material-symbols-outlined text-[14px] text-slate-400">chevron_right</span>
                <a href="{{ route('cliente.catalogo', ['categoria' => $producto->categoria->slug]) }}" wire:navigate class="text-slate-600 hover:text-emerald-700">
                    {{ $producto->categoria->nombre }}
                </a>
            @endif
            <span class="material-symbols-outlined text-[14px] text-slate-400">chevron_right</span>
            <span class="text-slate-900 font-bold">{{ $producto->nombre }}</span>
        </nav>

        <!-- Bloque Principal de Producto (Galería Prioritaria + Compra) -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm p-5 sm:p-8 lg:p-10 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
            
            <!-- Columna Izquierda: Galería de Imágenes Prioritaria y Ancha (7 columnas) -->
            <div class="lg:col-span-7 space-y-4 lg:sticky lg:top-24" id="galeria-producto-contenedor">
                
                @php
                    $imgPrincipal = $producto->imagenPrincipal();
                    $todasImagenes = $producto->imagenes ?? collect();
                    
                    // Si no tiene imágenes, crear un item por defecto
                    if ($todasImagenes->isEmpty()) {
                        $todasImagenes = collect([(object)[
                            'id' => 0,
                            'ruta' => 'image',
                            'es_principal' => true
                        ]]);
                    }

                    // Formatear array JSON estructurado para el visor dinámico
                    $imagenesJson = $todasImagenes->map(function($foto, $index) {
                        $ruta = $foto->ruta;
                        $esUrl = str_starts_with($ruta, 'http') || str_starts_with($ruta, 'data:image');
                        $esStorage = str_starts_with($ruta, 'storage/') || str_starts_with($ruta, '/storage');
                        $esSvg = str_contains($ruta, '<svg') || str_contains($ruta, '</svg>');
                        $esIcono = !$esUrl && !$esStorage && !$esSvg;
                        
                        $src = '';
                        if ($esStorage) {
                            $src = asset(ltrim($ruta, '/'));
                        } elseif ($esUrl) {
                            $src = $ruta;
                        }

                        return [
                            'index' => $index,
                            'id' => $foto->id ?? $index,
                            'ruta' => $ruta,
                            'src' => $src,
                            'es_svg' => $esSvg,
                            'es_icono' => $esIcono,
                            'es_principal' => (bool)($foto->es_principal ?? false)
                        ];
                    })->values();

                    // Índice inicial (priorizar la principal)
                    $indiceInicial = $imagenesJson->search(fn($img) => $img['es_principal']);
                    if ($indiceInicial === false) $indiceInicial = 0;
                @endphp

                <!-- Visor Principal de Imagen Amplio y Protagónico -->
                <div class="relative w-full h-[320px] sm:h-[380px] md:h-[400px] lg:h-[410px] rounded-3xl bg-white border border-slate-200/90 shadow-sm flex items-center justify-center p-6 sm:p-8 overflow-hidden select-none group/visor"
                     style="background: radial-gradient(circle at 50% 50%, #ffffff 0%, #fafbfc 75%, #f3f5f8 100%);">
                    
                    {{-- Badge de Oferta / Descuento --}}
                    @if($producto->tieneOfertaValida())
                        <div class="absolute top-4 left-4 z-20 flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-black bg-gradient-to-r from-rose-600 to-red-500 text-white shadow-sm tracking-wide">
                            <span class="material-symbols-outlined text-[14px]">local_fire_department</span>
                            <span>-{{ round((($producto->precio - $producto->precio_oferta) / $producto->precio) * 100) }}% AHORRO</span>
                        </div>
                    @endif

                    {{-- Badges Superiores Derechos (Contador de Fotos + Botón Zoom Fullscreen) --}}
                    <div class="absolute top-4 right-4 z-20 flex items-center gap-2">
                        @if($todasImagenes->count() > 1)
                            <span id="visor-contador" class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-900/75 backdrop-blur-md text-white shadow-xs flex items-center gap-1">
                                <span class="material-symbols-outlined text-[13px]">photo_camera</span>
                                <span id="visor-contador-texto">{{ $indiceInicial + 1 }} / {{ $todasImagenes->count() }}</span>
                            </span>
                        @endif

                        <button type="button" 
                                onclick="abrirLightbox()" 
                                title="Ver en pantalla completa"
                                class="w-8 h-8 rounded-full bg-white/90 hover:bg-white text-slate-700 hover:text-emerald-700 shadow-sm border border-slate-200/80 flex items-center justify-center transition-all hover:scale-110 active:scale-95">
                            <span class="material-symbols-outlined text-[16px]">zoom_in</span>
                        </button>
                    </div>

                    {{-- Botón Anterior (<) --}}
                    @if($todasImagenes->count() > 1)
                        <button type="button" 
                                onclick="navegarImagen(-1)" 
                                aria-label="Imagen anterior"
                                class="absolute left-3 sm:left-4 z-20 w-10 h-10 rounded-full bg-white/95 hover:bg-white text-slate-800 hover:text-emerald-700 shadow-md border border-slate-200/80 flex items-center justify-center transition-all duration-200 transform hover:scale-110 active:scale-95 backdrop-blur-xs group-hover/visor:opacity-100 opacity-90">
                            <span class="material-symbols-outlined text-[22px] font-bold">chevron_left</span>
                        </button>
                    @endif

                    {{-- Contenedor Interactivo de la Imagen con Zoom Lupa --}}
                    <div id="imagen-principal-visor" 
                         onmousemove="manejarZoomLupa(event)" 
                         onmouseleave="resetearZoomLupa()"
                         class="w-full h-full flex items-center justify-center relative cursor-crosshair overflow-hidden transition-all duration-300">
                        {{-- Se rellena dinámicamente con JavaScript desde el estado inicial --}}
                    </div>

                    {{-- Botón Siguiente (>) --}}
                    @if($todasImagenes->count() > 1)
                        <button type="button" 
                                onclick="navegarImagen(1)" 
                                aria-label="Siguiente imagen"
                                class="absolute right-3 sm:right-4 z-20 w-10 h-10 rounded-full bg-white/95 hover:bg-white text-slate-800 hover:text-emerald-700 shadow-md border border-slate-200/80 flex items-center justify-center transition-all duration-200 transform hover:scale-110 active:scale-95 backdrop-blur-xs group-hover/visor:opacity-100 opacity-90">
                            <span class="material-symbols-outlined text-[22px] font-bold">chevron_right</span>
                        </button>
                    @endif

                    {{-- Indicador sutil de Zoom al pasar el cursor --}}
                    <div class="absolute bottom-3 right-4 pointer-events-none opacity-0 group-hover/visor:opacity-75 transition-opacity duration-300 text-[11px] font-medium text-slate-400 flex items-center gap-1 bg-white/80 px-2.5 py-0.5 rounded-md backdrop-blur-xs">
                        <span class="material-symbols-outlined text-[13px]">pinch</span>
                        <span>Pasa el cursor para ampliar detalle</span>
                    </div>
                </div>

                <!-- Barra de Miniaturas Interactiva -->
                @if($todasImagenes->count() > 1)
                    <div class="flex items-center justify-center gap-3 overflow-x-auto pb-2 pt-1 px-1 scrollbar-thin scrollbar-thumb-slate-200" id="contenedor-miniaturas">
                        @foreach($imagenesJson as $foto)
                            <button type="button" 
                                    onclick="irAImagen({{ $foto['index'] }})" 
                                    id="miniatura-btn-{{ $foto['index'] }}"
                                    class="h-16 w-16 sm:h-18 sm:w-18 shrink-0 rounded-2xl bg-white border-2 {{ $foto['index'] === $indiceInicial ? 'border-emerald-500 ring-2 ring-emerald-500/20 shadow-sm scale-105' : 'border-slate-200/90 hover:border-slate-400 opacity-70 hover:opacity-100' }} flex items-center justify-center p-2 transition-all duration-200 relative group overflow-hidden">
                                
                                @if(!empty($foto['src']))
                                    <img src="{{ $foto['src'] }}" 
                                         alt="Miniatura {{ $foto['index'] + 1 }}" 
                                         class="h-full w-full object-contain mix-blend-multiply group-hover:scale-110 transition-transform duration-200 pointer-events-none">
                                @elseif($foto['es_svg'])
                                    <div class="h-7 w-7 flex items-center justify-center svg-container pointer-events-none">{!! $foto['ruta'] !!}</div>
                                @else
                                    <span class="material-symbols-outlined text-[26px] text-slate-700 pointer-events-none">{{ $foto['ruta'] }}</span>
                                @endif

                                @if($foto['es_principal'])
                                    <span class="absolute bottom-1 right-1 w-2 h-2 rounded-full bg-emerald-500" title="Imagen Principal"></span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Columna Derecha: Información y Opciones de Compra (5 columnas) -->
            <div class="lg:col-span-5 space-y-6">
                
                <!-- Encabezado de Producto -->
                <div class="space-y-2 border-b border-slate-100 pb-4">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-tight">
                        {{ $producto->nombre }}
                    </h1>

                    <!-- Bloque de Precios y Promociones Especiales -->
                    <div class="space-y-1">
                        @php
                            $promoMes = $producto->promocionDelMesActiva();
                            $tienePromocion = $producto->tienePromocionOPrecioOferta();
                            $precioFinal = $producto->precioFinalPromocional();
                            $porcentajeDesc = $producto->porcentajeDescuentoPromocional();
                        @endphp

                        @if($promoMes)
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-100 border border-amber-300 text-amber-900 rounded-lg text-xs font-bold shadow-2xs mb-1">
                                <span class="material-symbols-outlined text-[16px] text-amber-600">star</span>
                                <span>Producto del Mes (-{{ number_format($porcentajeDesc, 0) }}% OFF)</span>
                            </div>
                        @endif

                        <div class="flex items-baseline gap-3 pt-1">
                            @if($tienePromocion)
                                <span class="text-3xl sm:text-4xl font-extrabold text-emerald-700" id="precio-dinamico">${{ number_format($precioFinal, 2) }}</span>
                                <span class="text-lg text-slate-400 line-through">${{ number_format($producto->precio, 2) }}</span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-rose-100 text-rose-700 border border-rose-200">
                                    -{{ number_format($porcentajeDesc, 0) }}% OFF
                                </span>
                            @else
                                <span class="text-3xl sm:text-4xl font-extrabold text-slate-900" id="precio-dinamico">${{ number_format($producto->precio, 2) }}</span>
                            @endif
                        </div>
                    </div>
                    <p class="text-xs text-slate-400">
                        Precios en Balboas / Dólares (USD). {{ $producto->aplica_itbms ? 'Aplica ITBMS 7% en el checkout.' : 'Exento de ITBMS.' }}
                    </p>
                </div>

                <!-- Descripción Corta -->
                @if($producto->descripcion_corta)
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        {{ $producto->descripcion_corta }}
                    </p>
                @endif

                <!-- Selector de Variantes Dinámicas si existen -->
                @if($producto->variantes && $producto->variantes->isNotEmpty())
                    <div class="space-y-4 pt-1 border-t border-slate-100">
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                                Variantes disponibles:
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($producto->variantes as $v)
                                    @php
                                        $opcionesTexto = $v->opciones->map(fn($o) => $o->valor)->join(' / ');
                                        $vPrecioBase = $v->precio > 0 ? $v->precio : $producto->precio;
                                        $vPrecioFinal = $v->precioFinalPromocional();
                                    @endphp
                                    <button type="button" 
                                            onclick="seleccionarVariante('{{ $vPrecioFinal }}', '{{ $v->stock }}', this, {{ $v->id }})" 
                                            class="p-3 rounded-xl border border-slate-200 hover:border-emerald-500 text-left transition-all btn-variante {{ $loop->first ? 'border-emerald-500 bg-emerald-50/30' : 'bg-white' }}">
                                        <div class="text-xs font-bold text-slate-900">{{ $opcionesTexto ?: $v->sku }}</div>
                                        <div class="text-[11px] font-semibold mt-0.5">
                                            @if($vPrecioBase > $vPrecioFinal)
                                                <span class="text-slate-400 line-through mr-1">${{ number_format($vPrecioBase, 2) }}</span>
                                            @endif
                                            <span class="text-emerald-700">${{ number_format($vPrecioFinal, 2) }}</span>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Estado de Stock y Acciones de Compra o Notificación por Email -->
                @if($producto->stock <= 0)
                    <!-- Bloque de Fuera de Stock con Notificación por Email -->
                    <div class="space-y-3.5 pt-1">
                        
                        <div class="flex items-center gap-2 text-rose-600 font-extrabold text-sm sm:text-base">
                            <span class="material-symbols-outlined text-[18px]">cancel</span>
                            <span>Fuera de stock</span>
                        </div>

                        <!-- 2. Formulario Email con botón send a la derecha -->
                        <form id="form-notificacion-stock" onsubmit="enviarNotificacionStock(event)" class="w-full flex items-stretch border border-slate-300 rounded-md overflow-hidden bg-white shadow-2xs focus-within:border-slate-800 transition-all">
                            @csrf
                            <input type="hidden" name="producto_id" value="{{ $producto->id }}">
                            <input type="email" 
                                   name="email" 
                                   id="email_notificacion_stock" 
                                   required 
                                   placeholder="Email" 
                                   class="flex-1 px-3.5 py-2.5 text-xs text-slate-800 placeholder-slate-400 bg-transparent border-none outline-none focus:ring-0">
                            <button type="submit" 
                                    id="btn-notificacion-stock" 
                                    class="bg-black hover:bg-slate-800 active:scale-95 text-white px-4 py-2.5 flex items-center justify-center transition-all cursor-pointer shrink-0"
                                    title="Notificarme por Email">
                                <span class="material-symbols-outlined text-[18px]">send</span>
                            </button>
                        </form>

                        <!-- 3. Botón 'Guardar para más tarde' -->
                        <div>
                            <button type="button" 
                                    onclick="agregarDeseo({{ $producto->id }})" 
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-black hover:bg-slate-800 text-white text-xs font-bold rounded-md shadow-2xs transition-all active:scale-95 cursor-pointer">
                                <span class="material-symbols-outlined text-[16px]">favorite</span>
                                <span>Guardar en Lista de Deseos</span>
                            </button>
                        </div>
                    </div>
                @else
                    <!-- Bloque con Stock Disponible -->
                    <div class="space-y-3.5 pt-1">
                        
                        <div class="text-xs text-emerald-700 font-bold flex items-center gap-1.5" id="stock-container">
                            <span class="material-symbols-outlined text-[16px]">inventory_2</span>
                            <span id="stock-dinamico">Disponible ({{ $producto->variantes && $producto->variantes->isNotEmpty() ? $producto->variantes->first()->stock : $producto->stock }} en inventario)</span>
                        </div>

                        <!-- Selector de Cantidad y Botones de Compra Activos -->
                        <div class="space-y-3 pt-1">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center border border-slate-200 rounded-xl bg-slate-50 p-1">
                                    <button type="button" onclick="cambiarCantidad(-1)" class="w-8 h-8 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 flex items-center justify-center text-slate-700 font-bold transition-colors">
                                        -
                                    </button>
                                    <input type="number" id="input-cantidad" value="1" min="1" max="{{ max(1, $producto->variantes && $producto->variantes->isNotEmpty() ? $producto->variantes->first()->stock : $producto->stock) }}" class="w-12 text-center bg-transparent border-none text-xs font-bold text-slate-900 focus:outline-none">
                                    <button type="button" onclick="cambiarCantidad(1)" class="w-8 h-8 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 flex items-center justify-center text-slate-700 font-bold transition-colors">
                                        +
                                    </button>
                                </div>

                                <button type="button" 
                                        onclick="agregarAlCarrito({{ $producto->id }})" 
                                        class="flex-1 py-3 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer">
                                    <span class="material-symbols-outlined text-[18px]">add_shopping_cart</span>
                                    <span>Agregar al Carrito</span>
                                </button>

                                <button type="button" 
                                        onclick="agregarDeseo({{ $producto->id }})" 
                                        class="p-3 border border-slate-200 text-slate-400 hover:text-red-500 hover:bg-red-50 hover:border-red-200 rounded-xl transition-all cursor-pointer" 
                                        title="Guardar en Lista de Deseos">
                                    <span class="material-symbols-outlined text-[20px]">favorite</span>
                                </button>
                            </div>

                            <button type="button" 
                                    onclick="comprarAhora({{ $producto->id }})" 
                                    class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center justify-center gap-2 cursor-pointer">
                                <span class="material-symbols-outlined text-[18px]">bolt</span>
                                <span>Comprar Ahora (Checkout Rápido)</span>
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Metadatos del Producto (Estilo apilado y tamaño equilibrado) -->
                <div class="flex flex-col gap-2 pt-2.5 mt-2.5 border-t border-slate-100 text-xs sm:text-sm text-slate-900">
                    <!-- Código -->
                    <div>
                        Código: <span class="text-blue-600 font-normal">{{ $producto->sku ?: 'PTL-LEV-493' }}</span>
                    </div>

                    <!-- Marca (Logo en Recuadro + Nombre) -->
                    <div class="flex items-center gap-3 my-0.5">
                        @if($producto->marca_logo_html)
                            <div class="h-9 w-20 px-1.5 py-0.5 rounded border border-slate-200 bg-white flex items-center justify-center shadow-2xs shrink-0">
                                {!! $producto->marca_logo_html !!}
                            </div>
                        @else
                            <div class="h-9 w-20 px-1.5 py-0.5 rounded border border-slate-200 bg-white flex items-center justify-center shadow-2xs shrink-0 text-slate-400 text-[11px] font-semibold">
                                {{ $producto->marca ?: 'Marca' }}
                            </div>
                        @endif
                        <span class="text-base font-semibold text-slate-900">
                            {{ $producto->marca ?: 'Lenovo' }}
                        </span>
                    </div>

                    <!-- Categoría -->
                    <div>
                        Categoría: <a href="{{ route('cliente.catalogo', ['categoria' => $producto->categoria?->slug ?? 'all']) }}" wire:navigate class="text-blue-600 hover:underline font-normal">{{ $producto->categoria ? $producto->categoria->nombre : 'Portátiles' }}</a>
                    </div>

                    <!-- Modelo -->
                    <div>
                        Modelo : <span class="text-blue-600 font-normal">{{ $producto->modelo ?: '82VG00WXUS' }}</span>
                    </div>

                    <!-- Términos y Condiciones -->
                    <div class="pt-0.5">
                        <a href="{{ route('terminos') }}" target="_blank" class="text-slate-900 underline hover:text-blue-600 text-xs sm:text-sm font-normal">
                            Términos y condiciones
                        </a>
                    </div>
                </div>


            </div>

        </div>

        <!-- Descripción Completa y Especificaciones -->
        @if($producto->descripcion)
            <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-4">
                <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="material-symbols-outlined text-emerald-600">description</span>
                    <span>Descripción y Especificaciones Técnicas</span>
                </h2>
                <div class="text-xs sm:text-sm text-slate-600 leading-relaxed whitespace-pre-line">
                    {{ $producto->descripcion }}
                </div>
            </div>
        @endif

        <!-- Productos Relacionados -->
        @if($relacionados->isNotEmpty())
            <div class="space-y-4 pt-4">
                <h2 class="text-lg font-bold text-slate-900">Productos Relacionados</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($relacionados as $rel)
                        @php
                            $relImg = $rel->imagenPrincipal();
                        @endphp
                        <div class="bg-white rounded-2xl border border-slate-200 p-4 space-y-3 hover:shadow-md transition-shadow">
                            <div class="h-36 bg-white rounded-xl flex items-center justify-center p-2 border border-slate-100 overflow-hidden">
                                @if($relImg && (str_starts_with($relImg->ruta, 'http') || str_starts_with($relImg->ruta, 'storage/') || str_starts_with($relImg->ruta, '/storage')))
                                    <img src="{{ str_starts_with($relImg->ruta, 'storage/') ? asset($relImg->ruta) : $relImg->ruta }}" alt="{{ $rel->nombre }}" class="h-full object-contain mix-blend-multiply">
                                @elseif($relImg && (str_starts_with($relImg->ruta, '<svg') || str_contains($relImg->ruta, '</svg>')))
                                    <div class="h-full flex items-center justify-center svg-container">{!! $relImg->ruta !!}</div>
                                @elseif($relImg && !empty($relImg->ruta))
                                    <span class="material-symbols-outlined text-[48px] text-slate-600">{{ $relImg->ruta }}</span>
                                @else
                                    <span class="material-symbols-outlined text-[48px] text-slate-300">image</span>
                                @endif
                            </div>
                            <div class="space-y-1">
                                <h3 class="text-xs font-bold text-slate-900 line-clamp-1">
                                    <a href="{{ route('cliente.producto.detalle', $rel->slug) }}" wire:navigate class="hover:text-emerald-700">
                                        {{ $rel->nombre }}
                                    </a>
                                </h3>
                                <div class="text-sm font-extrabold text-slate-900">${{ number_format($rel->precio, 2) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</div>

{{-- Lightbox Modal Fullscreen para Alta Definición --}}
<div id="modal-lightbox" class="fixed inset-0 z-[99999] bg-black/90 backdrop-blur-md hidden items-center justify-center p-4 sm:p-8" onclick="cerrarLightbox(event)">
    <button type="button" onclick="cerrarLightbox()" class="absolute top-5 right-5 text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-2.5 rounded-full transition-all">
        <span class="material-symbols-outlined text-[28px]">close</span>
    </button>
    <button type="button" onclick="event.stopPropagation(); navegarImagen(-1);" class="absolute left-4 sm:left-8 text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-3 rounded-full transition-all">
        <span class="material-symbols-outlined text-[32px]">chevron_left</span>
    </button>
    <div id="lightbox-contenido" class="max-w-4xl max-h-[85vh] flex items-center justify-center p-4">
        {{-- Imagen del lightbox --}}
    </div>
    <button type="button" onclick="event.stopPropagation(); navegarImagen(1);" class="absolute right-4 sm:right-8 text-white/80 hover:text-white bg-white/10 hover:bg-white/20 p-3 rounded-full transition-all">
        <span class="material-symbols-outlined text-[32px]">chevron_right</span>
    </button>
</div>

<script>
    (function () {
    // Colección de imágenes inyectada desde Blade (scope local: evita colisiones al re-ejecutarse con wire:navigate)
    const galeriaImagenes = @json($imagenesJson);
    let indiceActual = {{ $indiceInicial }};
    const totalImagenes = galeriaImagenes.length;

    // Renderizar imagen en el visor principal con transición suave
    function renderizarVisor(indice, animar = true) {
        if (!galeriaImagenes || galeriaImagenes.length === 0) return;
        indiceActual = (indice + totalImagenes) % totalImagenes;
        const foto = galeriaImagenes[indiceActual];
        const visor = document.getElementById('imagen-principal-visor');
        if (!visor) return;

        if (animar) {
            visor.style.opacity = '0';
            visor.style.transform = 'scale(0.96)';
        }

        setTimeout(() => {
            if (foto.src) {
                visor.innerHTML = `
                    <img id="img-zoom-target" 
                         src="${foto.src}" 
                         class="max-h-full max-w-full object-contain mix-blend-multiply select-none transition-transform duration-150 ease-out" 
                         alt="{{ $producto->nombre }} - Foto ${indiceActual + 1}">
                `;
            } else if (foto.es_svg) {
                visor.innerHTML = `<div class="max-h-full max-w-full flex items-center justify-center svg-container">${foto.ruta}</div>`;
            } else {
                visor.innerHTML = `<span class="material-symbols-outlined text-[150px] text-slate-700">${foto.ruta}</span>`;
            }

            // Actualizar lightbox si está abierto
            actualizarLightboxContenido();

            // Actualizar texto del contador
            const contadorEl = document.getElementById('visor-contador-texto');
            if (contadorEl) {
                contadorEl.textContent = `${indiceActual + 1} / ${totalImagenes}`;
            }

            // Actualizar estado activo en las miniaturas
            galeriaImagenes.forEach((_, idx) => {
                const btn = document.getElementById(`miniatura-btn-${idx}`);
                if (btn) {
                    if (idx === indiceActual) {
                        btn.className = 'h-16 w-16 sm:h-18 sm:w-18 shrink-0 rounded-2xl bg-white border-2 border-emerald-500 ring-2 ring-emerald-500/20 shadow-sm scale-105 flex items-center justify-center p-2 transition-all duration-200 relative group overflow-hidden';
                        btn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
                    } else {
                        btn.className = 'h-16 w-16 sm:h-18 sm:w-18 shrink-0 rounded-2xl bg-white border-2 border-slate-200/90 hover:border-slate-400 opacity-70 hover:opacity-100 flex items-center justify-center p-2 transition-all duration-200 relative group overflow-hidden';
                    }
                }
            });

            if (animar) {
                requestAnimationFrame(() => {
                    visor.style.opacity = '1';
                    visor.style.transform = 'scale(1)';
                });
            }
        }, animar ? 120 : 0);
    }

    // Navegación con botones Anterior / Siguiente
    function navegarImagen(delta) {
        renderizarVisor(indiceActual + delta, true);
    }

    // Ir a una imagen específica haciendo clic en miniatura
    function irAImagen(indice) {
        if (indice === indiceActual) return;
        renderizarVisor(indice, true);
    }

    // Zoom interactivo estilo lupa en el visor principal
    function manejarZoomLupa(e) {
        const img = document.getElementById('img-zoom-target');
        if (!img) return;
        const rect = e.currentTarget.getBoundingClientRect();
        const x = ((e.clientX - rect.left) / rect.width) * 100;
        const y = ((e.clientY - rect.top) / rect.height) * 100;
        
        img.style.transformOrigin = `${x}% ${y}%`;
        img.style.transform = 'scale(1.75)';
    }

    function resetearZoomLupa() {
        const img = document.getElementById('img-zoom-target');
        if (!img) return;
        img.style.transformOrigin = 'center center';
        img.style.transform = 'scale(1)';
    }

    // Lightbox / Pantalla Completa
    function abrirLightbox() {
        const modal = document.getElementById('modal-lightbox');
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        actualizarLightboxContenido();
    }

    function cerrarLightbox(e) {
        if (e && e.target && e.target.closest('#lightbox-contenido img')) return;
        const modal = document.getElementById('modal-lightbox');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    function actualizarLightboxContenido() {
        const modal = document.getElementById('modal-lightbox');
        if (!modal || modal.classList.contains('hidden')) return;
        const contenedor = document.getElementById('lightbox-contenido');
        if (!contenedor) return;
        const foto = galeriaImagenes[indiceActual];
        if (foto && foto.src) {
            contenedor.innerHTML = `<img src="${foto.src}" class="max-h-[82vh] max-w-[90vw] object-contain rounded-2xl shadow-2xl bg-white p-4" alt="Detalle ampliado">`;
        } else if (foto && foto.es_svg) {
            contenedor.innerHTML = `<div class="max-h-[80vh] max-w-[80vw] bg-white p-8 rounded-2xl">${foto.ruta}</div>`;
        }
    }

    // Control por teclado (flechas izquierda / derecha y escape)
    // Se registra UNA sola vez en document (persiste entre navegaciones SPA) y
    // despacha a las funciones expuestas en window, que se re-enlazan por página.
    if (!window.__galeriaKeydownInicializado) {
        window.__galeriaKeydownInicializado = true;
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                window.navegarImagen(-1);
            } else if (e.key === 'ArrowRight') {
                window.navegarImagen(1);
            } else if (e.key === 'Escape') {
                window.cerrarLightbox();
            }
        });
    }

    // Control táctil para móviles (Swipe)
    let touchStartX = 0;
    let touchEndX = 0;
    const visorEl = document.getElementById('galeria-producto-contenedor');
    if (visorEl) {
        visorEl.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        visorEl.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            if (touchStartX - touchEndX > 50) {
                navegarImagen(1); // Swipe izquierda -> siguiente
            } else if (touchEndX - touchStartX > 50) {
                navegarImagen(-1); // Swipe derecha -> anterior
            }
        }, { passive: true });
    }

    // Funciones del formulario de compra
    let varianteSeleccionadaId = {{ $producto->variantes && $producto->variantes->isNotEmpty() ? $producto->variantes->first()->id : 'null' }};

    function cambiarCantidad(delta) {
        const input = document.getElementById('input-cantidad');
        if (!input) return;
        let val = parseInt(input.value) || 1;
        const maxVal = parseInt(input.max) || 1;
        
        if (delta > 0 && val >= maxVal) {
            const msj = `Stock máximo alcanzado. Solo hay ${maxVal} unidades disponibles.`;
            try {
                if (typeof window.mostrarToast === 'function') {
                    window.mostrarToast(msj, 'warning');
                } else if (window.Livewire) {
                    window.Livewire.dispatch('mostrar-toast', [{ tipo: 'warning', mensaje: msj }]);
                } else {
                    alert(msj);
                }
            } catch (e) {
                console.error(e);
            }
        }
        
        val = Math.max(1, Math.min(val + delta, maxVal));
        input.value = val;
    }

    function seleccionarVariante(precio, stock, btn, varianteId) {
        if (varianteId) {
            varianteSeleccionadaId = varianteId;
        }
        const precioEl = document.getElementById('precio-dinamico');
        if (precioEl) {
            precioEl.textContent = `$${parseFloat(precio).toFixed(2)}`;
        }
        
        const stockEl = document.getElementById('stock-dinamico');
        const stockContainer = document.getElementById('stock-container');
        const parsedStock = parseInt(stock) || 0;
        
        if (stockEl && stockContainer) {
            if (parsedStock > 0) {
                stockEl.textContent = `Disponible (${parsedStock} en inventario)`;
                stockContainer.classList.remove('text-rose-600');
                stockContainer.classList.add('text-emerald-700');
            } else {
                stockEl.textContent = `Agotado`;
                stockContainer.classList.remove('text-emerald-700');
                stockContainer.classList.add('text-rose-600');
            }
        }
        
        const inputCantidad = document.getElementById('input-cantidad');
        if (inputCantidad) {
            const maxStock = Math.max(1, parsedStock);
            inputCantidad.max = maxStock;
            if (parseInt(inputCantidad.value) > maxStock) {
                inputCantidad.value = maxStock;
            }
        }
        
        const btnAgregar = document.querySelector('button[onclick^="agregarAlCarrito"]');
        const btnComprar = document.querySelector('button[onclick^="comprarAhora"]');
        
        if (parsedStock <= 0) {
            if (btnAgregar) { btnAgregar.disabled = true; btnAgregar.classList.add('opacity-50', 'cursor-not-allowed'); }
            if (btnComprar) { btnComprar.disabled = true; btnComprar.classList.add('opacity-50', 'cursor-not-allowed'); }
        } else {
            if (btnAgregar) { btnAgregar.disabled = false; btnAgregar.classList.remove('opacity-50', 'cursor-not-allowed'); }
            if (btnComprar) { btnComprar.disabled = false; btnComprar.classList.remove('opacity-50', 'cursor-not-allowed'); }
        }

        document.querySelectorAll('.btn-variante').forEach(b => {
            b.classList.remove('border-emerald-500', 'bg-emerald-50/30');
            b.classList.add('bg-white');
        });
        btn.classList.remove('bg-white');
        btn.classList.add('border-emerald-500', 'bg-emerald-50/30');
    }

    function agregarAlCarrito(productoId, redireccionar = false) {
        const input = document.getElementById('input-cantidad');
        const cantidad = input ? parseInt(input.value) || 1 : 1;

        fetch("{{ route('cliente.carrito.agregar') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                producto_id: productoId,
                variante_producto_id: varianteSeleccionadaId,
                cantidad: cantidad
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito) {
                if (redireccionar) {
                    window.location.href = "{{ route('cliente.carrito') }}";
                    return;
                }
                if (window.Livewire) {
                    Livewire.dispatch('carrito-actualizado');
                }
                if (window.mostrarToast) {
                    window.mostrarToast('success', data.mensaje);
                }
            } else {
                if (window.mostrarToast) {
                    window.mostrarToast('warning', data.mensaje || 'Stock insuficiente');
                }
            }
        })
        .catch(err => {
            console.error(err);
            if (window.mostrarToast) {
                window.mostrarToast('error', 'Error al agregar el producto al carrito.');
            }
        });
    }

    function comprarAhora(productoId) {
        agregarAlCarrito(productoId, true);
    }

    function agregarDeseo(productoId) {
        fetch(`/lista-deseos/agregar/${productoId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (res.status === 401) {
                window.location.href = "{{ route('login') }}";
                return;
            }
            return res.json();
        })
        .then(data => {
            if (data && data.exito) {
                if (window.Livewire) {
                    Livewire.dispatch('deseos-actualizado');
                }
                if (window.mostrarToast) {
                    window.mostrarToast('success', data.mensaje);
                }
            } else if (data) {
                if (window.mostrarToast) {
                    window.mostrarToast('info', data.mensaje);
                }
            }
        })
        .catch(err => {
            console.error(err);
            if (window.mostrarToast) {
                window.mostrarToast('error', 'No se pudo guardar en la lista de deseos.');
            }
        });
    }

    // Enviar solicitud de aviso de stock por correo electrónico
    function enviarNotificacionStock(event) {
        event.preventDefault();
        const form = event.target;
        const btn = document.getElementById('btn-notificacion-stock');
        const emailInput = document.getElementById('email_notificacion_stock');
        
        if (!emailInput || !emailInput.value) return;

        const originalBtnHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `
            <span class="inline-block animate-spin material-symbols-outlined text-[16px]">progress_activity</span>
            <span>Enviando...</span>
        `;

        const formData = new FormData(form);

        fetch("{{ route('cliente.producto.notificar-stock') }}", {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (typeof mostrarToast === 'function') {
                    mostrarToast(data.message, 'success');
                }
                form.innerHTML = `
                    <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-600 text-[20px]">check_circle</span>
                        <span>${data.message}</span>
                    </div>
                `;
            } else {
                if (typeof mostrarToast === 'function') {
                    mostrarToast(data.message || 'Ocurrió un error. Intenta nuevamente.', 'error');
                }
                btn.disabled = false;
                btn.innerHTML = originalBtnHtml;
            }
        })
        .catch(err => {
            console.error(err);
            if (typeof mostrarToast === 'function') {
                mostrarToast('No se pudo procesar la solicitud. Verifica tu conexión.', 'error');
            }
            btn.disabled = false;
            btn.innerHTML = originalBtnHtml;
        });
    }

    // Renderizar imagen inicial (ejecución inmediata: ya estamos al final del body,
    // funciona tanto en carga inicial como al llegar vía wire:navigate)
    renderizarVisor(indiceActual, false);

    // Exponer las funciones usadas por los atributos inline (onclick/onmousemove/...)
    // al scope global, re-enlazándolas en cada carga para que apunten a la
    // galería del producto actual tras una navegación SPA.
    window.abrirLightbox = abrirLightbox;
    window.navegarImagen = navegarImagen;
    window.irAImagen = irAImagen;
    window.manejarZoomLupa = manejarZoomLupa;
    window.resetearZoomLupa = resetearZoomLupa;
    window.seleccionarVariante = seleccionarVariante;
    window.agregarDeseo = agregarDeseo;
    window.cambiarCantidad = cambiarCantidad;
    window.agregarAlCarrito = agregarAlCarrito;
    window.comprarAhora = comprarAhora;
    window.cerrarLightbox = cerrarLightbox;
    window.enviarNotificacionStock = enviarNotificacionStock;
    })();
</script>
@endsection
