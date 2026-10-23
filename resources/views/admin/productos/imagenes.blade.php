@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\ImagenProducto>|\App\Models\ImagenProducto[] $imagenes */
    $prodId = $productoId ?? ($producto->id ?? null);
    $imgPrincipal = optional($imagenes->where('es_principal', true)->first());
@endphp

<!-- Galería de Imágenes -->
<div class="card-elevated p-5 sm:p-6 rounded-2xl space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 dark:border-gray-700 pb-3 gap-2">
        <div>
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-600 text-[20px]">photo_library</span>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Galería de Imágenes</h2>
            </div>
            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                Arrastra las fotos como bloques para ordenarlas a tu gusto. Haz clic en "Hacer Portada" para fijar la foto principal.
            </p>
        </div>
        <span class="text-[11px] font-semibold text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-transparent px-2.5 py-1 rounded-full self-start sm:self-auto" id="contador-imagenes">
            {{ count($imagenes ?? []) }} imágenes cargadas
        </span>
    </div>

    <!-- Zona de carga de archivos -->
    <div id="dropzone-imagenes" 
         class="relative border-2 border-dashed border-slate-300 dark:border-gray-700 hover:border-emerald-500 bg-slate-50 dark:bg-transparent/50 hover:bg-emerald-50/30 rounded-xl p-3.5 text-center cursor-pointer transition-all group">
        
        <input type="file" 
               id="input-archivos-imagenes" 
               name="imagenes[]" 
               multiple 
               accept="image/png, image/jpeg, image/webp, image/svg+xml, .svg, .png, .jpg, .jpeg, .webp" 
               onchange="handleSeleccionArchivos(this)"
               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">

        <div class="flex items-center justify-center gap-3 pointer-events-none">
            <div class="w-8 h-8 rounded-lg bg-white dark:bg-[#181a1b] border border-slate-200 dark:border-gray-700 shadow-2xs flex items-center justify-center text-slate-400 group-hover:text-emerald-600 group-hover:border-emerald-300 transition-all shrink-0">
                <span class="material-symbols-outlined text-[20px]">cloud_upload</span>
            </div>
            <div class="text-left">
                <div class="text-xs text-slate-700 dark:text-slate-300 font-medium">
                    <span class="font-bold text-emerald-700 group-hover:underline">Haz clic para subir fotos</span> o arrástralas aquí
                </div>
                <p class="text-[10px] text-slate-400">PNG, JPG, WebP o SVG (Máx 5MB)</p>
            </div>
        </div>
    </div>

    <!-- Agregar imagen por enlace o icono -->
    <div class="flex items-center gap-2 pt-1">
        <div class="relative flex-1">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[16px]">link</span>
            <input type="text" 
                   id="input-url-imagen-rapida" 
                   placeholder="O pega una URL de imagen o ícono..." 
                   onkeydown="if(event.key==='Enter'){event.preventDefault(); agregarImagenPorUrl();}"
                   class="pl-8 text-xs py-1.5 px-3 rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-transparent/50 w-full focus:bg-white dark:bg-[#121415] focus:ring-emerald-500 focus:border-emerald-500 text-slate-800 dark:text-gray-100">
        </div>
        <button type="button" 
                onclick="agregarImagenPorUrl()" 
                class="inline-flex items-center gap-1 text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-transparent hover:bg-slate-200 dark:bg-gray-700 px-3 py-1.5 rounded-xl transition-colors shadow-2xs shrink-0">
            <span class="material-symbols-outlined text-[15px]">add</span>
            <span>Añadir URL</span>
        </button>
    </div>

    <!-- Datos ocultos para portada y orden -->
    <input type="hidden" name="imagen_portada_clave" id="input-imagen-portada-clave" value="{{ $imgPrincipal ? 'existente:' . $imgPrincipal->id : '' }}">
    <input type="hidden" name="imagen_principal_id" id="input-imagen-principal-id" value="{{ $imgPrincipal->id ?? '' }}">

    <!-- Cuadrícula de fotos -->
    <div id="grid-imagenes-producto" 
         data-producto-id="{{ $prodId }}"
         class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-8 gap-3.5 pt-2">
        
        @forelse($imagenes ?? [] as $idx => $img)
            @php /** @var \App\Models\ImagenProducto $img */ @endphp
            <div class="relative group card-elevated rounded-2xl overflow-hidden {{ $img->es_principal ? 'border-2 border-emerald-500 shadow-md ring-2 ring-emerald-500/20' : 'border border-slate-200 dark:border-gray-700/90 hover:border-slate-300 dark:border-gray-700 shadow-2xs' }} bg-white dark:bg-[#181a1b] flex flex-col item-imagen transition-all duration-200 cursor-grab active:cursor-grabbing select-none" 
                 draggable="true"
                 data-id="{{ $img->id }}" 
                 data-db-id="{{ $img->id }}"
                 data-clave="existente:{{ $img->id }}">
                
                <!-- Etiqueta de portada -->
                <div class="badge-principal-container {{ $img->es_principal ? '' : 'hidden' }}">
                    <div class="absolute top-2 left-2 z-10 pointer-events-none">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-600 text-white shadow-sm">
                            <span class="material-symbols-outlined text-[11px]">star</span>
                            <span>Portada</span>
                        </span>
                    </div>
                </div>

                <input type="hidden" name="imagenes_existentes[]" value="{{ $img->id }}" class="input-imagen-existente">
                <input type="hidden" name="orden_imagenes[]" value="{{ $img->id }}" class="input-orden-imagen">
                <input type="hidden" name="orden_galeria[]" value="existente:{{ $img->id }}" class="input-orden-galeria">

                <!-- Acciones de tarjeta -->
                <div class="absolute top-2 right-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1 bg-slate-900/85 p-1 rounded-xl backdrop-blur-xs shadow-md">
                    <div class="drag-handle text-slate-400 hover:text-white p-0.5 cursor-grab active:cursor-grabbing" title="Arrastrar para ordenar">
                        <span class="material-symbols-outlined text-[14px]">drag_indicator</span>
                    </div>
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

                <!-- Vista previa de foto -->
                <div class="h-32 w-full bg-slate-50 dark:bg-[#121415] flex items-center justify-center p-3 group-hover:bg-slate-100 dark:group-hover:bg-[#151718] transition-colors pointer-events-none">
                    <div class="w-full h-full flex items-center justify-center text-slate-700 dark:text-slate-300">
                        @if(!empty($img->ruta) && (str_starts_with($img->ruta, 'http') || str_starts_with($img->ruta, '/storage') || str_starts_with($img->ruta, 'storage/')))
                            <img src="{{ str_starts_with($img->ruta, 'storage/') ? asset($img->ruta) : $img->ruta }}" alt="Foto producto" class="max-h-full max-w-full object-contain transition-transform group-hover:scale-105 pointer-events-none">
                        @elseif(!empty($img->ruta) && (str_starts_with($img->ruta, '<svg') || str_contains($img->ruta, '</svg>')))
                            <div class="w-full h-full flex items-center justify-center svg-container">{!! $img->ruta !!}</div>
                        @elseif(!empty($img->ruta))
                            <span class="material-symbols-outlined text-[42px] text-slate-600 dark:text-slate-400">{{ $img->ruta }}</span>
                        @else
                            <span class="material-symbols-outlined text-[42px] text-slate-400">image</span>
                        @endif
                    </div>
                </div>

                <!-- Pie de tarjeta -->
                <div class="p-1.5 border-t border-slate-100 dark:border-gray-700 bg-white dark:bg-[#181a1b] flex items-center justify-center text-[11px] card-footer-actions min-h-[28px]">
                    <div class="estado-portada-container w-full text-center">
                        @if($img->es_principal)
                            <span class="text-emerald-600 font-extrabold text-[11px] badge-texto-portada flex items-center justify-center gap-1">
                                <span class="material-symbols-outlined text-[13px]">check_circle</span>
                                <span>Portada</span>
                            </span>
                        @else
                            <button type="button" 
                                    onclick="hacerImagenPrincipal(this)" 
                                    class="w-full text-[11px] font-bold text-slate-500 dark:text-slate-400 hover:text-emerald-700 opacity-0 group-hover:opacity-100 transition-all duration-200 btn-hacer-portada py-0.5">
                                Hacer Portada
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div id="empty-state-galeria" class="col-span-full py-8 text-center text-slate-400 text-xs">
                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-transparent flex items-center justify-center mx-auto text-slate-400 mb-1.5">
                    <span class="material-symbols-outlined text-[24px]">add_photo_alternate</span>
                </div>
                <p class="font-medium text-slate-600 dark:text-slate-400">No hay imágenes cargadas para este producto.</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Sube archivos desde tu equipo o añade una URL arriba.</p>
            </div>
        @endforelse

    </div>
