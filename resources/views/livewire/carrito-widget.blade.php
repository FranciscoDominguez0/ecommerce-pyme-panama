<div class="w-full">
    @if($items->isNotEmpty())
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#002349] tracking-tight">Carrito de Compras</h1>
            <p class="text-sm text-gray-500 mt-1 font-medium">
                {{ $resumen['cantidad_items'] }} {{ $resumen['cantidad_items'] === 1 ? 'artículo' : 'artículos' }} en tu carrito
            </p>
        </div>

        <!-- Two Column Layout -->
        <div class="flex flex-col lg:flex-row gap-8 items-start">
            
            <!-- Left Column: Lista de Productos -->
            <div class="flex-1 w-full space-y-4">
                @foreach($items as $item)
                    @php
                        $producto = $item->producto;
                        $variante = $item->variante;
                        $stock = $item->stock_disponible;
                        $imagenRuta = $item->imagen_url;
                    @endphp

                    <div wire:key="cart-item-{{ $item->id }}" 
                         class="bg-white border border-gray-200/90 rounded-2xl p-4 sm:p-5 flex flex-col sm:flex-row gap-4 sm:gap-5 items-start sm:items-center shadow-xs hover:border-gray-300 transition-all">
                        
                        <!-- Imagen del Producto -->
                        <div class="w-24 h-24 sm:w-28 sm:h-28 aspect-square shrink-0 bg-gray-50 rounded-xl overflow-hidden border border-gray-100 relative">
                            <img src="{{ $imagenRuta }}" 
                                 alt="{{ $producto->nombre ?? 'Producto' }}" 
                                 class="w-full h-full object-contain p-2"
                                 onerror="this.onerror=null; this.src='https://placehold.co/200x200?text=Sin+Imagen';" />
                            
                            @if($producto && $producto->oferta_activa && $producto->precio_oferta)
                                <span class="absolute top-1.5 left-1.5 bg-red-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-md shadow-xs">
                                    OFERTA
                                </span>
                            @endif
                        </div>

                        <!-- Detalles del Producto -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    @if($producto && $producto->brand)
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#006148] block mb-0.5">
                                            {{ $producto->brand->name }}
                                        </span>
                                    @endif

                                    <h3 class="text-base font-bold text-gray-900 leading-snug hover:text-[#006148] transition-colors">
                                        @if($producto)
                                            <a href="{{ route('cliente.producto.detalle', $producto->slug) }}" wire:navigate>
                                                {{ $producto->nombre }}
                                            </a>
                                        @else
                                            <span>Producto no disponible</span>
                                        @endif
                                    </h3>

                                    @if($item->variante_texto)
                                        <p class="text-xs font-semibold text-gray-600 mt-1 flex items-center gap-1">
                                            <span class="text-gray-400">Variante:</span>
                                            <span class="bg-gray-100 text-gray-800 px-2 py-0.5 rounded-md text-[11px] font-mono">
                                                {{ $item->variante_texto }}
                                            </span>
                                        </p>
                                    @endif

                                    @if($producto && $producto->sku)
                                        <p class="text-[11px] font-mono text-gray-400 mt-1">
                                            SKU: {{ $variante && $variante->sku ? $variante->sku : $producto->sku }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <!-- Alertas de Stock Reactivas -->
                            @if(isset($stockAdvertencias[$item->id]))
                                <div class="mt-2.5 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs font-semibold animate-pulse">
                                    <span class="material-symbols-outlined text-[15px]">error</span>
                                    <span>{{ $stockAdvertencias[$item->id] }}</span>
                                </div>
                            @elseif($stock > 0 && $stock <= 5)
                                <div class="mt-2.5 inline-flex items-center gap-1.5 text-xs text-amber-600 font-semibold">
                                    <span class="material-symbols-outlined text-[15px]">inventory_2</span>
                                    <span>¡Solo quedan {{ $stock }} unidades en inventario!</span>
                                </div>
                            @elseif($stock <= 0)
                                <div class="mt-2.5 inline-flex items-center gap-1.5 text-xs text-red-600 font-bold">
                                    <span class="material-symbols-outlined text-[15px]">cancel</span>
                                    <span>Agotado temporalmente</span>
                                </div>
                            @endif
                        </div>

                        <!-- Selector de Cantidad, Precio y Eliminación -->
                        <div class="w-full sm:w-auto flex items-center justify-between sm:flex-col sm:items-end gap-3 pt-3 sm:pt-0 border-t sm:border-t-0 border-gray-100">
                            
                            <!-- Control Cantidad -->
                            <div class="inline-flex items-center bg-gray-50 border border-gray-200 rounded-xl p-0.5 shadow-2xs">
                                <button type="button" 
                                        wire:click="decrementar({{ $item->id }})" 
                                        wire:loading.attr="disabled"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-600 hover:bg-white hover:text-gray-900 active:scale-95 transition-all cursor-pointer disabled:opacity-50" 
                                        title="Disminuir cantidad">
                                    <span class="material-symbols-outlined text-[16px]">remove</span>
                                </button>
                                
                                <span class="w-10 text-center font-bold text-sm text-gray-900 font-mono">
                                    {{ $item->cantidad }}
                                </span>

                                <button type="button" 
                                        wire:click="incrementar({{ $item->id }})" 
                                        wire:loading.attr="disabled"
                                        class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-600 hover:bg-white hover:text-gray-900 active:scale-95 transition-all cursor-pointer disabled:opacity-50" 
                                        title="Aumentar cantidad">
                                    <span class="material-symbols-outlined text-[16px]">add</span>
                                </button>
                            </div>

                            <!-- Precio Unitario y Subtotal -->
                            <div class="text-right">
                                @if($producto && $producto->oferta_activa && $producto->precio_oferta && (float)$producto->precio > (float)$item->precio_unitario)
                                    <p class="text-xs text-gray-400 line-through font-mono">
                                        ${{ number_format((float)$producto->precio * $item->cantidad, 2) }}
                                    </p>
                                @endif
                                <p class="text-base sm:text-lg font-extrabold text-[#002349] font-mono tracking-tight">
                                    ${{ number_format($item->subtotal, 2) }}
                                </p>
                                @if($item->cantidad > 1)
                                    <p class="text-[10px] text-gray-400 font-mono">
                                        ${{ number_format((float)$item->precio_unitario, 2) }} c/u
                                    </p>
                                @endif
                            </div>

                            <!-- Botón Eliminar -->
                            <button type="button" 
                                    wire:click="eliminar({{ $item->id }})" 
                                    wire:loading.attr="disabled"
                                    class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer" 
                                    title="Eliminar producto del carrito">
                                <span class="material-symbols-outlined text-[18px]">delete</span>
                            </button>
                        </div>
                    </div>
                @endforeach

                <!-- Enlace a continuar comprando -->
                <div class="pt-2 flex items-center justify-between">
                    <a href="{{ route('cliente.catalogo') }}" wire:navigate
                       class="inline-flex items-center gap-1.5 text-xs font-bold text-[#006148] hover:text-[#004f3b] transition-colors">
                        <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                        <span>Continuar comprando en el catálogo</span>
                    </a>
                </div>
            </div>

            <!-- Right Column: Resumen de Orden Sticky -->
            <div class="w-full lg:w-96 shrink-0">
                <div class="bg-white border border-gray-200/90 rounded-2xl p-6 shadow-xs sticky top-24 space-y-5">
                    
                    <h2 class="text-lg font-bold text-[#002349] pb-3 border-b border-gray-100 flex items-center justify-between">
                        <span>Resumen de Orden</span>
                        <span class="material-symbols-outlined text-[#006148] text-[20px]">receipt_long</span>
                    </h2>

                    <!-- Desglose de Costos -->
                    <div class="space-y-3 text-sm">
                        
                        <!-- Subtotal -->
                        <div class="flex justify-between items-center text-gray-600">
                            <span>Subtotal</span>
                            <span class="font-bold text-gray-900 font-mono">
                                ${{ number_format($resumen['subtotal'], 2) }}
                            </span>
                        </div>

                        <!-- Descuento por Cupón -->
                        @if($resumen['descuento'] > 0 || $carrito->cupon_id)
                            <div class="flex justify-between items-center text-emerald-700 bg-emerald-50/80 px-3 py-2 rounded-xl border border-emerald-200/70">
                                <div class="flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px]">sell</span>
                                    <span class="font-semibold text-xs">
                                        Cupón: <span class="font-mono font-bold uppercase">{{ $carrito->cupon ? $carrito->cupon->codigo : 'APLICADO' }}</span>
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold font-mono text-xs">
                                        -${{ number_format($resumen['descuento'], 2) }}
                                    </span>
                                    <button type="button" 
                                            wire:click="removerCupon" 
                                            class="text-emerald-800 hover:text-red-600 transition-colors p-0.5" 
                                            title="Quitar cupón">
                                        <span class="material-symbols-outlined text-[14px]">close</span>
                                    </button>
                                </div>
                            </div>
                        @endif

                        <!-- ITBMS (7%) -->
                        <div class="flex justify-between items-center text-gray-600">
                            <span class="flex items-center gap-1">
                                <span>ITBMS (7%)</span>
                                <span class="text-[10px] text-gray-400" title="Impuesto de Transferencia de Bienes Muebles y Servicios">(Ley de Panamá)</span>
                            </span>
                            <span class="font-bold text-gray-900 font-mono">
                                ${{ number_format($resumen['itbms'], 2) }}
                            </span>
                        </div>

                        <!-- Envío Estimado -->
                        <div class="flex justify-between items-center text-gray-600">
                            <div class="flex items-center gap-1">
                                <span>Envío Estimado</span>
                                <span class="text-[10px] text-gray-400">(Centro / Panamá)</span>
                            </div>
                            <span class="font-bold font-mono {{ $resumen['envio'] == 0 ? 'text-emerald-600' : 'text-gray-900' }}">
                                @if($resumen['envio'] == 0)
                                    GRATIS
                                @else
                                    ${{ number_format($resumen['envio'], 2) }}
                                @endif
                            </span>
                        </div>
                    </div>

                    <!-- Total Final -->
                    <div class="pt-4 border-t border-gray-100 flex justify-between items-baseline">
                        <div>
                            <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Total a Pagar</span>
                            <span class="text-xs text-gray-500">Impuestos y envío incluidos</span>
                        </div>
                        <span class="text-2xl sm:text-3xl font-extrabold text-[#002349] font-mono tracking-tight">
                            ${{ number_format($resumen['total'], 2) }}
                        </span>
                    </div>

                    <!-- Input de Cupón de Descuento (Desplegable al hacer clic) -->
                    <div x-data="{ abierto: {{ $carrito->cupon_id || $mensajeCupon || !empty($codigoCupon) ? 'true' : 'false' }} }" class="pt-2">
                        <!-- Enlace interactivo estilo botón subrayado -->
                        <button type="button" 
                                @click="abierto = !abierto; if(abierto) { $nextTick(() => document.getElementById('codigo_cupon_input')?.focus()); }" 
                                class="text-xs font-semibold text-[#002349] hover:text-[#006148] underline underline-offset-4 decoration-1 hover:decoration-[#006148] flex items-center gap-1.5 transition-colors cursor-pointer focus:outline-none select-none text-left">
                            <span>Código de descuento o tarjeta de regalo</span>
                            <span class="material-symbols-outlined text-[15px] transition-transform duration-200 text-gray-500" :class="abierto ? 'rotate-180' : ''">expand_more</span>
                        </button>

                        <!-- Contenedor desplegable del input y botón -->
                        <div x-show="abierto" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-cloak 
                             class="mt-3 space-y-2">
                            <div class="flex gap-2">
                                <input type="text" 
                                       id="codigo_cupon_input"
                                       wire:model="codigoCupon"
                                       wire:keydown.enter="aplicarCupon"
                                       placeholder="Ingresa tu código" 
                                       class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3.5 py-2 text-xs font-mono font-semibold uppercase text-gray-900 focus:bg-white focus:ring-1 focus:ring-[#006148] focus:border-[#006148] transition-all" />
                                
                                <button type="button" 
                                        wire:click="aplicarCupon"
                                        wire:loading.attr="disabled"
                                        class="bg-[#002349] hover:bg-[#001730] text-white border border-[#002349] text-xs font-bold px-4 py-2 rounded-xl transition-all cursor-pointer whitespace-nowrap disabled:opacity-50 shadow-2xs">
                                    <span wire:loading.remove wire:target="aplicarCupon">Aplicar</span>
                                    <span wire:loading wire:target="aplicarCupon">...</span>
                                </button>
                            </div>

                            <!-- Mensaje de feedback de cupón -->
                            @if($mensajeCupon)
                                <div wire:key="msg-{{ uniqid() }}"
                                     x-data="{ show: true }" 
                                     x-init="setTimeout(() => show = false, 4000)" 
                                     x-show="show" 
                                     x-transition.opacity.duration.500ms
                                     class="text-xs font-medium {{ $tipoMensajeCupon === 'success' ? 'text-emerald-700 bg-emerald-50 p-2 rounded-lg border border-emerald-200' : 'text-red-600 bg-red-50 p-2 rounded-lg border border-red-200' }}">
                                    {{ $mensajeCupon }}
                                </div>
                            @endif
                        </div>
                    </div>


                    <!-- Botón de Continuar al Pago (Checkout) -->
                    <a href="{{ route('cliente.checkout.direccion') }}" wire:navigate
                            class="w-full bg-[#006148] hover:bg-[#004f3b] active:scale-[0.99] text-white font-bold py-3.5 px-4 rounded-xl shadow-sm hover:shadow-md transition-all flex items-center justify-center gap-2 text-sm uppercase tracking-wider cursor-pointer">
                        <span>Continuar al Pago</span>
                        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </a>

                    <!-- Métodos de pago aceptados -->
                    <div class="pt-2 flex items-center justify-center gap-3 opacity-70">
                        <span class="text-[10px] text-gray-400 font-semibold uppercase tracking-wider">Aceptamos:</span>
                        <span class="text-xs font-bold text-gray-600">Yappy</span>
                        <span class="text-gray-300">•</span>
                        <span class="text-xs font-bold text-gray-600">Visa / Mastercard</span>
                        <span class="text-gray-300">•</span>
                        <span class="text-xs font-bold text-gray-600">ACH</span>
                    </div>

                </div>
            </div>
        </div>

    @else
        <!-- Estado Carrito Vacío (Diseño Stitch: Carrito Vacío - PayMe Panamá) -->
        <section class="bg-white border border-gray-200/90 rounded-2xl p-8 sm:p-16 flex flex-col items-center justify-center text-center shadow-xs my-6">
            <div class="w-20 h-20 sm:w-24 sm:h-24 bg-[#e5eeff] text-[#002349] rounded-full flex items-center justify-center mb-6 shadow-inner">
                <span class="material-symbols-outlined text-4xl sm:text-5xl text-[#002349]/70">shopping_cart</span>
            </div>

            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#002349] mb-2 tracking-tight">
                Tu carrito está vacío
            </h1>

            <p class="text-sm text-gray-600 mb-8 max-w-md leading-relaxed">
                Aún no has agregado productos a tu carrito. Explora nuestro catálogo de tecnología y encuentra las mejores herramientas para tu PyME.
            </p>

            <a href="{{ route('cliente.catalogo') }}" wire:navigate
               class="bg-[#006148] hover:bg-[#004f3b] text-white font-bold text-xs uppercase tracking-wider px-8 py-4 rounded-full shadow-sm hover:shadow-md transition-all flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">storefront</span>
                <span>Ir al catálogo</span>
            </a>
        </section>
    @endif

    <!-- Sección Inferior: Lista de Deseos (Si hay items guardados) -->
    @if(isset($productosDeseos) && $productosDeseos->isNotEmpty())
        <div class="w-full h-[1px] bg-gray-200/80 my-12"></div>

        <section class="mb-12">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl sm:text-2xl font-bold text-[#002349] flex items-center gap-2">
                    <span class="material-symbols-outlined text-red-500">favorite</span>
                    <span>Lista de Deseos</span>
                </h2>

                <a href="{{ route('cliente.lista-deseos') }}" wire:navigate class="text-xs font-bold text-[#006148] hover:underline">
                    Ver todos
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($productosDeseos as $deseo)
                    @php
                        $imgDeseo = $deseo->imagen_url;
                    @endphp
                    <div wire:key="wishlist-item-{{ $deseo->id }}" 
                         class="bg-white border border-gray-200/90 rounded-2xl p-4 flex flex-col group hover:shadow-md hover:border-gray-300 transition-all">
                        
                        <!-- Imagen -->
                        <div class="w-full aspect-square bg-gray-50 rounded-xl overflow-hidden mb-3.5 relative border border-gray-100">
                            <img src="{{ $imgDeseo }}" 
                                 alt="{{ $deseo->nombre }}" 
                                 class="w-full h-full object-contain p-2 group-hover:scale-105 transition-transform duration-300"
                                 onerror="this.onerror=null; this.src='https://placehold.co/200x200?text=Sin+Imagen';" />
                            
                            @if($deseo->oferta_activa && $deseo->precio_oferta && (float)$deseo->precio > (float)$deseo->precio_oferta)
                                @php
                                    $descuentoPorc = round((((float)$deseo->precio - (float)$deseo->precio_oferta) / (float)$deseo->precio) * 100);
                                @endphp
                                <span class="absolute top-2 right-2 bg-red-600 text-white font-bold text-[10px] px-2 py-0.5 rounded-full shadow-xs">
                                    -{{ $descuentoPorc }}%
                                </span>
                            @endif
                        </div>

                        <!-- Info -->
                        <h3 class="text-sm font-bold text-gray-900 line-clamp-2 min-h-[40px] leading-snug">
                            <a href="{{ route('cliente.producto.detalle', $deseo->slug) }}" wire:navigate class="hover:text-[#006148] transition-colors">
                                {{ $deseo->nombre }}
                            </a>
                        </h3>

                        <div class="mt-auto pt-3">
                            <div class="flex items-center gap-2 mb-3">
                                @if($deseo->oferta_activa && $deseo->precio_oferta)
                                    <span class="text-base font-extrabold text-[#002349] font-mono">
                                        ${{ number_format($deseo->precio_oferta, 2) }}
                                    </span>
                                    <span class="text-xs text-gray-400 line-through font-mono">
                                        ${{ number_format($deseo->precio, 2) }}
                                    </span>
                                @else
                                    <span class="text-base font-extrabold text-[#002349] font-mono">
                                        ${{ number_format($deseo->precio, 2) }}
                                    </span>
                                @endif
                            </div>

                            <div class="flex gap-2">
                                <button type="button" 
                                        wire:click="moverDeseoAlCarrito({{ $deseo->id }})" 
                                        class="flex-1 bg-[#002349] hover:bg-[#001730] text-white text-xs font-bold py-2 px-3 rounded-xl transition-all text-center flex items-center justify-center gap-1 shadow-2xs cursor-pointer">
                                    <span class="material-symbols-outlined text-[15px]">add_shopping_cart</span>
                                    <span>Mover al Carrito</span>
                                </button>
                                
                                <button type="button" 
                                        wire:click="eliminarDeseo({{ $deseo->id }})" 
                                        class="p-2 border border-gray-200 text-gray-400 hover:text-red-600 hover:bg-red-50 hover:border-red-200 rounded-xl transition-colors cursor-pointer" 
                                        title="Quitar de la lista de deseos">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
