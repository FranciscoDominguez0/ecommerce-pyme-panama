@extends('layouts.admin')

@section('title', 'Registrar Entrada de Inventario')

@section('breadcrumbs')
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <a href="{{ route('admin.inventario.index') }}" class="font-medium text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:text-slate-300 truncate">Inventario</a>
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <span class="font-bold text-slate-900 dark:text-white truncate">Registrar Entrada</span>
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center gap-3 pb-4 border-b border-slate-200 dark:border-gray-700/80">
        <a href="{{ route('admin.inventario.index') }}"
           class="p-1.5 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:bg-transparent hover:text-slate-700 dark:text-slate-300 transition-colors shrink-0">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
        </a>
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Registrar Entrada de Inventario</h2>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium mt-0.5">Agrega unidades al stock existente.</p>
        </div>
    </div>

    {{-- Error --}}
    @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 font-medium">
            <span class="material-symbols-outlined text-[18px] text-red-500">error</span>
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form Card --}}
    <div class="card-elevated rounded-xl p-6 sm:p-8">
        <form method="POST" action="{{ route('admin.inventario.entrada') }}" id="form-entrada">
            @csrf

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

            {{-- Amount + Reason --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                <div>
                    <label for="cantidad" class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                        Cantidad a agregar <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="cantidad" name="cantidad" min="1" value="{{ old('cantidad', 1) }}" required
                           class="w-full px-4 py-2.5 border {{ $errors->has('cantidad') ? 'border-red-400' : 'border-slate-200 dark:border-gray-700' }} rounded-xl bg-white dark:bg-[#121415] text-sm text-slate-800 dark:text-gray-100 font-bold focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-all tabular-nums">
                </div>
                <div>
                    <label for="motivo" class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                        Motivo <span class="text-red-500">*</span>
                    </label>
                    <select id="motivo" name="motivo" required
                            class="w-full px-4 py-2.5 border {{ $errors->has('motivo') ? 'border-red-400' : 'border-slate-200 dark:border-gray-700' }} rounded-xl bg-white dark:bg-[#121415] text-sm text-slate-800 dark:text-gray-100 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-all">
                        <option value="Compra / Adquisición"  {{ old('motivo') === 'Compra / Adquisición'  ? 'selected' : '' }}>Compra / Adquisición</option>
                        <option value="Reposición de stock"   {{ old('motivo') === 'Reposición de stock'   ? 'selected' : '' }}>Reposición de stock</option>
                        <option value="Devolución de cliente" {{ old('motivo') === 'Devolución de cliente' ? 'selected' : '' }}>Devolución de cliente</option>
                        <option value="Corrección de inventario" {{ old('motivo') === 'Corrección de inventario' ? 'selected' : '' }}>Corrección de inventario</option>
                        <option value="Transferencia desde bodega" {{ old('motivo') === 'Transferencia desde bodega' ? 'selected' : '' }}>Transferencia desde bodega</option>
                    </select>
                </div>
            </div>

            {{-- Optional fields --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                <div>
                    <label for="proveedor" class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                        Proveedor <span class="text-slate-400 font-normal">(opcional)</span>
                    </label>
                    <input type="text" id="proveedor" name="proveedor" value="{{ old('proveedor') }}"
                           placeholder="Nombre del proveedor…"
                           class="w-full px-4 py-2.5 border border-slate-200 dark:border-gray-700 rounded-xl bg-white dark:bg-[#121415] text-sm text-slate-700 dark:text-slate-300 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-all">
                </div>
                <div>
                    <label for="factura_proveedor" class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                        Factura / Remito <span class="text-slate-400 font-normal">(opcional)</span>
                    </label>
                    <input type="text" id="factura_proveedor" name="factura_proveedor" value="{{ old('factura_proveedor') }}"
                           placeholder="Nº de documento…"
                           class="w-full px-4 py-2.5 border border-slate-200 dark:border-gray-700 rounded-xl bg-white dark:bg-[#121415] text-sm text-slate-700 dark:text-slate-300 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-all">
                </div>
            </div>

            <div class="mb-5">
                <label for="notas" class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">
                    Notas <span class="text-slate-400 font-normal">(opcional)</span>
                </label>
                <textarea id="notas" name="notas" rows="3"
                          placeholder="Detalles adicionales sobre este movimiento…"
                          class="w-full px-4 py-2.5 border border-slate-200 dark:border-gray-700 rounded-xl bg-white dark:bg-[#121415] text-sm text-slate-700 dark:text-slate-300 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-all resize-none">{{ old('notas') }}</textarea>
            </div>

            {{-- Stock Summary Box --}}
            <div class="bg-slate-50 dark:bg-transparent border border-slate-200 dark:border-gray-700 rounded-xl p-5 mb-6">
                <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-4">Resumen del movimiento</p>
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex flex-col items-center sm:items-start text-center sm:text-left">
                        <span class="text-[11px] text-slate-400 font-medium mb-1">Stock actual</span>
                        <span id="resumen-stock-antes" class="text-2xl font-extrabold text-slate-700 dark:text-slate-300 tabular-nums">—</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-slate-300 text-[22px]">add</span>
                    </div>
                    <div class="flex flex-col items-center text-center bg-emerald-50 border border-emerald-200 rounded-xl px-6 py-3">
                        <span class="text-[11px] text-emerald-600 font-bold mb-1">Entrada</span>
                        <span id="resumen-cantidad" class="text-2xl font-extrabold text-emerald-600 tabular-nums">+0</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-slate-300 text-[22px]">arrow_forward</span>
                    </div>
                    <div class="flex flex-col items-center sm:items-end text-center sm:text-right">
                        <span class="text-[11px] text-slate-400 font-medium mb-1">Nuevo stock</span>
                        <span id="resumen-stock-nuevo" class="text-3xl font-extrabold text-slate-900 dark:text-white tabular-nums">—</span>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-100 dark:border-gray-700">
                <a href="{{ route('admin.inventario.index') }}"
                   class="px-5 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:text-slate-800 dark:text-gray-100 hover:bg-slate-100 dark:bg-transparent rounded-xl transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        class="flex items-center gap-2 px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-sm font-bold transition-all shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    Registrar Entrada
                </button>
            </div>
        </form>
    </div>

    {{-- Selector inteligente de productos y variantes para entrada --}}
    <x-inventario-selector-movimiento 
        tipo="entrada" 
        :productos="$productosDataJson" 
        titulo-modal="Seleccionar Producto" 
        subtitulo-modal="Busca y selecciona un producto para la entrada" 
    />
</div>
@endsection




