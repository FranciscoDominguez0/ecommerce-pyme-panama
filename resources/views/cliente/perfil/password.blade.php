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
        <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl overflow-hidden ambient-shadow h-fit flex flex-col">
            <div class="bg-gradient-to-r from-slate-900 to-slate-800 p-6 text-white relative">
                <!-- Abstract Glow -->
                <div class="absolute inset-0 overflow-hidden pointer-events-none">
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-emerald-500/20 rounded-full blur-2xl"></div>
                </div>

                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center backdrop-blur-sm border border-white/20 shadow-inner">
                            <span class="material-symbols-outlined text-[20px] text-emerald-400">shield_lock</span>
                        </div>
                        <h4 class="text-base font-bold tracking-tight">Autenticación de 2 Factores</h4>
                    </div>
                    <p class="text-xs text-slate-300 opacity-90 leading-relaxed max-w-sm">
                        Añade una capa extra de seguridad a tu cuenta requiriendo un código PIN de 4 dígitos enviado a tu correo al iniciar sesión.
                    </p>
                </div>
            </div>
            
            <div class="p-6 bg-white dark:bg-[#181a1b] flex-1">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-sm font-bold text-slate-900 dark:text-white">Estado actual:</span>
                            @if(auth()->user()->two_fa_habilitado)
                                <span class="inline-flex items-center gap-1.5 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider border border-emerald-200 dark:border-emerald-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Activado
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider border border-slate-200 dark:border-slate-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                    Desactivado
                                </span>
                            @endif
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed max-w-xs">
                            @if(auth()->user()->two_fa_habilitado)
                                Tu cuenta está protegida. Se requerirá un PIN cada vez que inicies sesión de forma segura.
                            @else
                                Protege tu cuenta de accesos no autorizados. Altamente recomendado para mantener tus datos seguros.
                            @endif
                        </p>
                    </div>
                    
                    <form action="{{ route('cliente.perfil.2fa.update') }}" method="POST" class="shrink-0 w-full sm:w-auto">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="two_fa_habilitado" value="{{ auth()->user()->two_fa_habilitado ? '0' : '1' }}">
                        @if(auth()->user()->two_fa_habilitado)
                            <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-xl border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-500/20 text-[11px] font-bold uppercase tracking-wider transition-all">
                                <span class="material-symbols-outlined text-[16px]">gpp_bad</span>
                                Desactivar 2FA
                            </button>
                        @else
                            <button type="submit" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm hover:shadow-md hover:-translate-y-0.5 text-[11px] font-bold uppercase tracking-wider transition-all">
                                <span class="material-symbols-outlined text-[16px]">security</span>
                                Activar 2FA
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-cliente.perfil.layout>
@endsection
