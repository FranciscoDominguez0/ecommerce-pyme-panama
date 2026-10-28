@php
    $defaultGarantiaContacto = "Puedes devolver tu producto directamente a PayMe Panamá y gestionaremos cualquier problema de garantía directamente con el fabricante por ti. También puedes comunicarte con el fabricante directamente usando tu factura.";
    $defaultGarantiaFabricante = [
        'nombre' => 'Garantía Oficial del Fabricante',
        'duracion' => '1 Año',
        'contacto' => $defaultGarantiaContacto
    ];
    $defaultGarantiaServicio = [
        'nombre' => 'Garantía de Servicio PayMe',
        'duracion' => '30 Días',
        'contacto' => 'Atención y soporte técnico directo a través de los canales de atención de PayMe Panamá.'
    ];

    $esEdicion = isset($producto->id);

    // Valores físicos actuales
    $pesoActual = old('peso', $producto->peso ?? '');
    $largoActual = old('dimension_largo', $producto->dimension_largo ?? '');
    $anchoActual = old('dimension_ancho', $producto->dimension_ancho ?? '');
    $altoActual = old('dimension_alto', $producto->dimension_alto ?? '');

    // Comprobar si tiene valores físicos válidos (mayores a cero y no vacíos)
    $tieneFisicasValidas = ((float)$pesoActual > 0 || (float)$largoActual > 0 || (float)$anchoActual > 0 || (float)$altoActual > 0);

    $esServicioProd = $esServicio ?? ($producto->es_digital ?? false);

    // Por defecto en nuevo: habilitado para físicos, deshabilitado para servicios. En edición: habilitado solo si tiene datos reales (> 0)
    $habilitarFisicasInicial = $esEdicion ? $tieneFisicasValidas : (!$esServicioProd);

    // Garantía actual
    $garantiaDb = $producto->garantia_info ?? null;
    $garantiaActual = old('garantia_info', $garantiaDb);
    if (is_string($garantiaActual)) {
        $garantiaActual = json_decode($garantiaActual, true);
    }

    $tieneGarantiaValida = !empty($garantiaActual) && !empty($garantiaActual['nombre']);
    // Por defecto en nuevo: habilitado con valores por defecto. En edición: habilitado si tiene nombre de garantía
    $habilitarGarantiaInicial = $esEdicion ? $tieneGarantiaValida : true;

    $garantiaInicial = $tieneGarantiaValida ? $garantiaActual : ($esServicioProd ? $defaultGarantiaServicio : $defaultGarantiaFabricante);
@endphp

