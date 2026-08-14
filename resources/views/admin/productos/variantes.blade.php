<!-- Submódulo: Constructor Visual de Variantes Multiatributo -->
<div class="card-elevated p-5 sm:p-6 rounded-2xl space-y-6" id="contenedor-constructor-variantes">
    
    <!-- Header y Switch de Activación -->
    <div class="flex items-center justify-between p-4 rounded-xl bg-slate-50 border border-slate-200/80">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[22px]">tune</span>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-900">¿Este producto tiene múltiples variantes?</h3>
                <p class="text-xs text-slate-500">Activa si el artículo cuenta con diferentes opciones como color, almacenamiento, RAM, tamaño, etc.</p>
            </div>
        </div>

        <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" name="tiene_variantes" id="toggle-habilitar-variantes" value="1" class="sr-only peer" {{ ($esEdicion ?? false) && ($producto->variantes->count() > 0) ? 'checked' : '' }} onchange="toggleVariantesSection(this.checked)">
            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
        </label>
    </div>

    <!-- Cuerpo del Constructor (Visible cuando el switch está activo) -->
    <div id="seccion-editor-variantes" class="space-y-6" style="{{ ($esEdicion ?? false) && ($producto->variantes->count() > 0) ? 'display:block;' : 'display:none;' }}">
        
        <!-- Bloque 1: Definición de Atributos & Opciones -->
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-slate-500">list_alt</span>
                        <span>1. Atributos Principales y Opciones</span>
                    </h4>
                    <p class="text-[11px] text-slate-500 mt-0.5">Selecciona un atributo de la lista para agregarlo y haz clic en las opciones que aplican a este producto.</p>
                </div>

                <!-- Selector de Atributos Principales (Modal) -->
                <div class="flex items-center gap-2">
                    <button type="button" 
                            onclick="abrirModalAtributosPrincipales()" 
                            class="inline-flex items-center gap-2 px-3.5 py-2 bg-slate-900 hover:bg-slate-800 rounded-xl text-xs font-bold text-white shadow-xs transition-all cursor-pointer group shrink-0">
                        <span class="material-symbols-outlined text-[16px] text-emerald-400 group-hover:rotate-90 transition-transform duration-200">add_circle</span>
                        <span>Seleccionar Atributo Principal...</span>
                    </button>
                </div>
            </div>

            <!-- Contenedor dinámico de atributos añadidos -->
            <div id="lista-tipos-variantes" class="space-y-3">
                <!-- Se pobla dinámicamente mediante JS (Vacío por defecto al crear) -->
            </div>
        </div>

        <!-- Bloque 2: Matriz de Combinaciones Generadas (Optimizada & Paginada) -->
        <div class="space-y-3 pt-4 border-t border-slate-200/80">
            <input type="hidden" name="variantes_json" id="input-variantes-json">

            <div class="flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-emerald-600">grid_view</span>
                        <span>2. Matriz de Combinaciones (<span id="contador-combinaciones">0</span> variantes generadas)</span>
                    </h4>
                    <p class="text-[11px] text-slate-500 mt-0.5">Asigna precios, SKU y stock individual para cada combinación.</p>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <!-- Buscador en tiempo real dentro de la matriz -->
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-2.5 text-slate-400 text-[15px]">search</span>
                        <input type="text" 
                               id="input-buscar-matriz" 
                               oninput="filtrarMatrizCombinaciones(this.value)" 
                               placeholder="Filtrar variantes..." 
                               class="text-xs py-1 pl-8 pr-2.5 rounded-lg border border-slate-200 bg-slate-50 focus:bg-white focus:border-emerald-500 w-40">
                    </div>

                    <button type="button" 
                            onclick="regenerarMatrizCombinaciones()" 
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors shadow-2xs">
                        <span class="material-symbols-outlined text-[15px]">autorenew</span>
                        <span>Regenerar Matriz</span>
                    </button>
                </div>
            </div>

            <!-- Tabla de Combinaciones Paginada -->
            <div class="border border-slate-200 rounded-xl overflow-hidden shadow-2xs bg-white">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[720px]">
                        <thead>
                            <tr class="border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider bg-slate-50/80">
                                <th class="py-2.5 px-4">Combinación / Variante</th>
                                <th class="py-2.5 px-4 w-44">SKU Específico</th>
                                <th class="py-2.5 px-4 w-36 text-right">Precio ($ USD)</th>
                                <th class="py-2.5 px-4 w-28 text-center">Stock</th>
                                <th class="py-2.5 px-4 w-24 text-center">Estado</th>
                                <th class="py-2.5 px-4 w-12 text-center"></th>
                            </tr>
                        </thead>
                        <tbody id="tbody-matriz-variantes" class="text-xs divide-y divide-slate-100">
                            <!-- Filas de variantes paginadas -->
                        </tbody>
                    </table>
                </div>

                <!-- Footer de Paginación de la Matriz -->
                <div id="footer-paginacion-matriz" class="px-4 py-2 bg-slate-50/70 border-t border-slate-200/80 flex items-center justify-between text-xs text-slate-500 flex-wrap gap-2">
                    <div id="info-paginacion-matriz" class="font-medium text-[11px]">
                        Mostrando 0 - 0 de 0 variantes
                    </div>
                    <div class="flex items-center gap-1.5" id="controles-paginacion-matriz">
                        <!-- Botones Anterior / Siguiente dinámicos -->
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- MODAL DE SELECCIÓN DE ATRIBUTOS PRINCIPALES REUTILIZABLE -->
<x-modal-busqueda 
    id="modal-atributos-principales" 
    titulo="Seleccionar Atributo Principal" 
    subtitulo="Busca y elige una característica para las variantes del producto" 
    icono="tune" 
    placeholder="Buscar atributo (ej. Color, RAM, Capacidad)..."
    :porPagina="15"
