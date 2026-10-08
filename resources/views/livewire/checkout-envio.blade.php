<div>
    {{-- Detalles de Contacto --}}
    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm mb-6">
        <h2 class="text-lg font-bold text-primary mb-6 border-b border-outline-variant pb-2">Detalles de Contacto</h2>
        
        <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-on-surface-variant mb-1">Nombre Completo: <span class="text-red-500">*</span></label>
                    <div class="flex gap-2">
                        <input type="text" wire:model="contacto_nombre" class="w-1/2 border border-slate-300 rounded-lg p-2 text-sm focus:ring-secondary focus:border-secondary" placeholder="Nombre">
                        <input type="text" wire:model="contacto_apellido" class="w-1/2 border border-slate-300 rounded-lg p-2 text-sm focus:ring-secondary focus:border-secondary" placeholder="Apellido">
                    </div>
                    @error('contacto_nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    @error('contacto_apellido') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-on-surface-variant mb-1">Correo electrónico: <span class="text-red-500">*</span></label>
                    <input type="email" wire:model="contacto_email" class="w-full border border-slate-300 rounded-lg p-2 text-sm focus:ring-secondary focus:border-secondary" placeholder="Correo electrónico">
                    @error('contacto_email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-on-surface-variant mb-1">Teléfono 1: <span class="text-red-500">*</span></label>
                    <div class="flex gap-2">
                        <select class="w-1/3 border border-slate-300 rounded-lg p-2 text-sm bg-slate-50 focus:ring-secondary focus:border-secondary">
                            <option>Panamá +507</option>
                        </select>
                        <input type="text" wire:model="contacto_telefono1" class="w-2/3 border border-slate-300 rounded-lg p-2 text-sm focus:ring-secondary focus:border-secondary" placeholder="Número de teléfono">
                    </div>
                    @error('contacto_telefono1') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-on-surface-variant mb-1">Teléfono 2:</label>
                    <div class="flex gap-2">
                        <select class="w-1/3 border border-slate-300 rounded-lg p-2 text-sm bg-slate-50 focus:ring-secondary focus:border-secondary">
                            <option>Panamá +507</option>
                        </select>
                        <input type="text" wire:model="contacto_telefono2" class="w-2/3 border border-slate-300 rounded-lg p-2 text-sm focus:ring-secondary focus:border-secondary" placeholder="Número de teléfono">
                    </div>
                    @error('contacto_telefono2') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
    </div>
    {{-- Vista Principal (Resumen y Selección) --}}
    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm mb-6">
        <div class="mb-6 border-b border-slate-100 pb-4">
            <h2 class="text-xl font-bold text-slate-800">Retiro / Delivery / Courier</h2>
        </div>

        <div class="flex justify-center gap-3 mb-6">
            <button wire:click="abrirModalMetodo('retiro_local')" class="px-6 py-2 text-sm font-semibold border rounded transition-colors {{ $metodo_entrega === 'retiro_local' ? 'border-orange-400 bg-orange-50/50 text-orange-600' : 'border-slate-300 text-slate-600 hover:bg-slate-50' }}">Retiro</button>
            <button wire:click="abrirModalMetodo('delivery')" class="px-6 py-2 text-sm font-semibold border rounded transition-colors {{ $metodo_entrega === 'delivery' ? 'border-orange-400 bg-orange-50/50 text-orange-600' : 'border-slate-300 text-slate-600 hover:bg-slate-50' }}">Delivery</button>
            <button wire:click="abrirModalMetodo('retiro_courier')" class="px-6 py-2 text-sm font-semibold border rounded transition-colors {{ $metodo_entrega === 'retiro_courier' ? 'border-orange-400 bg-orange-50/50 text-orange-600' : 'border-slate-300 text-slate-600 hover:bg-slate-50' }}">Courier</button>
        </div>

        @if(!$metodo_entrega)
            <div class="text-center">
                <p class="text-sm text-slate-500">Aún no has configurado tu método de entrega.</p>
            </div>
        @else
            <div class="text-sm text-on-surface-variant bg-slate-50 p-4 rounded-lg border border-slate-100 flex items-start justify-between">
                <div>

                @if($metodo_entrega === 'retiro_local')
                    <p class="font-semibold text-on-background mb-1">Retiro en sucursal PYME PANAMA</p>
                    <p>San Francisco (Ciudad de Panamá)</p>
                @elseif($metodo_entrega === 'delivery')
                    <p class="font-semibold text-on-background mb-1">Delivery a tu Casa u Oficina</p>
                    @if(session('checkout_direccion_id'))
                        @php
                            $dir = \App\Models\Direccion::find(session('checkout_direccion_id'));
                        @endphp
                        @if($dir)
                            <p>{{ $dir->direccion_exacta }}</p>
                            <p>{{ $dir->corregimiento }}, {{ $dir->distrito }}</p>
                        @else
                            <p class="text-red-500">Dirección seleccionada inválida. Por favor cambia tu método.</p>
                        @endif
                    @else
                        <p class="text-orange-600 font-bold mt-2">Falta seleccionar la dirección. Haz clic en "Cambiar".</p>
                    @endif
                @elseif($metodo_entrega === 'retiro_courier')
                    @if($sucursalSeleccionada)
                        @php
                            $suc = \App\Models\CourierSucursal::find($sucursalSeleccionada);
                        @endphp
                        @if($suc)
                            <p class="font-semibold text-on-background mb-1">Retiro por Courier - {{ $suc->courier }}</p>
                            <p class="font-bold">{{ $suc->sucursal }}</p>
                            <p>{{ $suc->zona }}</p>
                        @endif
                    @else
                        <p class="text-orange-600 font-bold mt-2">Falta seleccionar la sucursal. Haz clic en "Cambiar".</p>
                    @endif
                @endif
                </div>
            </div>
            
            @if(
                ($metodo_entrega === 'retiro_local') ||
                ($metodo_entrega === 'delivery' && session('checkout_direccion_id')) ||
                ($metodo_entrega === 'retiro_courier' && $sucursalSeleccionada)
            )
            <div class="mt-6 flex justify-end">
                <button wire:click="continuarCheckout" class="bg-primary text-on-primary font-bold px-6 py-2.5 rounded-lg hover:bg-primary-container hover:text-on-primary-container transition-colors shadow-sm">
                    Continuar al Pago
                </button>
            </div>
            @endif
        @endif
    </div>

    {{-- Modal de Selección --}}
    <x-modal name="modal-checkout-envio" maxWidth="3xl" :show="!$metodo_entrega">
        <div class="p-6 relative max-h-[90vh] overflow-y-auto">
            <button x-on:click="$dispatch('close-modal', 'modal-checkout-envio')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined">close</span>
            </button>
            
            <h2 class="text-xl font-bold text-primary mb-6 text-center">Retiro | Delivery | Courier</h2>

            <div class="grid grid-cols-1 gap-4 mb-6">
                {{-- Opción 1: Retiro Local --}}
                <label class="relative block cursor-pointer group">
                    <input type="radio" wire:model.live="metodo_entrega_modal" value="retiro_local" class="peer sr-only" />
                    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 transition-all duration-200 peer-checked:border-orange-500 peer-checked:shadow-[0_4px_20px_rgba(249,115,22,0.15)] hover:shadow-md flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-base font-bold text-primary group-hover:text-primary transition-colors">Retiro en sucursal PYME PANAMA - <span class="text-emerald-600">Gratis</span></span>
                            </div>
                            <p class="text-sm text-slate-600">San Francisco (Ciudad de Panamá)</p>
                        </div>
                    </div>
                </label>

                {{-- Opción 2: Delivery --}}
                <label class="relative block cursor-pointer group">
                    <input type="radio" wire:model.live="metodo_entrega_modal" value="delivery" class="peer sr-only" />
                    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 transition-all duration-200 peer-checked:border-orange-500 peer-checked:bg-orange-50/30 peer-checked:shadow-[0_4px_20px_rgba(249,115,22,0.15)] hover:shadow-md flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-base font-bold text-primary group-hover:text-primary transition-colors">Delivery a tu Casa u Oficina</span>
                            </div>
                            <p class="text-sm text-slate-600">Ciudad de Panamá / Panamá Pacífico / Panamá Norte / La Chorrera / Arraiján</p>
                        </div>
                    </div>
                </label>

                {{-- Opción 3: Courier --}}
                <label class="relative block cursor-pointer group">
                    <input type="radio" wire:model.live="metodo_entrega_modal" value="retiro_courier" class="peer sr-only" />
                    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4 transition-all duration-200 peer-checked:border-orange-500 peer-checked:bg-orange-50/30 peer-checked:shadow-[0_4px_20px_rgba(249,115,22,0.15)] hover:shadow-md flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-base font-bold text-primary group-hover:text-primary transition-colors">Retiro en una Sucursal de Courier</span>
                            </div>
                            <p class="text-sm text-slate-600">Todo El País (Uno Express / Servientrega / Fletes Chavale)</p>
                        </div>
                    </div>
                </label>
            </div>

            {{-- Secciones Dinámicas --}}
            @if($metodo_entrega_modal === 'retiro_local')
                <div class="flex justify-center mt-6">
                    <button wire:click="continuarRetiroLocal" class="bg-emerald-600 text-white font-bold px-6 py-2.5 rounded-lg hover:bg-emerald-700 transition-colors">
                        Guardar cambios
                    </button>
                </div>
            @elseif($metodo_entrega_modal === 'delivery')
                <div class="mt-6 border-t border-slate-200 pt-6">
                    <livewire:gestion-direcciones :compact="true" :mostrarPredeterminada="false" :zonasEnvio="$zonasEnvio" :requiereEnvio="$requiereEnvio" />
                </div>
            @elseif($metodo_entrega_modal === 'retiro_courier')
                <div class="mt-6 border-t border-slate-200 pt-6">
                    <h3 class="text-lg font-bold text-orange-600 mb-4">¿En cuál zona?</h3>
                    <div class="flex items-center gap-4 mb-6">
                        <label class="font-bold text-primary w-24">Zona:</label>
                        <select wire:model.live="zonaSeleccionada" class="flex-grow border border-slate-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500 text-sm">
                            <option value="">Seleccione una zona</option>
                            @foreach($this->zonasCourier as $zona)
                                <option value="{{ $zona }}">{{ $zona }}</option>
                            @endforeach
                        </select>
                        <a href="#" class="text-blue-500 hover:underline text-sm whitespace-nowrap {{ !$zonaSeleccionada ? 'opacity-50 pointer-events-none' : '' }}">Ver mapa</a>
                    </div>

                    <h3 class="text-lg font-bold text-orange-600 mb-4">¿Con cuál courier?</h3>
                    <div class="flex items-center gap-4 mb-6">
                        <label class="font-bold text-primary w-24">Courier:</label>
                        <select wire:model.live="courierSeleccionado" class="flex-grow border border-slate-300 rounded-lg p-2 focus:ring-orange-500 focus:border-orange-500 text-sm" {{ !$zonaSeleccionada ? 'disabled' : '' }}>
                            <option value="">Por favor seleccione</option>
                            @foreach($this->couriers as $courier)
                                <option value="{{ $courier }}">{{ $courier }}</option>
                            @endforeach
                        </select>
                    </div>

                    <h3 class="text-lg font-bold text-orange-600 mb-4">¿En cuál sucursal?</h3>
                    <div class="space-y-4 mb-6">
                        @if(!$zonaSeleccionada || !$courierSeleccionado)
                            <div class="bg-cyan-50 border border-cyan-100 text-cyan-800 p-4 rounded-lg text-sm">
                                Por Favor selecciona el Courier
                            </div>
                        @else
                            @foreach($this->sucursalesCourier as $suc)
                                <label class="block cursor-pointer relative">
                                    <input type="radio" name="sucursal" wire:model.live="sucursalSeleccionada" value="{{ $suc->id }}" class="peer sr-only" />
                                    <div class="p-4 border-2 border-slate-200 rounded-xl peer-checked:border-orange-500 peer-checked:bg-orange-50/50 transition-all">
                                        <div class="pr-8">
                                            <div class="font-bold text-primary mb-1">{{ $suc->sucursal }}</div>
                                            <div class="text-sm text-slate-600">{{ $suc->direccion }}</div>
                                            @if($suc->tarifa_uno_hasta_7lb)
                                                <div class="text-xs text-emerald-600 mt-2 font-bold">Tarifa (hasta 7lb): ${{ number_format($suc->tarifa_uno_hasta_7lb, 2) }}</div>
                                            @endif
                                            @if($suc->telefono)
                                                <div class="text-xs text-slate-500 mt-1">Tel: {{ $suc->telefono }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="absolute top-4 right-4 text-orange-500 opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none flex items-center justify-center">
                                        <span class="material-symbols-outlined" style="font-size: 24px;">check_circle</span>
                                    </div>
                                </label>
                            @endforeach
                            @error('sucursalSeleccionada') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        @endif
                    </div>

                    <div class="flex justify-center mt-6">
                        <button wire:click="continuarCourier" class="bg-emerald-600 text-white font-bold px-6 py-2.5 rounded-lg hover:bg-emerald-700 transition-colors">
                            Guardar cambios
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </x-modal>
</div>
