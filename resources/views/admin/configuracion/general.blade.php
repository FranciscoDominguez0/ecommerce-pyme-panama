@extends('layouts.admin')

@section('title', 'Configuración General')

@section('content')
<div class="space-y-6 font-sans">
    <!-- Page Header & Sub-nav -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Configuración General</h1>
            <p class="text-sm text-slate-500 mt-1">Administra los datos, pagos e impuestos globales de la tienda.</p>
        </div>
    </div>

    <div class="border-b border-slate-200">
        <nav class="flex gap-8">
            <a href="{{ route('admin.configuracion.general') }}" class="text-emerald-600 font-bold border-b-2 border-emerald-600 pb-3 px-1 text-sm font-semibold">General</a>
            <a href="{{ route('admin.configuracion.pagos') }}" class="text-slate-500 hover:text-emerald-600 transition-colors pb-3 px-1 text-sm">Pagos</a>
            <a href="{{ route('admin.configuracion.impuestos') }}" class="text-slate-500 hover:text-emerald-600 transition-colors pb-3 px-1 text-sm">Impuestos</a>
        </nav>
    </div>

    <!-- Settings Card -->
    <div class="card-elevated rounded-xl overflow-hidden mb-8">
        <div class="px-6 py-5 border-b border-slate-200 bg-white">
            <h3 class="text-lg font-semibold text-slate-900">Datos de la empresa</h3>
            <p class="text-sm text-slate-500 mt-1">Esta información aparecerá en facturas y comunicaciones</p>
        </div>
        
        <form action="{{ route('admin.configuracion.general.guardar') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="p-6 space-y-8">
                <!-- Logo Upload Section (utilizando Alpine.js para la preview real-time) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-3">Logo de la empresa</label>
                    <div class="flex items-start gap-6" x-data="{ 
                        previewUrl: '{{ isset($configuraciones['empresa.logo_ruta']) ? asset('storage/' . $configuraciones['empresa.logo_ruta']) : asset('images/placeholder-logo.png') }}',
                        fileChosen(event) {
                            if (event.target.files.length > 0) {
                                this.previewUrl = URL.createObjectURL(event.target.files[0]);
                            }
                        }
                    }">
                        <div class="w-32 h-32 rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 flex items-center justify-center overflow-hidden relative group cursor-pointer hover:border-emerald-500 transition-colors" @click="$refs.logoInput.click()">
                            <img :src="previewUrl" alt="Logo de la empresa" class="w-full h-full object-contain p-2">
                            <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <span class="material-symbols-outlined text-white">upload</span>
                            </div>
                            <input type="file" name="logo" x-ref="logoInput" class="hidden" accept="image/*" @change="fileChosen">
                        </div>
                        <div class="flex flex-col justify-center gap-3 h-32">
                            <button type="button" @click="$refs.logoInput.click()" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors w-fit shadow-sm">
                                Cambiar logo
                            </button>
                        </div>
                    </div>
                    @error('logo') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6">
                    <!-- Company Name -->
                    <div class="col-span-1 md:col-span-2 lg:col-span-1">
                        <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase tracking-wider" for="nombre">Nombre de la empresa</label>
                        <input type="text" id="nombre" name="nombre" value="{{ old('nombre', $configuraciones['empresa.nombre'] ?? '') }}" class="block w-full rounded-md border-slate-300 py-2 px-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500" required>
                        @error('nombre') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    
                    <!-- RUC -->
                    <div class="col-span-1 md:col-span-2 lg:col-span-1">
                        <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase tracking-wider" for="ruc">RUC / Aviso de operación</label>
                        <input type="text" id="ruc" name="ruc" value="{{ old('ruc', $configuraciones['empresa.ruc'] ?? '') }}" class="block w-full rounded-md border-slate-300 py-2 px-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500" required>
                        @error('ruc') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    
                    <!-- Phone -->
                    <div class="col-span-1 lg:col-span-1">
                        <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase tracking-wider" for="telefono">Teléfono de contacto</label>
                        <input type="tel" id="telefono" name="telefono" value="{{ old('telefono', $configuraciones['empresa.telefono'] ?? '') }}" class="block w-full rounded-md border-slate-300 py-2 px-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500">
                        @error('telefono') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    
                    <!-- Email -->
                    <div class="col-span-1 lg:col-span-1">
                        <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase tracking-wider" for="correo_contacto">Correo de contacto</label>
                        <input type="email" id="correo_contacto" name="correo_contacto" value="{{ old('correo_contacto', $configuraciones['empresa.correo_contacto'] ?? '') }}" class="block w-full rounded-md border-slate-300 py-2 px-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500">
                        @error('correo_contacto') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    
                    <!-- Address -->
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase tracking-wider" for="direccion">Dirección</label>
                        <textarea id="direccion" name="direccion" rows="3" class="block w-full rounded-md border-slate-300 py-2 px-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500 resize-y">{{ old('direccion', $configuraciones['empresa.direccion'] ?? '') }}</textarea>
                        @error('direccion') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            
            <!-- Card Footer Actions -->
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex flex-col sm:flex-row justify-between items-center gap-4">
                <span class="text-sm text-slate-500">Mantén esta información actualizada</span>
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition-colors shadow-sm w-full sm:w-auto">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
