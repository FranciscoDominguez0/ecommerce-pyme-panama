@extends('layouts.admin')

@section('title', 'Configuración de Notificaciones')

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
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Configuración de Notificaciones</h1>
            <p class="text-sm text-slate-500 mt-1">Gestiona los avisos del sistema y a quién se envían.</p>
        </div>
    </div>

    <div class="border-b border-slate-200">
        <nav class="flex gap-8">
            <a href="{{ route('admin.configuracion.general') }}" class="text-slate-500 hover:text-emerald-600 transition-colors pb-3 px-1 text-sm">General</a>
            <a href="{{ route('admin.configuracion.pagos') }}" class="text-slate-500 hover:text-emerald-600 transition-colors pb-3 px-1 text-sm">Pagos</a>
            <a href="{{ route('admin.configuracion.impuestos') }}" class="text-slate-500 hover:text-emerald-600 transition-colors pb-3 px-1 text-sm">Impuestos</a>
            <a href="{{ route('admin.configuracion.notificaciones') }}" class="text-emerald-600 font-bold border-b-2 border-emerald-600 pb-3 px-1 text-sm font-semibold">Notificaciones</a>
        </nav>
    </div>

    <!-- Main Card -->
    <div class="card-elevated rounded-xl overflow-hidden mb-8">
        <div class="px-6 py-5 border-b border-slate-200 bg-white">
            <h3 class="text-lg font-semibold text-slate-900">Notificaciones de Inventario</h3>
            <p class="text-sm text-slate-500 mt-1">Avisos relacionados a los niveles de stock de tus productos.</p>
        </div>
        
        <form action="{{ route('admin.configuracion.notificaciones.guardar') }}" method="POST">
            @csrf
            @method('PUT')
            
            @php
                $activo = \App\Models\Configuracion::obtenerBool('notificaciones.stock.email.activo', false);
                $rolesSeleccionados = json_decode(\App\Models\Configuracion::obtener('notificaciones.stock.email.roles', '[]'), true) ?? [];
                $adicionales = \App\Models\Configuracion::obtener('notificaciones.stock.email.adicionales', '');
            @endphp
            
            <div class="p-6 flex flex-col gap-8">
                <!-- Section 1: Stock Mínimo -->
                <section>
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-base font-semibold text-slate-900">Alerta de Stock Mínimo por Correo</h4>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl">
                        <!-- Toggle -->
                        <div class="flex items-center justify-between gap-4 p-4 rounded-lg border border-slate-200 bg-white hover:shadow-sm transition-shadow h-full md:col-span-2 max-w-2xl">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-900 truncate">Enviar correo de aviso</p>
                                <p class="text-xs text-slate-500 mt-1 break-words whitespace-normal">Envia un correo cuando un producto alcance el umbral de stock mínimo (además de la campana en el panel).</p>
                            </div>
                            <div class="relative inline-block w-12 shrink-0 align-middle select-none transition duration-200 ease-in">
                                <input type="checkbox" name="stock_email_activo" id="stock_email_activo" value="1" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer border-slate-200 transition-all duration-300 z-10" {{ $activo ? 'checked' : '' }}/>
                                <label for="stock_email_activo" class="toggle-label block overflow-hidden h-6 rounded-full bg-slate-200 cursor-pointer transition-colors duration-300"></label>
                            </div>
                        </div>

                        <!-- Roles -->
                        <div class="p-4 rounded-lg border border-slate-200 bg-white hover:shadow-sm transition-shadow h-full">
                            <label class="block text-sm font-medium text-slate-900 mb-2">Enviar a los siguientes Roles</label>
                            <p class="text-xs text-slate-500 mb-3">Todos los usuarios que tengan estos roles recibirán el correo.</p>
                            
                            <div class="space-y-2">
                                @foreach($roles as $rol)
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="stock_email_roles[]" value="{{ $rol->name }}" class="form-checkbox h-4 w-4 text-emerald-600 border-slate-300 rounded focus:ring-emerald-500" {{ in_array($rol->name, $rolesSeleccionados) ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-slate-700">{{ ucfirst(str_replace('_', ' ', $rol->name)) }}</span>
                                    </label><br>
                                @endforeach
                            </div>
                        </div>

                        <!-- Emails Adicionales -->
                        <div class="p-4 rounded-lg border border-slate-200 bg-white hover:shadow-sm transition-shadow h-full">
                            <label for="stock_email_adicionales" class="block text-sm font-medium text-slate-900 mb-2">Correos Adicionales</label>
                            <p class="text-xs text-slate-500 mb-3">Notificar a personas sin cuenta (separar con comas).</p>
                            
                            <div class="relative">
                                <textarea id="stock_email_adicionales" name="stock_email_adicionales" rows="3" class="block w-full rounded-md border-slate-300 py-2 px-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500" placeholder="ejemplo@correo.com, jefe@tienda.com">{{ old('stock_email_adicionales', $adicionales) }}</textarea>
                            </div>
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
