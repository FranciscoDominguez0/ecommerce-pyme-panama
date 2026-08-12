@extends('layouts.admin')

@section('title', $esEdicion ? 'Editar Categoría' : 'Nueva Categoría')

@section('breadcrumbs')
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <a href="{{ route('admin.categorias.index') }}" class="text-slate-500 hover:text-slate-900 transition-colors truncate max-w-[85px] sm:max-w-none">Categorías</a>
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <span class="font-bold text-slate-900 truncate shrink-0">{{ $esEdicion ? 'Editar' : 'Nueva' }}</span>
@endsection

@section('content')
<div class="space-y-6 max-w-5xl w-full min-w-0 mx-auto pb-12">

    <!-- Encabezado de Página -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200/80">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.categorias.index') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" 
               class="p-2 text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors" 
               title="Volver al listado">
                <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            </a>
            <div>
                <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                    {{ $esEdicion ? 'Editar Categoría: ' . $categoria->nombre : 'Crear Nueva Categoría' }}
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">
                    {{ $esEdicion ? 'Actualiza los parámetros, jerarquía e imagen de esta categoría.' : 'Define el nombre, categoría padre, visibilidad y recursos gráficos.' }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.categorias.index') }}{{ request()->getQueryString() ? '?' . request()->getQueryString() : '' }}" 
               class="px-4 py-2 border border-slate-200 text-slate-700 rounded-lg text-xs font-semibold hover:bg-slate-50 transition-colors shadow-xs">
                Cancelar
            </a>
            <button type="submit" form="categoria-form" 
                    class="flex items-center gap-1.5 px-4 py-2 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-slate-800 transition-colors shadow-xs">
                <span class="material-symbols-outlined text-[16px]">save</span>
                <span>{{ $esEdicion ? 'Guardar Cambios' : 'Crear Categoría' }}</span>
            </button>
        </div>
    </div>

    <!-- Formulario Principal -->
    <form id="categoria-form" 
          method="POST" 
          action="{{ $esEdicion ? route('admin.categorias.update', $categoria->id) . (request()->getQueryString() ? '?' . request()->getQueryString() : '') : route('admin.categorias.store') }}" 
          enctype="multipart/form-data" 
          class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @csrf
        @if($esEdicion)
            @method('PUT')
        @endif

        <!-- Columna Izquierda / Principal (2 columnas) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Card 1: Datos Básicos -->
            <div class="card-elevated rounded-xl p-5 sm:p-6 space-y-5">
                <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                    <span class="material-symbols-outlined text-[18px] text-emerald-600">category</span>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">Información General</h3>
                </div>

                <!-- Nombre -->
                <div class="space-y-1.5">
                    <label for="nombre" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        Nombre de la Categoría <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="nombre" 
                           name="nombre" 
                           value="{{ old('nombre', $categoria->nombre) }}" 
                           required 
                           autofocus
                           placeholder="Ej. Computadoras & Laptops" 
                           class="w-full bg-slate-50 border @error('nombre') border-red-300 ring-2 ring-red-500/10 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-xs text-slate-900 font-medium placeholder:text-slate-400 focus:bg-white focus:border-slate-400 focus:ring-2 focus:ring-slate-900/5 transition-all outline-none" />
                    @error('nombre')
                        <p class="text-[11px] text-red-600 font-medium flex items-center gap-1 mt-1">
                            <span class="material-symbols-outlined text-[14px]">error</span>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Slug -->
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label for="slug" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                            Slug URL (Identificador Único)
                        </label>
                        <button type="button" 
                                id="btn-regenerar-slug" 
                                title="Recalcular el enlace amigable a partir del texto actual del nombre"
                                class="text-[11px] font-semibold text-emerald-700 hover:text-emerald-800 active:scale-95 transition-all flex items-center gap-1 group/slugbtn">
                            <span class="material-symbols-outlined text-[14px] transition-transform duration-300 group-hover/slugbtn:rotate-90" id="icon-autorenew">autorenew</span>
                            <span>Regenerar desde nombre</span>
                        </button>
                    </div>
                    <div class="relative flex items-center">
                        <span class="absolute left-3 text-slate-400 font-mono text-xs select-none">/categoria/</span>
                        <input type="text" 
                               id="slug" 
                               name="slug" 
                               value="{{ old('slug', $categoria->slug) }}" 
                               placeholder="computadoras-laptops" 
                               class="w-full bg-slate-50 border @error('slug') border-red-300 ring-2 ring-red-500/10 @else border-slate-200 @enderror rounded-lg pl-24 pr-3.5 py-2.5 text-xs text-slate-900 font-mono placeholder:text-slate-400 focus:bg-white focus:border-slate-400 focus:ring-2 focus:ring-slate-900/5 transition-all outline-none" />
                    </div>
                    <p class="text-[11px] text-slate-400">Si se deja vacío, se creará automáticamente a partir del nombre.</p>
                    @error('slug')
                        <p class="text-[11px] text-red-600 font-medium flex items-center gap-1 mt-1">
                            <span class="material-symbols-outlined text-[14px]">error</span>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Categoría Padre (Jerarquía) con Modal y Buscador -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        Categoría Padre (Estructura Jerárquica)
                    </label>
                    <input type="hidden" 
                           id="padre_id" 
                           name="padre_id" 
                           value="{{ old('padre_id', $categoria->padre_id) }}">

                    <div id="contenedor-padre-card" class="relative">
                        <!-- Card Padre Seleccionado -->
                        <div id="card-padre-activo" class="hidden items-center justify-between p-2.5 bg-emerald-50/80 border border-emerald-200 rounded-xl hover:border-emerald-300 transition-all min-h-[48px]">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 border border-emerald-200/80 text-emerald-700 flex items-center justify-center shrink-0 shadow-2xs">
                                    <span class="material-symbols-outlined text-[18px]">account_tree</span>
                                </div>
                                <div class="min-w-0">
                                    <div id="display-padre-nombre" class="text-xs font-bold text-emerald-950 truncate">Nombre Categoría</div>
                                    <div id="display-padre-ruta" class="text-[11px] text-emerald-700/80 truncate font-medium">↳ Jerarquía Superior</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                <button type="button" 
                                        onclick="abrirModalPadres()" 
                                        class="px-2.5 py-1.5 text-xs font-semibold text-emerald-900 bg-white hover:bg-emerald-100/60 border border-emerald-200 rounded-lg transition-colors cursor-pointer flex items-center gap-1 shadow-2xs">
                                    <span class="material-symbols-outlined text-[14px]">sync_alt</span>
                                    <span>Cambiar</span>
                                </button>
                                <button type="button" 
                                        onclick="deseleccionarPadre()" 
                                        class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                        title="Quitar categoría padre (Hacer raíz)">
                                    <span class="material-symbols-outlined text-[16px]">close</span>
                                </button>
                            </div>
                        </div>

                        <!-- Card Sin Padre (Categoría Raíz / Ninguna) -->
                        <button type="button" 
                                id="btn-abrir-modal-padres" 
                                onclick="abrirModalPadres()" 
                                class="w-full flex items-center justify-between px-3.5 py-2.5 bg-slate-50 hover:bg-slate-100/80 border border-dashed border-slate-300 hover:border-slate-400 rounded-xl transition-all group text-left cursor-pointer min-h-[48px]">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-slate-200/60 text-slate-500 group-hover:text-slate-700 flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-[16px]">folder_open</span>
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-slate-700 group-hover:text-slate-900 block">— Ninguna (Categoría Principal / Raíz) —</span>
                                    <span class="text-[10px] text-slate-400 block font-normal">Haz clic para buscar y asignar una categoría padre</span>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 text-xs font-bold flex items-center gap-1 shadow-2xs group-hover:border-slate-300">
                                <span class="material-symbols-outlined text-[14px] text-slate-500">search</span>
                                <span>Explorar</span>
                            </span>
                        </button>
                    </div>

                    <p class="text-[11px] text-slate-400">Asigna un padre si esta categoría es una subcategoría de otra.</p>
                    @error('padre_id')
                        <p class="text-[11px] text-red-600 font-medium flex items-center gap-1 mt-1">
                            <span class="material-symbols-outlined text-[14px]">error</span>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Descripción -->
                <div class="space-y-1.5">
                    <label for="descripcion" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        Descripción Informativa
                    </label>
                    <textarea id="descripcion" 
                              name="descripcion" 
                              rows="3" 
                              placeholder="Breve reseña sobre los artículos agrupados en esta categoría..." 
                              class="w-full bg-slate-50 border @error('descripcion') border-red-300 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-xs text-slate-900 font-medium placeholder:text-slate-400 focus:bg-white focus:border-slate-400 focus:ring-2 focus:ring-slate-900/5 transition-all outline-none resize-y">{{ old('descripcion', $categoria->descripcion) }}</textarea>
                    @error('descripcion')
                        <p class="text-[11px] text-red-600 font-medium flex items-center gap-1 mt-1">
                            <span class="material-symbols-outlined text-[14px]">error</span>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

            </div>

        </div>

        <!-- Columna Derecha / Ajustes Secundarios (1 columna) -->
        <div class="space-y-6">

            <!-- Card 2: Visibilidad & Orden -->
            <div class="card-elevated rounded-xl p-5 sm:p-6 space-y-5">
                <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                    <span class="material-symbols-outlined text-[18px] text-slate-700">tune</span>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">Estado y Publicación</h3>
                </div>

                <!-- Toggle Estado Activo -->
                <div class="p-3.5 rounded-lg bg-slate-50 border border-slate-200/80 flex items-center justify-between gap-3">
                    <div>
                        <span class="text-xs font-bold text-slate-900 block">Categoría Activa</span>
                        <span class="text-[11px] text-slate-500 block mt-0.5">Visible en la tienda y catálogo</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" 
                               name="activo" 
                               value="1" 
                               class="sr-only peer" 
                               {{ old('activo', $categoria->activo ?? true) ? 'checked' : '' }}>
                        <div class="w-10 h-5 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                    </label>
                </div>

                <!-- Orden de Visualización -->
                <div class="space-y-1.5">
                    <label for="orden_visualizacion" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        Orden de Visualización
                    </label>
                    <input type="number" 
                           id="orden_visualizacion" 
                           name="orden_visualizacion" 
                           value="{{ old('orden_visualizacion', $categoria->orden_visualizacion ?? 0) }}" 
                           min="0" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2.5 text-xs text-slate-900 font-semibold focus:bg-white focus:border-slate-400 focus:ring-2 focus:ring-slate-900/5 transition-all outline-none" />
                    <p class="text-[11px] text-slate-400">Los números menores (0, 1, 2...) se mostrarán primero en la tienda.</p>
                </div>
            </div>

            <!-- Card 3: Imagen de la Categoría -->
            <div class="card-elevated rounded-xl p-5 sm:p-6 space-y-4">
                <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                    <span class="material-symbols-outlined text-[18px] text-slate-700">image</span>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">Imagen de Portada</h3>
                </div>

                <!-- Preview Actual / Nuevo -->
                <div class="space-y-3">
                    <div id="image-preview-container" class="w-full h-44 rounded-xl bg-slate-50 border-2 border-dashed border-slate-200 hover:border-slate-300 transition-colors flex flex-col items-center justify-center overflow-hidden relative group shadow-2xs">
                        @if($categoria->imagen_ruta)
                            <img id="image-preview" 
                                 src="{{ asset($categoria->imagen_ruta) }}" 
                                 alt="{{ $categoria->nombre }}" 
                                 class="w-full h-full object-contain p-3 group-hover:scale-105 transition-transform duration-300" />
                        @else
                            <img id="image-preview" 
                                 src="" 
                                 alt="Vista previa" 
                                 class="w-full h-full object-contain p-3 hidden" />
                            <div id="placeholder-icon" class="flex flex-col items-center text-slate-400 p-4 text-center">
                                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mb-2 text-slate-400">
                                    <span class="material-symbols-outlined text-[26px]">add_photo_alternate</span>
                                </div>
                                <span class="text-xs font-semibold text-slate-600">Sin imagen asignada</span>
                                <span class="text-[10px] text-slate-400 mt-0.5">Haz clic abajo para subir una foto</span>
                            </div>
                        @endif
                    </div>

                    <!-- Botón de subir archivo -->
                    <div class="relative">
                        <input type="file" 
                               id="imagen" 
                               name="imagen" 
                               accept="image/jpeg,image/png,image/webp,image/svg+xml" 
                               class="hidden" 
                               onchange="previewImage(this);" />
                        <label for="imagen" 
                               class="w-full py-2 px-3 border border-slate-200 rounded-lg text-slate-700 font-semibold text-xs hover:bg-slate-50 text-center transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                            <span class="material-symbols-outlined text-[16px]">upload</span>
                            <span>{{ $categoria->imagen_ruta ? 'Cambiar Imagen' : 'Seleccionar Imagen' }}</span>
                        </label>
                    </div>

                    @if($categoria->imagen_ruta)
                        <div class="flex items-center gap-2 pt-1">
                            <input type="checkbox" id="eliminar_imagen" name="eliminar_imagen" value="1" class="rounded text-red-600 focus:ring-red-500 border-slate-300">
                            <label for="eliminar_imagen" class="text-xs text-red-600 font-medium cursor-pointer">
                                Eliminar imagen existente al guardar
                            </label>
                        </div>
                    @endif

                    <p class="text-[10px] text-slate-400 text-center">Formatos admitidos: JPG, PNG, WEBP, SVG. Máx: 2MB.</p>
                </div>
            </div>

            <!-- Resumen de Productos (Solo en Edición) -->
            @if($esEdicion)
                <div class="card-elevated rounded-xl p-5 space-y-3 bg-slate-50/50">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-medium">Productos en esta categoría:</span>
                        <span class="font-bold text-slate-900 bg-white px-2 py-0.5 rounded border border-slate-200">
                            {{ $categoria->productos()->count() }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-medium">Subcategorías dependientes:</span>
                        <span class="font-bold text-slate-900 bg-white px-2 py-0.5 rounded border border-slate-200">
                            {{ $categoria->hijas()->count() }}
                        </span>
                    </div>
                </div>
            @endif

        </div>

    </form>

</div>

<!-- ── MODAL: SELECCIONAR CATEGORÍA PADRE ── -->
<div id="modal-padres-categoria" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full p-6 space-y-4 transform transition-all flex flex-col max-h-[90vh]">
        
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-3.5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100/80 border border-emerald-200 text-emerald-700 flex items-center justify-center shrink-0 shadow-2xs">
                    <span class="material-symbols-outlined text-[20px]">account_tree</span>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Seleccionar Categoría Padre</h3>
                    <p class="text-[11px] text-slate-500">Busca y asigna la categoría superior jerárquica.</p>
                </div>
            </div>
            <button type="button" onclick="cerrarModalPadres()" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <!-- Buscador en tiempo real -->
        <div class="relative">
            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
            <input type="text" 
                   id="buscador-padres-modal" 
                   oninput="filtrarPadresModal(this.value)" 
                   placeholder="Buscar categoría por nombre..." 
                   class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-10 pr-9 py-2.5 text-xs text-slate-900 placeholder:text-slate-400 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none">
            <button type="button" 
                    id="btn-limpiar-busqueda-padres" 
                    onclick="filtrarPadresModal('')" 
                    class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-1 rounded-md">
                <span class="material-symbols-outlined text-[16px]">close</span>
            </button>
        </div>

        <!-- Opción por defecto: Ninguna (Categoría Principal / Raíz) -->
        <div class="pt-1">
            <div onclick="seleccionarPadre('', '— Ninguna (Categoría Principal / Raíz) —', '')"
                 id="option-padre-ninguna"
                 class="p-3 bg-slate-50 hover:bg-slate-100/90 border border-slate-200/90 rounded-xl cursor-pointer transition-all flex items-center justify-between group">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-500 flex items-center justify-center shrink-0 shadow-2xs">
                        <span class="material-symbols-outlined text-[18px]">folder_open</span>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-slate-900 block">— Ninguna (Categoría Principal / Raíz) —</span>
                        <span class="text-[11px] text-slate-500 block">Esta categoría se ubicará en el nivel superior.</span>
                    </div>
                </div>
                <span id="check-padre-ninguna" class="material-symbols-outlined text-emerald-600 text-[20px] opacity-0 transition-opacity">check_circle</span>
            </div>
        </div>

        <div class="border-t border-slate-100 pt-2">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Categorías Disponibles</span>
        </div>

        <!-- Lista scrolleable de categorías -->
        <div id="lista-padres-modal" class="overflow-y-auto space-y-2 pr-1 flex-1 max-h-[320px]">
            <!-- Generado dinámicamente mediante JS -->
        </div>

        <!-- Empty State en búsqueda -->
        <div id="empty-state-padres" class="hidden text-center py-8 space-y-2">
            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                <span class="material-symbols-outlined text-[24px]">search_off</span>
            </div>
            <p class="text-xs font-semibold text-slate-700">No se encontraron categorías</p>
            <p class="text-[11px] text-slate-400">Intenta buscar con otro término de búsqueda.</p>
        </div>

        <!-- Footer Modal -->
        <div class="flex items-center justify-between border-t border-slate-100 pt-3">
            <span id="contador-padres-modal" class="text-[11px] text-slate-400 font-medium">Categorías disponibles</span>
            <button type="button" onclick="cerrarModalPadres()" class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
                Cerrar
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Categoría Padre Selector Modal Logic
    const padresData = @json($padresFormatted ?? []);

    function abrirModalPadres() {
        const modal = document.getElementById('modal-padres-categoria');
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        const input = document.getElementById('buscador-padres-modal');
        if (input) {
            input.value = '';
            setTimeout(() => input.focus(), 80);
        }
        
        filtrarPadresModal('');
    }

    function cerrarModalPadres() {
        const modal = document.getElementById('modal-padres-categoria');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function filtrarPadresModal(query) {
        const container = document.getElementById('lista-padres-modal');
        const empty = document.getElementById('empty-state-padres');
        const btnClear = document.getElementById('btn-limpiar-busqueda-padres');
        const contador = document.getElementById('contador-padres-modal');
        const inputBuscador = document.getElementById('buscador-padres-modal');
        const valorActual = (document.getElementById('padre_id')?.value || '').toString();

        if (inputBuscador && query === '') {
            inputBuscador.value = '';
        }

        if (btnClear) {
            btnClear.classList.toggle('hidden', !query);
        }

        const q = (query || '').trim().toLowerCase();
        let lista = padresData;

        if (q) {
            lista = lista.filter(p => 
                (p.nombre && p.nombre.toLowerCase().includes(q)) || 
                (p.ruta_padres && p.ruta_padres.toLowerCase().includes(q)) ||
                (p.ruta_jerarquica && p.ruta_jerarquica.toLowerCase().includes(q))
            );
        }

        container.innerHTML = '';

        // Status check icon en la opción "Ninguna"
        const checkNinguna = document.getElementById('check-padre-ninguna');
        const optionNinguna = document.getElementById('option-padre-ninguna');
        if (checkNinguna && optionNinguna) {
            if (!valorActual) {
                checkNinguna.classList.remove('opacity-0');
                optionNinguna.classList.add('border-emerald-300', 'bg-emerald-50/60');
            } else {
                checkNinguna.classList.add('opacity-0');
                optionNinguna.classList.remove('border-emerald-300', 'bg-emerald-50/60');
            }
        }

        if (contador) {
            contador.textContent = `${lista.length} categoría${lista.length === 1 ? '' : 's'} disponible${lista.length === 1 ? '' : 's'}`;
        }

        if (lista.length === 0) {
            container.classList.add('hidden');
            empty.classList.remove('hidden');
            return;
        }

        container.classList.remove('hidden');
        empty.classList.add('hidden');

        lista.forEach(padre => {
            const isSelected = valorActual && valorActual === padre.id.toString();
            const nivel = padre.nivel || 0;
            
            const card = document.createElement('div');
            card.className = `p-3 rounded-xl border transition-all cursor-pointer flex items-center justify-between group ${
                isSelected 
                    ? 'bg-emerald-50/90 border-emerald-400 ring-2 ring-emerald-500/20 shadow-xs' 
                    : 'bg-white border-slate-200/80 hover:border-slate-300 hover:bg-slate-50/80 hover:shadow-2xs'
            }`;

            // Mover horizontalmente según el nivel de profundidad (20px por nivel)
            if (nivel > 0) {
                card.style.marginLeft = `${Math.min(nivel * 20, 60)}px`;
            }

            const nombreEscapado = padre.nombre.replace(/'/g, "\\'");
            const rutaPadresEscapada = (padre.ruta_padres || '').replace(/'/g, "\\'");
            card.onclick = () => seleccionarPadre(padre.id, nombreEscapado, rutaPadresEscapada);

            card.innerHTML = `
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-8 h-8 rounded-lg ${isSelected ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700'} flex items-center justify-center shrink-0 shadow-2xs">
                        <span class="material-symbols-outlined text-[18px]">${nivel > 0 ? 'subdirectory_arrow_right' : 'folder'}</span>
                    </div>
                    <div class="min-w-0">
                        <div class="text-xs font-bold ${isSelected ? 'text-emerald-950' : 'text-slate-800 group-hover:text-slate-900'} truncate">
                            ${padre.nombre}
                        </div>
                        ${padre.padre_nombre ? `
                            <div class="text-[11px] text-slate-500 font-medium truncate flex items-center gap-1 mt-0.5">
                                <span class="material-symbols-outlined text-[13px] text-slate-400">schema</span>
                                <span class="text-slate-600 font-medium">Pertenece a: ${padre.padre_nombre}</span>
                            </div>
                        ` : `
                            <div class="text-[11px] text-slate-400 font-medium mt-0.5">Categoría Principal (Raíz)</div>
                        `}
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0 ml-2">
                    ${!padre.activo ? '<span class="px-2 py-0.5 text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200 rounded-full">Inactiva</span>' : ''}
                    <span class="material-symbols-outlined ${isSelected ? 'text-emerald-600 opacity-100' : 'text-slate-300 opacity-0 group-hover:opacity-100'} text-[20px] transition-opacity">
                        check_circle
                    </span>
                </div>
            `;
            container.appendChild(card);
        });
    }

    function seleccionarPadre(id, nombre, padreNombre) {
        const input = document.getElementById('padre_id');
        if (input) {
            input.value = id ? id : '';
        }
        actualizarUIPadre(id, nombre, padreNombre);
        cerrarModalPadres();
    }

    function deseleccionarPadre() {
        seleccionarPadre('', '', '');
    }

    function actualizarUIPadre(id, nombre, padreNombre) {
        const cardActiva = document.getElementById('card-padre-activo');
        const btnAbrir = document.getElementById('btn-abrir-modal-padres');
        const displayNombre = document.getElementById('display-padre-nombre');
        const displayRuta = document.getElementById('display-padre-ruta');

        if (!id) {
            if (cardActiva) cardActiva.classList.add('hidden');
            if (btnAbrir) btnAbrir.classList.remove('hidden');
            return;
        }

        if (!nombre) {
            const encontrado = padresData.find(p => p.id.toString() === id.toString());
            if (encontrado) {
                nombre = encontrado.nombre;
                padreNombre = encontrado.padre_nombre;
            }
        }

        if (displayNombre) displayNombre.textContent = nombre || `Categoría #${id}`;
        if (displayRuta) {
            displayRuta.textContent = padreNombre ? `↳ Pertenece a: ${padreNombre}` : 'Categoría Principal (Raíz)';
        }

        if (cardActiva) {
            cardActiva.classList.remove('hidden');
            cardActiva.classList.add('flex');
        }
        if (btnAbrir) btnAbrir.classList.add('hidden');
    }

    // Generación dinámica de Slug a partir del Nombre
    const nombreInput = document.getElementById('nombre');
    const slugInput = document.getElementById('slug');
    const btnRegenerar = document.getElementById('btn-regenerar-slug');
    let slugModificadoManualmente = {{ $esEdicion ? 'true' : 'false' }};

    function convertirASlug(texto) {
        return texto
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '') // Remover tildes
            .replace(/[^a-z0-9 -]/g, '')     // Remover caracteres no alfanuméricos
            .trim()
            .replace(/\s+/g, '-')            // Espacios a guiones
            .replace(/-+/g, '-');            // Eliminar guiones consecutivos
    }

    nombreInput.addEventListener('input', function() {
        if (!slugModificadoManualmente) {
            slugInput.value = convertirASlug(this.value);
        }
    });

    slugInput.addEventListener('input', function() {
        slugModificadoManualmente = true;
    });

    btnRegenerar.addEventListener('click', function() {
        const icon = document.getElementById('icon-autorenew');
        if (icon) {
            icon.classList.add('rotate-180');
            setTimeout(() => icon.classList.remove('rotate-180'), 350);
        }
        
        slugInput.value = convertirASlug(nombreInput.value);
        slugModificadoManualmente = false;

        // Feedback visual inmediato en el input
        slugInput.classList.add('ring-2', 'ring-emerald-500/40', 'bg-emerald-50/50');
        setTimeout(() => {
            slugInput.classList.remove('ring-2', 'ring-emerald-500/40', 'bg-emerald-50/50');
        }, 600);
    });

    // Vista previa instantánea de imagen
    function previewImage(input) {
        const preview = document.getElementById('image-preview');
        const placeholder = document.getElementById('placeholder-icon');

        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                if (placeholder) placeholder.classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const padreInicial = document.getElementById('padre_id')?.value;
        if (padreInicial) {
            actualizarUIPadre(padreInicial, '', '');
        }
    });
</script>
@endpush
@endsection
