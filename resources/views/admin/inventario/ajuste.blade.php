@extends('layouts.admin')

@section('title', 'Ajuste Manual de Inventario')

@section('breadcrumbs')
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <a href="{{ route('admin.inventario.index') }}" class="font-medium text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:text-slate-300 truncate">Inventario</a>
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <span class="font-bold text-slate-900 dark:text-white truncate">Ajuste Manual</span>
@endsection

@section('content')
<div class="space-y-6 w-full min-w-0 max-w-full">

    {{-- Encabezado de página --}}
    <div class="flex items-center gap-3 pb-4 border-b border-slate-200 dark:border-gray-700/80">
        <a href="{{ route('admin.inventario.index') }}"
           class="p-1.5 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:bg-transparent hover:text-slate-700 dark:text-slate-300 transition-colors shrink-0">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
        </a>
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Ajustar Inventario Manualmente</h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium mt-0.5">
                Corrige la cantidad disponible para reflejar discrepancias físicas, daños o conteos de inventario.
            </p>
        </div>
    </div>

    {{-- Errores de validación del formulario --}}
    @if($errors->any())
        <div class="px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Disposición de dos columnas estilo Bento --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        {{-- Columna izquierda: Formulario principal --}}
        <div class="lg:col-span-7 card-elevated rounded-xl p-6 sm:p-8">
            <form method="POST" action="{{ route('admin.inventario.ajuste') }}" id="form-ajuste">
                @csrf

                <div class="mb-6">
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white mb-1">Seleccionar ítem</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Elige el producto y la variante (si aplica) a ajustar.</p>
                </div>

                {{-- Product + Variant --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                    <div>
                        <label for="producto_id" class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                            Producto <span class="text-red-500">*</span>
                        </label>
                        <x-product-selector id="producto_id" name="producto_id" :value="old('producto_id', request('producto_id'))" :error="$errors->has('producto_id')" />
                    </div>
                    <div>
                        <label for="variante_id" class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                            Variante <span class="text-slate-400 font-normal">(si aplica)</span>
                        </label>
                        <select id="variante_id" name="variante_id" disabled
                                class="w-full px-4 py-2.5 border border-slate-200 dark:border-gray-700 rounded-xl bg-white dark:bg-[#121415] text-sm text-slate-800 dark:text-gray-100 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            <option value="">Selecciona un producto primero</option>
                        </select>
                    </div>
                </div>

                <hr class="border-slate-100 dark:border-gray-700 my-5">

                <div class="mb-6">
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white mb-1">Cantidad correcta</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Introduce el stock real según el conteo físico.</p>
                </div>

                {{-- New stock --}}
                <div class="mb-5">
                    <label for="nuevo_stock" class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                        Nuevo stock (cantidad real) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="nuevo_stock" name="nuevo_stock" min="0"
                           value="{{ old('nuevo_stock', '') }}" required
                           placeholder="0"
                           class="w-full px-4 py-3 border {{ $errors->has('nuevo_stock') ? 'border-red-400' : 'border-slate-200 dark:border-gray-700' }} rounded-xl bg-white dark:bg-[#121415] text-2xl text-slate-800 dark:text-gray-100 font-extrabold focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-all tabular-nums">
                </div>

                {{-- Reason --}}
                <div class="mb-5">
                    <label for="motivo" class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                        Motivo del ajuste <span class="text-red-500">*</span>
                    </label>
                    <select id="motivo" name="motivo" required
                            class="w-full px-4 py-2.5 border {{ $errors->has('motivo') ? 'border-red-400' : 'border-slate-200 dark:border-gray-700' }} rounded-xl bg-white dark:bg-[#121415] text-sm text-slate-800 dark:text-gray-100 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-all">
                        <option value="Conteo físico de inventario" {{ old('motivo') === 'Conteo físico de inventario' ? 'selected' : '' }}>Conteo físico de inventario</option>
                        <option value="Corrección de error de sistema" {{ old('motivo') === 'Corrección de error de sistema' ? 'selected' : '' }}>Corrección de error de sistema</option>
                        <option value="Merma / Caducidad / Daño"    {{ old('motivo') === 'Merma / Caducidad / Daño'    ? 'selected' : '' }}>Merma / Caducidad / Daño</option>
                        <option value="Robo / Pérdida"              {{ old('motivo') === 'Robo / Pérdida'              ? 'selected' : '' }}>Robo / Pérdida</option>
                        <option value="Discrepancia de proveedores" {{ old('motivo') === 'Discrepancia de proveedores' ? 'selected' : '' }}>Discrepancia de proveedores</option>
                    </select>
                </div>

                {{-- Notas internas del ajuste --}}
                <div class="mb-6">
                    <label for="notas" class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                        Notas internas <span class="text-slate-400 font-normal">(opcional)</span>
                    </label>
                    <textarea id="notas" name="notas" rows="3"
                              placeholder="Describe la razón del ajuste, quién lo autorizó, etc…"
                              class="w-full px-4 py-2.5 border border-slate-200 dark:border-gray-700 rounded-xl bg-white dark:bg-[#121415] text-sm text-slate-700 dark:text-slate-300 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-all resize-none">{{ old('notas') }}</textarea>
                </div>

                {{-- Acciones y confirmación --}}
                <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-100 dark:border-gray-700">
                    <a href="{{ route('admin.inventario.index') }}"
                       class="px-5 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:text-gray-100 hover:bg-slate-100 dark:bg-transparent rounded-xl transition-colors">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="flex items-center gap-2 px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-bold transition-all shadow-sm">
                        <span class="material-symbols-outlined text-[18px]">sync_alt</span>
                        Aplicar Ajuste
                    </button>
                </div>
            </form>
        </div>

        {{-- Columna derecha: Panel de vista previa en tiempo real --}}
        <div class="lg:col-span-5 space-y-4">

            {{-- Tarjeta de vista previa --}}
            <div class="card-elevated rounded-xl p-6 space-y-5">
                <div class="flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-[18px] text-amber-500">sync_alt</span>
                    <h3 class="text-sm font-extrabold text-slate-800 dark:text-gray-100">Vista previa del ajuste</h3>
                </div>

                {{-- Stock previo --}}
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Stock actual</p>
                    <p id="preview-antes" class="text-3xl font-extrabold text-slate-700 dark:text-slate-300 tabular-nums">—</p>
                </div>

                {{-- Flecha e indicador de diferencia --}}
                <div id="preview-diff-wrap" class="hidden">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-slate-300 text-[20px]">arrow_downward</span>
                        <span id="preview-diff"
                              class="text-sm font-bold px-2.5 py-1 rounded-lg tabular-nums">
                        </span>
                    </div>
                </div>

                {{-- Nuevo stock resultante --}}
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nuevo stock</p>
                    <p id="preview-nuevo" class="text-4xl font-extrabold tabular-nums text-slate-900 dark:text-white">—</p>
                </div>

                {{-- Divisor e información de stock mínimo --}}
                <div class="pt-3 border-t border-slate-100 dark:border-gray-700 flex items-center justify-between text-xs">
                    <span class="text-slate-400">Stock mínimo configurado</span>
                    <span id="preview-minimo" class="font-bold text-slate-600 dark:text-slate-400">—</span>
                </div>
            </div>

            {{-- Advertencia condicional si el stock queda bajo el mínimo --}}
            <div id="alerta-stock-bajo" class="hidden bg-red-50 border border-red-200 rounded-xl p-4">
                <div class="flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-red-500 text-[18px] shrink-0 mt-0.5">warning</span>
                    <div class="text-xs text-red-700 space-y-1">
                        <p class="font-bold">Nuevo stock por debajo del mínimo</p>
                        <p>El valor ingresado está por debajo del stock mínimo configurado para este producto.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
    
    {{-- Selector inteligente de productos y variantes para ajuste --}}
    <x-inventario-selector-movimiento 
        tipo="ajuste" 
        :productos="$productosDataJson" 
        titulo-modal="Seleccionar Producto" 
        subtitulo-modal="Busca y selecciona un producto para ajustar stock" 
    />
</div>
@endsection