</div>

<script>
    let archivosLocales = [];
    let tarjetaArrastrada = null;
    let ordenInicialDrag = '';

    // Sincroniza archivos y el orden visual en el formulario
    function sincronizarArchivosYOrden() {
        const grid = document.getElementById('grid-imagenes-producto');
        const fileInput = document.getElementById('input-archivos-imagenes');
        if (!grid) return;

        const allCards = Array.from(grid.querySelectorAll('.item-imagen'));

        if (fileInput) {
            try {
                const dt = new DataTransfer();
                allCards.forEach(card => {
                    if (card.dataset.nuevoId) {
                        const found = archivosLocales.find(a => a.id === card.dataset.nuevoId);
                        if (found && found.file) dt.items.add(found.file);
                    }
                });
                fileInput.files = dt.files;
            } catch (e) {
                console.warn('Error al actualizar DataTransfer del file input:', e);
            }
        }

        let archivoIdx = 0;
        let urlIdx = 0;

        allCards.forEach((card) => {
            let inputGaleria = card.querySelector('.input-orden-galeria');
            if (!inputGaleria) {
                inputGaleria = document.createElement('input');
                inputGaleria.type = 'hidden';
                inputGaleria.name = 'orden_galeria[]';
                inputGaleria.className = 'input-orden-galeria';
                card.appendChild(inputGaleria);
            }

            let inputOrden = card.querySelector('.input-orden-imagen');
            if (!inputOrden) {
                inputOrden = document.createElement('input');
                inputOrden.type = 'hidden';
                inputOrden.name = 'orden_imagenes[]';
                inputOrden.className = 'input-orden-imagen';
                card.appendChild(inputOrden);
            }

            if (card.dataset.dbId) {
                card.dataset.clave = `existente:${card.dataset.dbId}`;
                inputGaleria.value = card.dataset.clave;
                inputOrden.value = card.dataset.dbId;
            } else if (card.dataset.nuevoId) {
                card.dataset.clave = `archivo:${archivoIdx}`;
                inputGaleria.value = card.dataset.clave;
                inputOrden.value = card.dataset.nuevoId;
                archivoIdx++;
            } else if (card.dataset.esUrl === 'true') {
                card.dataset.clave = `url:${urlIdx}`;
                inputGaleria.value = card.dataset.clave;
                inputOrden.value = `url_${urlIdx}`;
                urlIdx++;
            }
        });

        const cardPortada = allCards.find(c => {
            const badge = c.querySelector('.badge-principal-container');
            return badge && !badge.classList.contains('hidden');
        }) || allCards[0];

        const inputPortadaClave = document.getElementById('input-imagen-portada-clave');
        const inputPrincipalId = document.getElementById('input-imagen-principal-id');

        if (cardPortada && cardPortada.dataset.clave) {
            if (inputPortadaClave) inputPortadaClave.value = cardPortada.dataset.clave;
            if (inputPrincipalId) inputPrincipalId.value = cardPortada.dataset.dbId || '';
        }
    }

    // Configura el arrastre estilo LEGO
    function inicializarDragAndDrop(card) {
        if (!card || card.dataset.dndInited === 'true') return;
        card.dataset.dndInited = 'true';
        card.setAttribute('draggable', 'true');

        card.addEventListener('dragstart', function(e) {
            if (e.target.closest('button, a, input')) {
                e.preventDefault();
                return;
            }

            tarjetaArrastrada = card;
            const grid = document.getElementById('grid-imagenes-producto');
            ordenInicialDrag = grid ? Array.from(grid.querySelectorAll('.item-imagen')).map(c => c.dataset.id || c.dataset.dbId || c.dataset.nuevoId).join(',') : '';

            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', card.dataset.dbId || card.dataset.nuevoId || card.dataset.id || '');

            setTimeout(() => {
                card.classList.add('opacity-40', 'scale-95', 'ring-2', 'ring-emerald-500', 'ring-dashed');
            }, 0);
        });

        card.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';

            if (!tarjetaArrastrada || tarjetaArrastrada === card) return;

            const grid = document.getElementById('grid-imagenes-producto');
            if (!grid) return;

            const rect = card.getBoundingClientRect();
            const midpointX = rect.left + rect.width / 2;

            if (e.clientX > midpointX) {
                if (card.nextElementSibling !== tarjetaArrastrada) {
                    grid.insertBefore(tarjetaArrastrada, card.nextElementSibling);
                }
            } else {
                if (card !== tarjetaArrastrada.nextElementSibling) {
                    grid.insertBefore(tarjetaArrastrada, card);
                }
            }
        });

        card.addEventListener('dragend', function() {
            card.classList.remove('opacity-40', 'scale-95', 'ring-2', 'ring-emerald-500', 'ring-dashed');

            const grid = document.getElementById('grid-imagenes-producto');
            if (grid) {
                const ordenFinal = Array.from(grid.querySelectorAll('.item-imagen')).map(c => c.dataset.id || c.dataset.dbId || c.dataset.nuevoId).join(',');
                if (ordenFinal !== ordenInicialDrag) {
                    sincronizarArchivosYOrden();
                    guardarNuevoOrden(true);
                }
            }

            tarjetaArrastrada = null;
            ordenInicialDrag = '';
        });
    }

    // Inicializa las tarjetas presentes en la página
    function inicializarTodasLasCards() {
        const grid = document.getElementById('grid-imagenes-producto');
        if (!grid) return;
        grid.querySelectorAll('.item-imagen').forEach(card => {
            inicializarDragAndDrop(card);
        });
        sincronizarArchivosYOrden();
    }

    // Carga de archivos locales con vista previa inmediata
    function handleSeleccionArchivos(input) {
        const files = Array.from(input.files || []);
        if (files.length === 0) return;

        const grid = document.getElementById('grid-imagenes-producto');
        const empty = document.getElementById('empty-state-galeria');
        if (empty) empty.remove();

        files.forEach((file) => {
            const fileUid = 'file_' + Date.now() + '_' + Math.random().toString(36).substr(2, 6);
            archivosLocales.push({ id: fileUid, file: file, name: file.name });

            const src = URL.createObjectURL(file);
            const esPrimera = grid.querySelectorAll('.item-imagen').length === 0;
            crearCardImagenHtml(src, file.name, esPrimera, false, fileUid);
        });

        actualizarContadorGaleria();
        sincronizarArchivosYOrden();
    }

    // Agrega imagen por URL o icono
    function agregarImagenPorUrl() {
        const input = document.getElementById('input-url-imagen-rapida');
        const val = input.value.trim();
        if (!val) {
            if (typeof window.mostrarToast === 'function') {
                window.mostrarToast('Por favor ingresa una URL de imagen o ícono válido.', 'warning');
            } else {
                alert('Por favor ingresa una URL de imagen válida o un ícono.');
            }
            return;
        }

        const grid = document.getElementById('grid-imagenes-producto');
        const empty = document.getElementById('empty-state-galeria');
        if (empty) empty.remove();

        const isFirst = grid.querySelectorAll('.item-imagen').length === 0;
        const nombreMostrar = val.length > 25 ? val.substring(0, 22) + '...' : val;
        crearCardImagenHtml(val, nombreMostrar, isFirst, true, null);
        actualizarContadorGaleria();
        sincronizarArchivosYOrden();
        input.value = '';
    }

    // Crea la tarjeta visual de una imagen
    function crearCardImagenHtml(rutaOrBase64, nombreArchivo, esPrincipal, esUrl, nuevoId) {
        const grid = document.getElementById('grid-imagenes-producto');
        const div = document.createElement('div');
        const tempId = nuevoId || ('temp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5));
        
        div.className = `relative group card-elevated rounded-2xl overflow-hidden ${esPrincipal ? 'border-2 border-emerald-500 shadow-md ring-2 ring-emerald-500/20' : 'border border-slate-200 dark:border-gray-700/90 hover:border-slate-300 dark:border-gray-700 shadow-2xs'} bg-white dark:bg-[#181a1b] flex flex-col item-imagen transition-all duration-200 cursor-grab active:cursor-grabbing select-none`;
        div.dataset.id = tempId;
        if (nuevoId) div.dataset.nuevoId = nuevoId;
        if (esUrl) div.dataset.esUrl = 'true';

        let contentPreview = '';
        if (rutaOrBase64.startsWith('blob:') || rutaOrBase64.startsWith('data:image') || rutaOrBase64.startsWith('http') || rutaOrBase64.startsWith('/storage') || rutaOrBase64.startsWith('storage/')) {
            contentPreview = `<img src="${rutaOrBase64}" alt="${nombreArchivo}" class="max-h-full max-w-full object-contain transition-transform group-hover:scale-105 pointer-events-none">`;
        } else if (rutaOrBase64.includes('<svg') || rutaOrBase64.includes('</svg>')) {
            contentPreview = `<div class="w-full h-full flex items-center justify-center svg-container">${rutaOrBase64}</div>`;
        } else {
            contentPreview = `<span class="material-symbols-outlined text-[42px] text-slate-600 dark:text-slate-400">${rutaOrBase64}</span>`;
        }

        let hiddenInput = '';
        if (esUrl) {
            const escapedValue = rutaOrBase64.replace(/"/g, '&quot;');
            hiddenInput = `<input type="hidden" name="imagenes_url[]" value="${escapedValue}">`;
        }

        div.innerHTML = `
            ${hiddenInput}
            <input type="hidden" name="orden_imagenes[]" value="${tempId}" class="input-orden-imagen">
            <input type="hidden" name="orden_galeria[]" value="" class="input-orden-galeria">

            <div class="badge-principal-container ${esPrincipal ? '' : 'hidden'}">
                <div class="absolute top-2 left-2 z-10 pointer-events-none">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-600 text-white shadow-sm">
                        <span class="material-symbols-outlined text-[11px]">star</span>
                        <span>Portada</span>
                    </span>
                </div>
            </div>

            <div class="absolute top-2 right-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1 bg-slate-900/85 p-1 rounded-xl backdrop-blur-xs shadow-md">
                <div class="drag-handle text-slate-400 hover:text-white p-0.5 cursor-grab active:cursor-grabbing" title="Arrastrar para ordenar">
                    <span class="material-symbols-outlined text-[14px]">drag_indicator</span>
                </div>
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

            <div class="h-32 w-full bg-slate-50 dark:bg-[#121415] flex items-center justify-center p-3 group-hover:bg-slate-100 dark:group-hover:bg-[#151718] transition-colors pointer-events-none">
                <div class="w-full h-full flex items-center justify-center text-slate-700 dark:text-slate-300">
                    ${contentPreview}
                </div>
            </div>

            <div class="p-1.5 border-t border-slate-100 dark:border-gray-700 bg-white dark:bg-[#181a1b] flex items-center justify-center text-[11px] card-footer-actions min-h-[28px]">
                <div class="estado-portada-container w-full text-center">
                    ${esPrincipal ? `
                        <span class="text-emerald-600 font-extrabold text-[11px] badge-texto-portada flex items-center justify-center gap-1">
                            <span class="material-symbols-outlined text-[13px]">check_circle</span>
                            <span>Portada</span>
                        </span>
                    ` : `
                        <button type="button" onclick="hacerImagenPrincipal(this)" class="w-full text-[11px] font-bold text-slate-500 dark:text-slate-400 hover:text-emerald-700 opacity-0 group-hover:opacity-100 transition-all duration-200 btn-hacer-portada py-0.5">
                            Hacer Portada
                        </button>
                    `}
                </div>
            </div>
        `;

        grid.appendChild(div);
        inicializarDragAndDrop(div);
    }

    // Marca una imagen como portada
    function hacerImagenPrincipal(btn) {
        const grid = document.getElementById('grid-imagenes-producto');
        const cardActual = btn.closest('.item-imagen');
        if (!grid || !cardActual) return;

        const productoId = grid.dataset.productoId;
        const dbId = cardActual.dataset.dbId;

        // Desmarcamos las demás tarjetas
        grid.querySelectorAll('.item-imagen').forEach(card => {
            card.classList.remove('border-2', 'border-emerald-500', 'shadow-md', 'ring-2', 'ring-emerald-500/20');
            card.classList.add('border', 'border-slate-200', 'dark:border-gray-700/90', 'shadow-2xs');

            const badgeContainer = card.querySelector('.badge-principal-container');
            if (badgeContainer) badgeContainer.classList.add('hidden');

            const estadoContainer = card.querySelector('.estado-portada-container');
            if (estadoContainer) {
                estadoContainer.innerHTML = `
                    <button type="button" onclick="hacerImagenPrincipal(this)" class="w-full text-[11px] font-bold text-slate-500 dark:text-slate-400 hover:text-emerald-700 opacity-0 group-hover:opacity-100 transition-all duration-200 btn-hacer-portada py-0.5">
                        Hacer Portada
                    </button>
                `;
            }
        });

        // Marcamos la tarjeta activa
        cardActual.classList.remove('border', 'border-slate-200', 'dark:border-gray-700/90', 'shadow-2xs');
        cardActual.classList.add('border-2', 'border-emerald-500', 'shadow-md', 'ring-2', 'ring-emerald-500/20');

        const badgeActual = cardActual.querySelector('.badge-principal-container');
        if (badgeActual) badgeActual.classList.remove('hidden');

        const estadoActual = cardActual.querySelector('.estado-portada-container');
        if (estadoActual) {
            estadoActual.innerHTML = `
                <span class="text-emerald-600 font-extrabold text-[11px] badge-texto-portada flex items-center justify-center gap-1">
                    <span class="material-symbols-outlined text-[13px]">check_circle</span>
                    <span>Portada</span>
                </span>
            `;
        }

        sincronizarArchivosYOrden();

        // Si ya existe en base de datos, guardamos por AJAX
        if (productoId && dbId) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch(`/admin/productos/${productoId}/imagenes/${dbId}/portada`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && typeof window.mostrarToast === 'function') {
                    window.mostrarToast(data.message || 'Imagen establecida como portada correctamente.', 'success');
                }
            })
            .catch(() => {
                if (typeof window.mostrarToast === 'function') {
                    window.mostrarToast('Error al conectar con el servidor para fijar portada.', 'error');
                }
            });
        } else {
            if (typeof window.mostrarToast === 'function') {
                window.mostrarToast('Imagen seleccionada como portada.', 'success');
            }
        }
    }

    // Guarda el orden de las imágenes
    function guardarNuevoOrden(notificar = true) {
        const grid = document.getElementById('grid-imagenes-producto');
        if (!grid) return;

        sincronizarArchivosYOrden();

        const cards = Array.from(grid.querySelectorAll('.item-imagen'));
        const productoId = grid.dataset.productoId;
        const ordenDbIds = cards.map(c => c.dataset.dbId).filter(Boolean);

        // Si todas las imágenes ya están en base de datos, guardamos por AJAX
        if (productoId && ordenDbIds.length > 0 && ordenDbIds.length === cards.length) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch(`/admin/productos/${productoId}/imagenes/reordenar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ orden: ordenDbIds })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && notificar && typeof window.mostrarToast === 'function') {
                    window.mostrarToast(data.message || 'Orden de imágenes actualizado correctamente.', 'success');
                }
            })
            .catch(() => {
                if (notificar && typeof window.mostrarToast === 'function') {
                    window.mostrarToast('Error al guardar el nuevo orden de imágenes.', 'error');
                }
            });
        } else if (notificar && cards.length > 1) {
            if (typeof window.mostrarToast === 'function') {
                window.mostrarToast('Orden de imágenes actualizado.', 'success');
            }
        }
    }

    // Elimina una imagen recién subida
    function eliminarCardImagen(btn) {
        const card = btn.closest('.item-imagen');
        if (!card) return;

        const nuevoId = card.dataset.nuevoId;
        if (nuevoId) {
            archivosLocales = archivosLocales.filter(a => a.id !== nuevoId);
        }

        const eraPortada = !card.querySelector('.badge-principal-container')?.classList.contains('hidden');
        card.remove();

        actualizarContadorGaleria();
        sincronizarArchivosYOrden();

        if (eraPortada) {
            const primerCard = document.querySelector('#grid-imagenes-producto .item-imagen');
            if (primerCard) {
                const btnPortada = primerCard.querySelector('.btn-hacer-portada');
                if (btnPortada) hacerImagenPrincipal(btnPortada);
            }
        }

        if (typeof window.mostrarToast === 'function') {
            window.mostrarToast('Imagen quitada de la galería.', 'info');
        }
    }

    // Elimina una imagen guardada en base de datos
    function eliminarCardImagenExistente(btn) {
        const card = btn.closest('.item-imagen');
        if (!card) return;

        const dbId = card.dataset.dbId;
        if (dbId) {
            const form = document.getElementById('form-producto');
            if (form) {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'imagenes_eliminar[]';
                hidden.value = dbId;
                form.appendChild(hidden);
            }
        }

        const eraPortada = !card.querySelector('.badge-principal-container')?.classList.contains('hidden');
        card.remove();

        actualizarContadorGaleria();
        sincronizarArchivosYOrden();

        if (eraPortada) {
            const primerCard = document.querySelector('#grid-imagenes-producto .item-imagen');
            if (primerCard) {
                const btnPortada = primerCard.querySelector('.btn-hacer-portada');
                if (btnPortada) hacerImagenPrincipal(btnPortada);
            }
        }

        if (typeof window.mostrarToast === 'function') {
            window.mostrarToast('Imagen marcada para eliminar. Guarda los cambios para confirmar.', 'info');
        }
    }

    // Mueve una imagen hacia la izquierda
    function moverImagenIzquierda(btn) {
        const card = btn.closest('.item-imagen');
        if (card && card.previousElementSibling && card.previousElementSibling.classList.contains('item-imagen')) {
            card.parentNode.insertBefore(card, card.previousElementSibling);
            sincronizarArchivosYOrden();
            guardarNuevoOrden(true);
        }
    }

    // Mueve una imagen hacia la derecha
    function moverImagenDerecha(btn) {
        const card = btn.closest('.item-imagen');
        if (card && card.nextElementSibling && card.nextElementSibling.classList.contains('item-imagen')) {
            card.parentNode.insertBefore(card.nextElementSibling, card);
            sincronizarArchivosYOrden();
            guardarNuevoOrden(true);
        }
    }

    // Actualiza el contador de imágenes
    function actualizarContadorGaleria() {
        const total = document.querySelectorAll('.item-imagen').length;
        const contador = document.getElementById('contador-imagenes');
        if (contador) contador.textContent = `${total} imágenes cargadas`;

        const grid = document.getElementById('grid-imagenes-producto');
        if (total === 0 && grid && !document.getElementById('empty-state-galeria')) {
            grid.innerHTML = `
                <div id="empty-state-galeria" class="col-span-full py-8 text-center text-slate-400 text-xs">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-transparent flex items-center justify-center mx-auto text-slate-400 mb-1.5">
                        <span class="material-symbols-outlined text-[24px]">add_photo_alternate</span>
                    </div>
                    <p class="font-medium text-slate-600 dark:text-slate-400">No hay imágenes cargadas para este producto.</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Sube archivos desde tu equipo o añade una URL arriba.</p>
                </div>
            `;
        }
    }

    // Inicialización al cargar la vista
    inicializarTodasLasCards();
    document.addEventListener('DOMContentLoaded', inicializarTodasLasCards);
    document.addEventListener('livewire:navigated', inicializarTodasLasCards);

    // Sincronización antes de guardar el formulario
    const formProducto = document.getElementById('form-producto');
    if (formProducto) {
        formProducto.addEventListener('submit', function() {
            sincronizarArchivosYOrden();
        });
    }
</script>
