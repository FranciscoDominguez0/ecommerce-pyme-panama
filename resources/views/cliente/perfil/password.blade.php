@extends('layouts.cliente')

@section('title', 'Cambiar Contraseña')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row gap-8">
        <x-cliente.perfil.sidebar active="password" />

        <div class="flex-1 min-w-0">
            <a href="{{ route('dashboard') }}"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-on-surface-variant hover:text-primary transition-colors mb-4">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                Volver al Dashboard
            </a>
            <div class="mb-6">
                <h1 class="text-xl sm:text-2xl font-bold text-primary">Cambiar Contraseña</h1>
                <p class="text-sm text-on-surface-variant mt-1">Asegúrate de usar una contraseña segura que no uses en otros sitios.</p>
            </div>

            <form action="{{ route('cliente.perfil.password.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="bg-white border border-outline-variant rounded-xl p-6 shadow-sm">
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

                <div class="flex justify-end gap-3">
                    <button type="submit"
                        class="px-6 py-2.5 rounded-lg bg-secondary text-on-secondary text-xs font-bold uppercase tracking-wider hover:bg-secondary-container hover:text-on-secondary-container transition-colors shadow-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px]">lock</span>
                        Actualizar Contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
