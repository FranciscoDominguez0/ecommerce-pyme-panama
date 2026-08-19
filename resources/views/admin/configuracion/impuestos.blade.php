@extends('layouts.admin')

@section('title', 'Configuración de Impuestos')

@push('styles')
<style>
    .toggle-checkbox:checked {
        right: 0;
        border-color: #059669;
    }
    .toggle-checkbox:checked + .toggle-label {
        background-color: #059669;
    }
</style>
@endpush

@section('content')
<div class="space-y-6 font-sans">
    <!-- Page Header & Tabs -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Configuración de Impuestos</h1>
            <p class="text-sm text-slate-500 mt-1">Gestiona las tasas impositivas y cómo se muestran en la tienda.</p>
        </div>
    </div>

    <div class="border-b border-slate-200">
        <nav class="flex gap-8">
            <a href="{{ route('admin.configuracion.general') }}" class="text-slate-500 hover:text-emerald-600 transition-colors pb-3 px-1 text-sm">General</a>
            <a href="{{ route('admin.configuracion.pagos') }}" class="text-slate-500 hover:text-emerald-600 transition-colors pb-3 px-1 text-sm">Pagos</a>
            <a href="{{ route('admin.configuracion.impuestos') }}" class="text-emerald-600 font-bold border-b-2 border-emerald-600 pb-3 px-1 text-sm font-semibold">Impuestos</a>
        </nav>
    </div>

    <!-- Main Card -->
    <div class="card-elevated rounded-xl overflow-hidden mb-8">
        <div class="px-6 py-5 border-b border-slate-200 bg-white">
            <h3 class="text-lg font-semibold text-slate-900">Configuración de Impuestos</h3>
            <p class="text-sm text-slate-500 mt-1">Gestiona las tasas impositivas y cómo se muestran en la tienda.</p>
        </div>
        
        <form action="{{ route('admin.configuracion.impuestos.guardar') }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="p-6 flex flex-col gap-8">
                <!-- Section 1: Impuestos Generales -->
                <section>
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-base font-semibold text-slate-900">Impuesto General (ITBMS)</h4>
                    </div>
                    
                    <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-lg p-4 mb-6 text-sm flex gap-3">
                        <span class="material-symbols-outlined text-blue-500">info</span>
                        <p><strong>Nota importante:</strong> Los cambios en la tasa de impuesto solo aplicarán a <strong>nuevas facturas</strong> generadas a partir de ahora. Las facturas ya emitidas mantendrán la tasa que tenían en el momento de su emisión.</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-2xl">
                        <!-- Toggle de ITBMS Activo -->
                        <div class="flex items-center justify-between gap-4 p-4 rounded-lg border border-slate-200 bg-white hover:shadow-sm transition-shadow h-full">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-900 truncate">Aplicar ITBMS en compras</p>
                                <p class="text-xs text-slate-500 mt-1 break-words whitespace-normal">Habilita el cálculo del impuesto en el carrito y facturación.</p>
                            </div>
                            <div class="relative inline-block w-12 shrink-0 align-middle select-none transition duration-200 ease-in">
                                <input type="checkbox" name="itbms_activo" id="itbms_activo" value="1" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer border-slate-200 transition-all duration-300 z-10" {{ $itbmsActivo ? 'checked' : '' }}/>
                                <label for="itbms_activo" class="toggle-label block overflow-hidden h-6 rounded-full bg-slate-200 cursor-pointer transition-colors duration-300"></label>
                            </div>
                        </div>

                        <!-- Tasa de ITBMS -->
                        <div class="p-4 rounded-lg border border-slate-200 bg-white hover:shadow-sm transition-shadow h-full">
                            <label for="itbms_tasa" class="block text-sm font-medium text-slate-900 mb-2">Tasa de ITBMS (%)</label>
                            <div class="relative">
                                <input type="number" id="itbms_tasa" name="itbms_tasa" value="{{ old('itbms_tasa', $itbmsTasa) }}" step="0.01" min="0" max="100" class="block w-full rounded-md border-slate-300 py-2 pl-4 pr-10 text-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500" required>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-500">%</div>
                            </div>
                            @error('itbms_tasa') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </section>
            </div>
            
            <!-- Footer Actions -->
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-4 mt-auto rounded-b-xl">
                <a href="{{ route('admin.configuracion.general') }}" class="px-5 py-2.5 rounded-lg text-slate-700 text-sm font-medium hover:bg-slate-200 transition-colors border border-transparent">Cancelar</a>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700 transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Ya no es necesario JS para el toggle, ahora es puro CSS como en pagos.
</script>
@endpush
