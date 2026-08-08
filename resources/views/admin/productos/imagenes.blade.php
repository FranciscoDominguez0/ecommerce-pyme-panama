@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\ImagenProducto>|\App\Models\ImagenProducto[] $imagenes */
@endphp

<!-- Submódulo: Galería de Imágenes del Producto -->
<div class="card-elevated p-5 sm:p-6 rounded-2xl space-y-4">
    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-emerald-600 text-[20px]">photo_library</span>
            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Galería de Imágenes</h2>
        </div>
        <span class="text-[11px] font-semibold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-full" id="contador-imagenes">
            {{ count($imagenes ?? []) }} imágenes cargadas
        </span>
    </div>

    <!-- Dropzone Visual para Cargar Archivos (Drag & Drop + Clic) Compacto -->
    <div id="dropzone-imagenes" 
         class="relative border-2 border-dashed border-slate-300 hover:border-emerald-500 bg-slate-50/50 hover:bg-emerald-50/30 rounded-xl p-3.5 text-center cursor-pointer transition-all group">
        
        <input type="file" 
               id="input-archivos-imagenes" 
               name="imagenes[]" 
               multiple 
               accept="image/png, image/jpeg, image/webp, image/svg+xml, .svg, .png, .jpg, .jpeg, .webp" 
               onchange="handleSeleccionArchivos(this)"
               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">

        <div class="flex items-center justify-center gap-3 pointer-events-none">
            <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 shadow-2xs flex items-center justify-center text-slate-400 group-hover:text-emerald-600 group-hover:border-emerald-300 transition-all shrink-0">
                <span class="material-symbols-outlined text-[20px]">cloud_upload</span>
            </div>
            <div class="text-left">
                <div class="text-xs text-slate-700 font-medium">
                    <span class="font-bold text-emerald-700 group-hover:underline">Haz clic para subir fotos</span> o arrástralas aquí
                </div>
                <p class="text-[10px] text-slate-400">PNG, JPG, WebP o SVG (Máx 5MB)</p>
            </div>
        </div>
    </div>

    <!-- Opción secundaria: Agregar Imagen por URL o SVG -->
    <div class="flex items-center gap-2 pt-1">
        <div class="relative flex-1">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[16px]">link</span>
            <input type="text" 
                   id="input-url-imagen-rapida" 
                   placeholder="O pega una URL de imagen o ícono..." 
                   onkeydown="if(event.key==='Enter'){event.preventDefault(); agregarImagenPorUrl();}"
                   class="pl-8 text-xs py-1.5 px-3 rounded-xl border border-slate-200 bg-slate-50/50 w-full focus:bg-white focus:ring-emerald-500 focus:border-emerald-500 text-slate-800">
        </div>
        <button type="button" 
                onclick="agregarImagenPorUrl()" 
                class="inline-flex items-center gap-1 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-xl transition-colors shadow-2xs shrink-0">
            <span class="material-symbols-outlined text-[15px]">add</span>
            <span>Añadir URL</span>
        </button>
    </div>

    <!-- Input oculto para rastrear el ID de la imagen principal -->
    <input type="hidden" name="imagen_principal_id" id="input-imagen-principal-id" value="{{ optional($imagenes->where('es_principal', true)->first())->id ?? '' }}">

    <!-- Grid de Miniaturas de Imágenes (Full-Width Responsive) -->
    <div id="grid-imagenes-producto" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3.5 pt-2">
        
        @forelse($imagenes ?? [] as $idx => $img)
            @php /** @var \App\Models\ImagenProducto $img */ @endphp
            {{-- Input oculto para marcar esta imagen como existente; si se elimina, el JS lo convierte a imagenes_eliminar[] --}}
            <div class="relative group card-elevated rounded-2xl overflow-hidden {{ $img->es_principal ? 'border-2 border-emerald-500 shadow-md ring-2 ring-emerald-500/20' : 'border border-slate-200/90 hover:border-slate-300 shadow-2xs' }} bg-white flex flex-col item-imagen transition-all duration-200" data-id="{{ $img->id }}" data-db-id="{{ $img->id }}">
                
                <!-- Badge de Portada / Principal -->
                <div class="badge-principal-container {{ $img->es_principal ? '' : 'hidden' }}">
                    <div class="absolute top-2 left-2 z-10">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-600 text-white shadow-sm">
                            <span class="material-symbols-outlined text-[11px]">star</span>
                            <span>Portada</span>
                        </span>
                    </div>
                </div>

                <!-- Input oculto: si el usuario elimina esta card, el JS agrega imagenes_eliminar[id] al form -->
                <input type="hidden" name="imagenes_existentes[]" value="{{ $img->id }}" class="input-imagen-existente">
                <input type="hidden" name="orden_imagenes[]" value="{{ $img->id }}" class="input-orden-imagen">

                <!-- Botones de Acción Rápida -->
                <div class="absolute top-2 right-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1 bg-slate-900/85 p-1 rounded-xl backdrop-blur-xs shadow-md">
                    <button type="button" 
                            onclick="moverImagenIzquierda(this)" 
                            title="Mover a la izquierda" 
                            class="text-white hover:text-emerald-400 p-0.5 transition-colors">
                        <span class="material-symbols-outlined text-[14px]">arrow_back</span>
                    </button>
                    <button type="button" 
                            onclick="moverImagenDerecha(this)" 
                            title="Mover a la derecha" 
                            class="text-white hover:text-emerald-400 p-0.5 transition-colors">
                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                    </button>
                    <button type="button" 
                            onclick="eliminarCardImagenExistente(this)" 
                            title="Eliminar imagen" 
                            class="text-rose-300 hover:text-rose-400 p-0.5 ml-0.5 border-l border-slate-700 transition-colors">
                        <span class="material-symbols-outlined text-[14px]">delete</span>
                    </button>
                </div>

                <!-- Contenedor de la Imagen Elegante -->
                <div class="h-32 w-full bg-slate-50/80 flex items-center justify-center p-3 group-hover:bg-slate-100/50 transition-colors">
                    <div class="w-full h-full flex items-center justify-center text-slate-700">
                        @if(!empty($img->ruta) && (str_starts_with($img->ruta, 'http') || str_starts_with($img->ruta, '/storage') || str_starts_with($img->ruta, 'storage/')))
                            <img src="{{ str_starts_with($img->ruta, 'storage/') ? asset($img->ruta) : $img->ruta }}" alt="Foto producto" class="max-h-full max-w-full object-contain mix-blend-multiply transition-transform group-hover:scale-105">
                        @elseif(!empty($img->ruta) && (str_starts_with($img->ruta, '<svg') || str_contains($img->ruta, '</svg>')))
                            <div class="w-full h-full flex items-center justify-center svg-container">{!! $img->ruta !!}</div>
                        @elseif(!empty($img->ruta))
                            <span class="material-symbols-outlined text-[42px] text-slate-600">{{ $img->ruta }}</span>
                        @else
                            <span class="material-symbols-outlined text-[42px] text-slate-400">image</span>
                        @endif
                    </div>
                </div>

                <!-- Footer sin nombre de archivo, centrado y limpio -->
                <div class="p-1.5 border-t border-slate-100 bg-white flex items-center justify-center text-[11px] card-footer-actions min-h-[28px]">
                    <div class="estado-portada-container w-full text-center">
                        @if($img->es_principal)
                            <span class="text-emerald-600 font-extrabold text-[11px] badge-texto-portada flex items-center justify-center gap-1">
                                <span class="material-symbols-outlined text-[13px]">check_circle</span>
                                <span>Portada</span>
                            </span>
                        @else
                            <button type="button" 
                                    onclick="hacerImagenPrincipal(this)" 
                                    class="w-full text-[11px] font-bold text-slate-500 hover:text-emerald-700 opacity-0 group-hover:opacity-100 transition-all duration-200 btn-hacer-portada py-0.5">
                                Hacer Portada
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div id="empty-state-galeria" class="col-span-full py-8 text-center text-slate-400 text-xs">
                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center mx-auto text-slate-400 mb-1.5">
                    <span class="material-symbols-outlined text-[24px]">add_photo_alternate</span>
                </div>
                <p class="font-medium text-slate-600">No hay imágenes cargadas para este producto.</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Sube archivos desde tu equipo o añade una URL arriba.</p>
            </div>
        @endforelse

    </div>
