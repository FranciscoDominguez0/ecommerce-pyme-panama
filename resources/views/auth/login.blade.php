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
            @if ($errors->any())
                <div class="mb-4 bg-red-50 text-red-700 p-3 rounded-lg flex items-start gap-2 border border-red-200 text-xs" id="error-alert">
                    <span class="material-symbols-outlined shrink-0 text-red-600 text-base mt-0.5" style="font-variation-settings: 'FILL' 1;">error</span>
                    <div class="flex-1">
                        <p class="font-semibold mb-0.5">Error de autenticación</p>
                        <p class="opacity-95">
                            {{ $errors->first('email') ?? $errors->first('password') ?? 'Las credenciales proporcionadas no son válidas.' }}
                        </p>
                    </div>
                    <button class="text-red-400 hover:text-red-600" onclick="document.getElementById('error-alert').classList.add('hidden')" type="button">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-3.5" id="login-form" x-data="{ isSubmitting: false }" @submit="isSubmitting = true">
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
                <button class="w-full mt-1.5 bg-primary-container hover:bg-primary-container/90 active:scale-[0.99] disabled:opacity-75 disabled:cursor-wait text-white font-semibold text-xs sm:text-sm py-2.5 px-4 rounded-lg shadow-sm hover:shadow transition-all flex justify-center items-center gap-1.5 group"
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

    <!-- Skeleton Transition Overlay -->
    <x-admin-skeleton :fullScreen="true" />

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

        // Interceptor de transición de Login a Skeleton
        document.addEventListener('DOMContentLoaded', () => {
            const loginForm = document.getElementById('login-form');
            if (loginForm) {
                loginForm.addEventListener('submit', (e) => {
                    setTimeout(() => {
                        // Ocultar login y mostrar esqueleto de la aplicación
                        const mainContent = document.querySelector('main.fade-in-up');
                        const skeleton = document.getElementById('global-admin-skeleton');
                        const bgGradients = document.querySelector('.fixed.inset-0.pointer-events-none');
                        
                        if (mainContent && skeleton) {
                            mainContent.classList.add('hidden');
                            if (bgGradients) bgGradients.classList.add('hidden');
                            skeleton.classList.remove('hidden');
                        }
                    }, 200);
                });
            }
        });
        
        // BFCache fix
        window.addEventListener('pageshow', (event) => {
            const mainContent = document.querySelector('main.fade-in-up');
            const skeleton = document.getElementById('global-admin-skeleton');
            const bgGradients = document.querySelector('.fixed.inset-0.pointer-events-none');
            
            if (mainContent && skeleton) {
                skeleton.classList.add('hidden');
                mainContent.classList.remove('hidden');
                if (bgGradients) bgGradients.classList.remove('hidden');
            }
        });
    </script>
</x-guest-layout>
