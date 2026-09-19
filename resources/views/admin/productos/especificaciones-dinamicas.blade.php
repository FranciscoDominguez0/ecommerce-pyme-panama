<!-- ── SECCIÓN: ESPECIFICACIONES DINÁMICAS (HIGHLIGHTS, FEATURES, SPECS) ── -->
<div class="card-elevated p-5 sm:p-6 rounded-2xl space-y-8" x-data="especificacionesDinamicas()">
    
    <div class="flex items-center gap-2 border-b border-slate-100 dark:border-gray-700 pb-3">
        <span class="material-symbols-outlined text-emerald-600 text-[20px]">dataset</span>
        <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Especificaciones Estructuradas</h2>
    </div>

    <!-- Inputs ocultos para enviar al backend -->
    <input type="hidden" name="destacados" :value="JSON.stringify(destacados)">
    <input type="hidden" name="caracteristicas" :value="JSON.stringify(caracteristicas)">
    <input type="hidden" name="especificaciones" :value="JSON.stringify(especificaciones)">

    <!-- 1. Destacados (Highlights) -->
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 dark:text-gray-100">Destacados (Highlights)</h3>
            <button type="button" @click="agregarDestacado()" class="btn-panama-outline text-xs px-2 py-1 flex items-center gap-1" x-show="destacados.length < 4">
                <span class="material-symbols-outlined text-[16px]">add</span> Añadir
            </button>
        </div>
        <p class="text-xs text-slate-500 dark:text-slate-400">Muestra hasta 4 cuadros principales en la parte superior. (Ej: Resolución -> 4K)</p>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <template x-for="(item, index) in destacados" :key="index">
                <div class="p-3 bg-slate-50 dark:bg-transparent border border-slate-200 dark:border-gray-700 rounded-xl flex gap-2 relative group">
                    <div class="flex-1 space-y-2">
                        <input type="text" x-model="item.icono" placeholder="Icono (Ej: tv, wifi)" class="input-panama w-full text-xs py-1.5 px-2 rounded-lg border-slate-200 dark:border-gray-700 bg-white dark:bg-[#121415] text-slate-900 dark:text-white">
                        <input type="text" x-model="item.titulo" placeholder="Título (Ej: Resolución)" class="input-panama w-full text-xs py-1.5 px-2 rounded-lg border-slate-200 dark:border-gray-700 bg-white dark:bg-[#121415] text-slate-900 dark:text-white">
                        <input type="text" x-model="item.valor" placeholder="Valor (Ej: 4K (2160p))" class="input-panama w-full text-xs py-1.5 px-2 rounded-lg border-slate-200 dark:border-gray-700 font-bold bg-white dark:bg-[#121415] text-slate-900 dark:text-white">
                    </div>
                    <button type="button" @click="eliminarDestacado(index)" class="text-rose-500 hover:bg-rose-100 p-1 rounded-md h-fit">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- 2. Características (Features) -->
    <div class="space-y-3 pt-4 border-t border-slate-100 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 dark:text-gray-100">Características Principales (Features)</h3>
            <button type="button" @click="agregarCaracteristica()" class="btn-panama-outline text-xs px-2 py-1 flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">add</span> Añadir
            </button>
        </div>
        
        <div class="space-y-2">
            <template x-for="(item, index) in caracteristicas" :key="index">
                <div class="p-3 bg-slate-50 dark:bg-transparent border border-slate-200 dark:border-gray-700 rounded-xl flex gap-2">
                    <div class="flex-1 space-y-2">
                        <input type="text" x-model="item.titulo" placeholder="Título de la característica" class="input-panama w-full text-sm py-1.5 px-3 rounded-lg border-slate-200 dark:border-gray-700 font-bold bg-white dark:bg-[#121415] text-slate-900 dark:text-white">
                        <textarea x-model="item.descripcion" placeholder="Descripción breve" rows="2" class="input-panama w-full text-xs py-1.5 px-3 rounded-lg border-slate-200 dark:border-gray-700 bg-white dark:bg-[#121415] text-slate-900 dark:text-white"></textarea>
                    </div>
                    <button type="button" @click="eliminarCaracteristica(index)" class="text-rose-500 hover:bg-rose-100 p-1 rounded-md h-fit">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- 3. Especificaciones (Specifications table) -->
    <div class="space-y-3 pt-4 border-t border-slate-100 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800 dark:text-gray-100">Especificaciones Técnicas (Tabla)</h3>
            <button type="button" @click="agregarEspecificacion()" class="btn-panama-outline text-xs px-2 py-1 flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">add</span> Añadir
            </button>
        </div>
        
        <div class="space-y-2">
            <template x-for="(item, index) in especificaciones" :key="index">
                <div class="flex gap-2 items-center">
                    <input type="text" x-model="item.clave" placeholder="Atributo (Ej: Tipo de Pantalla)" class="input-panama w-1/2 text-xs py-2 px-3 rounded-lg border-slate-200 dark:border-gray-700 font-bold bg-white dark:bg-[#121415] text-slate-900 dark:text-white">
                    <input type="text" x-model="item.valor" placeholder="Valor (Ej: LED)" class="input-panama w-1/2 text-xs py-2 px-3 rounded-lg border-slate-200 dark:border-gray-700 bg-white dark:bg-[#121415] text-slate-900 dark:text-white">
                    <button type="button" @click="eliminarEspecificacion(index)" class="text-rose-500 hover:bg-rose-100 p-1.5 rounded-md">
                        <span class="material-symbols-outlined text-[18px]">delete</span>
                    </button>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('especificacionesDinamicas', () => ({
            destacados: @json(old('destacados', $producto->destacados ?? [])),
            caracteristicas: @json(old('caracteristicas', $producto->caracteristicas ?? [])),
            especificaciones: @json(old('especificaciones', $producto->especificaciones ?? [])),

            init() {
                if (!Array.isArray(this.destacados)) this.destacados = [];
                if (!Array.isArray(this.caracteristicas)) this.caracteristicas = [];
                if (!Array.isArray(this.especificaciones)) this.especificaciones = [];
            },

            agregarDestacado() {
                if (this.destacados.length < 4) {
                    this.destacados.push({ icono: '', titulo: '', valor: '' });
                }
            },
            eliminarDestacado(index) {
                this.destacados.splice(index, 1);
            },

            agregarCaracteristica() {
                this.caracteristicas.push({ titulo: '', descripcion: '' });
            },
            eliminarCaracteristica(index) {
                this.caracteristicas.splice(index, 1);
            },

            agregarEspecificacion() {
                this.especificaciones.push({ clave: '', valor: '' });
            },
            eliminarEspecificacion(index) {
                this.especificaciones.splice(index, 1);
            }
        }));
    });
</script>



