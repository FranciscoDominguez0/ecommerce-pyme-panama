@extends('layouts.admin')

@section('title', 'Métodos de Pago')

@push('styles')
<style>
    .toggle-checkbox:checked {
        right: 0;
        border-color: #059669; /* emerald-600 */
    }
    .toggle-checkbox:checked + .toggle-label {
        background-color: #059669; /* emerald-600 */
    }
</style>
@endpush

@section('content')
<div class="space-y-6 font-sans">
    <!-- Page Header & Sub-nav -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Configuración de Pagos</h1>
            <p class="text-sm text-slate-500 mt-1">Activa o desactiva los métodos de pago disponibles.</p>
        </div>
    </div>

    <div class="border-b border-slate-200">
        <nav class="flex gap-8">
            <a href="{{ route('admin.configuracion.general') }}" class="text-slate-500 hover:text-emerald-600 transition-colors pb-3 px-1 text-sm">General</a>
            <a href="{{ route('admin.configuracion.pagos') }}" class="text-emerald-600 font-bold border-b-2 border-emerald-600 pb-3 px-1 text-sm font-semibold">Pagos</a>
            <a href="{{ route('admin.configuracion.impuestos') }}" class="text-slate-500 hover:text-emerald-600 transition-colors pb-3 px-1 text-sm">Impuestos</a>
            <a href="{{ route('admin.configuracion.notificaciones') }}" class="text-slate-500 hover:text-emerald-600 transition-colors pb-3 px-1 text-sm">Notificaciones</a>
        </nav>
    </div>

    <!-- Content Card -->
    <div class="card-elevated rounded-xl overflow-hidden mb-8">
        <div class="px-6 py-5 border-b border-slate-200 bg-white">
            <h3 class="text-lg font-semibold text-slate-900">Métodos de pago disponibles</h3>
            <p class="text-sm text-slate-500 mt-1">Activa o desactiva los métodos de pago que tus clientes podrán usar en el checkout.</p>
        </div>
        
        <form action="{{ route('admin.configuracion.pagos.guardar') }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="p-6 space-y-4">
                @foreach($metodos as $clave => $metodo)
                @php
                    $activo = isset($configuraciones["pagos.{$clave}.activo"]) && $configuraciones["pagos.{$clave}.activo"] === 'true';
                @endphp
                <div class="flex items-center justify-between p-4 border border-slate-200 rounded-lg bg-white hover:bg-slate-50 transition-colors group {{ !$activo ? 'opacity-75' : '' }}">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-slate-100 flex items-center justify-center {{ $activo ? 'text-emerald-600' : 'text-slate-400 group-hover:text-emerald-600' }} transition-colors p-1.5 overflow-hidden">
                            @if($clave === 'yappy')
                                <img src="{{ asset('images/pa-yappy.webp') }}" alt="Yappy" class="w-full h-full object-contain {{ !$activo ? 'grayscale opacity-60' : '' }}">
                            @else
                                <span class="material-symbols-outlined">{{ $metodo['icono'] }}</span>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-base font-semibold {{ $activo ? 'text-slate-900' : 'text-slate-600' }}">{{ $metodo['nombre'] }}</h3>
                            <p class="text-sm text-slate-500">{{ $metodo['descripcion'] }}</p>
                        </div>
                    </div>
                    <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
                        <input type="checkbox" name="metodos[{{ $clave }}]" id="toggle_{{ $clave }}" value="1" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer border-slate-200 transition-all duration-300 z-10" {{ $activo ? 'checked' : '' }}/>
                        <label for="toggle_{{ $clave }}" class="toggle-label block overflow-hidden h-6 rounded-full bg-slate-200 cursor-pointer transition-colors duration-300"></label>
                    </div>
                </div>
                @endforeach
            </div>
            
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
