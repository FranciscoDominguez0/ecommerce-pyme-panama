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

                <!-- Selector de Atributos Principales -->
                <div class="flex items-center gap-2">
                    <select id="selector-atributos-principales" 
                            class="text-xs py-1.5 px-3 rounded-xl border-slate-200 bg-slate-50 font-medium text-slate-700 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">-- Seleccionar Atributo Principal --</option>
                        <option value="Color">Color</option>
                        <option value="Capacidad de almacenamiento">Capacidad de almacenamiento</option>
                        <option value="Memoria RAM">Memoria RAM</option>
                        <option value="Tamaño">Tamaño</option>
                        <option value="Longitud">Longitud</option>
                        <option value="Tipo de conexión">Tipo de conexión</option>
                        <option value="Potencia">Potencia</option>
                        <option value="Frecuencia">Frecuencia</option>
                        <option value="Resolución">Resolución</option>
                        <option value="Tamaño de pantalla">Tamaño de pantalla</option>
                        <option value="Procesador">Procesador</option>
                        <option value="Tarjeta gráfica">Tarjeta gráfica</option>
                        <option value="Sistema operativo">Sistema operativo</option>
                        <option value="Distribución del teclado">Distribución del teclado</option>
                        <option value="Tipo de switch">Tipo de switch</option>
                        <option value="Voltaje">Voltaje</option>
                        <option value="Compatibilidad">Compatibilidad</option>
                        <option value="Material">Material</option>
                        <option value="Garantía">Garantía</option>
                    </select>

                    <button type="button" 
                            onclick="agregarAtributoDesdeSelector()" 
                            class="inline-flex items-center gap-1 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 px-3.5 py-1.5 rounded-xl transition-all shadow-xs shrink-0">
                        <span class="material-symbols-outlined text-[16px]">add</span>
                        <span>Agregar</span>
                    </button>
                </div>
            </div>

            <!-- Contenedor dinámico de atributos añadidos -->
            <div id="lista-tipos-variantes" class="space-y-3">
                <!-- Se pobla dinámicamente mediante JS (Vacío por defecto al crear) -->
            </div>
        </div>

        <!-- Bloque 2: Matriz de Combinaciones Generadas -->
        <div class="space-y-3 pt-4 border-t border-slate-200/80">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-emerald-600">grid_view</span>
                        <span>2. Matriz de Combinaciones (<span id="contador-combinaciones">0</span> variantes generadas)</span>
                    </h4>
                    <p class="text-[11px] text-slate-500 mt-0.5">Asigna precios, SKU y stock individual para cada combinación.</p>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" 
                            onclick="regenerarMatrizCombinaciones()" 
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors shadow-2xs">
                        <span class="material-symbols-outlined text-[15px]">autorenew</span>
                        <span>Regenerar Matriz</span>
                    </button>
                </div>
            </div>

            <!-- Tabla de Combinaciones -->
            <div class="border border-slate-200 rounded-xl overflow-hidden shadow-2xs">
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
                            <!-- Filas de variantes generadas en tiempo real -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Datos y Lógica JavaScript del Constructor de Variantes -->
