@extends('layouts.cliente')

@section('title', 'Cambiar Contraseña')

@section('content')
<x-cliente.perfil.layout active="password">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-base font-bold text-primary">Seguridad de la Cuenta</h3>
            <p class="text-xs text-on-surface-variant mt-0.5">Administra tu contraseña y la autenticación de dos factores.</p>
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
                Actualizar Contraseña
            </button>
        </div>
    </form>

    <!-- Sección de Autenticación de 2 Factores (2FA) -->
    <div class="mt-10 border-t border-outline-variant/20 pt-8 mb-4">
        <h3 class="text-base font-bold text-primary flex items-center gap-2 mb-1.5">
            <span class="material-symbols-outlined text-[18px]">verified_user</span>
            Autenticación de 2 Factores (2FA)
        </h3>
        <p class="text-sm text-on-surface-variant mb-6 max-w-2xl">Protege tu cuenta requiriendo un código adicional de seguridad cada vez que inicies sesión.</p>

        <form action="{{ route('cliente.perfil.2fa.update') }}" method="POST" class="bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-5 shadow-sm inline-block">
            @csrf
            @method('PUT')
            <div class="flex items-center gap-5">
                <div>
                    <p class="text-sm font-semibold text-primary mb-1">Estado actual: 
                        @if(auth()->user()->two_fa_habilitado)
                            <span class="text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded text-xs font-bold border border-emerald-200">Activado</span>
                        @else
                            <span class="text-slate-500 bg-slate-100 px-2.5 py-0.5 rounded text-xs font-bold border border-slate-200">Desactivado</span>
                        @endif
                    </p>
                    <p class="text-xs text-on-surface-variant">Si lo activas, te enviaremos un correo con un código PIN de 4 dígitos al iniciar sesión.</p>
                </div>
                <input type="hidden" name="two_fa_habilitado" value="{{ auth()->user()->two_fa_habilitado ? '0' : '1' }}">
                <button type="submit" class="px-5 py-2.5 rounded-lg font-bold text-xs uppercase tracking-wider shadow-sm transition-colors border {{ auth()->user()->two_fa_habilitado ? 'bg-white border-error text-error hover:bg-error/5' : 'bg-primary text-on-primary border-primary hover:bg-primary-container' }}">
                    {{ auth()->user()->two_fa_habilitado ? 'Desactivar 2FA' : 'Activar 2FA' }}
                </button>
            </div>
        </form>
    </div>
</x-cliente.perfil.layout>
@endsection
