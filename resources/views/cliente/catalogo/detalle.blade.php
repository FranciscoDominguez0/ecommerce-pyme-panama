@extends('layouts.cliente')

@section('title', ($producto->nombre ?? 'Detalle del Producto') . ' - PayMe Panamá')

@section('content')
<div class="min-h-screen bg-slate-50 py-6 sm:py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        
        <!-- Breadcrumbs Navegables -->
        <nav class="flex items-center gap-1.5 text-xs text-slate-500 font-medium overflow-x-auto whitespace-nowrap scrollbar-none" aria-label="Breadcrumb">
            <a href="{{ route('inicio') }}" class="hover:text-emerald-700 transition-colors flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">home</span>
                <span>Inicio</span>
            </a>
            <span class="material-symbols-outlined text-[14px] text-slate-400">chevron_right</span>
            <a href="{{ route('cliente.catalogo') }}" class="hover:text-emerald-700 transition-colors">Catálogo</a>
            @if($producto->categoria)
                <span class="material-symbols-outlined text-[14px] text-slate-400">chevron_right</span>
                <a href="{{ route('cliente.catalogo', ['categoria' => $producto->categoria->slug]) }}" class="text-slate-600 hover:text-emerald-700">
                    {{ $producto->categoria->nombre }}
                </a>
            @endif
            <span class="material-symbols-outlined text-[14px] text-slate-400">chevron_right</span>
            <span class="text-slate-900 font-bold truncate">{{ $producto->nombre }}</span>
        </nav>

        <!-- Bloque Principal de Producto (Galería + Compra) -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-10 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
            
            <!-- Columna Izquierda: Galería de Imágenes (6 columnas) -->
            <div class="lg:col-span-6 space-y-4">
                
                @php
                    $imgPrincipal = $producto->imagenPrincipal();
                    $todasImagenes = $producto->imagenes ?? collect();
                @endphp

                <!-- Visor Principal de Imagen -->
                <div class="relative h-80 sm:h-96 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center justify-center p-6 overflow-hidden group">
                    @if($producto->tieneOfertaValida())
                        <span class="absolute top-4 left-4 px-2.5 py-1 rounded-md text-xs font-extrabold bg-rose-600 text-white shadow-xs">
                            -{{ round((($producto->precio - $producto->precio_oferta) / $producto->precio) * 100) }}% AHORRO
                        </span>
                    @endif
                    <div id="imagen-principal-visor" class="w-full h-full flex items-center justify-center text-slate-700 group-hover:scale-105 transition-transform duration-300">
                        @if($imgPrincipal && (str_starts_with($imgPrincipal->ruta, 'http') || str_starts_with($imgPrincipal->ruta, '/storage') || str_starts_with($imgPrincipal->ruta, 'data:image') || str_starts_with($imgPrincipal->ruta, 'storage/')))
                            <img src="{{ str_starts_with($imgPrincipal->ruta, 'storage/') ? asset($imgPrincipal->ruta) : $imgPrincipal->ruta }}" alt="{{ $producto->nombre }}" class="max-h-full max-w-full object-contain">
                        @elseif($imgPrincipal && (str_starts_with($imgPrincipal->ruta, '<svg') || str_contains($imgPrincipal->ruta, '</svg>')))
                            <div class="max-h-full max-w-full flex items-center justify-center svg-container">{!! $imgPrincipal->ruta !!}</div>
                        @elseif($imgPrincipal && !empty($imgPrincipal->ruta))
                            <span class="material-symbols-outlined text-[140px] text-slate-700">{{ $imgPrincipal->ruta }}</span>
                        @else
                            <span class="material-symbols-outlined text-[140px] text-slate-400">image</span>
                        @endif
                    </div>
                </div>

                <!-- Miniaturas de la Galería -->
                @if($todasImagenes->count() > 1)
                    <div class="grid grid-cols-4 gap-3">
                        @foreach($todasImagenes as $idx => $foto)
                            <button type="button" 
                                    onclick="cambiarImagenVisor('{{ $foto->ruta }}', this)" 
                                    class="h-20 rounded-xl bg-slate-50 border {{ $loop->first ? 'border-2 border-emerald-500' : 'border-slate-200 hover:border-slate-300' }} flex items-center justify-center p-2 transition-all mini-galeria {{ $loop->first ? 'active' : '' }}">
                                @if(str_starts_with($foto->ruta, 'http') || str_starts_with($foto->ruta, '/storage') || str_starts_with($foto->ruta, 'data:image') || str_starts_with($foto->ruta, 'storage/'))
                                    <img src="{{ str_starts_with($foto->ruta, 'storage/') ? asset($foto->ruta) : $foto->ruta }}" alt="miniatura" class="h-full object-contain">
                                @elseif(str_starts_with($foto->ruta, '<svg') || str_contains($foto->ruta, '</svg>'))
                                    <div class="h-8 w-8 flex items-center justify-center svg-container">{!! $foto->ruta !!}</div>
                                @else
                                    <span class="material-symbols-outlined text-[32px] text-slate-700">{{ $foto->ruta }}</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Columna Derecha: Información y Opciones de Compra (6 columnas) -->
            <div class="lg:col-span-6 space-y-6">
                
                <!-- Encabezado de Producto -->
                <div class="space-y-2 border-b border-slate-100 pb-5">
                    <div class="flex items-center gap-2 flex-wrap">
                        @if($producto->categoria)
                            <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                                {{ $producto->categoria->nombre }}
                            </span>
                        @endif
                        <span class="text-xs font-mono text-slate-400 bg-slate-100 px-2 py-0.5 rounded">
                            SKU: {{ $producto->sku ?? 'N/A' }}
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        {{ $producto->nombre }}
                    </h1>

                    <!-- Rating y Disponibilidad -->
                    <div class="flex items-center gap-4 text-xs pt-1">
                        <div class="flex items-center gap-1 text-amber-500 font-bold">
                            <span class="material-symbols-outlined text-[18px]">star</span>
                            <span>5.0</span>
                            <span class="text-slate-400 font-normal">(Opiniones verificadas)</span>
                        </div>
                        <span class="text-slate-300">•</span>
                        <div class="flex items-center gap-1.5 text-emerald-700 font-bold">
                            @if($producto->stock > 0)
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>{{ $producto->stock }} en stock (Entrega inmediata)</span>
                            @else
                                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                <span class="text-rose-600">Agotado temporalmente</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Bloque de Precios -->
                <div class="space-y-1">
                    <div class="flex items-baseline gap-3">
                        @if($producto->tieneOfertaValida())
                            <span class="text-3xl sm:text-4xl font-extrabold text-emerald-700" id="precio-dinamico">${{ number_format($producto->precio_oferta, 2) }}</span>
                            <span class="text-lg text-slate-400 line-through">${{ number_format($producto->precio, 2) }}</span>
                        @else
                            <span class="text-3xl sm:text-4xl font-extrabold text-slate-900" id="precio-dinamico">${{ number_format($producto->precio, 2) }}</span>
                        @endif
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
                    <div class="space-y-4 pt-2 border-t border-slate-100">
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                                Variantes disponibles:
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($producto->variantes as $v)
                                    @php
                                        $opcionesTexto = $v->opciones->map(fn($o) => $o->valor)->join(' / ');
                                    @endphp
                                    <button type="button" 
                                            onclick="seleccionarVariante('{{ $v->precio }}', '{{ $v->stock }}', this)" 
                                            class="p-3 rounded-xl border border-slate-200 hover:border-emerald-500 text-left transition-all btn-variante {{ $loop->first ? 'border-emerald-500 bg-emerald-50/30' : 'bg-white' }}">
                                        <div class="text-xs font-bold text-slate-900">{{ $opcionesTexto ?: $v->sku }}</div>
                                        <div class="text-[11px] text-emerald-700 font-semibold mt-0.5">${{ number_format($v->precio, 2) }}</div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Selector de Cantidad y Botones de Compra -->
                <div class="space-y-3 pt-2">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center border border-slate-200 rounded-xl bg-slate-50 p-1">
                            <button type="button" onclick="cambiarCantidad(-1)" class="w-8 h-8 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 flex items-center justify-center text-slate-700 font-bold transition-colors">
                                -
                            </button>
                            <input type="number" id="input-cantidad" value="1" min="1" max="{{ max(1, $producto->stock) }}" class="w-12 text-center bg-transparent border-none text-xs font-bold text-slate-900 focus:outline-none">
                            <button type="button" onclick="cambiarCantidad(1)" class="w-8 h-8 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 flex items-center justify-center text-slate-700 font-bold transition-colors">
                                +
                            </button>
                        </div>

                        <button type="button" 
                                onclick="alert('Producto agregado al carrito')" 
                                class="flex-1 py-3 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">add_shopping_cart</span>
                            <span>Agregar al Carrito</span>
                        </button>
                    </div>

                    <button type="button" 
                            onclick="alert('Redirigiendo a Checkout Express...')" 
                            class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition-all flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">bolt</span>
                        <span>Comprar Ahora (Checkout Rápido)</span>
                    </button>
                </div>

                <!-- Badges de Confianza de Panamá -->
                <div class="grid grid-cols-2 gap-3 pt-4 border-t border-slate-100 text-slate-600">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="material-symbols-outlined text-emerald-600 text-[20px]">verified_user</span>
                        <span>Garantía Oficial 12 Meses</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="material-symbols-outlined text-emerald-600 text-[20px]">local_shipping</span>
                        <span>Envío Express Ciudad de Panamá e Interior</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="material-symbols-outlined text-emerald-600 text-[20px]">receipt_long</span>
                        <span>Factura Fiscal Oficial (DGI)</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="material-symbols-outlined text-emerald-600 text-[20px]">credit_card</span>
                        <span>Yappy, Tarjeta & Clave</span>
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
                            <div class="h-36 bg-slate-50 rounded-xl flex items-center justify-center p-2">
                                @if($relImg && (str_starts_with($relImg->ruta, 'http') || str_starts_with($relImg->ruta, 'storage/') || str_starts_with($relImg->ruta, '/storage')))
                                    <img src="{{ str_starts_with($relImg->ruta, 'storage/') ? asset($relImg->ruta) : $relImg->ruta }}" alt="{{ $rel->nombre }}" class="h-full object-contain">
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
                                    <a href="{{ route('cliente.producto.detalle', $rel->slug) }}" class="hover:text-emerald-700">
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

