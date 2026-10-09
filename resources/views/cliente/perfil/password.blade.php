@extends('layouts.cliente')

@section('title', 'Cambiar Contraseña')

@section('content')
<x-cliente.perfil.layout active="password">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-bold text-primary">Seguridad de la Cuenta</h3>
            <p class="text-sm text-on-surface-variant mt-0.5">Administra tu contraseña y la autenticación de dos factores.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Tarjeta: Cambiar Contraseña -->
        <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-6 ambient-shadow">
            <h4 class="text-base font-bold text-primary flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-[20px]">password</span>
                Cambiar Contraseña
            </h4>
            <p class="text-xs text-on-surface-variant mb-6">Asegúrate de usar una contraseña larga y difícil de adivinar.</p>
            
            <form action="{{ route('cliente.perfil.password.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="space-y-4">
                    <div>
                        <label for="current_password" class="block text-xs font-semibold text-on-surface-variant mb-1.5">Contraseña Actual</label>
                        <input type="password" name="current_password" id="current_password"
                            class="block w-full rounded-xl border-outline-variant shadow-sm focus:border-primary focus:ring-1 focus:ring-primary sm:text-sm bg-white py-2.5 px-3 @error('current_password') border-error @enderror"
                            required autocomplete="current-password">
                        @error('current_password')
                            <p class="mt-1 text-xs text-error flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-semibold text-on-surface-variant mb-1.5">Nueva Contraseña</label>
                        <input type="password" name="password" id="password"
                            class="block w-full rounded-xl border-outline-variant shadow-sm focus:border-primary focus:ring-1 focus:ring-primary sm:text-sm bg-white py-2.5 px-3 @error('password') border-error @enderror"
                            required autocomplete="new-password">
                        @error('password')
                            <p class="mt-1 text-xs text-error flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold text-on-surface-variant mb-1.5">Confirmar Nueva Contraseña</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="block w-full rounded-xl border-outline-variant shadow-sm focus:border-primary focus:ring-1 focus:ring-primary sm:text-sm bg-white py-2.5 px-3"
                            required autocomplete="new-password">
                    </div>
                </div>

                <div class="flex justify-end mt-6 pt-5 border-t border-outline-variant/40">
                    <button type="submit"
                        class="px-6 py-2.5 rounded-xl bg-primary text-on-primary text-xs font-bold uppercase tracking-wider hover:bg-primary/90 transition-all shadow-sm hover:shadow-md flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px]">save</span>
                        Guardar Contraseña
                    </button>
                </div>
            </form>
        </div>

        <!-- Tarjeta: 2FA -->
        <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-6 ambient-shadow h-fit flex flex-col">
            <h4 class="text-base font-bold text-primary flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-[20px]">shield_lock</span>
                Verificación en dos pasos
            </h4>
            <p class="text-xs text-on-surface-variant mb-6">
                Te enviaremos un código a tu correo cada vez que inicies sesión.
            </p>
            
            <div class="flex-1 flex flex-col">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 bg-surface-container/50 rounded-xl p-4 border border-outline-variant/50">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span class="text-xs font-semibold text-on-surface-variant">Estado:</span>
                            @if(auth()->user()->two_fa_habilitado)
                                <span class="inline-flex items-center gap-1.5 bg-emerald-100/80 text-emerald-700 px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Activada
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 bg-surface-variant text-on-surface-variant px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider border border-outline-variant">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                    Desactivada
                                </span>
                            @endif
                        </div>
                        <p class="text-[11px] text-on-surface-variant leading-relaxed max-w-xs">
                            @if(auth()->user()->two_fa_habilitado)
                                ¡Excelente! Tu cuenta está protegida.
                            @else
                                Actívala para mayor seguridad.
                            @endif
                        </p>
                    </div>
                    
                    <form action="{{ route('cliente.perfil.2fa.update') }}" method="POST" class="shrink-0 w-full sm:w-auto mt-2 sm:mt-0">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="two_fa_habilitado" value="{{ auth()->user()->two_fa_habilitado ? '0' : '1' }}">
                        @if(auth()->user()->two_fa_habilitado)
                            <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-xl border border-error/50 bg-error/10 text-error hover:bg-error/20 text-xs font-bold uppercase tracking-wider transition-all shadow-sm hover:shadow-md">
                                <span class="material-symbols-outlined text-[16px]">gpp_bad</span>
                                Desactivar
                            </button>
                        @else
                            <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-xl bg-primary text-on-primary hover:bg-primary/90 text-xs font-bold uppercase tracking-wider transition-all shadow-sm hover:shadow-md">
                                <span class="material-symbols-outlined text-[16px]">security</span>
                                Activar
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-cliente.perfil.layout>
@endsection
