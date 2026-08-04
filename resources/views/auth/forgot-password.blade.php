<x-guest-layout>
    <x-slot name="title">Recuperar Contraseña - PayMe Panamá</x-slot>

    <!-- Main Content Canvas -->
    <main class="w-full max-w-md fade-in-up">
        <!-- Logo Header -->
        <div class="flex flex-col items-center mb-5">
            <x-application-logo :boxed="true" class="mb-2.5" />
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 text-center tracking-tight">
                Recuperar Contraseña
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 text-center mt-0.5">
                Ingresa tu correo y te enviaremos un enlace para restablecer tu cuenta.
            </p>
        </div>

        <!-- Forgot Password Card -->
        <div class="glass-card rounded-xl p-5 sm:p-6 w-full transition-all duration-200">
            <!-- Session Status Alert -->
            @if (session('status'))
                <div class="mb-4 bg-emerald-50 text-emerald-800 border border-emerald-200 p-3 rounded-lg flex items-start gap-2 text-xs">
                    <span class="material-symbols-outlined shrink-0 text-emerald-600 text-base" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                    <div class="flex-1 font-medium leading-relaxed">
                        {{ session('status') }}
                    </div>
                </div>
            @endif

            <!-- Error Alert -->
            @if ($errors->any())
                <div class="mb-4 bg-red-50 text-red-700 p-3 rounded-lg flex items-start gap-2 border border-red-200 text-xs" id="error-alert">
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

            <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-3.5">
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

                <!-- Submit Button -->
                <button class="w-full mt-1.5 bg-primary-container hover:bg-primary-container/90 active:scale-[0.99] text-white font-semibold text-xs sm:text-sm py-2.5 px-4 rounded-lg shadow-sm hover:shadow transition-all flex justify-center items-center gap-1.5 group"
                        type="submit">
                    <span>Enviar enlace de recuperación</span>
                    <span class="material-symbols-outlined text-base group-hover:translate-x-0.5 transition-transform">send</span>
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
</x-guest-layout>
