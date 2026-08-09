@extends('layouts.cliente')

@section('title', 'Configuración de Perfil')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row gap-8">
        <x-cliente.perfil.sidebar active="configuracion" />

        <div class="flex-1 min-w-0">
            <a href="{{ route('dashboard') }}"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-on-surface-variant hover:text-primary transition-colors mb-4">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                Volver al Dashboard
            </a>

            <div class="mb-6">
                <h1 class="text-xl sm:text-2xl font-bold text-primary">Configuración de la Cuenta</h1>
                <p class="text-sm text-on-surface-variant mt-1">Administra tu información personal, preferencias de seguridad para mantener tu cuenta protegida y actualizada.</p>
            </div>

            <form action="{{ route('cliente.perfil.datos.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Profile Summary Card --}}
                <div class="bg-white border border-outline-variant rounded-xl p-6 md:p-8 shadow-sm">
                    <div class="flex flex-col sm:flex-row items-start gap-6">
                        <div class="relative shrink-0 group cursor-pointer" id="photo-wrapper">
                            <div class="w-24 h-24 rounded-full overflow-hidden border-2 border-outline-variant bg-surface-container-low flex items-center justify-center">
                                @if($usuario->foto_perfil_url)
                                    <img id="avatar-preview-img" src="{{ $usuario->foto_perfil_url }}" alt="{{ $usuario->nombre_completo }}" class="w-full h-full object-cover">
                                @else
                                    <span id="avatar-preview-initials" class="text-3xl font-bold text-on-surface-variant">{{ $usuario->iniciales }}</span>
                                    <img id="avatar-preview-img" src="" alt="" class="w-full h-full object-cover hidden">
                                @endif
                                <div class="absolute inset-0 rounded-full bg-black/0 group-hover:bg-black/30 transition-colors flex items-center justify-center">
                                    <span class="material-symbols-outlined text-white text-2xl opacity-0 group-hover:opacity-100 transition-opacity">photo_camera</span>
                                </div>
                            </div>
                            <button type="button" id="btn-eliminar-foto"
                                class="absolute -top-1 -right-1 w-6 h-6 rounded-full bg-error text-on-error items-center justify-center shadow-sm hover:bg-red-700 transition-colors {{ $usuario->foto_perfil_url ? 'flex' : 'hidden' }}"
                                title="Eliminar foto">
                                <span class="material-symbols-outlined text-[14px]">close</span>
                            </button>
                        </div>

                        <div class="flex-1 space-y-2 pt-1">
                            <h2 class="text-lg font-bold text-primary">{{ $usuario->nombre_completo }}</h2>
                            <div class="flex items-center gap-1.5 text-sm text-on-surface-variant">
                                <span class="material-symbols-outlined text-[16px]">mail</span>
                                <span>{{ $usuario->email }}</span>
                            </div>
                            @if($usuario->email_verified_at)
                            <div class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-secondary/10 text-secondary text-[11px] font-bold tracking-wider uppercase">
                                <span class="material-symbols-outlined text-[14px]">verified</span>
                                Cuenta Verificada
                            </div>
                            @endif
                        </div>
                    </div>

                    <input type="file" name="foto_perfil" id="foto_perfil" accept="image/png,image/jpeg,image/webp" class="hidden">
                    <input type="hidden" name="eliminar_foto" id="eliminar_foto" value="0">
                    <span id="file-name" class="block text-xs text-outline mt-3 hidden"></span>
                    @error('foto_perfil')
                        <p class="mt-2 text-xs text-error flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
                    @enderror
                </div>

                {{-- Personal Information Form --}}
                <div class="bg-white border border-outline-variant rounded-xl p-6 md:p-8 shadow-sm">
                    <h2 class="text-base font-bold text-primary mb-1">Información Personal</h2>
                    <p class="text-xs text-on-surface-variant mb-6">Actualiza los datos básicos de tu perfil.</p>

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
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-on-surface-variant">
                                    <span class="material-symbols-outlined text-[16px]">mail</span>
                                </span>
                                <input type="email" name="email" id="email" value="{{ old('email', $usuario->email) }}"
                                    class="block w-full rounded-lg border-outline-variant shadow-sm focus:border-secondary focus:ring-secondary sm:text-sm bg-white py-2.5 pl-9 pr-3 @error('email') border-error @enderror"
                                    required>
                            </div>
                            <p class="text-[10px] text-outline mt-1">Principal</p>
                            @error('email')
                                <p class="mt-1 text-xs text-error flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="telefono" class="block text-xs font-semibold text-on-surface-variant mb-1.5">Teléfono</label>
                            <div class="flex rounded-lg border border-outline-variant shadow-sm focus-within:border-secondary focus-within:ring-1 focus-within:ring-secondary overflow-hidden bg-white @error('telefono') border-error @enderror">
                                <span class="inline-flex items-center px-3 bg-surface-container-low text-on-surface-variant text-sm font-medium border-r border-outline-variant">+507</span>
                                <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $usuario->telefono) }}"
                                    class="block w-full border-0 focus:ring-0 sm:text-sm bg-white py-2.5 px-3"
                                    placeholder="6000-0000">
                            </div>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const inputFoto = document.getElementById('foto_perfil');
        const fileName = document.getElementById('file-name');
        const previewImg = document.getElementById('avatar-preview-img');
        const previewInitials = document.getElementById('avatar-preview-initials');
        const btnEliminar = document.getElementById('btn-eliminar-foto');
        const eliminarFotoInput = document.getElementById('eliminar_foto');
        const photoWrapper = document.getElementById('photo-wrapper');

        photoWrapper.addEventListener('click', function (e) {
            if (e.target.closest('#btn-eliminar-foto')) return;
            inputFoto.click();
        });

        inputFoto.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                fileName.textContent = file.name;
                fileName.classList.remove('hidden');
                btnEliminar.classList.remove('hidden');
                btnEliminar.style.display = 'flex';
                eliminarFotoInput.value = '0';

                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewImg.classList.remove('hidden');
                    if (previewInitials) previewInitials.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            }
        });

        btnEliminar.addEventListener('click', function (e) {
            e.stopPropagation();
            inputFoto.value = '';
            fileName.textContent = '';
            fileName.classList.add('hidden');
            eliminarFotoInput.value = '1';

            previewImg.src = '';
            previewImg.classList.add('hidden');
            if (previewInitials) previewInitials.classList.remove('hidden');
            btnEliminar.classList.add('hidden');
            btnEliminar.style.display = 'none';
        });
    });
</script>
@endpush
@endsection
