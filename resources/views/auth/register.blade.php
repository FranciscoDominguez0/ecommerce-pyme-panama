<x-guest-layout>
    <x-slot name="title">Crear Cuenta - PayMe Panamá</x-slot>

    <!-- Main Content Canvas: Mismo estilo que Login pero del lado opuesto -->
    <main class="w-full max-w-4xl lg:max-w-5xl my-auto px-2 sm:px-4">
        <div class="relative overflow-hidden flex flex-col lg:flex-row items-center justify-between lg:glass-card lg:rounded-3xl lg:sm:rounded-[36px] lg:shadow-xl lg:border lg:border-slate-200/90 lg:p-4 lg:sm:p-6 lg:p-10 lg:min-h-[580px] lg:bg-[linear-gradient(135deg,#f0fdf4_0%,#f8fafc_50%,#ecfdf5_100%)]">
            
            <!-- Ambient Fluid Organic Waves (Espejadas para registro en el lado derecho) -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden scale-x-[-1]">
                <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-emerald-200/40 blur-3xl animate-blob"></div>
                <div class="absolute bottom-[-10%] left-[-5%] w-80 h-80 rounded-full bg-teal-200/35 blur-3xl animate-blob animation-delay-2000"></div>
                <div class="absolute top-1/2 left-1/4 w-72 h-72 rounded-full bg-emerald-100/50 blur-2xl animate-blob animation-delay-4000"></div>
                <!-- Formas fluidas orgánicas en SVG (espejadas con elegancia) -->
                <svg class="absolute inset-0 w-full h-full opacity-70 hidden lg:block animate-swap-text-reverse" viewBox="0 0 900 650" fill="none" preserveAspectRatio="none">
                    <path d="M-80,-20 C240,60 190,380 -60,650 L-100,650 L-100,-20 Z" fill="url(#waveGradReg1)" />
                    <path d="M-40,140 C340,240 310,480 80,650 L-80,650 Z" fill="url(#waveGradReg2)" opacity="0.75"/>
                    <path d="M-20,320 C260,400 220,560 180,650 L-40,650 Z" fill="url(#waveGradReg3)" opacity="0.6"/>
                    <defs>
                        <linearGradient id="waveGradReg1" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#34d399" stop-opacity="0.35"/>
                            <stop offset="100%" stop-color="#059669" stop-opacity="0.20"/>
                        </linearGradient>
                        <linearGradient id="waveGradReg2" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#6ee7b7" stop-opacity="0.50"/>
                            <stop offset="100%" stop-color="#10b981" stop-opacity="0.25"/>
                        </linearGradient>
                        <linearGradient id="waveGradReg3" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#a7f3d0" stop-opacity="0.65"/>
                            <stop offset="100%" stop-color="#34d399" stop-opacity="0.30"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>

            <!-- Left Side: Floating White Card with Register Form (Desliza hacia la izquierda) -->
            <div class="w-full lg:w-1/2 flex justify-center z-10 my-2 lg:my-0 animate-swap-card-reverse">
                <div class="w-full max-w-[430px] bg-white rounded-2xl sm:rounded-3xl shadow-xl p-5 sm:p-7 flex flex-col justify-between border border-slate-100">
                    
                    <!-- Header inside card -->
                    <div class="flex flex-col items-start mb-3 text-left">
                        <div class="flex items-center gap-2.5 mb-4 lg:hidden">
                            <x-application-logo :boxed="false" size="default" class="w-8 h-8" />
                            <span class="text-xl font-black text-slate-900 tracking-tight">PayMe <span class="text-emerald-700">Panamá</span></span>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                            Crear Cuenta Nueva
                        </h1>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Regístrate para gestionar tus compras y pedidos
                        </p>
                    </div>

                    <!-- Error Alert -->
                    @if ($errors->any())
                        <div class="mb-3 bg-red-50 text-red-700 p-2.5 rounded-xl flex items-start gap-2 border border-red-200 text-xs" id="error-alert">
                            <span class="material-symbols-outlined shrink-0 text-red-600 text-base mt-0.5" style="font-variation-settings: 'FILL' 1;">error</span>
                            <div class="flex-1">
                                <p class="font-semibold mb-0.5">Por favor corrige los siguientes errores:</p>
                                <ul class="list-disc pl-4 space-y-0.5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            <button class="text-red-400 hover:text-red-600" onclick="document.getElementById('error-alert').classList.add('hidden')" type="button">
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}" class="flex flex-col gap-2.5" id="register-form">
                        @csrf

                        <!-- Nombre y Apellido (2 columnas) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-semibold text-slate-700" for="nombre">Nombre</label>
                                <div class="relative flex items-center bg-slate-50/70 hover:bg-slate-50 border @error('nombre') border-red-400 ring-2 ring-red-500/10 @else border-slate-200 focus-within:border-emerald-600 focus-within:ring-2 focus-within:ring-emerald-500/10 @enderror rounded-xl transition-all">
                                    <span class="material-symbols-outlined absolute left-2.5 text-slate-400 pointer-events-none text-base">person</span>
                                    <input class="w-full bg-transparent border-none py-2 pl-8 pr-2.5 text-slate-900 placeholder:text-slate-400 focus:ring-0 text-xs sm:text-sm"
                                           id="nombre"
                                           name="nombre"
                                           value="{{ old('nombre') }}"
                                           placeholder="Santiago"
                                           required
                                           autofocus
                                           autocomplete="given-name"
                                           type="text">
                                </div>
                                @error('nombre')
                                    <p class="text-[11px] text-red-600 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-semibold text-slate-700" for="apellido">Apellido</label>
                                <div class="relative flex items-center bg-slate-50/70 hover:bg-slate-50 border @error('apellido') border-red-400 ring-2 ring-red-500/10 @else border-slate-200 focus-within:border-emerald-600 focus-within:ring-2 focus-within:ring-emerald-500/10 @enderror rounded-xl transition-all">
                                    <span class="material-symbols-outlined absolute left-2.5 text-slate-400 pointer-events-none text-base">badge</span>
                                    <input class="w-full bg-transparent border-none py-2 pl-8 pr-2.5 text-slate-900 placeholder:text-slate-400 focus:ring-0 text-xs sm:text-sm"
                                           id="apellido"
                                           name="apellido"
                                           value="{{ old('apellido') }}"
                                           placeholder="Martínez"
                                           required
                                           autocomplete="family-name"
                                           type="text">
                                </div>
                                @error('apellido')
                                    <p class="text-[11px] text-red-600 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Teléfono y Correo Electrónico (2 columnas) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-semibold text-slate-700" for="telefono">
                                    Teléfono <span class="text-slate-400 font-normal">(Opcional)</span>
                                </label>
                                <div class="relative flex items-center bg-slate-50/70 hover:bg-slate-50 border @error('telefono') border-red-400 ring-2 ring-red-500/10 @else border-slate-200 focus-within:border-emerald-600 focus-within:ring-2 focus-within:ring-emerald-500/10 @enderror rounded-xl transition-all">
                                    <span class="material-symbols-outlined absolute left-2.5 text-slate-400 pointer-events-none text-base">call</span>
                                    <input class="w-full bg-transparent border-none py-2 pl-8 pr-2.5 text-slate-900 placeholder:text-slate-400 focus:ring-0 text-xs sm:text-sm"
                                           id="telefono"
                                           name="telefono"
                                           value="{{ old('telefono') }}"
                                           placeholder="6621-8585"
                                           autocomplete="tel"
                                           type="tel"
                                           maxlength="9">
                                </div>
                                @error('telefono')
                                    <p class="text-[11px] text-red-600 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-semibold text-slate-700" for="email">Correo Electrónico</label>
                                <div class="relative flex items-center bg-slate-50/70 hover:bg-slate-50 border @error('email') border-red-400 ring-2 ring-red-500/10 @else border-slate-200 focus-within:border-emerald-600 focus-within:ring-2 focus-within:ring-emerald-500/10 @enderror rounded-xl transition-all">
                                    <span class="material-symbols-outlined absolute left-2.5 text-slate-400 pointer-events-none text-base">mail</span>
                                    <input class="w-full bg-transparent border-none py-2 pl-8 pr-2.5 text-slate-900 placeholder:text-slate-400 focus:ring-0 text-xs sm:text-sm"
                                           id="email"
                                           name="email"
                                           value="{{ old('email') }}"
                                           placeholder="ejemplo@dominio.com"
                                           required
                                           autocomplete="email"
                                           type="email">
                                </div>
                                @error('email')
                                    <p class="text-[11px] text-red-600 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Contraseña -->
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-slate-700" for="password">Contraseña</label>
                            <div class="relative flex items-center bg-slate-50/70 hover:bg-slate-50 border @error('password') border-red-400 ring-2 ring-red-500/10 @else border-slate-200 focus-within:border-emerald-600 focus-within:ring-2 focus-within:ring-emerald-500/10 @enderror rounded-xl transition-all">
                                <span class="material-symbols-outlined absolute left-2.5 text-slate-400 pointer-events-none text-base">lock</span>
                                <input class="w-full bg-transparent border-none py-2 pl-8 pr-9 text-slate-900 placeholder:text-slate-400 focus:ring-0 text-xs sm:text-sm"
                                       id="password"
                                       name="password"
                                       placeholder="Mínimo 8 caracteres"
                                       required
                                       autocomplete="new-password"
                                       type="password">
                                <button class="absolute right-2.5 text-slate-400 hover:text-slate-600 focus:outline-none p-0.5 cursor-pointer"
                                        onclick="toggleFieldPassword('password', 'pwd-visibility-icon')"
                                        type="button"
                                        aria-label="Mostrar u ocultar contraseña">
                                    <span class="material-symbols-outlined text-lg" id="pwd-visibility-icon">visibility_off</span>
                                </button>
                            </div>
                            @error('password')
                                <p class="text-[11px] text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirmar Contraseña -->
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold text-slate-700" for="password_confirmation">Confirmar Contraseña</label>
                            <div class="relative flex items-center bg-slate-50/70 hover:bg-slate-50 border border-slate-200 focus-within:border-emerald-600 focus-within:ring-2 focus-within:ring-emerald-500/10 rounded-xl transition-all" id="confirm-container">
                                <span class="material-symbols-outlined absolute left-2.5 text-slate-400 pointer-events-none text-base">lock_reset</span>
                                <input class="w-full bg-transparent border-none py-2 pl-8 pr-9 text-slate-900 placeholder:text-slate-400 focus:ring-0 text-xs sm:text-sm"
                                       id="password_confirmation"
                                       name="password_confirmation"
                                       placeholder="Repite tu contraseña"
                                       required
                                       autocomplete="new-password"
                                       type="password">
                                <button class="absolute right-2.5 text-slate-400 hover:text-slate-600 focus:outline-none p-0.5 cursor-pointer"
                                        onclick="toggleFieldPassword('password_confirmation', 'confirm-visibility-icon')"
                                        type="button"
                                        aria-label="Mostrar u ocultar confirmación">
                                    <span class="material-symbols-outlined text-lg" id="confirm-visibility-icon">visibility_off</span>
                                </button>
                            </div>
                            <p class="hidden text-xs text-red-600 font-medium mt-0.5 flex items-center gap-1" id="match-error">
                                <span class="material-symbols-outlined text-sm">error</span> Las contraseñas no coinciden.
                            </p>
                        </div>

                        <!-- Términos y Condiciones -->
                        <div class="flex flex-col gap-1 pt-0.5">
                            <label class="inline-flex items-start cursor-pointer select-none">
                                <input id="terms"
                                       type="checkbox"
                                       class="w-3.5 h-3.5 mt-0.5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500/20 focus:ring-offset-0 transition-colors shrink-0"
                                       name="terms"
                                       required
                                       {{ old('terms') ? 'checked' : '' }}>
                                <span class="ml-2 text-[11px] sm:text-xs text-slate-500 leading-tight whitespace-nowrap">
                                    Acepto los <a href="{{ route('terminos') }}" target="_blank" class="text-emerald-600 hover:underline font-medium">Términos</a> y <a href="{{ route('privacidad') }}" target="_blank" class="text-emerald-600 hover:underline font-medium">Privacidad</a>.
                                </span>
                            </label>
                            @error('terms')
                                <p class="text-[11px] text-red-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <button class="w-full mt-1 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] disabled:opacity-75 disabled:cursor-wait text-white font-semibold text-xs sm:text-sm py-2.5 px-4 rounded-xl shadow-xs hover:shadow-md transition-all flex justify-center items-center gap-1.5 group cursor-pointer"
                                id="submit-register-btn"
                                type="submit">
                            <span>Crear mi cuenta</span>
                            <span class="material-symbols-outlined text-base group-hover:translate-x-0.5 transition-transform">arrow_forward</span>
                        </button>
                    </form>

                    <!-- Login Link -->
                    <div class="mt-3 text-center border-t border-slate-100 pt-2.5">
                        <p class="text-xs text-slate-500">
                            ¿Ya tienes una cuenta?
                            <a class="font-semibold text-emerald-600 hover:text-emerald-700 hover:underline ml-1" href="{{ route('login') }}" wire:navigate>
                                Iniciar Sesión
                            </a>
                        </p>
                    </div>

                </div>
            </div>

            <!-- Right Side: Clean Bold Statement con logo en la esquina derecha -->
            <div class="hidden lg:flex w-full lg:w-1/2 p-4 sm:p-6 lg:p-10 text-slate-800 z-10 flex-col justify-between self-stretch order-first lg:order-last mb-4 lg:mb-0 animate-swap-text-reverse">
                <!-- Logo en la esquina derecha superior -->
                <div class="flex items-center justify-end gap-2.5 mb-6">
                    <x-application-logo :boxed="false" size="default" class="w-8 h-8" />
                    <span class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">PayMe <span class="text-emerald-700">Panamá</span></span>
                </div>

                <div class="max-w-md my-auto py-6 ml-auto text-right">
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight leading-snug">
                        PayMe Panamá: <span class="text-emerald-700">Crea tu cuenta</span> y gestiona tus pedidos con total fluidez.
                    </h2>
                </div>
            </div>
        </div>
    </main>

    <script>
        function toggleFieldPassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (!input || !icon) return;

            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility';
            } else {
                input.type = 'password';
                icon.textContent = 'visibility_off';
            }
        }

        (function initRegisterForm() {
            function bindHandlers() {
                const pwd = document.getElementById('password');
                const confirm = document.getElementById('password_confirmation');
                const matchError = document.getElementById('match-error');
                const confirmContainer = document.getElementById('confirm-container');

                function checkPasswordsMatch() {
                    if (!pwd || !confirm) return;
                    if (confirm.value.length > 0) {
                        if (pwd.value !== confirm.value) {
                            if (matchError) matchError.classList.remove('hidden');
                            if (confirmContainer) {
                                confirmContainer.classList.add('border-red-400');
                                confirmContainer.classList.remove('border-slate-200');
                            }
                        } else {
                            if (matchError) matchError.classList.add('hidden');
                            if (confirmContainer) {
                                confirmContainer.classList.remove('border-red-400');
                                confirmContainer.classList.add('border-slate-200');
                            }
                        }
                    } else {
                        if (matchError) matchError.classList.add('hidden');
                        if (confirmContainer) {
                            confirmContainer.classList.remove('border-red-400');
                            confirmContainer.classList.add('border-slate-200');
                        }
                    }
                }

                if (confirm) confirm.addEventListener('input', checkPasswordsMatch);
                if (pwd) pwd.addEventListener('input', checkPasswordsMatch);

                // Phone formatting (XXXX-XXXX)
                const telefonoInput = document.getElementById('telefono');
                if (telefonoInput) {
                    telefonoInput.addEventListener('input', function(e) {
                        let x = e.target.value.replace(/\D/g, '').match(/(\d{0,4})(\d{0,4})/);
                        e.target.value = !x[2] ? x[1] : x[1] + '-' + x[2];
                    });
                }

                // Loading state on form submit
                const form = document.getElementById('register-form');
                const btn = document.getElementById('submit-register-btn');
                
                if (form && btn) {
                    form.addEventListener('submit', function() {
                        if (form.checkValidity()) {
                            btn.disabled = true;
                            btn.classList.add('opacity-80', 'cursor-not-allowed');
                            btn.innerHTML = '<span class="inline-block animate-spin material-symbols-outlined text-[18px]">progress_activity</span> <span>Creando cuenta...</span>';
                        }
                    });
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bindHandlers);
            } else {
                bindHandlers();
            }

            document.addEventListener('livewire:navigated', bindHandlers);
        })();
    </script>
</x-guest-layout>
