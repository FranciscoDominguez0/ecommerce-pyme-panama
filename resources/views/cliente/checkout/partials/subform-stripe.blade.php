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
<input type="hidden" name="stripe_payment_method_id" id="stripe_payment_method_id">
<input type="hidden" name="stripe_last4" id="stripe_last4">
<input type="hidden" name="stripe_brand" id="stripe_brand">