<script>
    // Catálogo Oficial de los 19 Atributos Principales con todas sus opciones y códigos HEX
    const CATALOGO_ATRIBUTOS = {
        'Color': {
            opciones: ['Negro', 'Blanco', 'Plata', 'Gris', 'Azul', 'Rojo', 'Verde', 'Dorado', 'Morado', 'Rosa'],
            hex: {
                'Negro': '#0F172A',
                'Blanco': '#F8FAFC',
                'Plata': '#E2E8F0',
                'Gris': '#64748B',
                'Azul': '#2563EB',
                'Rojo': '#DC2626',
                'Verde': '#16A34A',
                'Dorado': '#D97706',
                'Morado': '#7C3AED',
                'Rosa': '#DB2777'
            }
        },
        'Capacidad de almacenamiento': {
            opciones: ['64 GB', '128 GB', '256 GB', '512 GB', '1 TB', '2 TB', '4 TB']
        },
        'Memoria RAM': {
            opciones: ['4 GB', '8 GB', '16 GB', '32 GB', '64 GB', '128 GB']
        },
        'Tamaño': {
            opciones: ['XS', 'S', 'M', 'L', 'XL']
        },
        'Longitud': {
            opciones: ['0.5 m', '1 m', '2 m', '3 m', '5 m', '10 m']
        },
        'Tipo de conexión': {
            opciones: ['USB-A', 'USB-C', 'Micro USB', 'Lightning', 'HDMI', 'DisplayPort', 'VGA', 'DVI', 'RJ45']
        },
        'Potencia': {
            opciones: ['18 W', '20 W', '25 W', '45 W', '65 W', '100 W', '120 W']
        },
        'Frecuencia': {
            opciones: ['60 Hz', '75 Hz', '120 Hz', '144 Hz', '165 Hz', '240 Hz', '360 Hz']
        },
        'Resolución': {
            opciones: ['HD', 'Full HD', '2K', 'QHD', '4K', '8K']
        },
        'Tamaño de pantalla': {
            opciones: ['13"', '14"', '15.6"', '16"', '17.3"', '24"', '27"', '32"']
        },
        'Procesador': {
            opciones: ['Intel Core i3', 'Intel Core i5', 'Intel Core i7', 'Intel Core i9', 'Ryzen 3', 'Ryzen 5', 'Ryzen 7', 'Ryzen 9', 'Apple M1', 'Apple M2', 'Apple M3']
        },
        'Tarjeta gráfica': {
            opciones: ['Integrada', 'RTX 3050', 'RTX 3060', 'RTX 4060', 'RTX 4070', 'RTX 4080', 'RTX 4090']
        },
        'Sistema operativo': {
            opciones: ['Windows 11', 'Windows 10', 'Linux', 'macOS', 'FreeDOS', 'Android', 'iOS']
        },
        'Distribución del teclado': {
            opciones: ['Español', 'Inglés', 'Inglés US', 'Mecánico', 'Membrana']
        },
        'Tipo de switch': {
            opciones: ['Red', 'Blue', 'Brown', 'Black', 'Silver']
        },
        'Voltaje': {
            opciones: ['110 V', '220 V', '110-220 V']
        },
        'Compatibilidad': {
            opciones: ['iPhone', 'Android', 'Windows', 'macOS', 'Linux', 'PlayStation', 'Xbox', 'Nintendo Switch']
        },
        'Material': {
            opciones: ['Plástico', 'Aluminio', 'Acero', 'Silicona', 'Vidrio', 'Cuero']
        },
        'Garantía': {
            opciones: ['3 meses', '6 meses', '1 año', '2 años', '3 años']
        }
    };

    @php
        $atributosIniciales = [];
        $variantesExistentesData = [];
        if (isset($esEdicion) && $esEdicion && $producto->variantes->count() > 0) {
            $map = [];
            foreach ($producto->variantes as $variante) {
                $attrs = [];
                foreach ($variante->opciones as $opcion) {
                    $tipoNombre = $opcion->tipo->nombre;
                    $attrs[$tipoNombre] = $opcion->valor;
                    
                    if (!isset($map[$tipoNombre])) {
                        $map[$tipoNombre] = [];
                    }
                    if (!in_array($opcion->valor, $map[$tipoNombre])) {
                        $map[$tipoNombre][] = $opcion->valor;
                    }
                }
                $variantesExistentesData[] = [
                    'sku' => $variante->sku,
                    'precio' => $variante->precio,
                    'stock' => $variante->stock,
                    'atributos' => $attrs
                ];
            }
            foreach ($map as $nombre => $seleccionadas) {
                $atributosIniciales[] = [
                    'nombre' => $nombre,
                    'seleccionadas' => $seleccionadas
                ];
            }
        }
    @endphp

    // Estado inicial de la UI de variantes
    let atributosActivos = {!! json_encode($atributosIniciales) !!};
    let variantesExistentes = {!! json_encode($variantesExistentesData) !!};

    document.addEventListener('DOMContentLoaded', function() {
        renderizarAtributosUI();
        regenerarMatrizCombinaciones();
    });

    function toggleVariantesSection(habilitado) {
        const sec = document.getElementById('seccion-editor-variantes');
        if (sec) {
            sec.style.display = habilitado ? 'block' : 'none';
        }
    }

    function agregarAtributoDesdeSelector() {
        const sel = document.getElementById('selector-atributos-principales');
        const nombre = sel.value;
        if (!nombre) {
            alert('Por favor selecciona un atributo de la lista.');
            return;
        }

        // Evitar duplicados
        const existe = atributosActivos.some(a => a.nombre.toLowerCase() === nombre.toLowerCase());
        if (existe) {
            alert(`El atributo "${nombre}" ya está agregado.`);
            return;
        }

        // Se agrega con 0 opciones seleccionadas por defecto para que el usuario elija exactamente las que quiere
        atributosActivos.push({
            nombre: nombre,
            seleccionadas: []
        });

        sel.value = '';
        renderizarAtributosUI();
        regenerarMatrizCombinaciones();
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
                    <p class="text-[11px] text-slate-400 mt-0.5">Selecciona un Atributo Principal en el menú superior para empezar a configurar las variantes.</p>
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
                    <button type="button" onclick="eliminarAtributo(${idx})" class="text-slate-400 hover:text-rose-600 p-1 transition-colors" title="Eliminar atributo">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>
            `;

            // Chips / Pills de Opciones
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
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold transition-all border ${
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
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                        Selecciona al menos un atributo y activa alguna de sus opciones para generar la matriz de variantes.
                    </td>
                </tr>
            `;
            if (contador) contador.textContent = '0';
            return;
        }

        const opcionesAgrupadas = atributosConOpciones.map(a => a.seleccionadas.map(opc => ({
            atributo: a.nombre,
            valor: opc
        })));

        const combinaciones = generarProductoCartesiano(opcionesAgrupadas);
        if (contador) contador.textContent = combinaciones.length;

        const inputSkuEl = document.querySelector('input[name="sku"]');
        const baseSku = (inputSkuEl && inputSkuEl.value) ? inputSkuEl.value.trim() : 'PROD';
        
        const inputPrecioEl = document.querySelector('input[name="precio"]');
        const basePrecio = (inputPrecioEl && inputPrecioEl.value) ? inputPrecioEl.value : '0.00';

        tbody.innerHTML = '';
        combinaciones.forEach((comb, idx) => {
            const nombreCombinacion = comb.map(c => c.valor).join(' / ');
            const skuSugerido = `${baseSku}-${comb.map(c => c.valor.substring(0, 3).toUpperCase().replace(/[^A-Z0-9]/g, '')).join('-')}`;
            
            // Buscar si esta combinación ya existe para preservar sus datos
            let existente = variantesExistentes.find(ve => {
                if (Object.keys(ve.atributos).length !== comb.length) return false;
                return comb.every(c => ve.atributos[c.atributo] === c.valor);
            });

            const skuVal = existente ? existente.sku : skuSugerido;
            const precioVal = existente ? parseFloat(existente.precio).toFixed(2) : basePrecio;
            const stockVal = existente ? existente.stock : 10;

            const tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50/70 transition-colors';
            tr.innerHTML = `
                <td class="py-2.5 px-4">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-slate-900">${nombreCombinacion}</span>
                    </div>
                </td>
                <td class="py-2.5 px-4">
                    <input type="text" name="variantes[${idx}][sku]" value="${skuVal}" class="text-xs font-mono py-1 px-2.5 rounded-lg border border-slate-200 w-full focus:ring-emerald-500 focus:border-emerald-500">
                </td>
                <td class="py-2.5 px-4 text-right">
                    <input type="number" step="0.01" name="variantes[${idx}][precio]" value="${precioVal}" class="text-xs text-right py-1 px-2.5 rounded-lg border border-slate-200 w-full focus:ring-emerald-500 focus:border-emerald-500 font-bold text-slate-900">
                </td>
                <td class="py-2.5 px-4 text-center">
                    <input type="number" name="variantes[${idx}][stock]" value="${stockVal}" class="text-xs text-center py-1 px-2.5 rounded-lg border border-slate-200 w-full focus:ring-emerald-500 focus:border-emerald-500 font-semibold">
                </td>
                <td class="py-2.5 px-4 text-center">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Activo
                    </span>
                </td>
                <td class="py-2.5 px-4 text-center">
                    <button type="button" onclick="this.closest('tr').remove()" class="text-slate-400 hover:text-rose-600 transition-colors" title="Quitar combinación">
                        <span class="material-symbols-outlined text-[17px]">delete</span>
                    </button>
                    <input type="hidden" name="variantes[${idx}][nombre]" value="${nombreCombinacion}">
                    ${comb.map(c => `<input type="hidden" name="variantes[${idx}][atributos][${c.atributo}]" value="${c.valor}">`).join('')}
                </td>
            `;
            tbody.appendChild(tr);
        });
    }
</script>
