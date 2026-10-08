<div class="w-full">
    <div class="bg-white border border-slate-200 rounded-lg p-6 shadow-sm mb-6">
        <h2 class="text-2xl font-bold text-slate-800 mb-4">
            Retiro / Delivery / Courier
        </h2>
        <hr class="border-slate-200 mb-6">

        <div class="flex justify-center gap-0 sm:gap-2 mb-8">
            <button wire:click="abrirModalMetodo('retiro_local')" 
                    class="px-5 sm:px-8 py-2 text-[15px] border transition-colors {{ $metodo_entrega === 'retiro_local' ? 'border-emerald-400 text-emerald-500 bg-emerald-50/30' : 'border-slate-400 text-slate-500 hover:bg-slate-50' }}">
                Retiro
            </button>
            <button wire:click="abrirModalMetodo('delivery')" 
                    class="px-5 sm:px-8 py-2 text-[15px] border transition-colors {{ $metodo_entrega === 'delivery' ? 'border-emerald-400 text-emerald-500 bg-emerald-50/30' : 'border-slate-400 text-slate-500 hover:bg-slate-50' }}">
                Delivery
            </button>
            <button wire:click="abrirModalMetodo('retiro_courier')" 
                    class="px-5 sm:px-8 py-2 text-[15px] border transition-colors {{ $metodo_entrega === 'retiro_courier' ? 'border-[#ff8c69] text-[#ff6b3d] bg-[#fff5f2]' : 'border-slate-400 text-slate-500 hover:bg-slate-50' }}">
                Courier
            </button>
        </div>

        @if(!$metodo_entrega)
            <div class="text-center py-8 px-4 bg-slate-50 rounded border border-dashed border-slate-300">
                <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">package_2</span>
                <p class="text-slate-500 font-medium">Aún no has configurado tu método de entrega.</p>
                <button wire:click="abrirModalMetodo('delivery')" class="mt-3 text-emerald-600 font-bold hover:underline text-sm">Configurar ahora</button>
            </div>
        @else
            <div class="px-2 sm:px-6">
                @if($metodo_entrega === 'retiro_local')
                    <ul class="list-disc ml-4 space-y-3 text-[15px] text-slate-800">
                        <li><strong>Sucursal:</strong> PYME Panamá - San Francisco</li>
                        <li><strong>Dirección:</strong> Planta Baja, Calle 67 Este <a href="#" class="text-blue-500 hover:underline ml-1">(Ver mapa)</a></li>
                        <li><strong>Costo de Retiro:</strong> GRATIS</li>
                    </ul>
                @elseif($metodo_entrega === 'delivery')
                    @if(session('checkout_direccion_id'))
                        @php
                            $dir = \App\Models\Direccion::with('zonaEnvio')->find(session('checkout_direccion_id'));
                        @endphp
                        @if($dir)
                            <ul class="list-disc ml-4 space-y-3 text-[15px] text-slate-800">
                                <li><strong>Zona:</strong> {{ $dir->zonaEnvio ? $dir->zonaEnvio->nombre : 'Zona por defecto' }}</li>
                                <li><strong>Dirección:</strong> {{ $dir->direccion_exacta }}</li>
                                <li><strong>Costo de Delivery:</strong> ${{ $dir->zonaEnvio ? number_format($dir->zonaEnvio->costo, 2) : '0.00' }} USD</li>
                            </ul>
                        @else
                            <div class="text-red-500 text-sm font-bold py-2">Dirección seleccionada inválida. <button wire:click="abrirModalMetodo('delivery')" class="underline">Cambiar</button></div>
                        @endif
                    @else
                        <div class="text-emerald-600 text-sm font-bold py-2 cursor-pointer hover:underline" wire:click="abrirModalMetodo('delivery')">
                            Falta seleccionar la dirección. Haz clic aquí para Cambiar.
                        </div>
                    @endif
                @elseif($metodo_entrega === 'retiro_courier')
                    @if($sucursalSeleccionada)
                        @php
                            $suc = \App\Models\CourierSucursal::find($sucursalSeleccionada);
                        @endphp
                        @if($suc)
                            <ul class="list-disc ml-4 space-y-3 text-[15px] text-slate-800">
                                <li><strong>Zona del Courier:</strong> {{ $suc->zona }} <a href="#" class="text-blue-500 hover:underline ml-1">(Ver mapa)</a></li>
                                <li><strong>Lugar del Courier:</strong> {{ $suc->courier }} {{ $suc->sucursal }}</li>
                                <li><strong>Servicio de Courier:</strong> Recoger en Tienda de Courier</li>
                                <li><strong>Costo de Servicio:</strong> ${{ number_format($suc->tarifa_uno_hasta_7lb, 2) }} USD</li>
                            </ul>
                        @endif
                    @else
                        <div class="text-emerald-600 text-sm font-bold py-2 cursor-pointer hover:underline" wire:click="abrirModalMetodo('retiro_courier')">
                            Falta seleccionar la sucursal. Haz clic aquí para Cambiar.
                        </div>
                    @endif
                @endif
            </div>
        @endif
    </div>

    {{-- Modal de Selección --}}
    <x-modal name="modal-calculadora-envio" maxWidth="3xl" :show="!$metodo_entrega">
        <div class="p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-primary">Selecciona cómo deseas recibir tu pedido</h2>
                <button wire:click="$dispatch('close-modal', 'modal-calculadora-envio')" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <!-- Tabs de métodos -->
            <div class="flex space-x-1 bg-slate-100 p-1 rounded-lg mb-6">
                <button wire:click="$set('metodo_entrega_modal', 'retiro_local')" 
                    class="flex-1 py-2 text-sm font-medium rounded-md transition-colors {{ $metodo_entrega_modal === 'retiro_local' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-600 hover:bg-slate-200' }}">
                    Retiro en Local
                </button>
                <button wire:click="$set('metodo_entrega_modal', 'delivery')" 
                    class="flex-1 py-2 text-sm font-medium rounded-md transition-colors {{ $metodo_entrega_modal === 'delivery' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-600 hover:bg-slate-200' }}">
                    Delivery
                </button>
                <button wire:click="$set('metodo_entrega_modal', 'retiro_courier')" 
                    class="flex-1 py-2 text-sm font-medium rounded-md transition-colors {{ $metodo_entrega_modal === 'retiro_courier' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-600 hover:bg-slate-200' }}">
                    Envío por Courier
                </button>
            </div>

            <div class="min-h-[250px]">
                @if($metodo_entrega_modal === 'retiro_local')
                    <div class="bg-blue-50 border border-blue-100 p-4 rounded-xl mb-4">
                        <h4 class="font-semibold text-blue-900 mb-2">Retiro en Sucursal San Francisco</h4>
                        <p class="text-sm text-blue-800">Dirección: Local Pyme Panamá, Planta Baja, Calle 67 Este, San Francisco, Ciudad de Panamá</p>
                        <p class="text-sm text-blue-800 mt-1">Horario: Lunes a Sábado, 9:00 AM - 6:00 PM</p>
                    </div>
                @elseif($metodo_entrega_modal === 'delivery')
                    @if(Auth::check())
                        <div class="mb-4">
                            <h4 class="font-semibold text-slate-800 mb-3">Tus direcciones guardadas</h4>
                            <livewire:gestion-direcciones :compact="true" />
                        </div>
                    @else
                        <div class="text-center py-6">
                            <p class="text-slate-600 mb-4">Inicia sesión para seleccionar una dirección guardada o ingresa una nueva.</p>
                            <a href="{{ route('login') }}" class="inline-block bg-primary text-on-primary px-6 py-2 rounded-lg font-semibold hover:bg-primary-container hover:text-on-primary-container transition-colors">Iniciar Sesión</a>
                        </div>
                    @endif
                @elseif($metodo_entrega_modal === 'retiro_courier')
                    <div class="mt-2 border-t border-slate-200 pt-6">
                        <h3 class="text-lg font-bold text-emerald-600 mb-4">¿En cuál zona?</h3>
                        <div class="flex items-center gap-4 mb-6">
                            <label class="font-bold text-primary w-24">Zona:</label>
                            <select wire:model.live="zonaSeleccionada" class="flex-grow border border-slate-300 rounded-lg p-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                                <option value="">Seleccione una zona</option>
                                @foreach(\App\Models\CourierSucursal::where('activo', true)->select('zona')->distinct()->pluck('zona') as $zona)
                                    <option value="{{ $zona }}">{{ $zona }}</option>
                                @endforeach
                            </select>
                            <a href="#" class="text-blue-500 hover:underline text-sm whitespace-nowrap {{ !$zonaSeleccionada ? 'opacity-50 pointer-events-none' : '' }}">Ver mapa</a>
                        </div>
                        
                        <h3 class="text-lg font-bold text-emerald-600 mb-4">¿Con cuál courier?</h3>
                        <div class="flex items-center gap-4 mb-6">
                            <label class="font-bold text-primary w-24">Courier:</label>
                            <select wire:model.live="courierSeleccionado" class="flex-grow border border-slate-300 rounded-lg p-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm" {{ !$zonaSeleccionada ? 'disabled' : '' }}>
                                <option value="">Por favor seleccione</option>
                                @foreach($this->couriers as $courier)
                                    <option value="{{ $courier }}">{{ $courier }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <h3 class="text-lg font-bold text-emerald-600 mb-4">¿En cuál sucursal?</h3>
                        <div class="space-y-4 mb-6">
                            @if(!$zonaSeleccionada || !$courierSeleccionado)
                                <div class="bg-cyan-50 border border-cyan-100 text-cyan-800 p-4 rounded-lg text-sm">
                                    Por Favor selecciona el Courier
                                </div>
                            @else
                                @foreach($this->sucursales as $suc)
                                    <label class="block cursor-pointer relative">
                                        <input type="radio" name="sucursal" wire:model.live="sucursalSeleccionada" value="{{ $suc->id }}" class="peer sr-only" />
                                        <div class="p-4 border-2 border-slate-200 rounded-xl peer-checked:border-emerald-500 peer-checked:bg-emerald-50/50 transition-all">
                                            <div class="pr-8">
                                                <div class="font-bold text-primary mb-1">{{ $suc->sucursal }}</div>
                                                <div class="text-sm text-slate-600">{{ $suc->direccion }}</div>

                                            </div>
                                        </div>
                                        <div class="absolute top-4 right-4 text-emerald-500 opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none flex items-center justify-center">
                                            <span class="material-symbols-outlined" style="font-size: 24px;">check_circle</span>
                                        </div>
                                    </label>
                                @endforeach
                                @error('sucursalSeleccionada') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <button wire:click="$dispatch('close-modal', 'modal-calculadora-envio')" class="px-5 py-2.5 text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                    Cancelar
                </button>
                @if($metodo_entrega_modal !== 'delivery')
                    <button wire:click="confirmarSeleccion" class="px-5 py-2.5 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors">
                        Confirmar Selección
                    </button>
                @endif
            </div>
        </div>
    </x-modal>
</div>