<script>
    // Cambiar imagen principal en el visor de producto
    function cambiarImagenVisor(ruta, btn) {
        const visor = document.getElementById('imagen-principal-visor');
        if (!visor) return;

        // Determinar el tipo de imagen y renderizar apropiadamente
        if (ruta.startsWith('http') || ruta.startsWith('/storage') || ruta.startsWith('storage/')) {
            // Imagen real (URL o archivo en storage)
            const src = ruta.startsWith('storage/') ? `{{ asset('') }}${ruta}` : ruta;
            visor.innerHTML = `<img src="${src}" class="max-h-full max-w-full object-contain" alt="Foto producto">`;
        } else if (ruta.includes('<svg') || ruta.includes('</svg>')) {
            // SVG inline
            visor.innerHTML = `<div class="max-h-full max-w-full flex items-center justify-center svg-container">${ruta}</div>`;
        } else {
            // Nombre de ícono Material Symbols
            visor.innerHTML = `<span class="material-symbols-outlined text-[140px] text-slate-700">${ruta}</span>`;
        }

        document.querySelectorAll('.mini-galeria').forEach(b => {
            b.classList.remove('border-2', 'border-emerald-500', 'active');
            b.classList.add('border-slate-200');
        });
        btn.classList.remove('border-slate-200');
        btn.classList.add('border-2', 'border-emerald-500', 'active');
    }

    function cambiarCantidad(delta) {
        const input = document.getElementById('input-cantidad');
        if (!input) return;
        let val = parseInt(input.value) || 1;
        val = Math.max(1, val + delta);
        input.value = val;
    }

    function seleccionarVariante(precio, stock, btn) {
        const precioEl = document.getElementById('precio-dinamico');
        if (precioEl) {
            precioEl.textContent = `$${parseFloat(precio).toFixed(2)}`;
        }
        document.querySelectorAll('.btn-variante').forEach(b => {
            b.classList.remove('border-emerald-500', 'bg-emerald-50/30');
            b.classList.add('bg-white');
        });
        btn.classList.remove('bg-white');
        btn.classList.add('border-emerald-500', 'bg-emerald-50/30');
    }
</script>
@endsection
