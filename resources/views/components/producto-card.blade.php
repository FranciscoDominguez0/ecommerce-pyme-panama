<!-- Tarjeta individual de producto -->
@props(['prod', 'priority' => false])

@php
    $img = $prod->imagenPrincipal();
@endphp
<div
    class="bg-white dark:bg-[#181a1b] rounded-2xl border border-slate-200 dark:border-gray-700 overflow-hidden shadow-xs hover:shadow-md transition-all flex flex-col group relative">

    <!-- Indicador de stock (verde si hay, rojo si está agotado) -->
    @if($prod->stock > 0)
        <div
            class="absolute top-4 right-4 z-10 flex items-center justify-center w-6 h-6 rounded-full bg-white dark:bg-[#181a1b] shadow-xs">
            <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
        </div>
    @else
        <div
            class="absolute top-4 right-4 z-10 flex items-center justify-center w-6 h-6 rounded-full bg-white dark:bg-[#181a1b] shadow-xs">
            <div class="w-2 h-2 rounded-full bg-rose-500"></div>
        </div>
    @endif

    <!-- Zona de la imagen y etiqueta de descuento -->
    <div class="relative h-48 bg-white dark:bg-[#181a1b] p-4 flex items-center justify-center">
        @if($prod->tienePromocionOPrecioOferta())
            <div class="absolute top-4 left-4 z-10">
                <span class="px-2 py-1 rounded bg-rose-500 text-white text-[10px] font-bold">
                    -{{ number_format($prod->porcentajeDescuentoPromocional(), 0) }}%
                </span>
            </div>
        @endif

        @if($img && (str_starts_with($img->ruta, 'http') || str_starts_with($img->ruta, '/storage') || str_starts_with($img->ruta, 'data:image') || str_starts_with($img->ruta, 'storage/')))
            <img src="{{ str_starts_with($img->ruta, 'storage/') ? asset($img->ruta) : $img->ruta }}"
                alt="{{ $prod->nombre }}" width="180" height="180"
                class="h-full object-contain group-hover:scale-105 transition-transform duration-300" @if($priority)
                loading="eager" fetchpriority="high" @else loading="lazy" decoding="async" @endif>
        @elseif($img && (str_starts_with($img->ruta, '<svg') || str_contains($img->ruta, '</svg>')))
            <div
                class="h-full flex items-center justify-center svg-container group-hover:scale-105 transition-transform duration-300">
                {!! $img->ruta !!}
            </div>
        @elseif($img && !empty($img->ruta))
            <span
                class="material-symbols-outlined text-[64px] text-slate-600 dark:text-slate-400 group-hover:scale-105 transition-transform duration-300"
                aria-hidden="true">{{ $img->ruta }}</span>
        @else
            <span class="material-symbols-outlined text-[64px] text-slate-300" aria-hidden="true">image</span>
        @endif
    </div>

    <!-- Botones ocultos que aparecen al pasar el ratón (Carrito, Deseos, Compartir, Ver) -->
    <div
        class="flex items-center justify-center gap-3 -mt-6 z-20 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
        <!-- Botón Carrito -->
        <button type="button" onclick="agregarAlCarritoListado({{ $prod->id }})"
            class="w-11 h-11 rounded-full bg-white dark:bg-[#181a1b] shadow-md border border-slate-100 dark:border-gray-700/80 flex items-center justify-center text-slate-600 dark:text-slate-400 hover:text-white hover:bg-emerald-700 transition-colors tooltip-trigger"
            title="Añadir al carrito" aria-label="Añadir {{ $prod->nombre }} al carrito">
            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">shopping_cart</span>
        </button>
        <!-- Botón Deseos -->
        <button type="button" onclick="agregarDeseoListado({{ $prod->id }})"
            class="w-11 h-11 rounded-full bg-white dark:bg-[#181a1b] shadow-md border border-slate-100 dark:border-gray-700/80 flex items-center justify-center text-slate-600 dark:text-slate-400 hover:text-white hover:bg-rose-500 transition-colors tooltip-trigger"
            title="Añadir a deseos" aria-label="Añadir {{ $prod->nombre }} a la lista de deseos">
            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">favorite</span>
        </button>
        <!-- Botón Compartir -->
        <button type="button" onclick="copiarLink('{{ route('cliente.producto.detalle', $prod->slug) }}')"
            class="w-11 h-11 rounded-full bg-white dark:bg-[#181a1b] shadow-md border border-slate-100 dark:border-gray-700/80 flex items-center justify-center text-slate-600 dark:text-slate-400 hover:text-white hover:bg-blue-600 transition-colors tooltip-trigger"
            title="Copiar enlace" aria-label="Copiar enlace de {{ $prod->nombre }}">
            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">share</span>
        </button>
        <!-- Botón Ver Detalle -->
        <a href="{{ route('cliente.producto.detalle', $prod->slug) }}" wire:navigate
            class="w-11 h-11 rounded-full bg-white dark:bg-[#181a1b] shadow-md border border-slate-100 dark:border-gray-700/80 flex items-center justify-center text-slate-600 dark:text-slate-400 hover:text-white hover:bg-slate-800 transition-colors tooltip-trigger"
            title="Vista rápida" aria-label="Ver detalles de {{ $prod->nombre }}">
            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">visibility</span>
        </a>
    </div>

    <!-- Información del producto (Nombre, SKU y precio) -->
    <div class="p-5 flex-1 flex flex-col justify-end space-y-2 text-center mt-2">
        <h3
            class="text-[13px] font-bold text-slate-900 dark:text-white group-hover:text-emerald-700 transition-colors line-clamp-2 leading-tight">
            <a href="{{ route('cliente.producto.detalle', $prod->slug) }}" wire:navigate>
                {{ $prod->nombre }}
            </a>
        </h3>

        <span class="text-[11px] font-medium text-slate-500 block">
            SKU: {{ $prod->sku }}
        </span>

        @if($prod->relationLoaded('valoracionesAprobadas') && $prod->total_valoraciones > 0)
            <div class="flex items-center justify-center gap-1 text-[11px] text-amber-500 font-bold">
                <span class="material-symbols-outlined star-filled text-[14px] text-amber-400"
                    style="font-variation-settings: 'FILL' 1;">star</span>
                <span>{{ number_format($prod->promedio_calificacion, 1) }}</span>
                <span class="text-slate-500 font-normal">({{ $prod->total_valoraciones }})</span>
            </div>
        @endif

        <div class="pt-2">
            @if($prod->tienePromocionOPrecioOferta())
                <div class="text-lg font-extrabold text-emerald-700">
                    ${{ number_format($prod->precioFinalPromocional(), 2) }}</div>
                <div class="text-[11px] text-slate-500 line-through">${{ number_format($prod->precio, 2) }}</div>
            @else
                <div class="text-lg font-extrabold text-emerald-700">${{ number_format($prod->precio, 2) }}</div>
            @endif
        </div>
    </div>