</div>

<script>
    // Manejo de carga de archivos locales con Previsualización Inmediata
    function handleSeleccionArchivos(input) {
        const files = input.files;
        if (!files || files.length === 0) return;

        const grid = document.getElementById('grid-imagenes-producto');
        const empty = document.getElementById('empty-state-galeria');
        if (empty) empty.remove();

        Array.from(files).forEach((file, i) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const src = e.target.result;
                const isFirst = grid.querySelectorAll('.item-imagen').length === 0;
                crearCardImagenHtml(src, file.name, isFirst, false);
                actualizarContadorGaleria();
            };
            reader.readAsDataURL(file);
        });
    }

    // Agregar imagen rápida por URL
    function agregarImagenPorUrl() {
        const input = document.getElementById('input-url-imagen-rapida');
        const val = input.value.trim();
        if (!val) {
            alert('Por favor ingresa una URL de imagen válida o un ícono.');
            return;
        }

        const grid = document.getElementById('grid-imagenes-producto');
        const empty = document.getElementById('empty-state-galeria');
        if (empty) empty.remove();

        const isFirst = grid.querySelectorAll('.item-imagen').length === 0;
        const nombreMostrar = val.length > 25 ? val.substring(0, 22) + '...' : val;
        crearCardImagenHtml(val, nombreMostrar, isFirst, true);
        actualizarContadorGaleria();
        input.value = '';
    }

    // Crear el elemento HTML de miniatura de imagen
    function crearCardImagenHtml(rutaOrBase64, nombreArchivo, esPrincipal, esUrl) {
        const grid = document.getElementById('grid-imagenes-producto');
        const div = document.createElement('div');
        div.className = `relative group card-elevated rounded-2xl overflow-hidden ${esPrincipal ? 'border-2 border-emerald-500 shadow-md ring-2 ring-emerald-500/20' : 'border border-slate-200/90 hover:border-slate-300 shadow-2xs'} bg-white flex flex-col item-imagen transition-all duration-200`;

        let contentPreview = '';
        if (rutaOrBase64.startsWith('data:image') || rutaOrBase64.startsWith('http') || rutaOrBase64.startsWith('/storage') || rutaOrBase64.startsWith('storage/')) {
            contentPreview = `<img src="${rutaOrBase64}" alt="${nombreArchivo}" class="max-h-full max-w-full object-contain mix-blend-multiply transition-transform group-hover:scale-105">`;
        } else if (rutaOrBase64.includes('<svg') || rutaOrBase64.includes('</svg>')) {
            contentPreview = `<div class="w-full h-full flex items-center justify-center svg-container">${rutaOrBase64}</div>`;
        } else {
            contentPreview = `<span class="material-symbols-outlined text-[42px] text-slate-600">${rutaOrBase64}</span>`;
        }

        // Input oculto para enviar al backend si fue ingresada por URL
        let hiddenInput = '';
        if (esUrl) {
            const escapedValue = rutaOrBase64.replace(/"/g, '&quot;');
            hiddenInput = `<input type="hidden" name="imagenes_url[]" value="${escapedValue}">`;
        }

        div.innerHTML = `
            ${hiddenInput}

            <div class="badge-principal-container ${esPrincipal ? '' : 'hidden'}">
                <div class="absolute top-2 left-2 z-10">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-600 text-white shadow-sm">
                        <span class="material-symbols-outlined text-[11px]">star</span>
                        <span>Portada</span>
                    </span>
                </div>
            </div>

            <div class="absolute top-2 right-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1 bg-slate-900/85 p-1 rounded-xl backdrop-blur-xs shadow-md">
                <button type="button" onclick="moverImagenIzquierda(this)" title="Mover a la izquierda" class="text-white hover:text-emerald-400 p-0.5 transition-colors">
                    <span class="material-symbols-outlined text-[14px]">arrow_back</span>
                </button>
                <button type="button" onclick="moverImagenDerecha(this)" title="Mover a la derecha" class="text-white hover:text-emerald-400 p-0.5 transition-colors">
                    <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                </button>
                <button type="button" onclick="eliminarCardImagen(this)" title="Eliminar imagen" class="text-rose-300 hover:text-rose-400 p-0.5 ml-0.5 border-l border-slate-700 transition-colors">
                    <span class="material-symbols-outlined text-[14px]">delete</span>
                </button>
            </div>

            <div class="h-32 w-full bg-slate-50/80 flex items-center justify-center p-3 group-hover:bg-slate-100/50 transition-colors">
                <div class="w-full h-full flex items-center justify-center text-slate-700">
                    ${contentPreview}
                </div>
            </div>

            <div class="p-1.5 border-t border-slate-100 bg-white flex items-center justify-center text-[11px] card-footer-actions min-h-[28px]">
                <div class="estado-portada-container w-full text-center">
                    ${esPrincipal ? `
                        <span class="text-emerald-600 font-extrabold text-[11px] badge-texto-portada flex items-center justify-center gap-1">
                            <span class="material-symbols-outlined text-[13px]">check_circle</span>
                            <span>Portada</span>
                        </span>
                    ` : `
                        <button type="button" onclick="hacerImagenPrincipal(this)" class="w-full text-[11px] font-bold text-slate-500 hover:text-emerald-700 opacity-0 group-hover:opacity-100 transition-all duration-200 btn-hacer-portada py-0.5">
                            Hacer Portada
                        </button>
                    `}
                </div>
            </div>
        `;

        grid.appendChild(div);
    }

    function hacerImagenPrincipal(btn) {
        const grid = document.getElementById('grid-imagenes-producto');
        const cardActual = btn.closest('.item-imagen');
        if (!grid || !cardActual) return;

        // Actualizar input oculto para ID de BD si aplica
        const principalInput = document.getElementById('input-imagen-principal-id');
        if (principalInput) {
            principalInput.value = cardActual.dataset.dbId || '';
        }

        // Resetear todas las tarjetas
        grid.querySelectorAll('.item-imagen').forEach(card => {
            card.classList.remove('border-2', 'border-emerald-500', 'shadow-md', 'ring-2', 'ring-emerald-500/20');
            card.classList.add('border', 'border-slate-200/90', 'shadow-2xs');

            // Ocultar badge principal
            const badgeContainer = card.querySelector('.badge-principal-container');
            if (badgeContainer) {
                badgeContainer.classList.add('hidden');
            }

            // Restablecer botón a "Hacer Portada" (solo visible al pasar mouse)
            const estadoContainer = card.querySelector('.estado-portada-container');
            if (estadoContainer) {
                estadoContainer.innerHTML = `
                    <button type="button" onclick="hacerImagenPrincipal(this)" class="w-full text-[11px] font-bold text-slate-500 hover:text-emerald-700 opacity-0 group-hover:opacity-100 transition-all duration-200 btn-hacer-portada py-0.5">
                        Hacer Portada
                    </button>
                `;
            }
        });

        // Activar borde y estilo de tarjeta seleccionada
        cardActual.classList.remove('border', 'border-slate-200/90', 'shadow-2xs');
        cardActual.classList.add('border-2', 'border-emerald-500', 'shadow-md', 'ring-2', 'ring-emerald-500/20');

        // Mostrar badge principal en la seleccionada
        const badgeActual = cardActual.querySelector('.badge-principal-container');
        if (badgeActual) {
            badgeActual.classList.remove('hidden');
        }

        // Mostrar texto de "Portada" en la seleccionada
        const estadoActual = cardActual.querySelector('.estado-portada-container');
        if (estadoActual) {
            estadoActual.innerHTML = `
                <span class="text-emerald-600 font-extrabold text-[11px] badge-texto-portada flex items-center justify-center gap-1">
                    <span class="material-symbols-outlined text-[13px]">check_circle</span>
                    <span>Portada</span>
                </span>
            `;
        }
    }

    // Eliminar imagen NUEVA (subida en esta sesión, sin ID de BD)
    function eliminarCardImagen(btn) {
        const card = btn.closest('.item-imagen');
        if (card) {
            card.remove();
            actualizarContadorGaleria();
        }
    }

    // Eliminar imagen EXISTENTE (guardada en BD) → inyecta hidden input imagenes_eliminar[]
    function eliminarCardImagenExistente(btn) {
        const card = btn.closest('.item-imagen');
        if (!card) return;

        const dbId = card.dataset.dbId;
        if (dbId) {
            // Agrega un input oculto al formulario para que el controller elimine este ID
            const form = document.getElementById('form-producto');
            if (form) {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'imagenes_eliminar[]';
                hidden.value = dbId;
                form.appendChild(hidden);
            }
        }

        card.remove();
        actualizarContadorGaleria();
    }

    function moverImagenIzquierda(btn) {
        const card = btn.closest('.item-imagen');
        if (card && card.previousElementSibling && !card.previousElementSibling.id) {
            card.parentNode.insertBefore(card, card.previousElementSibling);
        }
    }

    function moverImagenDerecha(btn) {
        const card = btn.closest('.item-imagen');
        if (card && card.nextElementSibling) {
            card.parentNode.insertBefore(card.nextElementSibling, card);
        }
    }

    function actualizarContadorGaleria() {
        const total = document.querySelectorAll('.item-imagen').length;
        const contador = document.getElementById('contador-imagenes');
        if (contador) {
            contador.textContent = `${total} imágenes cargadas`;
        }

        const grid = document.getElementById('grid-imagenes-producto');
        if (total === 0 && grid && !document.getElementById('empty-state-galeria')) {
            grid.innerHTML = `
                <div id="empty-state-galeria" class="col-span-full py-8 text-center text-slate-400 text-xs">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center mx-auto text-slate-400 mb-1.5">
                        <span class="material-symbols-outlined text-[24px]">add_photo_alternate</span>
                    </div>
                    <p class="font-medium text-slate-600">No hay imágenes cargadas para este producto.</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Sube archivos desde tu equipo o añade una URL arriba.</p>
                </div>
            `;
        }
    }
</script>
