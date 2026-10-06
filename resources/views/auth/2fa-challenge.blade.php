<x-guest-layout>
    <x-slot name="title">Verificación de Seguridad - PayMe Panamá</x-slot>

    <!-- Main Content Canvas: Mismo estilo que Login/Registro -->
    <main class="w-full max-w-4xl lg:max-w-5xl my-auto px-2 sm:px-4">
        <div class="glass-card relative rounded-3xl sm:rounded-[36px] shadow-xl border border-slate-200/90 overflow-hidden flex flex-col lg:flex-row items-center justify-between p-4 sm:p-6 lg:p-10 min-h-[580px]" style="background: linear-gradient(135deg, #f0fdf4 0%, #f8fafc 50%, #ecfdf5 100%) !important;">
            
            <!-- Ambient Fluid Organic Waves -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden">
                <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-emerald-200/40 blur-3xl animate-blob"></div>
                <div class="absolute bottom-[-10%] left-[-5%] w-80 h-80 rounded-full bg-teal-200/35 blur-3xl animate-blob animation-delay-2000"></div>
                <div class="absolute top-1/2 left-1/4 w-72 h-72 rounded-full bg-emerald-100/50 blur-2xl animate-blob animation-delay-4000"></div>
                <!-- Formas fluidas orgánicas en SVG -->
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

            <!-- Left Side: Clean Bold Statement con logo -->
            <div class="hidden lg:flex w-full lg:w-1/2 p-4 sm:p-6 lg:p-10 text-slate-800 z-10 flex-col justify-between self-stretch animate-swap-text">
                <!-- Logo en la esquina izquierda superior -->
                <div class="flex items-center gap-2.5 mb-6">
                    <x-application-logo :boxed="false" size="default" class="w-8 h-8" />
                    <span class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">PayMe <span class="text-emerald-700">Panamá</span></span>
                </div>

                <div class="max-w-md my-auto py-6">
                    <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 tracking-tight leading-snug">
                        Protegemos tu <span class="text-emerald-700">seguridad.</span> Ingresa el código para continuar.
                    </h2>
                </div>
            </div>

            <!-- Right Side: Floating White Card with Form Content -->
            <div class="w-full lg:w-1/2 flex justify-center z-10 mt-4 lg:mt-0 animate-swap-card">
                <div class="w-full max-w-[420px] bg-white rounded-2xl sm:rounded-3xl shadow-xl p-6 sm:p-8 flex flex-col justify-between border border-slate-100">
                    
                    <!-- Header inside card -->
                    <div class="flex flex-col items-start mb-5 text-left">
                        <x-application-logo :boxed="false" size="default" class="w-10 h-10 mb-4 block lg:hidden" />
                        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                            Verificación en dos pasos
                        </h1>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Hemos enviado un código de 4 dígitos a tu correo electrónico.
                        </p>
                    </div>

                    <!-- Toast messages handling for resend success/warnings -->
                    @if (session('toast_success'))
                        <div class="mb-4 bg-emerald-50 text-emerald-800 border border-emerald-200 p-3 rounded-xl flex items-start gap-2 text-xs">
                            <span class="material-symbols-outlined shrink-0 text-emerald-600 text-base" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            <div class="flex-1 font-medium">
                                {{ session('toast_success') }}
                            </div>
                        </div>
                    @endif

                    @if (session('toast_warning'))
                        <div class="mb-4 bg-amber-50 text-amber-800 border border-amber-200 p-3 rounded-xl flex items-start gap-2 text-xs">
                            <span class="material-symbols-outlined shrink-0 text-amber-600 text-base" style="font-variation-settings: 'FILL' 1;">warning</span>
                            <div class="flex-1 font-medium">
                                {{ session('toast_warning') }}
                            </div>
                        </div>
                    @endif

                    <!-- Error Alert -->
                    <div id="error-alert" class="hidden mb-4 bg-red-50 text-red-700 p-3 rounded-xl flex items-start gap-2 border border-red-200 text-xs">
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
                            <label class="text-xs font-semibold text-slate-700 uppercase tracking-widest" for="code">
                                Código de Acceso
                            </label>
                            <div class="relative w-full max-w-[220px]">
                                <div class="flex justify-center gap-2 sm:gap-3 w-full" x-data="otpComponent()">
                                    <input type="hidden" name="code" :value="code">
                                    
                                    <!-- Input 1 -->
                                    <input type="text" x-ref="input0" inputmode="numeric" maxlength="1" required
                                           class="w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-bold rounded-xl border-2 bg-slate-50 text-slate-900 focus:bg-white focus:outline-none transition-all @error('code') border-red-500 focus:border-red-500 focus:ring-red-500/20 @else border-slate-200 focus:border-emerald-600 focus:ring-emerald-500/20 @enderror"
                                           @input="handleInput(0, $event)" @keydown.backspace="handleBackspace(0, $event)" @paste="handlePaste($event)">
                                           
                                    <!-- Input 2 -->
                                    <input type="text" x-ref="input1" inputmode="numeric" maxlength="1" required
                                           class="w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-bold rounded-xl border-2 bg-slate-50 text-slate-900 focus:bg-white focus:outline-none transition-all @error('code') border-red-500 focus:border-red-500 focus:ring-red-500/20 @else border-slate-200 focus:border-emerald-600 focus:ring-emerald-500/20 @enderror"
                                           @input="handleInput(1, $event)" @keydown.backspace="handleBackspace(1, $event)" @paste="handlePaste($event)">
                                           
                                    <!-- Input 3 -->
                                    <input type="text" x-ref="input2" inputmode="numeric" maxlength="1" required
                                           class="w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-bold rounded-xl border-2 bg-slate-50 text-slate-900 focus:bg-white focus:outline-none transition-all @error('code') border-red-500 focus:border-red-500 focus:ring-red-500/20 @else border-slate-200 focus:border-emerald-600 focus:ring-emerald-500/20 @enderror"
                                           @input="handleInput(2, $event)" @keydown.backspace="handleBackspace(2, $event)" @paste="handlePaste($event)">
                                           
                                    <!-- Input 4 -->
                                    <input type="text" x-ref="input3" inputmode="numeric" maxlength="1" required
                                           class="w-12 h-14 sm:w-14 sm:h-16 text-center text-2xl font-bold rounded-xl border-2 bg-slate-50 text-slate-900 focus:bg-white focus:outline-none transition-all @error('code') border-red-500 focus:border-red-500 focus:ring-red-500/20 @else border-slate-200 focus:border-emerald-600 focus:ring-emerald-500/20 @enderror"
                                           @input="handleInput(3, $event)" @keydown.backspace="handleBackspace(3, $event)" @paste="handlePaste($event)">
                                </div>
                                <p id="code-error-msg" class="text-xs text-red-600 font-medium mt-1 hidden"></p>
                                @error('code')
                                    <p class="text-[11px] text-red-600 font-medium mt-1 text-center">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button class="w-full mt-2 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.99] disabled:opacity-75 disabled:cursor-wait text-white font-semibold text-xs sm:text-sm py-2.5 px-4 rounded-xl shadow-xs hover:shadow-md transition-all flex justify-center items-center gap-2 group cursor-pointer"
                                type="submit"
                                :disabled="isVerifying">
                            
                            <!-- Estado Normal -->
                            <span x-show="!isVerifying" class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[18px]">lock_open</span>
                                <span>Verificar e Ingresar</span>
                            </span>

                            <!-- Estado Cargando -->
                            <span x-show="isVerifying" class="flex items-center gap-1.5" style="display: none;">
                                <span class="material-symbols-outlined text-[18px] animate-spin">progress_activity</span>
                                <span>Verificando...</span>
                            </span>
                        </button>
                    </form>

                    <!-- Resend Link -->
                    <div class="mt-5 text-center border-t border-slate-100 pt-4">
                        <form method="POST" action="{{ route('2fa.resend') }}">
                            @csrf
                            <p class="text-xs text-slate-500">
                                ¿No recibiste el código?
                                <button type="submit" class="font-semibold text-emerald-600 hover:text-emerald-700 hover:underline ml-1 focus:outline-none transition-colors">
                                    Reenviar código
                                </button>
                            </p>
                        </form>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <form method="POST" action="{{ route('2fa.cancel') }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center gap-1.5 text-xs text-slate-500 hover:text-emerald-600 font-medium transition-colors focus:outline-none">
                                <span class="material-symbols-outlined text-sm">arrow_back</span>
                                <span>Cancelar y volver</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
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

        document.addEventListener('alpine:init', () => {
            Alpine.data('otpComponent', () => ({
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
            }));
        });
    </script>
</x-guest-layout>
