<div x-data="{ 
        abierto: @entangle('abierto'),
        init() {
            window.addEventListener('abrir-carrito', () => {
                this.abierto = true;
                $wire.abrir();
            });
        }
     }" 
     @keydown.window.escape="abierto = false; $wire.cerrar();"
     class="relative z-50">

    <!-- Backdrop Oscuro -->
    <div x-show="abierto" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="abierto = false; $wire.cerrar();"
         class="fixed inset-0 bg-black/50 backdrop-blur-2xs transition-opacity z-50"
         x-cloak></div>

    <!-- Panel Lateral Drawer (Slide-over) -->
    <div x-show="abierto" 
         x-transition:enter="transform transition ease-in-out duration-300"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transform transition ease-in-out duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full"
         class="fixed inset-y-0 right-0 max-w-full flex pl-10 z-50 pointer-events-auto"
         x-cloak>
        
        <div class="w-screen max-w-md bg-white shadow-2xl flex flex-col h-full border-l border-gray-200">
            
            <!-- Header del Drawer -->
            <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between bg-white sticky top-0 z-10">
                <div class="flex items-center gap-2.5 text-[#002349]">
                    <span class="material-symbols-outlined text-[24px]">shopping_cart</span>
                    <h2 class="text-base sm:text-lg font-bold tracking-tight text-gray-900">
                        Carrito de Compras ({{ $cantidadTotal }})
                    </h2>
                </div>

                <button type="button" 
                        @click="abierto = false; $wire.cerrar();"
                        class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer"
                        title="Cerrar carrito">
                    <span class="material-symbols-outlined text-[22px]">close</span>
                </button>
            </div>

            <!-- Contenido / Lista de Items (Scrollable) -->
            <div class="flex-1 overflow-y-auto px-5 py-4 divide-y divide-gray-100 space-y-4">
                @forelse($items as $item)
                    <div class="pt-4 first:pt-0 flex gap-3.5 group">
                        
                        <!-- Miniatura Imagen -->
                        <div class="w-20 h-20 shrink-0 bg-gray-50 border border-gray-200 rounded-xl overflow-hidden p-1.5 flex items-center justify-center">
                            <img src="{{ $item['imagen_url'] }}" 
                                 alt="{{ $item['nombre'] }}" 
                                 class="w-full h-full object-contain"
                                 onerror="this.onerror=null; this.src='https://placehold.co/100x100?text=Sin+Imagen';" />
                        </div>

                        <!-- Detalles del Producto -->
                        <div class="flex-1 min-w-0 flex flex-col justify-between">
                            <div>
                                <a href="{{ route('cliente.producto.detalle', $item['slug']) }}" wire:navigate
                                   @click="abierto = false; $wire.cerrar();"
                                   class="text-xs font-bold text-gray-900 hover:text-[#006148] transition-colors line-clamp-2 leading-snug">
                                    {{ $item['nombre'] }}
                                </a>

                                @if(!empty($item['variante_info']))
                                    <p class="text-[11px] text-gray-500 mt-0.5 font-medium">
                                        {{ $item['variante_info'] }}
                                    </p>
                                @endif

                                <!-- Precio Destacado -->
                                <p class="text-xs sm:text-sm font-extrabold text-red-600 font-mono mt-1">
                                    $ {{ number_format($item['precio'], 2) }}
                                </p>
                            </div>

                            <!-- Selector de Cantidad y Enlace de Eliminar -->
                            <div class="flex items-center justify-between mt-2 pt-1">
                                <!-- Caja de Cantidad [ -  1  + ] -->
                                <div class="inline-flex items-center border border-gray-300 rounded-md bg-white text-xs font-semibold">
                                    <button type="button" 
                                            wire:click="decrementar({{ $item['id'] }})"
                                            wire:loading.attr="disabled"
                                            class="w-7 h-7 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition-colors disabled:opacity-40 cursor-pointer">
                                        -
                                    </button>
                                    <span class="w-8 text-center font-mono font-bold text-gray-900 select-none">
                                        {{ $item['cantidad'] }}
                                    </span>
                                    <button type="button" 
                                            wire:click="incrementar({{ $item['id'] }})"
                                            wire:loading.attr="disabled"
                                            class="w-7 h-7 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition-colors disabled:opacity-40 cursor-pointer">
                                        +
                                    </button>
                                </div>

                                <!-- Botón Eliminar Texto -->
                                <button type="button" 
                                        wire:click="eliminar({{ $item['id'] }})"
                                        wire:loading.attr="disabled"
                                        class="text-xs font-medium text-gray-500 hover:text-red-600 underline underline-offset-2 transition-colors cursor-pointer disabled:opacity-40">
                                    Eliminar
                                </button>
                            </div>
                        </div>

                    </div>
                @empty
                    <!-- Estado Vacío dentro del Drawer -->
                    <div class="py-16 text-center flex flex-col items-center justify-center">
                        <div class="w-16 h-16 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center mb-3">
                            <span class="material-symbols-outlined text-3xl">shopping_cart</span>
                        </div>
                        <p class="text-sm font-bold text-gray-800 mb-1">Tu carrito está vacío</p>
                        <p class="text-xs text-gray-500 max-w-xs mb-5">
                            Explora nuestro catálogo para encontrar los mejores productos tecnológicos.
                        </p>
                        <a href="{{ route('cliente.catalogo') }}" 
                           wire:navigate
                           @click="abierto = false; $wire.cerrar();"
                           class="bg-[#002349] hover:bg-[#001730] text-white text-xs font-bold px-5 py-2.5 rounded-full transition-all">
                            Ir al Catálogo
                        </a>
                    </div>
                @endforelse
            </div>

            <!-- Footer del Drawer -->
            @if(count($items) > 0)
                <div class="p-5 border-t border-gray-200 bg-gray-50/70 space-y-4">
                    
                    <!-- Subtotal / Total Parcial -->
                    <div class="flex justify-between items-baseline">
                        <span class="text-sm font-semibold text-gray-800">Total parcial:</span>
                        <span class="text-lg font-extrabold text-gray-900 font-mono">
                            $ {{ number_format($totalParcial, 2) }}
                        </span>
                    </div>

                    <p class="text-[11px] text-gray-500 leading-tight">
                        Impuestos y envío calculados al finalizar la compra
                    </p>

                    <!-- Botones de Acción (FINALIZAR COMPRA & VER CARRITO) -->
                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <!-- Botón Finalizar Compra (Negro) -->
                        <a href="{{ route('cliente.carrito') }}" wire:navigate
                           class="w-full bg-black hover:bg-gray-800 text-white font-bold text-xs uppercase tracking-wider py-3.5 px-3 rounded-lg text-center transition-all shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                            <span>Finalizar Compra</span>
                        </a>

                        <!-- Botón Ver Carrito (Azul PayMe) -->
                        <a href="{{ route('cliente.carrito') }}" wire:navigate
                           class="w-full bg-[#002349] hover:bg-[#001730] text-white font-bold text-xs uppercase tracking-wider py-3.5 px-3 rounded-lg text-center transition-all shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                            <span>Ver Carrito</span>
                        </a>
                    </div>

                </div>
            @endif

        </div>

    </div>

</div>
