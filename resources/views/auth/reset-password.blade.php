<x-guest-layout>
    <x-slot name="title">Restablecer Contraseña - PayMe Panamá</x-slot>

    <!-- Main Content Canvas -->
    <main class="w-full max-w-md fade-in-up">
        <!-- Logo Header -->
        <div class="flex flex-col items-center mb-5">
            <x-application-logo :boxed="true" class="mb-2.5" />
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 text-center tracking-tight">
                Restablecer Contraseña
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 text-center mt-0.5">
                Crea una nueva contraseña segura para tu cuenta.
            </p>
        </div>

        <!-- Reset Password Card -->
        <div class="glass-card rounded-xl p-5 sm:p-6 w-full transition-all duration-200">
            <!-- Error Alert -->
            @if ($errors->any())
                <div class="mb-4 bg-red-50 text-red-700 p-3 rounded-lg flex items-start gap-2 border border-red-200 text-xs" id="error-alert">
                    <span class="material-symbols-outlined shrink-0 text-red-600 text-base mt-0.5" style="font-variation-settings: 'FILL' 1;">error</span>
                    <div class="flex-1">
                        <p class="font-semibold mb-0.5">Atención</p>
                        <p class="opacity-95">
                            {{ $errors->first('email') ?? $errors->first('password') ?? 'Por favor verifique los datos ingresados.' }}
                        </p>
                    </div>
                    <button class="text-red-400 hover:text-red-600" onclick="document.getElementById('error-alert').classList.add('hidden')" type="button">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>
            @endif

            <form method="POST" action="{{ route('password.store') }}" class="flex flex-col gap-3.5" id="reset-form">
                @csrf

                <!-- Password Reset Token -->
                <input type="hidden" name="token" value="{{ $token ?? request()->route('token') }}">

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
                               value="{{ old('email', $email ?? request()->email) }}"
                               placeholder="tu@correo.com"
                               required
                               autofocus
                               autocomplete="email"
                               type="email">
                    </div>
                    @error('email')
                        <p class="text-xs text-red-600 font-medium mt-0.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- New Password Field -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-700" for="password">
                        Nueva Contraseña
                    </label>
                    <div class="relative flex items-center bg-white border @error('password') border-red-500 input-error-ring @else border-gray-300 @enderror rounded-lg input-focus-ring">
                        <span class="material-symbols-outlined absolute left-2.5 text-gray-400 pointer-events-none text-lg">lock</span>
                        <input class="w-full bg-transparent border-none py-2 pl-9 pr-9 text-gray-900 placeholder:text-gray-400 focus:ring-0 rounded-lg text-xs sm:text-sm"
                               id="password"
                               name="password"
                               placeholder="••••••••"
                               required
                               autocomplete="new-password"
                               type="password">
                        <button class="absolute right-2.5 text-gray-400 hover:text-gray-600 focus:outline-none flex items-center justify-center p-0.5 rounded"
                                onclick="toggleFieldPassword('password', 'new-pwd-visibility-icon')"
                                type="button"
                                aria-label="Mostrar u ocultar contraseña">
                            <span class="material-symbols-outlined text-lg" id="new-pwd-visibility-icon">visibility_off</span>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-xs text-red-600 font-medium mt-0.5">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password Field -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-gray-700" for="password_confirmation">
                        Confirmar Contraseña
                    </label>
                    <div class="relative flex items-center bg-white border border-gray-300 rounded-lg input-focus-ring" id="confirm-container">
                        <span class="material-symbols-outlined absolute left-2.5 text-gray-400 pointer-events-none text-lg">lock_reset</span>
                        <input class="w-full bg-transparent border-none py-2 pl-9 pr-9 text-gray-900 placeholder:text-gray-400 focus:ring-0 rounded-lg text-xs sm:text-sm"
                               id="password_confirmation"
                               name="password_confirmation"
                               placeholder="••••••••"
                               required
                               autocomplete="new-password"
                               type="password">
                        <button class="absolute right-2.5 text-gray-400 hover:text-gray-600 focus:outline-none flex items-center justify-center p-0.5 rounded"
                                onclick="toggleFieldPassword('password_confirmation', 'confirm-visibility-icon')"
                                type="button"
                                aria-label="Mostrar u ocultar confirmación de contraseña">
                            <span class="material-symbols-outlined text-lg" id="confirm-visibility-icon">visibility_off</span>
                        </button>
                    </div>
                    <p class="hidden text-xs text-red-600 font-medium mt-0.5 flex items-center gap-1" id="match-error">
                        <span class="material-symbols-outlined text-sm">error</span> Las contraseñas no coinciden.
                    </p>
                </div>

                <!-- Submit Button -->
                <button class="w-full mt-1.5 bg-primary-container hover:bg-primary-container/90 active:scale-[0.99] text-white font-semibold text-xs sm:text-sm py-2.5 px-4 rounded-lg shadow-sm hover:shadow transition-all flex justify-center items-center gap-1.5 group"
                        id="submit-reset-btn"
                        type="submit">
                    <span>Restablecer Contraseña</span>
                    <span class="material-symbols-outlined text-base group-hover:translate-x-0.5 transition-transform">arrow_forward</span>
                </button>
            </form>

            <!-- Back to Login Link -->
            <div class="mt-4 text-center border-t border-gray-100 pt-3">
                <a class="inline-flex items-center gap-1 text-xs text-gray-600 hover:text-gray-900 font-medium transition-colors"
                   href="{{ route('login') }}">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    <span>Volver al Inicio de Sesión</span>
                </a>
            </div>
        </div>

        <!-- Secure Banner -->
        <x-secure-badge />
    </main>

    <script>
        function toggleFieldPassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility';
            } else {
                input.type = 'password';
                icon.textContent = 'visibility_off';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const pwd = document.getElementById('password');
            const confirm = document.getElementById('password_confirmation');
            const matchError = document.getElementById('match-error');
            const confirmContainer = document.getElementById('confirm-container');
            const submitBtn = document.getElementById('submit-reset-btn');

            function checkPasswordsMatch() {
                if (confirm.value.length > 0) {
                    if (pwd.value !== confirm.value) {
                        matchError.classList.remove('hidden');
                        confirmContainer.classList.add('input-error-ring');
                        confirmContainer.classList.remove('border-gray-300');
                        submitBtn.disabled = true;
                    } else {
                        matchError.classList.add('hidden');
                        confirmContainer.classList.remove('input-error-ring');
                        confirmContainer.classList.add('border-gray-300');
                        submitBtn.disabled = false;
                    }
                } else {
                    matchError.classList.add('hidden');
                    confirmContainer.classList.remove('input-error-ring');
                    confirmContainer.classList.add('border-gray-300');
                }
            }

            confirm.addEventListener('input', checkPasswordsMatch);
            pwd.addEventListener('input', checkPasswordsMatch);
        });
    </script>
</x-guest-layout>
