<x-guest-layout>
    <x-slot name="title">Iniciar Sesión - PayMe Panamá</x-slot>

    <!-- Main Content Canvas -->
    <main class="w-full max-w-md fade-in-up">
        <!-- Logo Header -->
        <div class="flex flex-col items-center mb-5">
            <x-application-logo :boxed="true" class="mb-2.5" />
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 text-center tracking-tight">
                Iniciar sesión
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 text-center mt-0.5">
                Bienvenido de nuevo a su portal de compras y finanzas.
            </p>
        </div>

        <!-- Login Card -->
        <div class="glass-card rounded-xl p-5 sm:p-6 w-full transition-all duration-200">
            <!-- Session Status Alert -->
            @if (session('status'))
                <div class="mb-4 bg-emerald-50 text-emerald-800 border border-emerald-200 p-3 rounded-lg flex items-start gap-2 text-xs">
                    <span class="material-symbols-outlined shrink-0 text-emerald-600 text-base" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                    <div class="flex-1 font-medium">
                        {{ session('status') }}
                    </div>
                </div>
            @endif

            <!-- Error Alert -->
            <div class="{{ $errors->any() ? '' : 'hidden' }} mb-4 bg-red-50 text-red-700 p-3 rounded-lg flex items-start gap-2 border border-red-200 text-xs" id="error-alert">
                <span class="material-symbols-outlined shrink-0 text-red-600 text-base mt-0.5" style="font-variation-settings: 'FILL' 1;">error</span>
                <div class="flex-1">
                    <p class="font-semibold mb-0.5">Error de autenticación</p>
                    <p class="opacity-95 message-text">
                        {{ $errors->first('email') ?? $errors->first('password') ?? 'Las credenciales proporcionadas no son válidas.' }}
                    </p>
                </div>
                <button class="text-red-400 hover:text-red-600" onclick="document.getElementById('error-alert').classList.add('hidden')" type="button">
                    <span class="material-symbols-outlined text-sm">close</span>
                </button>
            </div>

            <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-3.5" id="login-form" x-data="{ isSubmitting: false }" @submit.prevent="isSubmitting = true; submitLogin($event, () => isSubmitting = false)">
                @csrf

                <!-- Email Field -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-700" for="email">
                        Correo Electrónico
                    </label>
                    <div class="relative flex items-center bg-white border @error('email') border-red-500 input-error-ring @else border-gray-300 @enderror rounded-lg input-focus-ring">
                        <span class="material-symbols-outlined absolute left-2.5 text-gray-400 pointer-events-none text-lg">mail</span>
                        <input class="w-full bg-transparent border-none py-2 pl-9 pr-3 text-gray-900 placeholder:text-gray-400 focus:ring-0 rounded-lg text-xs sm:text-sm"
                               id="email"
                               name="email"
                               value="{{ old('email') }}"
                               placeholder="ejemplo@empresa.com"
                               required
                               autofocus
                               autocomplete="username"
                               type="email">
                    </div>
                    @error('email')
                        <p class="text-xs text-red-600 font-medium mt-0.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Field -->
                <div class="flex flex-col gap-1">
                    <div class="flex justify-between items-center">
                        <label class="text-xs font-medium text-gray-700" for="password">
                            Contraseña
                        </label>
                        @if (Route::has('password.request'))
                            <a class="text-xs text-secondary hover:underline transition-all" href="{{ route('password.request') }}">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>
                    <div class="relative flex items-center bg-white border @error('password') border-red-500 input-error-ring @else border-gray-300 @enderror rounded-lg input-focus-ring">
                        <span class="material-symbols-outlined absolute left-2.5 text-gray-400 pointer-events-none text-lg">lock</span>
                        <input class="w-full bg-transparent border-none py-2 pl-9 pr-9 text-gray-900 placeholder:text-gray-400 focus:ring-0 rounded-lg text-xs sm:text-sm"
                               id="password"
                               name="password"
                               placeholder="••••••••"
                               required
                               autocomplete="current-password"
                               type="password">
                        <button class="absolute right-2.5 text-gray-400 hover:text-gray-600 focus:outline-none flex items-center justify-center p-0.5 rounded"
                                onclick="togglePassword()"
                                type="button"
                                aria-label="Mostrar u ocultar contraseña">
                            <span class="material-symbols-outlined text-lg" id="visibility-icon">visibility_off</span>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-xs text-red-600 font-medium mt-0.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center justify-between mt-0.5">
                    <label class="inline-flex items-center cursor-pointer select-none">
                        <input id="remember_me"
                               type="checkbox"
                               class="w-3.5 h-3.5 rounded border-gray-300 text-secondary focus:ring-secondary focus:ring-offset-0 transition-colors"
                               name="remember"
                               {{ old('remember') ? 'checked' : '' }}>
                        <span class="ml-2 text-xs text-gray-600">Recordarme en este dispositivo</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button class="w-full mt-1.5 bg-primary-container hover:bg-primary-container/90 active:scale-[0.99] disabled:opacity-75 disabled:cursor-wait text-white font-semibold text-xs sm:text-sm py-2.5 px-4 rounded-full shadow-sm hover:shadow transition-all flex justify-center items-center gap-1.5 group"
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
            </form>

            <!-- Google Login -->
            <div class="mt-5 flex flex-col gap-4">
                <div class="flex items-center justify-center gap-3">
                    <div class="flex-1 h-px bg-gray-200"></div>
                    <span class="text-xs text-gray-500 font-medium">O continuar con</span>
                    <div class="flex-1 h-px bg-gray-200"></div>
                </div>
                
                <a href="{{ route('auth.google') }}" class="w-full flex items-center justify-center gap-3 bg-white border border-gray-300 hover:bg-gray-50 hover:shadow-md active:bg-gray-100 text-gray-700 font-semibold text-sm py-2.5 px-4 rounded-full shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    Continuar con Google
                </a>
            </div>

            <!-- Register Link -->
            <div class="mt-4 text-center border-t border-gray-100 pt-3">
                <p class="text-xs text-gray-600">
                    ¿No tienes una cuenta?
                    @if (Route::has('register'))
                        <a class="font-semibold text-secondary hover:underline ml-1" href="{{ route('register') }}">
                            Regístrate
                        </a>
                    @else
                        <a class="font-semibold text-secondary hover:underline ml-1" href="/register">
                            Regístrate
                        </a>
                    @endif
                </p>
            </div>
        </div>

        <!-- Secure Banner -->
        <x-secure-badge />
    </main>

    <script>
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

        async function submitLogin(e, resetLoading) {
            const form = e.target;
            const errorAlert = document.getElementById('error-alert');
            
            const formData = new FormData(form);
            
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
                    
                    if (errorAlert) {
                        errorAlert.classList.remove('hidden');
                        errorAlert.querySelector('p.opacity-95').textContent = data.message || 'Las credenciales proporcionadas no son válidas.';
                    } else {
                        window.location.reload();
                    }
                } else {
                    throw new Error('Server error');
                }
            } catch (error) {
                resetLoading();
                window.location.reload();
            }
        }

    </script>
</x-guest-layout>
