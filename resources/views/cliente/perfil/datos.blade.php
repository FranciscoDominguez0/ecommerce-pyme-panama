@extends('layouts.cliente')

@section('title', 'Configuración de Perfil')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row gap-8">
        <x-cliente.perfil.sidebar active="configuracion" />

        <div class="flex-1 min-w-0">
            <div class="mb-6">
                <h1 class="text-xl sm:text-2xl font-bold text-primary">Configuración de Perfil</h1>
                <p class="text-sm text-on-surface-variant mt-1">Actualiza tu información personal y de contacto.</p>
            </div>

            <form action="{{ route('cliente.perfil.datos.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="bg-white border border-outline-variant rounded-xl p-6 shadow-sm">
                    <h2 class="text-base font-bold text-primary mb-1">Información Personal</h2>
                    <p class="text-xs text-on-surface-variant mb-6">Estos datos se usarán en tus pedidos y facturas.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="nombre" class="block text-xs font-semibold text-on-surface-variant mb-1.5">Nombre</label>
                            <input type="text" name="nombre" id="nombre" value="{{ old('nombre', $usuario->nombre) }}"
                                class="block w-full rounded-lg border-outline-variant shadow-sm focus:border-secondary focus:ring-secondary sm:text-sm bg-white py-2.5 px-3 @error('nombre') border-error @enderror"
                                required>
                            @error('nombre')
                                <p class="mt-1 text-xs text-error flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="apellido" class="block text-xs font-semibold text-on-surface-variant mb-1.5">Apellido</label>
                            <input type="text" name="apellido" id="apellido" value="{{ old('apellido', $usuario->apellido) }}"
                                class="block w-full rounded-lg border-outline-variant shadow-sm focus:border-secondary focus:ring-secondary sm:text-sm bg-white py-2.5 px-3 @error('apellido') border-error @enderror"
                                required>
                            @error('apellido')
                                <p class="mt-1 text-xs text-error flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-xs font-semibold text-on-surface-variant mb-1.5">Correo Electrónico</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $usuario->email) }}"
                                class="block w-full rounded-lg border-outline-variant shadow-sm focus:border-secondary focus:ring-secondary sm:text-sm bg-white py-2.5 px-3 @error('email') border-error @enderror"
                                required>
                            @error('email')
                                <p class="mt-1 text-xs text-error flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="telefono" class="block text-xs font-semibold text-on-surface-variant mb-1.5">Teléfono</label>
                            <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $usuario->telefono) }}"
                                class="block w-full rounded-lg border-outline-variant shadow-sm focus:border-secondary focus:ring-secondary sm:text-sm bg-white py-2.5 px-3 @error('telefono') border-error @enderror"
                                placeholder="+507 6000-0000">
                            @error('telefono')
                                <p class="mt-1 text-xs text-error flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('dashboard') }}"
                        class="px-6 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant text-xs font-semibold hover:bg-surface-container-low transition-colors">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-6 py-2.5 rounded-lg bg-primary text-on-primary text-xs font-bold uppercase tracking-wider hover:bg-primary-container transition-colors shadow-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px]">save</span>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