</div>

@pushOnce('scripts')
    <script data-navigate-once>
        (function () {
            if (window._productoCardScriptsInited) return;
            window._productoCardScriptsInited = true;

            window.agregarAlCarritoListado = function (productoId) {
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
                        variante_producto_id: null,
                        cantidad: 1
                    })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.exito) {
                            if (window.Livewire) {
                                Livewire.dispatch('carrito-actualizado');
                            }
                            if (window.ModalArticuloAgregado) {
                                window.ModalArticuloAgregado.abrir({
                                    id: productoId,
                                    nombre: data.producto_nombre,
                                    imagen: data.producto_imagen,
                                    precio: data.item ? data.item.precio_unitario : 0,
                                    cantidad: 1,
                                    cantidadTotal: data.cantidad_total || 1,
                                    variante: data.variante_texto
                                });
                            } else if (window.mostrarToast) {
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
            };

            window.agregarDeseoListado = function (productoId) {
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
            };

            window.copiarLink = function (url) {
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(url).then(() => {
                        if (window.mostrarToast) window.mostrarToast('success', 'Enlace copiado al portapapeles');
                    });
                } else {
                    const textArea = document.createElement("textarea");
                    textArea.value = url;
                    textArea.style.position = "fixed";
                    textArea.style.left = "-999999px";
                    textArea.style.top = "-999999px";
                    document.body.appendChild(textArea);
                    textArea.focus();
                    textArea.select();
                    try {
                        document.execCommand('copy');
                        if (window.mostrarToast) window.mostrarToast('success', 'Enlace copiado al portapapeles');
                    } catch (err) {
                        console.error('Fallback copy failed', err);
                    }
                    textArea.remove();
                }
            };
        })();
    </script>
@endPushOnce