>
    <x-slot:headerExtra>
        <div class="flex items-center gap-2">
            <input type="text" 
                   id="input-nuevo-atributo-modal" 
                   placeholder="+ Crear atributo personalizado..." 
                   onkeydown="if(event.key==='Enter'){event.preventDefault(); agregarAtributoPersonalizadoModal();}"
                   class="w-full py-1 px-3 text-xs bg-slate-50 border border-dashed border-slate-300 rounded-xl focus:bg-white focus:border-solid focus:border-emerald-500 text-slate-800">
            <button type="button" 
                    onclick="agregarAtributoPersonalizadoModal()" 
                    class="px-3 py-1 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shrink-0 transition-colors shadow-2xs cursor-pointer">
                Añadir
            </button>
        </div>
    </x-slot:headerExtra>
</x-modal-busqueda>

<!-- Datos y Lógica JavaScript del Constructor de Variantes -->
<script>
    // Estado del catálogo y variantes procesado desde el Controlador (MVC)
    const CATALOGO_ATRIBUTOS = {!! json_encode($catalogoAtributos ?? []) !!};
    let atributosActivos = {!! json_encode($atributosIniciales ?? []) !!};
    let variantesExistentes = {!! json_encode($variantesExistentesData ?? []) !!};

    document.addEventListener('DOMContentLoaded', function() {
        if (window.ModalBuscador) {
            window.ModalBuscador.init('modal-atributos-principales', {
                items: Object.keys(CATALOGO_ATRIBUTOS).map(n => ({ nombre: n })),
                porPagina: 15,
                emptyText: 'No se encontró el atributo',
                render: (item) => {
                    const nombre = item.nombre;
                    const cat = CATALOGO_ATRIBUTOS[nombre] || { opciones: [] };
                    const yaAgregado = atributosActivos.some(a => a.nombre.toLowerCase() === nombre.toLowerCase());
                    const numOpciones = cat.opciones ? cat.opciones.length : 0;

                    const div = document.createElement('div');
                    div.onclick = () => {
                        if (!yaAgregado) seleccionarAtributoDesdeModal(nombre);
                    };
                    div.className = `flex items-center justify-between p-3 rounded-xl border transition-all select-none ${
                        yaAgregado 
                        ? 'bg-slate-50 border-slate-200 opacity-60 cursor-not-allowed' 
                        : 'bg-white border-slate-200/90 hover:border-emerald-500 hover:bg-emerald-50/40 hover:shadow-2xs cursor-pointer'
                    }`;
                    div.innerHTML = `
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg ${yaAgregado ? 'bg-slate-200 text-slate-500' : 'bg-slate-900 text-white'} flex items-center justify-center font-bold text-xs shadow-2xs">
                                ${nombre.charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <span class="text-xs font-bold ${yaAgregado ? 'text-slate-500' : 'text-slate-900'} block leading-tight">${nombre}</span>
                                <span class="text-[10px] text-slate-400 font-medium">${numOpciones} opciones predefinidas</span>
                            </div>
                        </div>
                        <div>
                            ${yaAgregado ? `
                                <span class="text-[10px] font-bold text-slate-400 bg-slate-200 px-2 py-0.5 rounded-md">
                                    Ya agregado
                                </span>
                            ` : `
                                <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2.5 py-1 rounded-lg flex items-center gap-0.5 border border-emerald-200">
                                    <span class="material-symbols-outlined text-[12px]">add</span>
                                    <span>Seleccionar</span>
                                </span>
                            `}
                        </div>
                    `;
                    return div;
                }
            });
        }
        renderizarAtributosUI();
        regenerarMatrizCombinaciones();
    });

    function recalcularStockGlobal() {
        const checkbox = document.getElementById('toggle-habilitar-variantes');
        const stockInput = document.getElementById('stock');
        
        if (stockInput && checkbox) {
            if (checkbox.checked) {
                stockInput.readOnly = true;
                const totalStock = combinacionesMatrizState.reduce((sum, item) => sum + (parseInt(item.stock) || 0), 0);
                stockInput.value = totalStock;
                stockInput.classList.add('bg-slate-100', 'cursor-not-allowed', 'text-slate-500');
            } else {
                stockInput.readOnly = false;
                stockInput.classList.remove('bg-slate-100', 'cursor-not-allowed', 'text-slate-500');
            }
        }
    }

    function toggleVariantesSection(habilitado) {
        const sec = document.getElementById('seccion-editor-variantes');
        if (sec) {
            sec.style.display = habilitado ? 'block' : 'none';
        }
        if (!habilitado) {
            const inputJson = document.getElementById('input-variantes-json');
            if (inputJson) inputJson.value = '[]';
            recalcularStockGlobal();
        } else {
            renderizarAtributosUI();
            regenerarMatrizCombinaciones();
        }
    }

    function abrirModalAtributosPrincipales() {
        const inputNuevo = document.getElementById('input-nuevo-atributo-modal');
        if (inputNuevo) inputNuevo.value = '';
        if (window.ModalBuscador) window.ModalBuscador.abrir('modal-atributos-principales');
    }

    function cerrarModalAtributosPrincipales() {
        if (window.ModalBuscador) window.ModalBuscador.cerrar('modal-atributos-principales');
    }

    function seleccionarAtributoDesdeModal(nombreAtributo) {
        const nombre = nombreAtributo.trim();
        if (!nombre) return;

        const existe = atributosActivos.some(a => a.nombre.toLowerCase() === nombre.toLowerCase());
        if (existe) {
            alert(`El atributo "${nombre}" ya está agregado.`);
            return;
        }

        atributosActivos.push({
            nombre: nombre,
            seleccionadas: []
        });

        cerrarModalAtributosPrincipales();
        renderizarAtributosUI();
        regenerarMatrizCombinaciones();
    }

    function agregarAtributoPersonalizadoModal() {
        const input = document.getElementById('input-nuevo-atributo-modal');
        if (!input) return;
        const val = input.value.trim();
        if (!val) return;

        seleccionarAtributoDesdeModal(val);
        input.value = '';
    }

    function renderizarListaAtributosModal(filtro = '') {
        const contenedor = document.getElementById('lista-atributos-modal');
        if (!contenedor) return;

        contenedor.innerHTML = '';

        const listaNombres = Object.keys(CATALOGO_ATRIBUTOS);

        let filtrados = listaNombres;
        if (filtro) {
            filtrados = listaNombres.filter(n => n.toLowerCase().includes(filtro));
        }

        if (filtrados.length === 0) {
            contenedor.innerHTML = `
                <div class="py-6 text-center text-slate-400 text-xs">
                    <p>No se encontró el atributo "${filtro}".</p>
                    <p class="text-[11px] text-slate-400 mt-1">Puedes crearlo usando el campo "+ Crear atributo personalizado".</p>
                </div>
            `;
            return;
        }

        filtrados.forEach(nombre => {
            const cat = CATALOGO_ATRIBUTOS[nombre] || { opciones: [] };
            const yaAgregado = atributosActivos.some(a => a.nombre.toLowerCase() === nombre.toLowerCase());
            const numOpciones = cat.opciones ? cat.opciones.length : 0;

            const div = document.createElement('div');
            div.onclick = () => {
                if (!yaAgregado) seleccionarAtributoDesdeModal(nombre);
            };
            div.className = `flex items-center justify-between p-3 rounded-xl border transition-all select-none ${
                yaAgregado 
                ? 'bg-slate-50 border-slate-200 opacity-60 cursor-not-allowed' 
                : 'bg-white border-slate-200/90 hover:border-emerald-500 hover:bg-emerald-50/40 hover:shadow-2xs cursor-pointer'
            }`;
            div.innerHTML = `
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg ${yaAgregado ? 'bg-slate-200 text-slate-500' : 'bg-slate-900 text-white'} flex items-center justify-center font-bold text-xs shadow-2xs">
                        ${nombre.charAt(0).toUpperCase()}
                    </div>
                    <div>
                        <span class="text-xs font-bold ${yaAgregado ? 'text-slate-500' : 'text-slate-900'} block leading-tight">${nombre}</span>
                        <span class="text-[10px] text-slate-400 font-medium">${numOpciones} opciones predefinidas</span>
                    </div>
                </div>
                <div>
                    ${yaAgregado ? `
                        <span class="text-[10px] font-bold text-slate-400 bg-slate-200 px-2 py-0.5 rounded-md">
                            Ya agregado
                        </span>
                    ` : `
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2.5 py-1 rounded-lg flex items-center gap-0.5 border border-emerald-200">
                            <span class="material-symbols-outlined text-[12px]">add</span>
                            <span>Seleccionar</span>
                        </span>
                    `}
                </div>
            `;
            contenedor.appendChild(div);
        });
    }

    function renderizarAtributosUI() {
        const contenedor = document.getElementById('lista-tipos-variantes');
        if (!contenedor) return;

        contenedor.innerHTML = '';

        if (atributosActivos.length === 0) {
            contenedor.innerHTML = `
                <div class="p-6 text-center rounded-xl border border-dashed border-slate-200 text-slate-400 text-xs">
                    <span class="material-symbols-outlined text-[24px] text-slate-300 mb-1">tune</span>
                    <p class="font-medium text-slate-600">No hay atributos agregados aún.</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">Haz clic en <strong>"+ Seleccionar Atributo Principal"</strong> en el menú superior para empezar.</p>
                </div>
            `;
            return;
        }

        atributosActivos.forEach((attr, idx) => {
            const cat = CATALOGO_ATRIBUTOS[attr.nombre] || { opciones: [], hex: {} };
            const todasOpciones = Array.from(new Set([...(cat.opciones || []), ...(attr.seleccionadas || [])]));

            const div = document.createElement('div');
            div.className = 'p-4 rounded-xl border border-slate-200 bg-white shadow-2xs space-y-3';
            
            // Header del Atributo
            let headerHtml = `
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 flex-1">
                        <span class="material-symbols-outlined text-slate-400 text-[18px]">drag_indicator</span>
                        <span class="text-xs font-extrabold text-slate-900 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">
                            ${attr.nombre}
                        </span>
                        <span class="text-[11px] text-slate-400">(${attr.seleccionadas.length} opciones seleccionadas)</span>
                    </div>
                    <button type="button" onclick="eliminarAtributo(${idx})" class="text-slate-400 hover:text-rose-600 p-1 transition-colors cursor-pointer" title="Eliminar atributo">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>
            `;

            // Chips / Pills de Opciones (Selección inline)
            let chipsHtml = `<div class="flex items-center gap-2 flex-wrap pt-1">`;
            
            todasOpciones.forEach(opc => {
                const estaSeleccionada = attr.seleccionadas.includes(opc);
                const hex = cat.hex && cat.hex[opc] ? cat.hex[opc] : null;

                let colorCircle = '';
                if (hex) {
                    colorCircle = `<span class="w-3 h-3 rounded-full inline-block border border-slate-300 shadow-2xs" style="background-color: ${hex}"></span>`;
                }

                chipsHtml += `
                    <button type="button" 
                            onclick="toggleOpcion(${idx}, '${opc.replace(/'/g, "\\'")}')" 
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold transition-all border cursor-pointer ${
                                estaSeleccionada 
                                ? 'bg-slate-900 text-white border-slate-900 shadow-xs' 
                                : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'
                            }">
                        ${colorCircle}
                        <span>${opc}</span>
                        ${estaSeleccionada ? '<span class="material-symbols-outlined text-[13px]">check</span>' : ''}
                    </button>
                `;
            });

            // Input para nueva opción personalizada
            chipsHtml += `
                <div class="inline-flex items-center">
                    <input type="text" 
                           placeholder="+ Nueva opción..." 
                           onkeydown="handleNuevaOpcionInput(event, ${idx}, this)" 
                           class="text-xs py-1 px-2.5 rounded-lg border-dashed border-slate-300 focus:border-solid focus:border-emerald-500 focus:ring-emerald-500 w-32 bg-slate-50/50">
                </div>
            </div>`;

            div.innerHTML = headerHtml + chipsHtml;
            contenedor.appendChild(div);
        });
    }

    function toggleOpcion(attrIdx, opcion) {
        const attr = atributosActivos[attrIdx];
        if (!attr) return;

        const pos = attr.seleccionadas.indexOf(opcion);
        if (pos > -1) {
            attr.seleccionadas.splice(pos, 1);
        } else {
            attr.seleccionadas.push(opcion);
        }

        renderizarAtributosUI();
        regenerarMatrizCombinaciones();
    }

    function handleNuevaOpcionInput(e, attrIdx, input) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const val = input.value.trim();
            if (!val) return;

            const attr = atributosActivos[attrIdx];
            if (attr && !attr.seleccionadas.includes(val)) {
                attr.seleccionadas.push(val);
                input.value = '';
                renderizarAtributosUI();
                regenerarMatrizCombinaciones();
            }
        }
    }

    function eliminarAtributo(idx) {
        atributosActivos.splice(idx, 1);
        renderizarAtributosUI();
        regenerarMatrizCombinaciones();
    }

    // Estado Global de Combinaciones (Ligero y escalable para 10,000+ variantes)
    let combinacionesMatrizState = [];
    let paginaActualMatriz = 1;
    const itemsPorPaginaMatriz = 15;
    let filtroMatrizTexto = '';

    function generarProductoCartesiano(arrays) {
        return arrays.reduce((acc, curr) => {
            const res = [];
            acc.forEach(a => {
                curr.forEach(c => {
                    res.push(a.concat([c]));
                });
            });
            return res;
        }, [[]]);
    }

    function regenerarMatrizCombinaciones() {
        const tbody = document.getElementById('tbody-matriz-variantes');
        const contador = document.getElementById('contador-combinaciones');
        if (!tbody) return;

        const atributosConOpciones = atributosActivos.filter(a => a.seleccionadas.length > 0);

        if (atributosConOpciones.length === 0) {
            combinacionesMatrizState = [];
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                        Selecciona al menos un atributo y activa alguna de sus opciones para generar la matriz de variantes.
                    </td>
                </tr>
            `;
            if (contador) contador.textContent = '0';
            actualizarInfoPaginacionMatriz(0, 0, 0, 1);
            sincronizarJsonVariantes();
            return;
        }

        const opcionesAgrupadas = atributosConOpciones.map(a => a.seleccionadas.map(opc => ({
            atributo: a.nombre,
            valor: opc
        })));

        const productoCartesiano = generarProductoCartesiano(opcionesAgrupadas);
        if (contador) contador.textContent = productoCartesiano.length;

        const inputSkuEl = document.querySelector('input[name="sku"]');
        const baseSku = (inputSkuEl && inputSkuEl.value) ? inputSkuEl.value.trim() : 'PROD';
        
        const inputPrecioEl = document.querySelector('input[name="precio"]');
        const basePrecio = (inputPrecioEl && inputPrecioEl.value) ? inputPrecioEl.value : '0.00';

        combinacionesMatrizState = productoCartesiano.map((comb) => {
            const nombreCombinacion = comb.map(c => c.valor).join(' / ');
            const skuSugerido = `${baseSku}-${comb.map(c => c.valor.substring(0, 3).toUpperCase().replace(/[^A-Z0-9]/g, '')).join('-')}`;
            
            let existente = variantesExistentes.find(ve => {
                if (Object.keys(ve.atributos).length !== comb.length) return false;
                return comb.every(c => ve.atributos[c.atributo] === c.valor);
            });

            const attrsObj = {};
            comb.forEach(c => { attrsObj[c.atributo] = c.valor; });

            return {
                nombre: nombreCombinacion,
                sku: existente ? existente.sku : skuSugerido,
                precio: existente ? parseFloat(existente.precio).toFixed(2) : basePrecio,
                stock: existente ? existente.stock : 10,
                atributos: attrsObj
            };
        });

        paginaActualMatriz = 1;
        renderizarPaginaMatriz();
        sincronizarJsonVariantes();
    }

    function filtrarMatrizCombinaciones(texto) {
        filtroMatrizTexto = texto.trim().toLowerCase();
        paginaActualMatriz = 1;
        renderizarPaginaMatriz();
    }

    function renderizarPaginaMatriz() {
        const tbody = document.getElementById('tbody-matriz-variantes');
        if (!tbody) return;

        let itemsFiltrados = combinacionesMatrizState.map((item, indexOriginal) => ({ item, indexOriginal }));

        if (filtroMatrizTexto) {
            itemsFiltrados = itemsFiltrados.filter(({ item }) => 
                item.nombre.toLowerCase().includes(filtroMatrizTexto) || 
                item.sku.toLowerCase().includes(filtroMatrizTexto)
            );
        }

        const totalItems = itemsFiltrados.length;
        const totalPaginas = Math.ceil(totalItems / itemsPorPaginaMatriz) || 1;

        if (paginaActualMatriz > totalPaginas) paginaActualMatriz = totalPaginas;
        if (paginaActualMatriz < 1) paginaActualMatriz = 1;

        const inicio = (paginaActualMatriz - 1) * itemsPorPaginaMatriz;
        const fin = Math.min(inicio + itemsPorPaginaMatriz, totalItems);
        const paginaItems = itemsFiltrados.slice(inicio, fin);

        tbody.innerHTML = '';

        if (paginaItems.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="py-6 text-center text-slate-400 text-xs">
                        No se encontraron variantes que coincidan con "${filtroMatrizTexto}".
                    </td>
                </tr>
            `;
            actualizarInfoPaginacionMatriz(0, 0, totalItems, totalPaginas);
            return;
        }

        paginaItems.forEach(({ item, indexOriginal }) => {
            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50/70 transition-colors';
            tr.innerHTML = `
                <td class="py-2.5 px-4">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-slate-900">${item.nombre}</span>
                    </div>
                </td>
                <td class="py-2.5 px-4">
                    <input type="text" 
                           value="${item.sku}" 
                           oninput="actualizarCampoMatriz(${indexOriginal}, 'sku', this.value)"
                           class="text-xs font-mono py-1 px-2.5 rounded-lg border border-slate-200 w-full focus:ring-emerald-500 focus:border-emerald-500">
                </td>
                <td class="py-2.5 px-4 text-right">
                    <input type="number" 
                           step="0.01" 
                           value="${item.precio}" 
                           oninput="actualizarCampoMatriz(${indexOriginal}, 'precio', this.value)"
                           class="text-xs text-right py-1 px-2.5 rounded-lg border border-slate-200 w-full focus:ring-emerald-500 focus:border-emerald-500 font-bold text-slate-900">
                </td>
                <td class="py-2.5 px-4 text-center">
                    <input type="number" 
                           value="${item.stock}" 
                           oninput="actualizarCampoMatriz(${indexOriginal}, 'stock', this.value)"
                           class="text-xs text-center py-1 px-2.5 rounded-lg border border-slate-200 w-full focus:ring-emerald-500 focus:border-emerald-500 font-semibold">
                </td>
                <td class="py-2.5 px-4 text-center">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Activo
                    </span>
                </td>
                <td class="py-2.5 px-4 text-center">
                    <button type="button" onclick="eliminarVariantePorIndice(${indexOriginal})" class="text-slate-400 hover:text-rose-600 transition-colors cursor-pointer" title="Quitar combinación">
                        <span class="material-symbols-outlined text-[17px]">delete</span>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        actualizarInfoPaginacionMatriz(inicio + 1, fin, totalItems, totalPaginas);
    }

    function actualizarCampoMatriz(indexGlobal, campo, valor) {
        if (combinacionesMatrizState[indexGlobal]) {
            combinacionesMatrizState[indexGlobal][campo] = valor;
            sincronizarJsonVariantes();
        }
    }

    function eliminarVariantePorIndice(indexGlobal) {
        combinacionesMatrizState.splice(indexGlobal, 1);
        renderizarPaginaMatriz();
        sincronizarJsonVariantes();
        const contador = document.getElementById('contador-combinaciones');
        if (contador) contador.textContent = combinacionesMatrizState.length;
    }

    function cambiarPaginaMatriz(nuevaPagina) {
        paginaActualMatriz = nuevaPagina;
        renderizarPaginaMatriz();
    }

    function actualizarInfoPaginacionMatriz(desde = 0, hasta = 0, total = 0, paginasTotal = 1) {
        const info = document.getElementById('info-paginacion-matriz');
        const controles = document.getElementById('controles-paginacion-matriz');

        if (info) {
            info.textContent = total > 0 
                ? `Mostrando ${desde} - ${hasta} de ${total} variantes` 
                : 'Mostrando 0 variantes';
        }

        if (controles) {
            controles.innerHTML = '';
            if (paginasTotal > 1) {
                controles.innerHTML = `
                    <button type="button" 
                            onclick="cambiarPaginaMatriz(${paginaActualMatriz - 1})" 
                            ${paginaActualMatriz === 1 ? 'disabled' : ''} 
                            class="px-2.5 py-1 text-xs font-semibold rounded-lg border border-slate-200 bg-white hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">
                        Anterior
                    </button>
                    <span class="text-xs font-bold text-slate-700 px-2">Pág. ${paginaActualMatriz} de ${paginasTotal}</span>
                    <button type="button" 
                            onclick="cambiarPaginaMatriz(${paginaActualMatriz + 1})" 
                            ${paginaActualMatriz === paginasTotal ? 'disabled' : ''} 
                            class="px-2.5 py-1 text-xs font-semibold rounded-lg border border-slate-200 bg-white hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer">
                        Siguiente
                    </button>
                `;
            }
        }
    }

    function sincronizarJsonVariantes() {
        const inputJson = document.getElementById('input-variantes-json');
        if (inputJson) {
            inputJson.value = JSON.stringify(combinacionesMatrizState);
        }
        recalcularStockGlobal();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('form-producto');
        if (form) {
            form.addEventListener('submit', function() {
                sincronizarJsonVariantes();
            });
        }
    });
</script>
