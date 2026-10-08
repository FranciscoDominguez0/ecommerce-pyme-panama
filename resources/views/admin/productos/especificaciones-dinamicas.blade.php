@php
    $defaultGarantia = ['nombre' => '', 'duracion' => '', 'contacto' => ''];
@endphp
<!-- ── SECCIÓN: ESPECIFICACIONES & LOGÍSTICA ── -->
<div class="card-elevated p-5 sm:p-6 rounded-2xl space-y-4" x-data="especificacionesLogistica()">
    
    <div class="flex items-center justify-between border-b border-slate-100 dark:border-gray-700 pb-3">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-emerald-600 text-[20px]">dataset</span>
            <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Especificaciones, Fletes y Garantía</h2>
        </div>
        <button type="button" @click="abrirModal = true" class="btn-panama-outline text-xs px-3 py-1.5 flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">edit</span> Agregar / Editar
        </button>
    </div>

    <!-- Resumen visual en la tarjeta -->
    <div class="text-xs text-slate-500 space-y-1">
        <p x-show="especificaciones.length === 0 && !peso && !garantia_info.nombre">
            No se han configurado especificaciones, dimensiones ni garantía.
        </p>
        <p x-show="especificaciones.length > 0" class="text-emerald-700 font-bold">
            <span class="material-symbols-outlined text-[14px] align-middle">check_circle</span> <span x-text="especificaciones.length"></span> grupos de especificaciones agregados.
        </p>
        <p x-show="peso || dimension_largo" class="text-emerald-700 font-bold">
            <span class="material-symbols-outlined text-[14px] align-middle">check_circle</span> Dimensiones físicas configuradas.
        </p>
        <p x-show="garantia_info.nombre" class="text-emerald-700 font-bold">
            <span class="material-symbols-outlined text-[14px] align-middle">check_circle</span> Garantía: <span x-text="garantia_info.nombre"></span>.
        </p>
    </div>

    <!-- Inputs ocultos para JSON y Backend -->
    <input type="hidden" name="especificaciones" :value="JSON.stringify(especificaciones)">
    <input type="hidden" name="garantia_info" :value="JSON.stringify(garantia_info)">
    <input type="hidden" name="peso" :value="peso">
    <input type="hidden" name="dimension_largo" :value="dimension_largo">
    <input type="hidden" name="dimension_ancho" :value="dimension_ancho">
    <input type="hidden" name="dimension_alto" :value="dimension_alto">

    <!-- MODAL ALPINE -->
    <template x-teleport="body">
        <div x-show="abrirModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" x-cloak style="display: none;">
            <div class="bg-white dark:bg-[#181a1b] rounded-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col shadow-2xl" @click.away="abrirModal = false">
                
                <!-- Header -->
                <div class="p-4 border-b border-slate-200 dark:border-gray-700 flex justify-between items-center bg-slate-50 dark:bg-gray-800">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Editar Especificaciones, Fletes y Garantía</h2>
                    <button type="button" @click="abrirModal = false" class="text-slate-400 hover:text-rose-500 transition-colors">
                        <span class="material-symbols-outlined text-[24px]">close</span>
                    </button>
                </div>

                <!-- Body (Scrollable) -->
                <div class="p-6 overflow-y-auto space-y-8 flex-1">
                    
                    <!-- 1. Especificaciones Anidadas -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-bold text-slate-800 dark:text-gray-100">Especificaciones Técnicas</h3>
                            <button type="button" @click="agregarGrupo()" class="btn-panama-outline text-xs px-2 py-1 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px]">add_circle</span> Añadir Grupo
                            </button>
                        </div>
                        
                        <div class="space-y-4">
                            <template x-for="(grupoItem, indexGrupo) in especificaciones" :key="indexGrupo">
                                <div class="border border-slate-200 dark:border-gray-700 rounded-xl overflow-hidden">
                                    <!-- Cabecera del Grupo -->
                                    <div class="bg-slate-50 dark:bg-gray-800 p-3 border-b border-slate-200 dark:border-gray-700 flex items-center justify-between">
                                        <input type="text" x-model="grupoItem.grupo" placeholder="Nombre del Grupo (Ej: Modelo, Mouse)" class="input-panama w-1/2 text-sm py-1.5 px-3 rounded-lg border-slate-300 dark:border-gray-600 font-bold bg-white dark:bg-[#121415] text-slate-900 dark:text-white">
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="agregarAtributo(indexGrupo)" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-0.5">
                                                <span class="material-symbols-outlined text-[16px]">add</span> Añadir Atributo
                                            </button>
                                            <button type="button" @click="eliminarGrupo(indexGrupo)" class="text-rose-500 hover:bg-rose-100 p-1.5 rounded-md">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Atributos del Grupo -->
                                    <div class="p-3 space-y-2 bg-white dark:bg-[#181a1b]">
                                        <template x-for="(attr, indexAttr) in grupoItem.atributos" :key="indexAttr">
                                            <div class="flex gap-2 items-center">
                                                <input type="text" x-model="attr.clave" placeholder="Atributo (Ej: Marca, Interfaz)" class="input-panama w-1/2 text-xs py-2 px-3 rounded-lg border-slate-200 dark:border-gray-700 font-medium bg-slate-50 dark:bg-[#121415] text-slate-900 dark:text-white">
                                                <input type="text" x-model="attr.valor" placeholder="Valor (Ej: Logitech, Inalámbrico)" class="input-panama w-1/2 text-xs py-2 px-3 rounded-lg border-slate-200 dark:border-gray-700 bg-white dark:bg-[#121415] text-slate-900 dark:text-white">
                                                <button type="button" @click="eliminarAtributo(indexGrupo, indexAttr)" class="text-rose-400 hover:text-rose-600 p-1 rounded-md">
                                                    <span class="material-symbols-outlined text-[18px]">close</span>
                                                </button>
                                            </div>
                                        </template>
                                        <p x-show="!grupoItem.atributos || grupoItem.atributos.length === 0" class="text-[11px] text-slate-400">No hay atributos en este grupo.</p>
                                    </div>
                                </div>
                            </template>
                            <p x-show="especificaciones.length === 0" class="text-xs text-slate-400">No hay grupos de especificaciones agregados.</p>
                        </div>
                    </div>

                    <!-- 2. Especificaciones Físicas (Fletes) -->
                    <div class="space-y-3 pt-6 border-t border-slate-100 dark:border-gray-700">
                        <h3 class="text-sm font-bold text-slate-800 dark:text-gray-100">Especificaciones Físicas (Logística / Fletes)</h3>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Peso (kg)</label>
                                <input type="number" step="0.001" x-model="peso" class="input-panama w-full text-xs py-2 px-3 rounded-lg border-slate-200 bg-white dark:bg-[#121415] text-slate-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Largo (cm)</label>
                                <input type="number" step="0.01" x-model="dimension_largo" class="input-panama w-full text-xs py-2 px-3 rounded-lg border-slate-200 bg-white dark:bg-[#121415] text-slate-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Ancho (cm)</label>
                                <input type="number" step="0.01" x-model="dimension_ancho" class="input-panama w-full text-xs py-2 px-3 rounded-lg border-slate-200 bg-white dark:bg-[#121415] text-slate-900 dark:text-white">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Alto (cm)</label>
                                <input type="number" step="0.01" x-model="dimension_alto" class="input-panama w-full text-xs py-2 px-3 rounded-lg border-slate-200 bg-white dark:bg-[#121415] text-slate-900 dark:text-white">
                            </div>
                        </div>
                    </div>

                <!-- 3. Garantía -->
                <div class="space-y-3 pt-6 border-t border-slate-100 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-gray-100">Garantía</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Nombre de la Garantía</label>
                            <input type="text" x-model="garantia_info.nombre" placeholder="Ej: Garantía Estándar Logitech" class="input-panama w-full text-xs py-2 px-3 rounded-lg border-slate-200 bg-white dark:bg-[#121415] text-slate-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Duración</label>
                            <input type="text" x-model="garantia_info.duracion" placeholder="Ej: 1 Año" class="input-panama w-full text-xs py-2 px-3 rounded-lg border-slate-200 bg-white dark:bg-[#121415] text-slate-900 dark:text-white">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">¿Con quién me comunico?</label>
                            <textarea x-model="garantia_info.contacto" rows="2" placeholder="Puedes devolver tu producto..." class="input-panama w-full text-xs py-2 px-3 rounded-lg border-slate-200 bg-white dark:bg-[#121415] text-slate-900 dark:text-white"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="p-4 border-t border-slate-200 dark:border-gray-700 flex justify-end gap-2 bg-slate-50 dark:bg-gray-800">
                <button type="button" @click="abrirModal = false" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-sm transition-colors">
                    Guardar y Cerrar Modal
                </button>
            </div>
        </div>
    </div>
    </template>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('especificacionesLogistica', () => ({
            abrirModal: false,
            especificaciones: [],
            garantia_info: @json(old('garantia_info', $producto->garantia_info ?? $defaultGarantia)),
            peso: '{{ old('peso', $producto->peso ?? '') }}',
            dimension_largo: '{{ old('dimension_largo', $producto->dimension_largo ?? '') }}',
            dimension_ancho: '{{ old('dimension_ancho', $producto->dimension_ancho ?? '') }}',
            dimension_alto: '{{ old('dimension_alto', $producto->dimension_alto ?? '') }}',

            init() {
                let specsInit = @json(old('especificaciones', $producto->especificaciones ?? []));
                
                // Normalizar especificaciones antiguas (plano) a nueva estructura anidada si es necesario
                if (Array.isArray(specsInit) && specsInit.length > 0) {
                    if (specsInit[0].hasOwnProperty('clave') && !specsInit[0].hasOwnProperty('atributos')) {
                        // Es formato antiguo plano: [{grupo: 'X', clave: 'Y', valor: 'Z'}]
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
                    } else {
                        // Ya es formato nuevo: [{grupo: 'X', atributos: [...]}]
                        this.especificaciones = specsInit;
                    }
                }

                if (!this.garantia_info || typeof this.garantia_info !== 'object') {
                    this.garantia_info = {nombre: '', duracion: '', contacto: ''};
                }
            },

            agregarGrupo() {
                this.especificaciones.push({ grupo: '', atributos: [{ clave: '', valor: '' }] });
            },
            eliminarGrupo(index) {
                this.especificaciones.splice(index, 1);
            },
            agregarAtributo(indexGrupo) {
                if (!this.especificaciones[indexGrupo].atributos) {
                    this.especificaciones[indexGrupo].atributos = [];
                }
                this.especificaciones[indexGrupo].atributos.push({ clave: '', valor: '' });
            },
            eliminarAtributo(indexGrupo, indexAttr) {
                this.especificaciones[indexGrupo].atributos.splice(indexAttr, 1);
            }
        }));
    });
</script>



