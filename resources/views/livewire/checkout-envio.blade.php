<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 w-full">
    <!-- Columna Izquierda: Formulario y Envío -->
    <div class="lg:col-span-8">
        {{-- Detalles de Contacto --}}
        <div class="bg-white border border-gray-200/90 rounded-2xl p-6 shadow-xs mb-6">
            <h2 class="text-lg font-bold text-[#002349] mb-6 border-b border-gray-100 pb-3 flex items-center justify-between">
                <span>Detalles de Contacto</span>
                <span class="material-symbols-outlined text-[#006148] text-[20px]">person</span>
            </h2>
            
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Nombre Completo: <span class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            <input type="text" wire:model="contacto_nombre" class="w-1/2 border border-gray-300 rounded-lg p-2 text-sm focus:ring-[#006148] focus:border-[#006148]" placeholder="Nombre">
                            <input type="text" wire:model="contacto_apellido" class="w-1/2 border border-gray-300 rounded-lg p-2 text-sm focus:ring-[#006148] focus:border-[#006148]" placeholder="Apellido">
                        </div>
                        @error('contacto_nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        @error('contacto_apellido') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Correo electrónico: <span class="text-red-500">*</span></label>
                        <input type="email" wire:model="contacto_email" class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-[#006148] focus:border-[#006148]" placeholder="Correo electrónico">
                        @error('contacto_email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Teléfono 1: <span class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            <select class="w-1/3 border border-gray-300 rounded-lg p-2 text-sm bg-gray-50 focus:ring-[#006148] focus:border-[#006148]">
                                <option>Panamá +507</option>
                            </select>
                            <input type="text" wire:model="contacto_telefono1" class="w-2/3 border border-gray-300 rounded-lg p-2 text-sm focus:ring-[#006148] focus:border-[#006148]" placeholder="Número de teléfono">
                        </div>
                        @error('contacto_telefono1') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Teléfono 2:</label>
                        <div class="flex gap-2">
                            <select class="w-1/3 border border-gray-300 rounded-lg p-2 text-sm bg-gray-50 focus:ring-[#006148] focus:border-[#006148]">
                                <option>Panamá +507</option>
                            </select>
                            <input type="text" wire:model="contacto_telefono2" class="w-2/3 border border-gray-300 rounded-lg p-2 text-sm focus:ring-[#006148] focus:border-[#006148]" placeholder="Número de teléfono">
                        </div>
                        @error('contacto_telefono2') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>
        {{-- Componente de Opciones de Entrega --}}
        <livewire:calculadora-envio />

        <div class="mt-8 flex justify-end">
            <button wire:click="continuarCheckout" class="bg-primary text-on-primary font-bold px-6 py-2.5 rounded-lg hover:bg-primary-container hover:text-on-primary-container transition-colors shadow-sm">
                Continuar al Pago
            </button>
        </div>
    </div>

    <!-- Columna Derecha: Totales -->
    <div class="lg:col-span-4">
        <!-- Resumen de Orden Sticky -->
        <div class="bg-white border border-gray-200/90 rounded-2xl p-6 shadow-xs sticky top-24 space-y-5">
            
            <h2 class="text-lg font-bold text-[#002349] pb-3 border-b border-gray-100 flex items-center justify-between">
                <span>Resumen de Orden</span>
                <span class="material-symbols-outlined text-[#006148] text-[20px]">receipt_long</span>
            </h2>

            <!-- Desglose de Costos -->
            <div class="space-y-3 text-sm">
                
                <!-- Subtotal -->
                <div class="flex justify-between items-center text-gray-600">
                    <span>Subtotal</span>
                    <span class="font-bold text-gray-900 font-mono">
                        ${{ number_format($resumen['subtotal'], 2) }}
                    </span>
                </div>

                <!-- Descuento por Cupón -->
                @if($resumen['descuento'] > 0 || $carrito->cupon_id)
                    <div class="flex justify-between items-center text-emerald-700 bg-emerald-50/80 px-3 py-2 rounded-xl border border-emerald-200/70">
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">sell</span>
                            <span class="font-semibold text-xs">
                                Cupón: <span class="font-mono font-bold uppercase">{{ $carrito->cupon ? $carrito->cupon->codigo : 'APLICADO' }}</span>
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-bold font-mono text-xs">
                                -${{ number_format($resumen['descuento'], 2) }}
                            </span>
                        </div>
                    </div>
                @endif

                <!-- ITBMS (7%) -->
                <div class="flex justify-between items-center text-gray-600">
                    <span class="flex items-center gap-1">
                        <span>ITBMS (7%)</span>
                        <span class="text-[10px] text-gray-400" title="Impuesto de Transferencia de Bienes Muebles y Servicios">(Ley de Panamá)</span>
                    </span>
                    <span class="font-bold text-gray-900 font-mono">
                        ${{ number_format($resumen['itbms'], 2) }}
                    </span>
                </div>

                <!-- Envío Estimado -->
                <div class="flex justify-between items-center text-gray-600">
                    <div class="flex items-center gap-1">
                        <span>Envío / Retiro</span>
                    </div>
                    <span class="font-bold font-mono {{ $resumen['envio'] == 0 ? 'text-emerald-600' : 'text-gray-900' }}">
                        @if(!($resumen['requiere_envio'] ?? true) || $resumen['envio'] == 0)
                            GRATIS
                        @else
                            ${{ number_format($resumen['envio'], 2) }}
                        @endif
                    </span>
                </div>

                <!-- Indicador de Ubicación si requiere envío -->
                @if($resumen['requiere_envio'] ?? true)
                    <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-200/70 text-xs text-slate-600 mt-2">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <span class="material-symbols-outlined text-[15px] text-emerald-600 shrink-0">local_shipping</span>
                            <span class="truncate text-[11px]">Método: <strong class="text-slate-800 font-semibold">{{ $ubicacion['ubicacion'] }}</strong></span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Total Final -->
            <div class="pt-4 border-t border-gray-100 flex justify-between items-baseline">
                <div>
                    <span class="block text-xs font-semibold text-gray-400 uppercase tracking-wider">Total a Pagar</span>
                    <span class="text-xs text-gray-500">Impuestos y envío incluidos</span>
                </div>
                <span class="text-2xl sm:text-3xl font-extrabold text-[#002349] font-mono tracking-tight">
                    ${{ number_format($resumen['total'], 2) }}
                </span>
            </div>
        </div>
    </div>
</div>

</div>
