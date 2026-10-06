<x-guest-layout>
    <x-slot name="title">Recuperar Contraseña - PayMe Panamá</x-slot>

    <!-- Main Content Canvas: Mismo estilo que Login/Registro -->
    <main class="w-full max-w-4xl lg:max-w-5xl fade-in-up my-auto px-2 sm:px-4">
        <div class="glass-card relative rounded-3xl sm:rounded-[36px] shadow-xl border border-slate-200/90 overflow-hidden flex flex-col lg:flex-row items-center justify-between p-4 sm:p-6 lg:p-10 min-h-[580px]" style="background: linear-gradient(135deg, #f0fdf4 0%, #f8fafc 50%, #ecfdf5 100%) !important;">
            
            <!-- Ambient Fluid Organic Waves -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden">
                <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-emerald-200/40 blur-3xl"></div>
                <div class="absolute bottom-[-10%] left-[-5%] w-80 h-80 rounded-full bg-teal-200/35 blur-3xl"></div>
                <div class="absolute top-1/2 left-1/4 w-72 h-72 rounded-full bg-emerald-100/50 blur-2xl"></div>
                <!-- Formas fluidas orgánicas en SVG -->
                <svg class="absolute inset-0 w-full h-full opacity-70 hidden lg:block" viewBox="0 0 900 650" fill="none" preserveAspectRatio="none">
                    <path d="M-80,-20 C240,60 190,380 -60,650 L-100,650 L-100,-20 Z" fill="url(#waveGrad1)" />
                    <path d="M-40,140 C340,240 310,480 80,650 L-80,650 Z" fill="url(#waveGrad2)" opacity="0.75"/>
                    <path d="M-20,320 C260,400 220,560 180,650 L-40,650 Z" fill="url(#waveGrad3)" opacity="0.6"/>
                    <defs>
                        <linearGradient id="waveGrad1" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#34d399" stop-opacity="0.35"/>
                            <stop offset="100%" stop-color="#059669" stop-opacity="0.20"/>
                        </linearGradient>
                        <linearGradient id="waveGrad2" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#6ee7b7" stop-opacity="0.50"/>
                            <stop offset="100%" stop-color="#10b981" stop-opacity="0.25"/>
                        </linearGradient>
                        <linearGradient id="waveGrad3" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#a7f3d0" stop-opacity="0.65"/>
                            <stop offset="100%" stop-color="#34d399" stop-opacity="0.30"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>

            <!-- Left Side: Clean Bold Statement con logo -->
            <div class="w-full lg:w-1/2 p-4 sm:p-6 lg:p-10 text-slate-800 z-10 flex flex-col justify-between self-stretch">
                <!-- Logo en la esquina izquierda superior -->
                <div class="flex items-center gap-2.5 mb-6">
                    <x-application-logo :boxed="false" size="default" class="w-8 h-8" />
                    <span class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">PayMe <span class="text-emerald-700">Panamá</span></span>
                </div>

                <div class="max-w-md my-auto py-6">
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight leading-snug">
                        ¿Olvidaste tu <span class="text-emerald-700">contraseña?</span> Te ayudamos a recuperarla.
                    </h2>
                </div>
            </div>

            <!-- Right Side: Floating White Card with Form Content -->
            <div class="w-full lg:w-1/2 flex justify-center z-10 mt-4 lg:mt-0 auth-card-slide-up">
                <div class="w-full max-w-[420px] bg-white rounded-2xl sm:rounded-3xl shadow-xl p-6 sm:p-8 flex flex-col justify-between border border-slate-100">
                    
                    <!-- Header inside card -->
                    <div class="flex flex-col items-start mb-5 text-left">
                        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                            Recuperar Contraseña
                        </h1>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Ingresa tu correo y te enviaremos un enlace para restablecer el acceso a tu cuenta.
                        </p>
                    </div>

                    <!-- Session Status Alert -->
                    @if (session('status'))
                        <div class="mb-4 bg-emerald-50 text-emerald-800 border border-emerald-200 p-3 rounded-xl flex items-start gap-2 text-xs">
                            <span class="material-symbols-outlined shrink-0 text-emerald-600 text-base mt-0.5" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            <div class="flex-1 font-medium leading-relaxed">
                                {{ session('status') }}
                            </div>
                        </div>
                    @endif

                    <!-- Error Alert -->
                    @if ($errors->any())
                        <div class="mb-4 bg-red-50 text-red-700 p-3 rounded-xl flex items-start gap-2 border border-red-200 text-xs" id="error-alert">
                            <span class="material-symbols-outlined shrink-0 text-red-600 text-base mt-0.5" style="font-variation-settings: 'FILL' 1;">error</span>
                            <div class="flex-1">
                                <p class="font-semibold mb-0.5">Error al procesar</p>
                                <p class="opacity-95">
                                    {{ $errors->first('email') ?? 'Ocurrió un error al enviar el enlace de recuperación.' }}
                                </p>
                            </div>
                            <button class="text-red-400 hover:text-red-600" onclick="document.getElementById('error-alert').classList.add('hidden')" type="button">
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-4">
                        @csrf

                        <!-- Email Field -->
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-slate-700" for="email">
                                Correo Electrónico
                            </label>
                            <div class="relative flex items-center bg-slate-50/70 hover:bg-slate-50 border @error('email') border-red-400 ring-2 ring-red-500/10 @else border-slate-200 focus-within:border-emerald-600 focus-within:ring-2 focus-within:ring-emerald-500/10 @enderror rounded-xl transition-all">
                                <span class="material-symbols-outlined absolute left-3 text-slate-400 pointer-events-none text-lg">mail</span>
                                <input class="w-full bg-transparent border-none py-2.5 pl-10 pr-3 text-slate-900 placeholder:text-slate-400 focus:ring-0 text-xs sm:text-sm"
                                       id="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       placeholder="tu@correo.com"
                                       required
                                       autofocus
                                       autocomplete="email"
                                       type="email">
                            </div>
                            @error('email')
                                <p class="text-[11px] text-red-600 font-medium mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <button class="w-full mt-2 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] text-white font-semibold text-xs sm:text-sm py-2.5 px-4 rounded-xl shadow-xs hover:shadow-md transition-all flex justify-center items-center gap-1.5 group"
                                type="submit"
                                onclick="if(this.form.checkValidity()) { this.disabled=true; this.innerHTML='<span class=\'material-symbols-outlined animate-spin\'>progress_activity</span> Enviando...'; this.form.submit(); }">
                            <span>Enviar enlace</span>
                            <span class="material-symbols-outlined text-base group-hover:translate-x-0.5 transition-transform">send</span>
                        </button>
                    </form>

                    <!-- Back to Login Link -->
                    <div class="mt-4 text-center border-t border-slate-100 pt-3">
                        <a class="inline-flex items-center justify-center gap-1.5 text-xs text-slate-500 hover:text-emerald-600 font-medium transition-colors"
                           href="{{ route('login') }}" wire:navigate>
                            <span class="material-symbols-outlined text-sm">arrow_back</span>
                            <span>Volver al Inicio de Sesión</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</x-guest-layout>
