@extends('layouts.cliente')

@section('title', 'Cambiar Contraseña')

@section('content')
<x-cliente.perfil.layout active="password">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-base font-bold text-primary">Cambiar Contraseña</h3>
            <p class="text-xs text-on-surface-variant mt-0.5">Actualiza la contraseña de tu cuenta.</p>
        </div>
    </div>

    <form action="{{ route('cliente.perfil.password.update') }}" method="POST" class="mt-5">
        @csrf
        @method('PUT')

        <div class="border-t border-outline-variant/20 pt-5">
            <div class="grid grid-cols-1 gap-5 max-w-lg">
                <div>
                    <label for="current_password" class="block text-xs font-semibold text-on-surface-variant mb-1.5">Contraseña Actual</label>
                    <input type="password" name="current_password" id="current_password"
                        class="block w-full rounded-lg border-outline-variant shadow-sm focus:border-secondary focus:ring-secondary sm:text-sm bg-white py-2.5 px-3 @error('current_password') border-error @enderror"
                        required autocomplete="current-password">
                    @error('current_password')
                        <p class="mt-1 text-xs text-error flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-on-surface-variant mb-1.5">Nueva Contraseña</label>
                    <input type="password" name="password" id="password"
                        class="block w-full rounded-lg border-outline-variant shadow-sm focus:border-secondary focus:ring-secondary sm:text-sm bg-white py-2.5 px-3 @error('password') border-error @enderror"
                        required autocomplete="new-password">
                    @error('password')
                        <p class="mt-1 text-xs text-error flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-semibold text-on-surface-variant mb-1.5">Confirmar Nueva Contraseña</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        class="block w-full rounded-lg border-outline-variant shadow-sm focus:border-secondary focus:ring-secondary sm:text-sm bg-white py-2.5 px-3"
                        required autocomplete="new-password">
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6 pt-5 border-t border-outline-variant/20">
            <button type="submit"
                class="px-6 py-2.5 rounded-lg bg-primary text-on-primary text-xs font-bold uppercase tracking-wider hover:bg-primary-container transition-colors shadow-sm flex items-center gap-2">
                <span class="material-symbols-outlined text-[16px]">lock</span>
                Guardar cambios
            </button>
        </div>
    </form>
</x-cliente.perfil.layout>
@endsection