<!-- ── SECCIÓN: ESPECIFICACIONES & LOGÍSTICA ── -->
<div class="card-elevated p-5 sm:p-6 rounded-2xl space-y-4" x-data="especificacionesLogistica()">
    
    <div class="flex items-center justify-between border-b border-slate-100 dark:border-gray-700 pb-3">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-emerald-600 text-[20px]">dataset</span>
            <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Especificaciones, Fletes y Garantía</h2>
        </div>
        <button type="button" @click="abrirModal = true" class="btn-panama-outline text-xs px-3 py-1.5 flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">edit</span> Configurar Opciones
        </button>
    </div>

    <!-- Resumen visual en la tarjeta -->
    <div class="text-xs text-slate-500 space-y-1.5">
        <p x-show="especificaciones.length === 0 && (!habilitarFisicas || !peso) && (!habilitarGarantia || !garantia_info.nombre)">
            No se han configurado especificaciones técnicas, dimensiones ni garantía.
        </p>

        <!-- Resumen de especificaciones técnicas -->
        <p x-show="especificaciones.length > 0" class="text-emerald-700 dark:text-emerald-400 font-semibold flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[15px]">check_circle</span>
            <span><strong x-text="especificaciones.length"></strong> grupos de especificaciones técnicas.</span>
        </p>

        <!-- Resumen de especificaciones físicas -->
        <p x-show="habilitarFisicas && (peso || dimension_largo || dimension_ancho || dimension_alto)" class="text-emerald-700 dark:text-emerald-400 font-semibold flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[15px]">local_shipping</span>
            <span>Fletes y peso configurados (<span x-text="peso ? peso + ' kg' : 'Sin peso'"></span>).</span>
        </p>
        <p x-show="!habilitarFisicas" class="text-slate-400 dark:text-slate-500 flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[15px]">do_not_disturb_on</span>
            <span>Exento de fletes / Sin especificaciones físicas (ideal para servicios o digitales).</span>
        </p>

        <!-- Resumen de garantía -->
        <p x-show="habilitarGarantia && garantia_info.nombre" class="text-emerald-700 dark:text-emerald-400 font-semibold flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[15px]">verified_user</span>
            <span>Garantía: <span x-text="garantia_info.nombre"></span> (<span x-text="garantia_info.duracion || 'Sin duración'"></span>).</span>
        </p>
        <p x-show="!habilitarGarantia" class="text-slate-400 dark:text-slate-500 flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[15px]">shield_off</span>
            <span>Sin información de garantía asignada.</span>
        </p>
    </div>

    <!-- Inputs ocultos para JSON y Backend -->
    <input type="hidden" name="especificaciones" :value="JSON.stringify(especificaciones)">
    <input type="hidden" name="garantia_info" :value="habilitarGarantia && garantia_info.nombre ? JSON.stringify(garantia_info) : ''">
    <input type="hidden" name="habilitar_fisicas" :value="habilitarFisicas ? '1' : '0'">
    <input type="hidden" name="peso" :value="habilitarFisicas ? peso : ''">
    <input type="hidden" name="dimension_largo" :value="habilitarFisicas ? dimension_largo : ''">
    <input type="hidden" name="dimension_ancho" :value="habilitarFisicas ? dimension_ancho : ''">
    <input type="hidden" name="dimension_alto" :value="habilitarFisicas ? dimension_alto : ''">

    <!-- MODAL CON ALPINE -->
    <template x-teleport="body">
        <div x-show="abrirModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" x-cloak style="display: none;">
            <div class="bg-white dark:bg-[#181a1b] rounded-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col shadow-2xl border border-slate-200 dark:border-gray-700" @click.away="abrirModal = false">
                
                <!-- Encabezado -->
                <div class="p-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center bg-slate-50 dark:bg-gray-800">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-600 text-[22px]">tune</span>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Editar Especificaciones, Fletes y Garantía</h2>
                    </div>
                    <button type="button" @click="abrirModal = false" class="text-slate-400 hover:text-rose-500 transition-colors p-1 rounded-lg">
                        <span class="material-symbols-outlined text-[24px]">close</span>
                    </button>
                </div>

                <!-- Body (Scrollable) -->
                <div id="modal-especificaciones-body" class="p-6 overflow-y-auto space-y-8 flex-1 scroll-smooth">
                    
                    <!-- 1. ESPECIFICACIONES TÉCNICAS (ANIDADAS) -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-slate-800 dark:text-gray-100 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[18px] text-emerald-600">list_alt</span>
                                    <span>Especificaciones Técnicas</span>
                                </h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Grupos de características como Modelo, Conectividad, Pantalla, etc.</p>
                            </div>
                            <button type="button" @click="agregarGrupo()" class="btn-panama-outline text-xs px-2.5 py-1.5 flex items-center gap-1 cursor-pointer">
                                <span class="material-symbols-outlined text-[16px]">add_circle</span> Añadir Grupo
                            </button>
                        </div>
                        
                        <div class="space-y-4">
                            <template x-for="(grupoItem, indexGrupo) in especificaciones" :key="indexGrupo">
                                <div class="card-grupo-especificacion border border-slate-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-2xs transition-all">
                                    <!-- Cabecera del Grupo -->
                                    <div class="bg-slate-50 dark:bg-gray-800 p-3 border-b border-slate-200 dark:border-gray-700 flex items-center justify-between gap-3">
                                        <input type="text" x-model="grupoItem.grupo" placeholder="Nombre del Grupo (Ej: Modelo, Pantalla, Batería)" class="input-panama input-nombre-grupo w-full sm:w-1/2 text-xs py-1.5 px-3 rounded-lg border-slate-300 dark:border-gray-600 font-bold bg-white dark:bg-[#121415] text-slate-900 dark:text-white">
                                        <div class="flex items-center gap-2 shrink-0">
                                            <button type="button" @click="agregarAtributo(indexGrupo)" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 flex items-center gap-0.5">
                                                <span class="material-symbols-outlined text-[16px]">add</span> Añadir Atributo
                                            </button>
                                            <button type="button" @click="eliminarGrupo(indexGrupo)" class="text-rose-500 hover:bg-rose-100 dark:hover:bg-rose-950/40 p-1.5 rounded-md transition-colors" title="Eliminar Grupo">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Atributos del Grupo -->
                                    <div class="p-3 space-y-2 bg-white dark:bg-[#181a1b]">
                                        <template x-for="(attr, indexAttr) in grupoItem.atributos" :key="indexAttr">
                                            <div class="flex gap-2 items-center">
                                                <input type="text" x-model="attr.clave" placeholder="Atributo (Ej: Resolución, Interfaz)" class="input-panama input-atributo-clave w-1/2 text-xs py-2 px-3 rounded-lg border-slate-200 dark:border-gray-700 font-medium bg-slate-50 dark:bg-[#121415] text-slate-900 dark:text-white">
                                                <input type="text" x-model="attr.valor" placeholder="Valor (Ej: 1920x1080, USB-C)" class="input-panama w-1/2 text-xs py-2 px-3 rounded-lg border-slate-200 dark:border-gray-700 bg-white dark:bg-[#121415] text-slate-900 dark:text-white">
                                                <button type="button" @click="eliminarAtributo(indexGrupo, indexAttr)" class="text-rose-400 hover:text-rose-600 p-1 rounded-md transition-colors" title="Eliminar Atributo">
                                                    <span class="material-symbols-outlined text-[18px]">close</span>
                                                </button>
                                            </div>
                                        </template>
                                        <p x-show="!grupoItem.atributos || grupoItem.atributos.length === 0" class="text-[11px] text-slate-400 italic">No hay atributos en este grupo.</p>
                                    </div>
                                </div>
                            </template>
                            
                            <div x-show="especificaciones.length === 0" class="p-4 rounded-xl border border-dashed border-slate-200 dark:border-gray-700 text-center">
                                <p class="text-xs text-slate-400 dark:text-slate-500">No hay grupos de especificaciones técnicas agregados.</p>
                            </div>
                        </div>
                    </div>

                    <!-- 2. ESPECIFICACIONES FÍSICAS (LOGÍSTICA / FLETES) -->
                    <div class="space-y-3 pt-6 border-t border-slate-100 dark:border-gray-700">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-slate-800 dark:text-gray-100 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[18px] text-emerald-600">local_shipping</span>
                                    <span>Especificaciones Físicas (Logística / Fletes)</span>
                                </h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Datos usados para el cálculo automático de flete y peso volumétrico.</p>
                            </div>
                            
                            <!-- Botón para quitar o activar flete -->
                            <div>
                                <button type="button" x-show="habilitarFisicas" @click="quitarFisicas()" class="text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 px-2.5 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1 transition-colors">
                                    <span class="material-symbols-outlined text-[16px]">delete</span> Quitar Fletes / Eliminar
                                </button>
                                <button type="button" x-show="!habilitarFisicas" @click="activarFisicas()" class="btn-panama-outline text-xs px-2.5 py-1.5 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px]">add_circle</span> + Añadir Dimensiones y Fletes
                                </button>
                            </div>
                        </div>
                        
                        <!-- Bloque cuando está habilitado -->
                        <div x-show="habilitarFisicas" class="space-y-2">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 rounded-xl bg-slate-50 dark:bg-gray-800/40 border border-slate-200 dark:border-gray-700">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Peso (kg)</label>
                                    <input type="number" step="0.001" min="0" x-model="peso" placeholder="0.500" class="input-panama w-full text-xs py-2 px-3 rounded-lg border-slate-200 bg-white dark:bg-[#121415] text-slate-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Largo (cm)</label>
                                    <input type="number" step="0.01" min="0" x-model="dimension_largo" placeholder="10.00" class="input-panama w-full text-xs py-2 px-3 rounded-lg border-slate-200 bg-white dark:bg-[#121415] text-slate-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Ancho (cm)</label>
                                    <input type="number" step="0.01" min="0" x-model="dimension_ancho" placeholder="10.00" class="input-panama w-full text-xs py-2 px-3 rounded-lg border-slate-200 bg-white dark:bg-[#121415] text-slate-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Alto (cm)</label>
                                    <input type="number" step="0.01" min="0" x-model="dimension_alto" placeholder="5.00" class="input-panama w-full text-xs py-2 px-3 rounded-lg border-slate-200 bg-white dark:bg-[#121415] text-slate-900 dark:text-white">
                                </div>
                            </div>
                            <p class="text-[11px] text-slate-400">Si este producto es un servicio técnico, licencia o no requiere flete de paquetería, haz clic en «Quitar Fletes / Eliminar».</p>
                        </div>

                        <!-- Bloque cuando está deshabilitado / eliminado -->
                        <div x-show="!habilitarFisicas" class="p-4 rounded-xl border border-dashed border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800/30 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5 text-slate-500 dark:text-slate-400 text-xs">
                                <span class="material-symbols-outlined text-[20px] text-slate-400">no_crash</span>
                                <span>Sin especificaciones físicas ni cálculo de flete (recomendado para servicios, armados o software digital).</span>
                            </div>
                            <button type="button" @click="activarFisicas()" class="text-xs text-emerald-600 dark:text-emerald-400 font-bold hover:underline shrink-0">
                                Habilitar fletes
                            </button>
                        </div>
                    </div>

                    <!-- 3. GARANTÍA -->
                    <div class="space-y-3 pt-6 border-t border-slate-100 dark:border-gray-700">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <h3 class="text-sm font-bold text-slate-800 dark:text-gray-100 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[18px] text-emerald-600">verified_user</span>
                                    <span>Garantía</span>
                                </h3>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Términos de cobertura y contacto de soporte visibles para el comprador.</p>
                            </div>

                            <!-- Botón para quitar o activar garantía -->
                            <div>
                                <button type="button" x-show="habilitarGarantia" @click="quitarGarantia()" class="text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 px-2.5 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1 transition-colors">
                                    <span class="material-symbols-outlined text-[16px]">delete</span> Quitar Garantía / Eliminar
                                </button>
                                <button type="button" x-show="!habilitarGarantia" @click="activarGarantia('fabricante')" class="btn-panama-outline text-xs px-2.5 py-1.5 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[16px]">add_circle</span> + Añadir Garantía
                                </button>
                            </div>
                        </div>

                        <!-- Bloque cuando está habilitada -->
                        <div x-show="habilitarGarantia" class="space-y-3">
                            <!-- Presets rápidos para rellenar en 1 clic -->
                            <div class="flex flex-wrap items-center gap-2 pt-1 pb-1">
                                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Cargar Plantilla:</span>
                                <button type="button" @click="cargarPresetGarantia('fabricante')" class="px-2.5 py-1 rounded-lg text-[11px] font-medium bg-slate-100 hover:bg-slate-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-slate-700 dark:text-slate-300 transition-colors">
                                    Fabricante (1 Año)
                                </button>
                                <button type="button" @click="cargarPresetGarantia('servicio')" class="px-2.5 py-1 rounded-lg text-[11px] font-medium bg-slate-100 hover:bg-slate-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-slate-700 dark:text-slate-300 transition-colors">
                                    Servicio PayMe (30 Días)
                                </button>
                                <button type="button" @click="cargarPresetGarantia('tienda')" class="px-2.5 py-1 rounded-lg text-[11px] font-medium bg-slate-100 hover:bg-slate-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-slate-700 dark:text-slate-300 transition-colors">
                                    Garantía Tienda (3 Meses)
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 rounded-xl bg-slate-50 dark:bg-gray-800/40 border border-slate-200 dark:border-gray-700">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Nombre de la Garantía</label>
                                    <input type="text" x-model="garantia_info.nombre" placeholder="Ej: Garantía Oficial del Fabricante" class="input-panama w-full text-xs py-2 px-3 rounded-lg border-slate-200 bg-white dark:bg-[#121415] text-slate-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Duración</label>
                                    <input type="text" x-model="garantia_info.duracion" placeholder="Ej: 1 Año, 30 Días" class="input-panama w-full text-xs py-2 px-3 rounded-lg border-slate-200 bg-white dark:bg-[#121415] text-slate-900 dark:text-white">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">¿Con quién me comunico?</label>
                                    <textarea x-model="garantia_info.contacto" rows="2" placeholder="Describe los canales de devolución, soporte o fabricante..." class="input-panama w-full text-xs py-2 px-3 rounded-lg border-slate-200 bg-white dark:bg-[#121415] text-slate-900 dark:text-white"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Bloque cuando está deshabilitada / eliminada -->
                        <div x-show="!habilitarGarantia" class="p-4 rounded-xl border border-dashed border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800/30 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5 text-slate-500 dark:text-slate-400 text-xs">
                                <span class="material-symbols-outlined text-[20px] text-slate-400">shield_off</span>
                                <span>Sin información de garantía (el producto no mostrará la sección de garantía en la tienda).</span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <button type="button" @click="activarGarantia('fabricante')" class="text-xs text-emerald-600 dark:text-emerald-400 font-bold hover:underline">
                                    + Añadir Garantía
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Pie de página -->
                <div class="p-4 border-t border-slate-200 dark:border-gray-700 flex justify-end gap-2 bg-slate-50 dark:bg-gray-800">
                    <button type="button" @click="abrirModal = false" class="px-4 py-2 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-gray-700 font-semibold rounded-xl text-sm transition-colors cursor-pointer">
                        Cancelar
                    </button>
                    <button type="button" 
                            @click="guardarYCerrar()" 
                            :disabled="guardando"
                            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold rounded-xl text-sm transition-all shadow-sm flex items-center gap-2 cursor-pointer disabled:opacity-50">
                        <span x-show="guardando" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span class="material-symbols-outlined text-[18px]" x-show="!guardando">check</span>
                        <span x-text="guardando ? 'Guardando...' : 'Guardar y Cerrar'">Guardar y Cerrar</span>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
    function registrarEspecificacionesLogistica() {
        if (typeof Alpine !== 'undefined' && typeof Alpine.data === 'function') {
            Alpine.data('especificacionesLogistica', () => ({
                abrirModal: false,
                guardando: false,
                especificaciones: [],
                
                // Estado de fletes / dimensiones físicas
                habilitarFisicas: {{ $habilitarFisicasInicial ? 'true' : 'false' }},
                peso: '{{ $pesoActual }}',
                dimension_largo: '{{ $largoActual }}',
                dimension_ancho: '{{ $anchoActual }}',
                dimension_alto: '{{ $altoActual }}',

            // Estado de garantía
            habilitarGarantia: {{ $habilitarGarantiaInicial ? 'true' : 'false' }},
            garantia_info: @json($garantiaInicial),

            defaultContacto: @json($defaultGarantiaContacto),

            init() {
                let specsInit = @json(old('especificaciones', $producto->especificaciones ?? []));
                
                // Normalizar especificaciones según el formato recibido
                if (Array.isArray(specsInit) && specsInit.length > 0) {
                    if (specsInit[0].hasOwnProperty('clave') && !specsInit[0].hasOwnProperty('atributos')) {
                        let groups = {};
                        specsInit.forEach(item => {
                            let g = item.grupo || 'General';
                            if (!groups[g]) groups[g] = [];
                            groups[g].push({ clave: item.clave, valor: item.valor });
                        });
                        
                        this.especificaciones = Object.keys(groups).map(g => ({
                            grupo: g,
                            atributos: groups[g]
                        }));
                    } else if (specsInit[0].hasOwnProperty('grupo') && specsInit[0].hasOwnProperty('atributos')) {
                        this.especificaciones = specsInit;
                    }
                } else if (specsInit && typeof specsInit === 'object' && Object.keys(specsInit).length > 0) {
                    let attrs = Object.entries(specsInit).map(([k, v]) => ({
                        clave: k,
                        valor: typeof v === 'object' ? JSON.stringify(v) : String(v)
                    }));
                    this.especificaciones = [{
                        grupo: 'Especificaciones Técnicas',
                        atributos: attrs
                    }];
                }

                if (!this.garantia_info || typeof this.garantia_info !== 'object') {
                    this.garantia_info = {nombre: '', duracion: '', contacto: ''};
                }
            },

            // Acciones para grupos y atributos
            agregarGrupo() {
                this.especificaciones.push({ grupo: '', atributos: [{ clave: '', valor: '' }] });
                this.$nextTick(() => {
                    const grupos = document.querySelectorAll('.card-grupo-especificacion');
                    const ultimoGrupo = grupos[grupos.length - 1];
                    if (ultimoGrupo) {
                        ultimoGrupo.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        const inputNombre = ultimoGrupo.querySelector('.input-nombre-grupo');
                        if (inputNombre) {
                            setTimeout(() => inputNombre.focus(), 200);
                        }
                    } else {
                        const body = document.getElementById('modal-especificaciones-body');
                        if (body) {
                            body.scrollTo({ top: body.scrollHeight, behavior: 'smooth' });
                        }
                    }
                });
            },
            eliminarGrupo(index) {
                this.especificaciones.splice(index, 1);
            },
            agregarAtributo(indexGrupo) {
                if (!this.especificaciones[indexGrupo].atributos) {
                    this.especificaciones[indexGrupo].atributos = [];
                }
                this.especificaciones[indexGrupo].atributos.push({ clave: '', valor: '' });
                this.$nextTick(() => {
                    const grupos = document.querySelectorAll('.card-grupo-especificacion');
                    const grupoTarget = grupos[indexGrupo];
                    if (grupoTarget) {
                        const inputs = grupoTarget.querySelectorAll('.input-atributo-clave');
                        const ultimoInput = inputs[inputs.length - 1];
                        if (ultimoInput) {
                            ultimoInput.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                            setTimeout(() => ultimoInput.focus(), 150);
                        }
                    }
                });
            },
            eliminarAtributo(indexGrupo, indexAttr) {
                this.especificaciones[indexGrupo].atributos.splice(indexAttr, 1);
            },

            // Acciones para Fletes / Dimensiones
            quitarFisicas() {
                this.habilitarFisicas = false;
                this.peso = '';
                this.dimension_largo = '';
                this.dimension_ancho = '';
                this.dimension_alto = '';
            },
            activarFisicas() {
                this.habilitarFisicas = true;
            },

            // Acciones para Garantía
            quitarGarantia() {
                this.habilitarGarantia = false;
                this.garantia_info = { nombre: '', duracion: '', contacto: '' };
            },
            activarGarantia(tipo = 'fabricante') {
                this.habilitarGarantia = true;
                this.cargarPresetGarantia(tipo);
            },
            cargarPresetGarantia(tipo) {
                this.habilitarGarantia = true;
                if (tipo === 'servicio') {
                    this.garantia_info = {
                        nombre: 'Garantía de Servicio PayMe',
                        duracion: '30 Días',
                        contacto: 'Atención y soporte técnico directo a través de los canales de atención de PayMe Panamá.'
                    };
                } else if (tipo === 'tienda') {
                    this.garantia_info = {
                        nombre: 'Garantía Estándar PayMe',
                        duracion: '3 Meses',
                        contacto: 'Cobertura directa en sucursal PayMe Panamá presentando tu factura de compra.'
                    };
                } else {
                    this.garantia_info = {
                        nombre: 'Garantía Oficial del Fabricante',
                        duracion: '1 Año',
                        contacto: this.defaultContacto
                    };
                }
            },

            // Guardar cambios directamente en el producto y cerrar el modal con toast de éxito
            guardarYCerrar() {
                const productoId = {{ ($esEdicion ?? false) && isset($producto->id) ? (int)$producto->id : 'null' }};

                if (productoId) {
                    this.guardando = true;
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    fetch(`/admin/productos/${productoId}/especificaciones`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            especificaciones: this.especificaciones,
                            habilitar_fisicas: this.habilitarFisicas ? '1' : '0',
                            peso: this.habilitarFisicas ? this.peso : '',
                            dimension_largo: this.habilitarFisicas ? this.dimension_largo : '',
                            dimension_ancho: this.habilitarFisicas ? this.dimension_ancho : '',
                            dimension_alto: this.habilitarFisicas ? this.dimension_alto : '',
                            garantia_info: this.habilitarGarantia && this.garantia_info && this.garantia_info.nombre ? this.garantia_info : null
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.guardando = false;
                        if (data.success) {
                            this.abrirModal = false;
                            if (typeof window.mostrarToast === 'function') {
                                window.mostrarToast(data.message || 'Especificaciones, fletes y garantía guardados correctamente.', 'success');
                            }
                        } else {
                            if (typeof window.mostrarToast === 'function') {
                                window.mostrarToast(data.message || 'Error al guardar especificaciones.', 'error');
                            }
                        }
                    })
                    .catch(err => {
                        this.guardando = false;
                        if (typeof window.mostrarToast === 'function') {
                            window.mostrarToast('Error de conexión al guardar especificaciones.', 'error');
                        }
                    });
                } else {
                    this.abrirModal = false;
                    if (typeof window.mostrarToast === 'function') {
                        window.mostrarToast('Especificaciones configuradas para el nuevo artículo.', 'success');
                    }
                }
            }
        }));
    }
}

document.addEventListener('alpine:init', registrarEspecificacionesLogistica);
if (typeof Alpine !== 'undefined') {
    registrarEspecificacionesLogistica();
}
</script>
