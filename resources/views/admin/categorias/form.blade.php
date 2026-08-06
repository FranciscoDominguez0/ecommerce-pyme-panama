@extends('layouts.admin')

@section('title', $esEdicion ? 'Editar Categoría' : 'Nueva Categoría')

@section('breadcrumbs')
    <span class="hidden md:inline-flex items-center gap-1.5 text-slate-500">
        <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
        <span>Catálogo</span>
    </span>
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
            <a href="{{ route('admin.categorias.index') }}" 
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
            <a href="{{ route('admin.categorias.index') }}" 
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

    <!-- Alertas de Errores Globales -->
    @if (isset($errors) && $errors->any())
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs flex items-start gap-3 shadow-xs">
            <span class="material-symbols-outlined text-red-600 text-[20px] shrink-0 mt-0.5">error</span>
            <div class="space-y-1">
                <p class="font-bold">Por favor corrige los siguientes errores:</p>
                <ul class="list-disc list-inside space-y-0.5 text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Formulario Principal -->
    <form id="categoria-form" 
          method="POST" 
          action="{{ $esEdicion ? route('admin.categorias.update', $categoria->id) : route('admin.categorias.store') }}" 
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
                                class="text-[11px] font-semibold text-emerald-700 hover:text-emerald-800 flex items-center gap-1">
                            <span class="material-symbols-outlined text-[13px]">autorenew</span>
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

                <!-- Categoría Padre (Jerarquía) -->
                <div class="space-y-1.5">
                    <label for="padre_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        Categoría Padre (Estructura Jerárquica)
                    </label>
                    <div class="relative">
                        <select id="padre_id" 
                                name="padre_id" 
                                class="w-full bg-slate-50 border @error('padre_id') border-red-300 @else border-slate-200 @enderror rounded-lg px-3.5 py-2.5 text-xs text-slate-900 font-medium focus:bg-white focus:border-slate-400 focus:ring-2 focus:ring-slate-900/5 transition-all outline-none appearance-none cursor-pointer">
                            <option value="">— Ninguna (Categoría Principal / Raíz) —</option>
                            @foreach($padres as $padre)
                                <option value="{{ $padre->id }}" {{ (string) old('padre_id', $categoria->padre_id) === (string) $padre->id ? 'selected' : '' }}>
                                    {{ $padre->padre ? '↳ ' . $padre->padre->nombre . ' > ' : '' }}{{ $padre->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-[18px]">
                            unfold_more
                        </span>
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
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                        @else
                            <img id="image-preview" 
                                 src="" 
                                 alt="Vista previa" 
                                 class="w-full h-full object-cover hidden" />
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

@push('scripts')
<script>
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
        slugInput.value = convertirASlug(nombreInput.value);
        slugModificadoManualmente = false;
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
</script>
@endpush
@endsection
