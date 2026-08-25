<x-guest-layout>
    <x-slot name="title">Verificación de Seguridad - PayMe Panamá</x-slot>

    <!-- Main Content Canvas -->
    <main class="w-full max-w-md fade-in-up">
        <!-- Logo Header -->
        <div class="flex flex-col items-center mb-5">
            <x-application-logo :boxed="true" class="mb-2.5" />
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 text-center tracking-tight">
                Verificación en dos pasos
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 text-center mt-0.5">
                Hemos enviado un código de 4 dígitos a tu correo electrónico.
            </p>
        </div>

        <!-- Card -->
        <div class="glass-card rounded-xl p-5 sm:p-6 w-full transition-all duration-200">
            <!-- Toast messages handling for resend success/warnings -->
            @if (session('toast_success'))
                <div class="mb-4 bg-emerald-50 text-emerald-800 border border-emerald-200 p-3 rounded-lg flex items-start gap-2 text-xs">
                    <span class="material-symbols-outlined shrink-0 text-emerald-600 text-base" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                    <div class="flex-1 font-medium">
                        {{ session('toast_success') }}
                    </div>
                </div>
            @endif

            @if (session('toast_warning'))
                <div class="mb-4 bg-amber-50 text-amber-800 border border-amber-200 p-3 rounded-lg flex items-start gap-2 text-xs">
                    <span class="material-symbols-outlined shrink-0 text-amber-600 text-base" style="font-variation-settings: 'FILL' 1;">warning</span>
                    <div class="flex-1 font-medium">
                        {{ session('toast_warning') }}
                    </div>
                </div>
            @endif

            <!-- Error Alert -->
            <div id="error-alert" class="hidden mb-4 bg-red-50 text-red-700 p-3 rounded-lg flex items-start gap-2 border border-red-200 text-xs">
                <span class="material-symbols-outlined shrink-0 text-red-600 text-base mt-0.5" style="font-variation-settings: 'FILL' 1;">error</span>
                <div class="flex-1">
                    <p class="font-semibold mb-0.5">Error de verificación</p>
                    <p class="opacity-95 message-text">El código ingresado es incorrecto.</p>
                </div>
                <button class="text-red-400 hover:text-red-600 focus:outline-none" onclick="document.getElementById('error-alert').classList.add('hidden')" type="button">
                    <span class="material-symbols-outlined text-sm">close</span>
                </button>
            </div>

            <form method="POST" action="{{ route('2fa.verify') }}" class="flex flex-col gap-5" id="2fa-form" x-data="{ isVerifying: false }" @submit.prevent="isVerifying = true; submitVerify($event, () => isVerifying = false)">
                @csrf

                <!-- Code Input -->
                <div class="flex flex-col gap-2 items-center">
                    <label class="text-xs font-semibold text-gray-700 uppercase tracking-widest" for="code">
                        Código de Acceso
                    </label>
                    <div class="relative w-full max-w-[200px]">
                    <div class="flex justify-center gap-2 sm:gap-4 w-full" x-data="otpComponent()">
                        <input type="hidden" name="code" :value="code">
                        
                        <!-- Input 1 -->
                        <input type="text" x-ref="input0" inputmode="numeric" maxlength="1" required
                               class="w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-bold rounded-xl border-2 bg-slate-50 text-slate-900 focus:bg-white focus:outline-none transition-all @error('code') border-red-500 focus:border-red-500 focus:ring-red-500/20 @else border-slate-200 focus:border-primary focus:ring-primary/20 @enderror"
                               @input="handleInput(0, $event)" @keydown.backspace="handleBackspace(0, $event)" @paste="handlePaste($event)">
                               
                        <!-- Input 2 -->
                        <input type="text" x-ref="input1" inputmode="numeric" maxlength="1" required
                               class="w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-bold rounded-xl border-2 bg-slate-50 text-slate-900 focus:bg-white focus:outline-none transition-all @error('code') border-red-500 focus:border-red-500 focus:ring-red-500/20 @else border-slate-200 focus:border-primary focus:ring-primary/20 @enderror"
                               @input="handleInput(1, $event)" @keydown.backspace="handleBackspace(1, $event)" @paste="handlePaste($event)">
                               
                        <!-- Input 3 -->
                        <input type="text" x-ref="input2" inputmode="numeric" maxlength="1" required
                               class="w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-bold rounded-xl border-2 bg-slate-50 text-slate-900 focus:bg-white focus:outline-none transition-all @error('code') border-red-500 focus:border-red-500 focus:ring-red-500/20 @else border-slate-200 focus:border-primary focus:ring-primary/20 @enderror"
                               @input="handleInput(2, $event)" @keydown.backspace="handleBackspace(2, $event)" @paste="handlePaste($event)">
                               
                        <!-- Input 4 -->
                        <input type="text" x-ref="input3" inputmode="numeric" maxlength="1" required
                               class="w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-bold rounded-xl border-2 bg-slate-50 text-slate-900 focus:bg-white focus:outline-none transition-all @error('code') border-red-500 focus:border-red-500 focus:ring-red-500/20 @else border-slate-200 focus:border-primary focus:ring-primary/20 @enderror"
                               @input="handleInput(3, $event)" @keydown.backspace="handleBackspace(3, $event)" @paste="handlePaste($event)">
                    </div>
                    <p id="code-error-msg" class="text-xs text-red-600 font-medium mt-1 hidden"></p>
                    @error('code')
                        <p class="text-xs text-red-600 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button class="w-full mt-2 bg-primary-container hover:bg-primary-container/90 active:scale-[0.99] disabled:opacity-75 disabled:cursor-wait text-white font-semibold text-xs sm:text-sm py-3 px-4 rounded-xl shadow-sm hover:shadow transition-all flex justify-center items-center gap-2 group"
                        type="submit"
                        :disabled="isVerifying">
                    
                    <!-- Estado Normal -->
                    <span x-show="!isVerifying" class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px]">lock_open</span>
                        <span>Verificar e Ingresar</span>
                    </span>

                    <!-- Estado Cargando -->
                    <span x-show="isVerifying" class="flex items-center gap-2" style="display: none;">
                        <span class="material-symbols-outlined text-[20px] animate-spin">progress_activity</span>
                        <span>Verificando...</span>
                    </span>
                </button>
            </form>

            <!-- Resend Link -->
            <div class="mt-6 text-center border-t border-gray-100 pt-4">
                <form method="POST" action="{{ route('2fa.resend') }}">
                    @csrf
                    <p class="text-xs text-gray-600">
                        ¿No recibiste el código?
                        <button type="submit" class="font-semibold text-secondary hover:underline ml-1 focus:outline-none">
                            Reenviar código
                        </button>
                    </p>
                </form>
            </div>
            
            <div class="mt-4 text-center">
                <form method="POST" action="{{ route('2fa.cancel') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-xs text-gray-500 hover:text-gray-700 transition-colors flex items-center justify-center gap-1 w-full focus:outline-none">
                        <span class="material-symbols-outlined text-[14px]">arrow_back</span>
                        Volver al inicio de sesión
                    </button>
                </form>
            </div>
        </div>

        <!-- Secure Banner -->
        <x-secure-badge />
    </main>

    <script>
        async function submitVerify(e, resetLoading) {
            const form = e.target;
            const formData = new FormData(form);
            
            // Calcular el código manualmente para evitar problemas de sincronización (Race Condition) 
            // entre la escritura rápida del usuario y la actualización del DOM de Alpine.js
            const inputs = Array.from(form.querySelectorAll('input[inputmode="numeric"]'));
            const actualCode = inputs.map(input => input.value).join('');
            formData.set('code', actualCode);
            
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
                    
                    window.location.href = data.redirect;

                } else if (response.status === 422) {
                    const data = await response.json();
                    resetLoading();
                    
                    const errorAlert = document.getElementById('error-alert');
                    if (errorAlert) {
                        errorAlert.classList.remove('hidden');
                        let errorMsg = 'El código ingresado es incorrecto.';
                        if (data.errors && data.errors.code && data.errors.code.length > 0) {
                            errorMsg = data.errors.code[0];
                        } else if (data.message) {
                            errorMsg = data.message;
                        }
                        errorAlert.querySelector('.message-text').textContent = errorMsg;
                    }
                } else {
                    throw new Error('Server error');
                }
            } catch (error) {
                resetLoading();
                window.location.reload();
            }
        }

        // Definir el componente de forma global para Alpine
        document.addEventListener('alpine:init', () => {
            Alpine.data('otpComponent', otpComponentData);
        });

        // Fallback por si Alpine ya se inicializó (muy común en producción con Livewire/Vite)
        if (window.Alpine) {
            window.Alpine.data('otpComponent', otpComponentData);
        }

        function otpComponentData() {
            return {
                digits: ['', '', '', ''],
                get code() {
                    return this.digits.join('');
                },
                handleInput(index, event) {
                    // Solo permitir números
                    let val = event.target.value.replace(/[^0-9]/g, '');
                    event.target.value = val;
                    this.digits[index] = val;
                    
                    if (val !== '' && index < 3) {
                        this.$refs['input' + (index + 1)].focus();
                    }
                },
                handleBackspace(index, event) {
                    if (event.target.value === '' && index > 0) {
                        this.digits[index - 1] = '';
                        this.$refs['input' + (index - 1)].value = '';
                        this.$refs['input' + (index - 1)].focus();
                    } else {
                        this.digits[index] = '';
                    }
                },
                handlePaste(event) {
                    event.preventDefault();
                    const pastedData = (event.clipboardData || window.clipboardData).getData('text');
                    const numbers = pastedData.replace(/[^0-9]/g, '').substring(0, 4).split('');
                    
                    numbers.forEach((num, i) => {
                        this.digits[i] = num;
                        this.$refs['input' + i].value = num;
                    });
                    
                    const focusIndex = Math.min(numbers.length, 3);
                    if(this.$refs['input' + focusIndex]) {
                        this.$refs['input' + focusIndex].focus();
                    }
                },
                init() {
                    setTimeout(() => {
                        this.$refs.input0.focus();
                    }, 100);
                }
            };
        }
    </script>
</x-guest-layout>
