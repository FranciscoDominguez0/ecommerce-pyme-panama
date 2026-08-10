@extends('layouts.cliente')

@section('title', 'Configuración de Perfil')

@section('content')
<x-cliente.perfil.layout active="configuracion">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-base font-bold text-primary">Información personal</h3>
            <p class="text-xs text-on-surface-variant mt-0.5">Administra y actualiza los datos asociados a tu cuenta.</p>
        </div>
        <button type="button" id="btn-toggle-edit"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-outline-variant text-xs font-semibold text-on-surface-variant hover:text-primary hover:bg-surface-container-low transition-colors shrink-0">
            <span class="material-symbols-outlined text-[14px]" id="toggle-icon">edit</span>
            <span id="toggle-text">Editar información</span>
        </button>
    </div>

    <div class="border-t border-outline-variant/20 pt-5">

        {{-- Display Mode --}}
        <div id="display-mode">
            <div class="divide-y divide-outline-variant/20">
                <div class="flex items-center py-3 gap-4">
                    <span class="material-symbols-outlined text-on-surface-variant text-[18px] shrink-0">cake</span>
                    <span class="text-sm text-on-surface-variant w-40 shrink-0">Fecha de nacimiento</span>
                    <span class="text-sm font-semibold text-primary">{{ $usuario->fecha_nacimiento ? $usuario->fecha_nacimiento->format('d/m/Y') : '—' }}</span>
                </div>
                <div class="flex items-center py-3 gap-4">
                    <span class="material-symbols-outlined text-on-surface-variant text-[18px] shrink-0">location_on</span>
                    <span class="text-sm text-on-surface-variant w-40 shrink-0">Dirección</span>
                    <span class="text-sm font-semibold text-primary truncate">
                        @if($direccionPredeterminada)
                            {{ $direccionPredeterminada->direccion_exacta }}
                        @else
                            —
                        @endif
                    </span>
                </div>
                <div class="flex items-center py-3 gap-4">
                    <span class="material-symbols-outlined text-on-surface-variant text-[18px] shrink-0">mail</span>
                    <span class="text-sm text-on-surface-variant w-40 shrink-0">Correo electrónico</span>
                    <span class="text-sm font-semibold text-primary truncate">{{ $usuario->email }}</span>
                </div>
                <div class="flex items-center py-3 gap-4">
                    <span class="material-symbols-outlined text-on-surface-variant text-[18px] shrink-0">call</span>
                    <span class="text-sm text-on-surface-variant w-40 shrink-0">Teléfono</span>
                    <span class="text-sm font-semibold text-primary">{{ $usuario->telefono ? '+507 ' . $usuario->telefono : '—' }}</span>
                </div>
            </div>
        </div>

        {{-- Edit Mode --}}
        <form id="datos-form" action="{{ route('cliente.perfil.datos.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div id="edit-mode" class="hidden">
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
                    <div>
                        <label for="fecha_nacimiento" class="block text-xs font-semibold text-on-surface-variant mb-1.5">Fecha de Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento"
                            value="{{ old('fecha_nacimiento', $usuario->fecha_nacimiento ? $usuario->fecha_nacimiento->format('Y-m-d') : '') }}"
                            class="block w-full rounded-lg border-outline-variant shadow-sm focus:border-secondary focus:ring-secondary sm:text-sm bg-white py-2.5 px-3 @error('fecha_nacimiento') border-error @enderror">
                        @error('fecha_nacimiento')
                            <p class="mt-1 text-xs text-error flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div id="action-buttons" class="hidden justify-end gap-3 mt-6 pt-5 border-t border-outline-variant/20">
                <button type="button" id="btn-cancelar-edit"
                    class="px-6 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant text-xs font-semibold hover:bg-surface-container-low transition-colors">
                    Cancelar
                </button>
                <button type="submit"
                    class="px-6 py-2.5 rounded-lg bg-primary text-on-primary text-xs font-bold uppercase tracking-wider hover:bg-primary-container transition-colors shadow-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-[16px]">save</span>
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</x-cliente.perfil.layout>

@push('scripts')
<script>
    (function () {
        const btnToggle = document.getElementById('btn-toggle-edit');
        if (btnToggle && !btnToggle.dataset.inited) {
            btnToggle.dataset.inited = '1';

            const displayMode = document.getElementById('display-mode');
            const editMode = document.getElementById('edit-mode');
            const toggleIcon = document.getElementById('toggle-icon');
            const toggleText = document.getElementById('toggle-text');
            const actionButtons = document.getElementById('action-buttons');
            const btnCancelarEdit = document.getElementById('btn-cancelar-edit');
            let isEditing = false;

            btnToggle.addEventListener('click', function () {
                isEditing = !isEditing;
                if (isEditing) {
                    displayMode.classList.add('hidden');
                    editMode.classList.remove('hidden');
                    actionButtons.classList.remove('hidden');
                    actionButtons.classList.add('flex');
                    toggleIcon.textContent = 'visibility';
                    toggleText.textContent = 'Vista previa';
                } else {
                    displayMode.classList.remove('hidden');
                    editMode.classList.add('hidden');
                    actionButtons.classList.add('hidden');
                    actionButtons.classList.remove('flex');
                    toggleIcon.textContent = 'edit';
                    toggleText.textContent = 'Editar información';
                }
            });

            btnCancelarEdit.addEventListener('click', function () {
                isEditing = false;
                displayMode.classList.remove('hidden');
                editMode.classList.add('hidden');
                actionButtons.classList.add('hidden');
                actionButtons.classList.remove('flex');
                toggleIcon.textContent = 'edit';
                toggleText.textContent = 'Editar información';
            });
        }
    })();
</script>
@endpush
@endsection
