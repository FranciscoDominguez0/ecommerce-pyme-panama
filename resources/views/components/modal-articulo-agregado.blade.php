@props([
    'id' => 'modal-articulo-agregado',
])

<!-- Panel Flotante a la Derecha (Sin difuminar ni bloquear la pantalla): Artículo agregado al carrito -->
<div id="{{ $id }}"
     class="fixed top-18 sm:top-20 right-3 sm:right-6 md:right-8 z-[100] hidden flex-col w-[calc(100vw-1.5rem)] max-w-sm sm:max-w-[370px] select-none pointer-events-auto"
     aria-live="polite">

    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200/90 p-5 space-y-4 animate-in fade-in slide-in-from-top-4 sm:slide-in-from-right-6 duration-200 relative">

        <!-- Header: Checkmark, Título y Botón Cerrar -->
        <div class="flex items-center justify-between pb-0.5">
            <div class="flex items-center gap-2 text-slate-800">
                <svg class="w-4 h-4 text-slate-900 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
                <h2 class="text-xs sm:text-[13px] font-semibold text-slate-900 tracking-tight">
                    Artículo agregado a tu carrito
                </h2>
            </div>

            <button type="button"
                    onclick="window.ModalArticuloAgregado.cerrar()"
                    class="text-slate-400 hover:text-slate-700 p-1 -mr-1 rounded-lg transition-colors cursor-pointer"
                    aria-label="Cerrar notificación">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Cuerpo: Imagen del Producto y Nombre -->
        <div class="flex items-center gap-3.5 py-1">
            <!-- Miniatura del Producto -->
            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-white border border-slate-200/90 rounded-xl p-1.5 flex items-center justify-center shrink-0 overflow-hidden shadow-2xs">
                <img id="{{ $id }}-img"
                     src=""
                     alt="Producto"
                     class="max-w-full max-h-full object-contain"
                     onerror="this.onerror=null; this.src='https://placehold.co/100x100?text=Producto';">
            </div>

            <!-- Nombre y detalles del producto -->
            <div class="flex-1 min-w-0">
                <h3 id="{{ $id }}-nombre"
                    class="text-xs sm:text-[13px] font-bold text-slate-900 leading-snug tracking-tight uppercase line-clamp-2">
                </h3>
                <p id="{{ $id }}-variante" class="text-[11px] text-slate-500 mt-0.5 font-medium hidden"></p>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="space-y-2.5 pt-1">
            <!-- Botón 1: Ver Carrito (Outline Pill) -->
            <a href="{{ route('cliente.carrito') }}"
               wire:navigate
               onclick="window.ModalArticuloAgregado.cerrar()"
               id="{{ $id }}-btn-carrito"
               class="w-full block py-2.5 px-4 text-center text-sm font-semibold rounded-full border-2 border-[#1b8058] text-[#1b8058] hover:bg-[#1b8058]/5 active:scale-[0.99] transition-all cursor-pointer">
                Ver carrito (<span id="{{ $id }}-cantidad">1</span>)
            </a>

            <!-- Botón 2: Pagar Pedido (Solid Emerald Pill) -->
            <a href="{{ route('cliente.checkout.direccion') }}"
               wire:navigate
               onclick="window.ModalArticuloAgregado.cerrar()"
               id="{{ $id }}-btn-pagar"
               class="w-full block py-2.5 px-4 text-center text-sm font-semibold rounded-full bg-[#1b8058] hover:bg-[#156e4a] active:bg-[#0f5438] active:scale-[0.99] text-white shadow-xs hover:shadow transition-all cursor-pointer">
                Pagar pedido
            </a>

            <!-- Botón 3: Seguir Comprando (Text Link) -->
            <button type="button"
                    onclick="window.ModalArticuloAgregado.cerrar()"
                    class="block mx-auto text-sm font-medium text-[#1b8058] hover:text-[#156e4a] underline underline-offset-4 py-1 transition-colors cursor-pointer">
                Seguir comprando
            </button>
        </div>

    </div>
</div>

<script>
    (function() {
        const id = '{{ $id }}';

        window.ModalArticuloAgregado = {
            abrir: function(datos) {
                datos = datos || {};
                const panel = document.getElementById(id);
                if (!panel) return;

                const imgEl = document.getElementById(id + '-img');
                const nombreEl = document.getElementById(id + '-nombre');
                const cantidadEl = document.getElementById(id + '-cantidad');
                const varianteEl = document.getElementById(id + '-variante');

                if (nombreEl) {
                    nombreEl.textContent = (datos.nombre || 'Producto agregado').trim();
                }

                if (imgEl) {
                    if (datos.imagen) {
                        imgEl.src = datos.imagen;
                    } else {
                        imgEl.src = 'https://placehold.co/100x100?text=Producto';
                    }
                }

                if (cantidadEl) {
                    cantidadEl.textContent = datos.cantidadTotal || '1';
                }

                if (varianteEl) {
                    if (datos.variante) {
                        varianteEl.textContent = datos.variante;
                        varianteEl.classList.remove('hidden');
                    } else {
                        varianteEl.classList.add('hidden');
                    }
                }

                panel.classList.remove('hidden');
                panel.classList.add('flex');
            },

            cerrar: function() {
                const panel = document.getElementById(id);
                if (!panel) return;

                panel.classList.add('hidden');
                panel.classList.remove('flex');
            }
        };

        // Cerrar al hacer clic afuera del panel
        document.addEventListener('click', function(e) {
            const panel = document.getElementById(id);
            if (!panel || panel.classList.contains('hidden')) return;

            // Si el clic fue afuera del panel y no fue el botón que lo activó
            if (!panel.contains(e.target) && !e.target.closest('button[onclick*="agregarAlCarrito"]')) {
                window.ModalArticuloAgregado.cerrar();
            }
        });

        // Cerrar con tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const panel = document.getElementById(id);
                if (panel && !panel.classList.contains('hidden')) {
                    window.ModalArticuloAgregado.cerrar();
                }
            }
        });
    })();
</script>
