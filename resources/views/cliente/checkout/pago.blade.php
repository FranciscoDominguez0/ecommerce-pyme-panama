@extends('layouts.cliente')

@section('title', 'Método de Pago')

@section('content')
<div id="checkout-pago-container" class="flex-grow pt-8 pb-12 px-4 md:px-16 max-w-7xl mx-auto w-full">
    <!-- Progress Indicator -->
    <div class="mb-12 flex justify-center w-full max-w-3xl mx-auto">
        <div class="flex items-center w-full relative">
            <!-- Step 1: Address (Completed) -->
            <div class="flex flex-col items-center relative z-10 w-1/3">
                <a href="{{ route('cliente.checkout.direccion') }}" wire:navigate class="w-8 h-8 rounded-full bg-secondary text-on-secondary flex items-center justify-center mb-2 hover:bg-secondary-container hover:text-on-secondary-container transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">check</span>
                </a>
                <span class="font-label-caps text-xs font-semibold uppercase tracking-wide text-secondary text-center">Dirección</span>
            </div>
            <!-- Connector 1-2 -->
            <div class="absolute top-4 left-[16.6%] right-[50%] h-[2px] bg-secondary -z-10"></div>
            <!-- Step 2: Payment (Active) -->
            <div class="flex flex-col items-center relative z-10 w-1/3">
                <div class="w-8 h-8 rounded-full bg-primary text-on-primary flex items-center justify-center font-numeric-data text-xl font-semibold mb-2 shadow-[0_4px_20px_rgba(0,35,73,0.12)]">2</div>
                <span class="font-label-caps text-xs font-semibold uppercase tracking-wide text-primary text-center">Pago</span>
            </div>
            <!-- Connector 2-3 -->
            <div class="absolute top-4 left-[50%] right-[16.6%] h-[2px] bg-outline-variant -z-10"></div>
            <!-- Step 3: Confirmation (Pending) -->
            <div class="flex flex-col items-center relative z-10 w-1/3">
                <div class="w-8 h-8 rounded-full bg-surface-container border-2 border-outline-variant text-outline flex items-center justify-center font-numeric-data text-xl font-semibold mb-2">3</div>
                <span class="font-label-caps text-xs font-semibold uppercase tracking-wide text-on-surface-variant text-center">Confirmación</span>
            </div>
        </div>
    </div>

    <div class="mb-8 max-w-4xl mx-auto">
        <h1 class="text-lg sm:text-xl font-bold text-primary mb-2">Método de Pago</h1>
        <p class="text-on-surface-variant text-sm">Seleccione la opción con la que desea procesar su pedido.</p>
    </div>

    <form id="form-checkout-pago" action="{{ route('cliente.checkout.guardar-pago') }}" method="POST" enctype="multipart/form-data" class="max-w-4xl mx-auto space-y-8">
        @csrf

        <div class="space-y-4">
            
            <!-- Tarjeta de Crédito (Stripe) -->
            @if (\App\Models\Configuracion::obtenerBool('pagos.stripe.activo', true))
            <label class="relative block cursor-pointer group" id="label_stripe">
                <input type="radio" name="metodo_pago" value="stripe" class="peer sr-only" id="radio_stripe" required />
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 transition-all duration-200 peer-checked:border-secondary peer-checked:shadow-[0_4px_20px_rgba(0,35,73,0.05)] hover:shadow-[0_4px_20px_rgba(0,35,73,0.05)]">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            <span class="material-symbols-outlined text-on-surface-variant text-3xl">credit_card</span>
                            <div>
                                <span class="block text-base font-semibold text-primary">Tarjeta de Crédito / Débito</span>
                                <span class="block text-xs text-on-surface-variant">Pago seguro y validación en tiempo real con Stripe</span>
                            </div>
                        </div>
                        <div class="w-5 h-5 rounded-full border-2 border-outline-variant flex items-center justify-center peer-checked:group-[]:border-secondary peer-checked:group-[]:bg-secondary transition-colors shrink-0">
                            <span class="material-symbols-outlined text-on-secondary text-[14px] opacity-0 peer-checked:group-[]:opacity-100">check</span>
                        </div>
                    </div>

                    <!-- Sub-formulario Stripe Elements -->
                    <div id="form_stripe" class="hidden mt-6 pt-6 border-t border-outline-variant w-full">
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-xs font-semibold text-primary">Ingresa los datos de tu tarjeta</label>
                                <div class="flex items-center gap-1.5">
                                    <x-tarjeta-marca-logo brand="visa" class="w-7 h-4.5" />
                                    <x-tarjeta-marca-logo brand="mastercard" class="w-7 h-4.5" />
                                    <x-tarjeta-marca-logo brand="amex" class="w-7 h-4.5" />
                                </div>
                            </div>
                            
                            <!-- Contenedor donde Stripe monta el formulario seguro de tarjeta -->
                            <div id="stripe-card-element" class="p-3.5 bg-white border border-slate-300 rounded-lg text-sm transition-all focus-within:border-emerald-500 focus-within:ring-2 focus-within:ring-emerald-500/20 shadow-2xs"></div>
                            
                            <!-- Mensajes de validación de Stripe (tarjeta incorrecta, vencida, etc.) -->
                            <div id="stripe-card-errors" class="text-rose-600 text-xs mt-2 font-semibold hidden flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px]">error</span>
                                <span id="stripe-error-text"></span>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-1.5 text-[11px] text-slate-400">
                            <span class="material-symbols-outlined text-[14px]">lock</span>
                            <span>Tus datos viajan cifrados bajo estándar PCI-DSS y nunca se almacenan en nuestros servidores.</span>
                        </div>
                    </div>
                </div>
            </label>
            <input type="hidden" name="stripe_payment_method_id" id="stripe_payment_method_id">
            <input type="hidden" name="stripe_last4" id="stripe_last4">
            <input type="hidden" name="stripe_brand" id="stripe_brand">
            @endif

            <!-- Yappy -->
            @if (\App\Models\Configuracion::obtenerBool('pagos.yappy.activo', true))
            <label class="relative block cursor-pointer group">
                <input type="radio" name="metodo_pago" value="yappy" class="peer sr-only" required />
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 transition-all duration-200 peer-checked:border-secondary peer-checked:shadow-[0_4px_20px_rgba(0,35,73,0.05)] hover:shadow-[0_4px_20px_rgba(0,35,73,0.05)]">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center overflow-hidden border border-slate-200 p-1">
                                <img src="{{ asset('images/pa-yappy.webp') }}" alt="Yappy" class="max-w-full max-h-full object-contain">
                            </div>
                            <div>
                                <span class="block text-base font-semibold text-primary">Yappy</span>
                                <span class="block text-xs text-on-surface-variant">Paga rápido con tu directorio de Banco General</span>
                            </div>
                        </div>
                        <div class="w-5 h-5 rounded-full border-2 border-outline-variant flex items-center justify-center peer-checked:group-[]:border-secondary peer-checked:group-[]:bg-secondary transition-colors shrink-0">
                            <span class="material-symbols-outlined text-on-secondary text-[14px] opacity-0 peer-checked:group-[]:opacity-100">check</span>
                        </div>
                    </div>
                </div>
            </label>
            @endif

            <!-- Transferencia ACH -->
            @if (\App\Models\Configuracion::obtenerBool('pagos.transferencia.activo', true))
            <label class="relative block cursor-pointer group">
                <input type="radio" name="metodo_pago" value="transferencia" class="peer sr-only" id="radio_transferencia" required />
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 transition-all duration-200 peer-checked:border-secondary peer-checked:shadow-[0_4px_20px_rgba(0,35,73,0.05)] hover:shadow-[0_4px_20px_rgba(0,35,73,0.05)]">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            <span class="material-symbols-outlined text-on-surface-variant text-3xl">account_balance</span>
                            <div>
                                <span class="block text-base font-semibold text-primary">Transferencia Bancaria (ACH)</span>
                                <span class="block text-xs text-on-surface-variant">Envía el comprobante para procesar tu pedido</span>
                            </div>
                        </div>
                        <div class="w-5 h-5 rounded-full border-2 border-outline-variant flex items-center justify-center peer-checked:group-[]:border-secondary peer-checked:group-[]:bg-secondary transition-colors shrink-0">
                            <span class="material-symbols-outlined text-on-secondary text-[14px] opacity-0 peer-checked:group-[]:opacity-100">check</span>
                        </div>
                    </div>

                    <!-- Sub-formulario Transferencia -->
                    <div id="form_transferencia" class="hidden mt-6 pt-6 border-t border-outline-variant w-full">
                        <div class="bg-surface-container-low p-4 rounded-lg mb-6 border border-outline-variant">
                            <p class="text-sm font-semibold text-primary mb-2">Datos Bancarios:</p>
                            <div class="space-y-1 text-xs text-on-surface-variant">
                                <p>Banco: Banco General</p>
                                <p>Cuenta Corriente: 03-XX-XXXX-X</p>
                                <p>A nombre de: PayMe Panamá S.A.</p>
                            </div>
                        </div>
                        
                        <label class="block text-xs font-semibold text-on-surface-variant mb-2">Sube tu comprobante de pago</label>
                        <input type="file" name="comprobante_pago" id="comprobante_pago" accept="image/*,.pdf" class="block w-full text-sm text-on-surface-variant file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-surface-container file:text-primary hover:file:bg-surface-container-high transition-colors cursor-pointer">
                        @error('comprobante_pago')
                            <p class="mt-2 text-sm text-error flex items-center gap-1"><span class="material-symbols-outlined text-[16px]">error</span> {{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </label>
            @endif

            <!-- Contra Entrega -->
            @if (\App\Models\Configuracion::obtenerBool('pagos.contra_entrega.activo', true))
            <label class="relative block cursor-pointer group">
                <input type="radio" name="metodo_pago" value="contra_entrega" class="peer sr-only" required />
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 transition-all duration-200 peer-checked:border-secondary peer-checked:shadow-[0_4px_20px_rgba(0,35,73,0.05)] hover:shadow-[0_4px_20px_rgba(0,35,73,0.05)]">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            <span class="material-symbols-outlined text-on-surface-variant text-3xl">local_shipping</span>
                            <div>
                                <span class="block text-base font-semibold text-primary">Pago Contra Entrega</span>
                                <span class="block text-xs text-on-surface-variant">Paga en efectivo o tarjeta al recibir tu pedido</span>
                            </div>
                        </div>
                        <div class="w-5 h-5 rounded-full border-2 border-outline-variant flex items-center justify-center peer-checked:group-[]:border-secondary peer-checked:group-[]:bg-secondary transition-colors shrink-0">
                            <span class="material-symbols-outlined text-on-secondary text-[14px] opacity-0 peer-checked:group-[]:opacity-100">check</span>
                        </div>
                    </div>
                </div>
            </label>
            @endif

        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mt-8 pt-6 border-t border-outline-variant">
            <a href="{{ route('cliente.checkout.direccion') }}" wire:navigate class="text-on-surface-variant hover:text-primary font-label-caps text-xs font-semibold uppercase tracking-wide transition-colors flex items-center gap-2 w-full sm:w-auto justify-center">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                Volver a Dirección
            </a>
            
            <button type="submit" id="btn-submit-pago" class="bg-primary text-on-primary font-label-caps text-xs font-semibold uppercase tracking-wide px-8 py-4 rounded-lg hover:bg-primary-container transition-colors shadow-[0_4px_20px_rgba(0,35,73,0.12)] w-full sm:w-auto text-center flex justify-center items-center gap-2">
                Continuar a Confirmación
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
(function() {
    let stripeInstance = null;
    let cardElementInstance = null;

    function initCheckoutPago() {
        const container = document.getElementById('checkout-pago-container');
        if (!container) return;

        const form = document.getElementById('form-checkout-pago');
        const radios = document.querySelectorAll('input[name="metodo_pago"]');
        const formTransferencia = document.getElementById('form_transferencia');
        const inputComprobante = document.getElementById('comprobante_pago');
        const formStripe = document.getElementById('form_stripe');
        const submitBtn = document.getElementById('btn-submit-pago');
        const stripeErrorBox = document.getElementById('stripe-card-errors');
        const stripeErrorText = document.getElementById('stripe-error-text');

        // Configurar Stripe Elements si la clave publicable está disponible
        const stripeKey = "{{ config('services.stripe.key') }}";
        const cardMount = document.getElementById('stripe-card-element');

        if (stripeKey && typeof Stripe !== 'undefined' && cardMount && !cardMount.dataset.mounted) {
            try {
                stripeInstance = Stripe(stripeKey);
                const elements = stripeInstance.elements();
                cardElementInstance = elements.create('card', {
                    hidePostalCode: true,
                    style: {
                        base: {
                            color: '#0f172a',
                            fontFamily: '"Plus Jakarta Sans", system-ui, -apple-system, sans-serif',
                            fontSize: '14px',
                            '::placeholder': { color: '#94a3b8' },
                        },
                        invalid: {
                            color: '#e11d48',
                            iconColor: '#e11d48',
                        }
                    }
                });
                cardElementInstance.mount('#stripe-card-element');
                cardMount.dataset.mounted = 'true';

                cardElementInstance.on('change', function(event) {
                    if (event.error) {
                        stripeErrorText.textContent = event.error.message;
                        stripeErrorBox.classList.remove('hidden');
                    } else {
                        stripeErrorText.textContent = '';
                        stripeErrorBox.classList.add('hidden');
                    }
                });
            } catch (err) {
                console.warn('Error inicializando Stripe:', err);
            }
        }

        function toggleMetodos() {
            const selected = document.querySelector('input[name="metodo_pago"]:checked');
            if (!selected) return;

            if (selected.value === 'transferencia') {
                if (formTransferencia) formTransferencia.classList.remove('hidden');
                if (inputComprobante) inputComprobante.required = true;
                if (formStripe) formStripe.classList.add('hidden');
            } else if (selected.value === 'stripe') {
                if (formTransferencia) formTransferencia.classList.add('hidden');
                if (inputComprobante) inputComprobante.required = false;
                if (formStripe) formStripe.classList.remove('hidden');
            } else {
                if (formTransferencia) formTransferencia.classList.add('hidden');
                if (inputComprobante) inputComprobante.required = false;
                if (formStripe) formStripe.classList.add('hidden');
            }
        }

        radios.forEach(radio => {
            radio.removeEventListener('change', toggleMetodos);
            radio.addEventListener('change', toggleMetodos);
        });
        toggleMetodos();

        if (form && !form.dataset.listenerAttached) {
            form.dataset.listenerAttached = 'true';
            form.addEventListener('submit', async function(e) {
                const selected = document.querySelector('input[name="metodo_pago"]:checked');
                
                // Si seleccionó Stripe y está configurado
                if (selected && selected.value === 'stripe' && stripeInstance && cardElementInstance) {
                    e.preventDefault();
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `
                        <span class="inline-block animate-spin material-symbols-outlined text-[16px]">progress_activity</span>
                        <span>Validando tarjeta...</span>
                    `;

                    try {
                        const { paymentMethod, error } = await stripeInstance.createPaymentMethod({
                            type: 'card',
                            card: cardElementInstance,
                        });

                        if (error) {
                            stripeErrorText.textContent = error.message;
                            stripeErrorBox.classList.remove('hidden');
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = `Continuar a Confirmación <span class="material-symbols-outlined text-sm">arrow_forward</span>`;
                        } else {
                            document.getElementById('stripe_payment_method_id').value = paymentMethod.id;
                            document.getElementById('stripe_last4').value = paymentMethod.card.last4;
                            document.getElementById('stripe_brand').value = paymentMethod.card.brand;
                            form.submit();
                        }
                    } catch (submitErr) {
                        console.error(submitErr);
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = `Continuar a Confirmación <span class="material-symbols-outlined text-sm">arrow_forward</span>`;
                    }
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCheckoutPago);
    } else {
        initCheckoutPago();
    }
    document.addEventListener('livewire:navigated', initCheckoutPago);
})();
</script>
@endpush
@endsection
