<!-- Sub-formulario Transferencia ACH -->
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
