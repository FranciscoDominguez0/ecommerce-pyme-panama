@extends('layouts.admin')

@section('title', 'Nueva Marca')

@section('breadcrumbs')
    <span class="hidden sm:inline-flex items-center gap-1.5 text-slate-500">
        <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
        <span>Catálogo</span>
    </span>
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <a href="{{ route('admin.brands.index') }}" class="text-slate-500 hover:text-slate-800 transition-colors">Marcas</a>
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <span class="font-bold text-slate-900 truncate">Nueva Marca</span>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between pb-4 border-b border-slate-200/80">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.brands.index') }}" class="p-2 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors">
                <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            </a>
            <div>
                <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Registrar Nueva Marca</h2>
                <p class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">Agrega un nuevo fabricante o marca comercial con su logotipo oficial.</p>
            </div>
        </div>
    </div>

    <!-- Formulario Principal -->
    <form method="POST" action="{{ route('admin.brands.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="card-elevated rounded-2xl p-6 sm:p-8 space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Nombre de la Marca -->
                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nombre de la Marca <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}" 
                           required
                           placeholder="Ej: Nintendo, Corsair, Kingston" 
                           oninput="generarSlugAutomatico(this.value)"
                           class="w-full text-sm rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 py-2.5 px-3.5 @error('name') border-rose-500 ring-rose-500/10 @enderror">
                    @error('name')
                        <p class="text-rose-600 text-xs font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Slug URL -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="slug" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                            Slug URL (Identificador)
                        </label>
                        <button type="button" onclick="generarSlugAutomatico(document.getElementById('name').value)" class="text-[11px] text-emerald-600 font-bold hover:underline flex items-center gap-0.5 cursor-pointer">
                            <span class="material-symbols-outlined text-[13px]">refresh</span>
                            <span>Generar</span>
                        </button>
                    </div>
                    <input type="text" 
                           id="slug" 
                           name="slug" 
                           value="{{ old('slug') }}" 
                           placeholder="ej: nintendo, corsair-gaming" 
                           class="w-full text-sm font-mono rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 py-2.5 px-3.5 @error('slug') border-rose-500 ring-rose-500/10 @enderror">
                    @error('slug')
                        <p class="text-rose-600 text-xs font-semibold mt-1">{{ $message }}</p>
                    @enderror
                </div>

            </div>

            <!-- Logotipo Upload & Preview -->
            <div class="border-t border-slate-100 pt-6">
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">
                    Logotipo Oficial de la Marca (Imagen WebP, PNG, SVG)
                </label>
                
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-center">
                    
                    <!-- Drag & Drop / File Input -->
                    <div class="sm:col-span-2">
                        <label for="logo" class="flex flex-col items-center justify-center border-2 border-dashed border-slate-200 hover:border-emerald-400 rounded-2xl p-6 bg-slate-50 hover:bg-emerald-50/20 transition-all cursor-pointer group">
                            <div class="w-10 h-10 rounded-xl bg-white shadow-2xs flex items-center justify-center text-slate-400 group-hover:text-emerald-600 transition-colors mb-2">
                                <span class="material-symbols-outlined text-[24px]">cloud_upload</span>
                            </div>
                            <span class="text-xs font-bold text-slate-700 group-hover:text-emerald-700 transition-colors">
                                Seleccionar archivo o arrastrar aquí
                            </span>
                            <span class="text-[10px] text-slate-400 mt-0.5">Formatos permitidos: WebP, PNG transparente o SVG (Máx. 4MB)</span>
                            <input type="file" id="logo" name="logo" accept="image/*" onchange="previsualizarLogo(this)" class="hidden">
                        </label>
                        @error('logo')
                            <p class="text-rose-600 text-xs font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Live Preview Box -->
                    <div class="flex flex-col items-center justify-center p-4 rounded-2xl bg-slate-100 border border-slate-200/80 min-h-[120px] text-center">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Vista Previa</span>
                        <div id="preview-container" class="w-28 h-12 rounded-xl bg-white border border-slate-200/80 shadow-2xs flex items-center justify-center p-2 overflow-hidden">
                            <span id="preview-placeholder" class="text-[10px] text-slate-400 italic">Sin imagen</span>
                            <img id="preview-img" src="" alt="Preview" class="max-h-full max-w-full object-contain hidden">
                        </div>
                    </div>

                </div>
            </div>

            <!-- Opciones y Destacados -->
            <div class="border-t border-slate-100 pt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <!-- Sugerida Checkbox Card -->
                <label class="flex items-start gap-3 p-4 rounded-2xl border border-slate-200 hover:border-amber-300 hover:bg-amber-50/20 transition-all cursor-pointer group">
                    <input type="checkbox" 
                           name="is_suggested" 
                           value="1" 
                           {{ old('is_suggested') ? 'checked' : '' }}
                           class="mt-0.5 w-4 h-4 rounded text-amber-600 focus:ring-amber-500 border-slate-300">
                    <div>
                        <span class="text-xs font-bold text-slate-900 group-hover:text-amber-900 flex items-center gap-1">
                            <span>⭐ Marca Sugerida (Destacada)</span>
                        </span>
                        <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                            Aparecerá en la parte superior y como acceso rápido en los formularios de creación de productos.
                        </p>
                    </div>
                </label>

                <!-- Verificada Checkbox Card -->
                <label class="flex items-start gap-3 p-4 rounded-2xl border border-slate-200 hover:border-emerald-300 hover:bg-emerald-50/20 transition-all cursor-pointer group">
                    <input type="checkbox" 
                           name="verified" 
                           value="1" 
                           {{ old('verified', '1') == '1' ? 'checked' : '' }}
                           class="mt-0.5 w-4 h-4 rounded text-emerald-600 focus:ring-emerald-500 border-slate-300">
                    <div>
                        <span class="text-xs font-bold text-slate-900 group-hover:text-emerald-900 flex items-center gap-1">
                            <span>🛡️ Marca Oficial Verificada</span>
                        </span>
                        <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                            Muestra el sello de autenticidad oficial en las fichas y filtros de la tienda.
                        </p>
                    </div>
                </label>

            </div>

        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.brands.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer">
                <span class="material-symbols-outlined text-[16px]">save</span>
                <span>Guardar Marca</span>
            </button>
        </div>

    </form>

</div>

@push('scripts')
<script>
    function generarSlugAutomatico(texto) {
        const slugInput = document.getElementById('slug');
        if (!slugInput) return;
        const slug = (texto || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9 -]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
        slugInput.value = slug;
    }

    function previsualizarLogo(input) {
        const file = input.files[0];
        const placeholder = document.getElementById('preview-placeholder');
        const img = document.getElementById('preview-img');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
                img.classList.remove('hidden');
                placeholder.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        } else {
            img.src = '';
            img.classList.add('hidden');
            placeholder.classList.remove('hidden');
        }
    }
</script>
@endpush

@endsection
