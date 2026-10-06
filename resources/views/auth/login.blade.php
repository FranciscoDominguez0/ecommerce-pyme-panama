<x-guest-layout>
    <x-slot name="title">Iniciar Sesión - PayMe Panamá</x-slot>

    <!-- Main Content Canvas: Inspired by Reference with Brand Colors -->
    <main class="w-full max-w-4xl lg:max-w-5xl my-auto px-2 sm:px-4">
        <div class="glass-card relative rounded-3xl sm:rounded-[36px] shadow-xl border border-slate-200/90 overflow-hidden flex flex-col lg:flex-row items-center justify-between p-4 sm:p-6 lg:p-10 min-h-[580px]" style="background: linear-gradient(135deg, #f0fdf4 0%, #f8fafc 50%, #ecfdf5 100%) !important;">
            
            <!-- Ambient Fluid Organic Waves (Background of Left Side - Estilo claro) -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden">
                <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-emerald-200/40 blur-3xl"></div>
                <div class="absolute bottom-[-10%] left-[-5%] w-80 h-80 rounded-full bg-teal-200/35 blur-3xl"></div>
                <div class="absolute top-1/2 left-1/4 w-72 h-72 rounded-full bg-emerald-100/50 blur-2xl"></div>
                <!-- Formas fluidas orgánicas en SVG (tonos suaves y elegantes sobre fondo claro) -->
                <svg class="absolute inset-0 w-full h-full opacity-70 hidden lg:block animate-swap-text" viewBox="0 0 900 650" fill="none" preserveAspectRatio="none">
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

            <!-- Left Side: Clean Bold Statement con logo en la esquina izquierda -->
            <div class="w-full lg:w-1/2 p-4 sm:p-6 lg:p-10 text-slate-800 z-10 flex flex-col justify-between self-stretch animate-swap-text">
                <!-- Logo en la esquina izquierda superior -->
                <div class="flex items-center gap-2.5 mb-6">
                    <x-application-logo :boxed="false" size="default" class="w-8 h-8" />
                    <span class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">PayMe <span class="text-emerald-700">Panamá</span></span>
                </div>

                <div class="max-w-md my-auto py-6">
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight leading-snug">
                        PayMe Panamá: <span class="text-emerald-700">Tu plataforma de compras</span> y tecnología comercial.
                    </h2>
                </div>
            </div>

            <!-- Right Side: Floating White Card with Form Content -->
            <div class="w-full lg:w-1/2 flex justify-center z-10 mt-4 lg:mt-0 animate-swap-card">
                <div class="w-full max-w-[420px] bg-white rounded-2xl sm:rounded-3xl shadow-2xl p-6 sm:p-8 flex flex-col justify-between border border-slate-100">
                    
                    <!-- Header inside card (sin logo duplicado) -->
                    <div class="flex flex-col items-start mb-5 text-left">
                        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                            Bienvenido de nuevo
                        </h1>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Ingresa tus datos para continuar
                        </p>
                    </div>

                    <!-- Session Status Alert -->
                    @if (session('status'))
                        <div class="mb-4 bg-emerald-50 text-emerald-800 border border-emerald-200 p-3 rounded-xl flex items-start gap-2 text-xs">
                            <span class="material-symbols-outlined shrink-0 text-emerald-600 text-base" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            <div class="flex-1 font-medium">
                                {{ session('status') }}
                            </div>
                        </div>
                    @endif

                    <!-- Error Alert -->
                    <div class="{{ $errors->any() ? '' : 'hidden' }} mb-4 bg-red-50 text-red-700 p-3 rounded-xl flex items-start gap-2 border border-red-200 text-xs" id="error-alert">
                        <span class="material-symbols-outlined shrink-0 text-red-600 text-base mt-0.5" style="font-variation-settings: 'FILL' 1;">error</span>
                        <div class="flex-1">
                            <p class="font-semibold mb-0.5">Error de autenticación</p>
                            <p class="opacity-95 message-text">
                                {{ $errors->first('cf-turnstile-response') ?? $errors->first('email') ?? $errors->first('password') ?? 'Las credenciales proporcionadas no son válidas.' }}
                            </p>
                        </div>
                        <button class="text-red-400 hover:text-red-600" onclick="document.getElementById('error-alert').classList.add('hidden')" type="button">
                            <span class="material-symbols-outlined text-sm">close</span>
                        </button>
                    </div>

                    <!-- Login Form (Email & Password First) -->
                    <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-3" id="login-form" x-data="{ isSubmitting: false }" @submit.prevent="isSubmitting = true; submitLogin($event, () => isSubmitting = false)">
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
                                       placeholder="nombre@empresa.com"
                                       required
                                       autofocus
                                       autocomplete="username"
                                       type="email">
                            </div>
                            @error('email')
                                <p class="text-[11px] text-red-600 font-medium mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password Field -->
                        <div class="flex flex-col gap-1">
                            <div class="flex justify-between items-center">
                                <label class="text-xs font-semibold text-slate-700" for="password">
                                    Contraseña
                                </label>
                            </div>
                            <div class="relative flex items-center bg-slate-50/70 hover:bg-slate-50 border @error('password') border-red-400 ring-2 ring-red-500/10 @else border-slate-200 focus-within:border-emerald-600 focus-within:ring-2 focus-within:ring-emerald-500/10 @enderror rounded-xl transition-all">
                                <span class="material-symbols-outlined absolute left-3 text-slate-400 pointer-events-none text-lg">lock</span>
                                <input class="w-full bg-transparent border-none py-2.5 pl-10 pr-10 text-slate-900 placeholder:text-slate-400 focus:ring-0 text-xs sm:text-sm"
                                       id="password"
                                       name="password"
                                       placeholder="••••••••"
                                       required
                                       autocomplete="current-password"
                                       type="password">
                                <button class="absolute right-3 text-slate-400 hover:text-slate-600 focus:outline-none p-0.5 cursor-pointer"
                                        onclick="togglePassword()"
                                        type="button"
                                        aria-label="Mostrar u ocultar contraseña">
                                    <span class="material-symbols-outlined text-lg" id="visibility-icon">visibility_off</span>
                                </button>
                            </div>
                            @error('password')
                                <p class="text-[11px] text-red-600 font-medium mt-0.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Remember Me & Forgot Password (en una misma línea como en la referencia) -->
                        <div class="flex items-center justify-between pt-0.5">
                            <label class="inline-flex items-center cursor-pointer select-none">
                                <input id="remember_me"
                                       type="checkbox"
                                       class="w-3.5 h-3.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500/20 focus:ring-offset-0 transition-colors"
                                       name="remember"
                                       {{ old('remember') ? 'checked' : '' }}>
                                <span class="ml-2 text-xs text-slate-500">Recordarme en este dispositivo</span>
                            </label>
                            @if (Route::has('password.request'))
                                <a class="text-xs text-emerald-600 hover:text-emerald-700 hover:underline transition-colors font-medium" href="{{ route('password.request') }}">
                                    ¿Olvidaste tu contraseña?
                                </a>
                            @endif
                        </div>

                        <!-- Submit Button -->
                        <button class="w-full mt-1 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] disabled:opacity-75 disabled:cursor-wait text-white font-semibold text-xs sm:text-sm py-2.5 px-4 rounded-xl shadow-xs hover:shadow-md transition-all flex justify-center items-center gap-1.5 group cursor-pointer"
                                type="submit"
                                :disabled="isSubmitting">
                            
                            <!-- Estado Normal -->
                            <span x-show="!isSubmitting" class="flex items-center gap-1.5">
                                <span>Entrar a mi cuenta</span>
                                <span class="material-symbols-outlined text-base group-hover:translate-x-0.5 transition-transform">arrow_forward</span>
                            </span>

                            <!-- Estado Cargando -->
                            <span x-show="isSubmitting" class="flex items-center gap-1.5" style="display: none;">
                                <span class="material-symbols-outlined text-base animate-spin">progress_activity</span>
                                <span>Verificando...</span>
                            </span>
                        </button>

                        <!-- Desafío Cloudflare Turnstile -->
                        <div class="mt-1 flex flex-col items-center justify-center min-h-[65px]">
                            <div id="turnstile-container" class="cf-turnstile flex justify-center" data-sitekey="{{ config('services.turnstile.key') }}" data-theme="light"></div>
                            @error('cf-turnstile-response')
                                <p class="text-xs text-red-600 font-medium mt-1 text-center">{{ $message }}</p>
                            @enderror
                        </div>
                    </form>

                    <!-- Divider -->
                    <div class="my-3 flex items-center gap-3">
                        <div class="flex-1 h-px bg-slate-100"></div>
                        <span class="text-[11px] text-slate-400 font-medium">o continuar con</span>
                        <div class="flex-1 h-px bg-slate-100"></div>
                    </div>

                    <!-- Google Login (Alternativa secundaria) -->
                    <a href="{{ route('auth.google') }}" onclick="openGoogleSignIn(event, this.href)" class="w-full flex items-center justify-center gap-2.5 bg-white hover:bg-slate-50 border border-slate-200 hover:border-slate-300 text-slate-700 font-medium text-xs sm:text-sm py-2 px-4 rounded-xl shadow-xs transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                        <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        <span>Continuar con Google</span>
                    </a>

                    <!-- Register Footer Link -->
                    <div class="mt-3 text-center border-t border-slate-100 pt-3">
                        <p class="text-xs text-slate-500">
                            ¿No tienes una cuenta?
                            @if (Route::has('register'))
                                <a class="font-semibold text-emerald-600 hover:text-emerald-700 hover:underline ml-1" href="{{ route('register') }}" wire:navigate>
                                    Regístrate
                                </a>
                            @else
                                <a class="font-semibold text-emerald-600 hover:text-emerald-700 hover:underline ml-1" href="/register" wire:navigate>
                                    Regístrate
                                </a>
                            @endif
                        </p>
                    </div>

                    <!-- Mini Footer en la tarjeta blanca (como en la referencia: Privacy Policy | Copyright) -->
                    <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                        <a href="{{ route('terminos') }}" class="hover:text-slate-600 transition-colors">Términos y Privacidad</a>
                        <span>&copy; {{ date('Y') }} PayMe Panamá</span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Cloudflare Turnstile API -->
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?onload=onloadTurnstileCallback" async defer></script>

    <script>
        let errorTimeoutId = null;
        let turnstileWidgetId = null;

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const visibilityIcon = document.getElementById('visibility-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                visibilityIcon.textContent = 'visibility';
            } else {
                passwordInput.type = 'password';
                visibilityIcon.textContent = 'visibility_off';
            }
        }

        // Renderizado e inicialización de Cloudflare Turnstile
        function initTurnstile() {
            const container = document.getElementById('turnstile-container');
            if (!container || !window.turnstile) return;

            if (container.dataset.inited === 'true') return;

            try {
                container.innerHTML = '';
                turnstileWidgetId = window.turnstile.render(container, {
                    sitekey: '{{ config('services.turnstile.key') }}',
                    theme: 'light',
                });
                container.dataset.inited = 'true';
            } catch (err) {
                container.dataset.inited = 'true';
            }
        }

        window.onloadTurnstileCallback = function() {
            initTurnstile();
        };

        if (window.turnstile) {
            initTurnstile();
        }

        document.addEventListener('livewire:navigated', () => {
            const container = document.getElementById('turnstile-container');
            if (container) {
                container.dataset.inited = 'false';
                initTurnstile();
            }
        });

        async function submitLogin(e, resetLoading) {
            const form = e.target;
            const errorAlert = document.getElementById('error-alert');
            
            const formData = new FormData(form);
            const turnstileToken = formData.get('cf-turnstile-response');

            // Validar que el desafío esté resuelto si está configurada la llave
            if (!turnstileToken && '{{ config('services.turnstile.key') }}') {
                resetLoading();
                if (errorAlert) {
                    errorAlert.classList.remove('hidden');
                    errorAlert.querySelector('p.opacity-95').textContent = 'Por favor, complete el desafío de seguridad antes de continuar.';
                    if (errorTimeoutId) {
                        clearTimeout(errorTimeoutId);
                    }
                    errorTimeoutId = setTimeout(() => {
                        errorAlert.classList.add('hidden');
                    }, 5000);
                }
                return;
            }
            
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    
                    if (data.is2fa) {
                        window.location.href = data.redirect;
                        return;
                    }
                    
                    window.location.href = data.redirect;

                } else if (response.status === 422) {
                    const data = await response.json();
                    resetLoading();

                    // Resetear el captcha de Cloudflare en caso de fallo para permitir un nuevo intento
                    if (window.turnstile) {
                        try {
                            if (turnstileWidgetId !== null) {
                                window.turnstile.reset(turnstileWidgetId);
                            } else {
                                window.turnstile.reset();
                            }
                        } catch (err) {}
                    }
                    
                    if (errorAlert) {
                        errorAlert.classList.remove('hidden');
                        const errorMsg = (data.errors && data.errors['cf-turnstile-response'] ? data.errors['cf-turnstile-response'][0] : null)
                            || (data.errors && data.errors['email'] ? data.errors['email'][0] : null)
                            || data.message
                            || 'Las credenciales proporcionadas no son válidas.';
                        errorAlert.querySelector('p.opacity-95').textContent = errorMsg;
                        document.getElementById('password').value = '';
                        
                        if (errorTimeoutId) {
                            clearTimeout(errorTimeoutId);
                        }
                        
                        errorTimeoutId = setTimeout(() => {
                            errorAlert.classList.add('hidden');
                        }, 5000);
                        
                    } else {
                        window.location.reload();
                    }
                } else {
                    throw new Error('Server error');
                }
            } catch (error) {
                if (window.turnstile) {
                    try {
                        if (turnstileWidgetId !== null) {
                            window.turnstile.reset(turnstileWidgetId);
                        } else {
                            window.turnstile.reset();
                        }
                    } catch (err) {}
                }
                resetLoading();
                window.location.reload();
            }
        }

        // Si el error está visible al cargar la página (por recarga clásica), ocultarlo después de 5 seg
        const initialErrorAlert = document.getElementById('error-alert');
        if (initialErrorAlert && !initialErrorAlert.classList.contains('hidden')) {
            errorTimeoutId = setTimeout(() => {
                initialErrorAlert.classList.add('hidden');
            }, 5000);
        }
    </script>
</x-guest-layout>
