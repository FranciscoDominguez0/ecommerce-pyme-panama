@extends('layouts.cliente')

@section('title', 'Método de Pago')

@section('content')
<div id="checkout-pago-container" class="flex-grow pt-8 pb-12 px-4 md:px-16 w-full max-w-[1600px] mx-auto">
    <!-- Progress Indicator -->
    <div class="mb-12 flex justify-center w-full max-w-3xl mx-auto">
        <div class="flex items-center w-full relative">
            <!-- Paso 1: Dirección (Completado) -->
            <div class="flex flex-col items-center relative z-10 w-1/3">
                <a href="{{ route('cliente.checkout.direccion') }}" wire:navigate class="w-8 h-8 rounded-full bg-secondary text-on-secondary flex items-center justify-center mb-2 hover:bg-secondary-container hover:text-on-secondary-container transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">check</span>
                </a>
                <span class="font-label-caps text-xs font-semibold uppercase tracking-wide text-secondary text-center">Dirección</span>
            </div>
            <!-- Connector 1-2 -->
            <div class="absolute top-4 left-[16.6%] right-[50%] h-[2px] bg-secondary -z-10"></div>
            <!-- Paso 2: Pago (Activo) -->
            <div class="flex flex-col items-center relative z-10 w-1/3">
                <div class="w-8 h-8 rounded-full bg-primary text-on-primary flex items-center justify-center font-numeric-data text-xl font-semibold mb-2 shadow-[0_4px_20px_rgba(0,35,73,0.12)]">2</div>
                <span class="font-label-caps text-xs font-semibold uppercase tracking-wide text-primary text-center">Pago</span>
            </div>
            <!-- Connector 2-3 -->
            <div class="absolute top-4 left-[50%] right-[16.6%] h-[2px] bg-outline-variant -z-10"></div>
            <!-- Paso 3: Confirmación (Pendiente) -->
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
            @forelse($metodosPago as $metodo)
            <label class="relative block cursor-pointer group" id="label_{{ $metodo->id() }}">
                <input type="radio" 
                       name="metodo_pago" 
                       value="{{ $metodo->id() }}" 
                       class="peer sr-only" 
                       id="radio_{{ $metodo->id() }}" 
                       {{ (session('checkout_metodo_pago') === $metodo->id() || $loop->first) ? 'checked' : '' }}
                       required />
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 transition-all duration-200 peer-checked:border-secondary peer-checked:shadow-[0_4px_20px_rgba(0,35,73,0.05)] hover:shadow-[0_4px_20px_rgba(0,35,73,0.05)]">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-4">
                            @if(str_starts_with($metodo->icono(), 'asset:'))
                            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center overflow-hidden border border-slate-200 p-1">
                                <img src="{{ asset(substr($metodo->icono(), 6)) }}" alt="{{ $metodo->nombre() }}" class="max-w-full max-h-full object-contain">
                            </div>
                            @else
                            <span class="material-symbols-outlined text-on-surface-variant text-3xl">{{ $metodo->icono() }}</span>
                            @endif
                            <div>
                                <span class="block text-base font-semibold text-primary">{{ $metodo->nombre() }}</span>
                                <span class="block text-xs text-on-surface-variant">{{ $metodo->descripcion() }}</span>
                            </div>
                        </div>
                        <div class="w-5 h-5 rounded-full border-2 border-outline-variant flex items-center justify-center peer-checked:group-[]:border-secondary peer-checked:group-[]:bg-secondary transition-colors shrink-0">
                            <span class="material-symbols-outlined text-on-secondary text-[14px] opacity-0 peer-checked:group-[]:opacity-100">check</span>
                        </div>
                    </div>

                    @if($metodo->vistaSubformulario())
                        @include($metodo->vistaSubformulario())
                    @endif
                </div>
            </label>
            @empty
            <div class="bg-amber-50 border border-amber-200 text-amber-800 p-4 rounded-xl text-sm">
                No hay métodos de pago habilitados temporalmente. Por favor, comunícate con el soporte técnico.
            </div>
            @endforelse
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

            // Ocultar todos los subformularios dinámicamente
            document.querySelectorAll('[id^="form_"]').forEach(el => el.classList.add('hidden'));

            // Mostrar el subformulario del método seleccionado si existe
            const subform = document.getElementById('form_' + selected.value);
            if (subform) {
                subform.classList.remove('hidden');
            }

            if (inputComprobante) {
                inputComprobante.required = (selected.value === 'transferencia');
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
