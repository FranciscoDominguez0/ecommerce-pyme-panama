<div>
    @if($compact)
        {{-- ====================== MODO CHECKOUT (compact) ====================== --}}
        <form wire:submit="continuar" novalidate class="max-w-4xl mx-auto space-y-8">

            {{-- Direcciones guardadas --}}
            @if($this->direcciones->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                @foreach($this->direcciones as $dir)
                <label class="relative block cursor-pointer group">
                    <input type="radio" name="seleccion" value="{{ $dir->id }}" wire:model.live="seleccion" class="peer sr-only" />
                    <div class="h-full bg-surface-container-lowest border border-outline-variant rounded-xl p-6 transition-all duration-200 peer-checked:border-secondary peer-checked:shadow-[0_4px_20px_rgba(0,35,73,0.05)] hover:shadow-[0_4px_20px_rgba(0,35,73,0.05)]">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-2xl">
                                    {{ strtolower($dir->alias) == 'casa' ? 'home' : (strtolower($dir->alias) == 'oficina' ? 'work' : 'location_on') }}
                                </span>
                                <span class="text-base font-semibold text-primary">{{ $dir->alias }}</span>
                                @if($dir->es_predeterminada)
                                    <span class="bg-secondary/10 text-secondary font-label-caps text-[10px] uppercase font-bold tracking-wider px-2 py-1 rounded-sm ml-2">Predeterminada</span>
                                @endif
                            </div>
                            <div class="w-5 h-5 rounded-full border-2 border-outline-variant flex items-center justify-center peer-checked:group-[]:border-secondary peer-checked:group-[]:bg-secondary transition-colors">
                                <span class="material-symbols-outlined text-on-secondary text-[14px] opacity-0 peer-checked:group-[]:opacity-100">check</span>
                            </div>
                        </div>
                        <div class="space-y-1 text-on-surface-variant text-sm">
                            <p class="font-semibold text-on-background">{{ $dir->nombre_receptor }}</p>
                            <p>{{ $dir->direccion_exacta }}</p>
                            <p>{{ $dir->corregimiento }}, {{ $dir->distrito }}</p>
                            <p>{{ $dir->provincia }}</p>
                            @if($dir->referencia)
                                <p class="text-xs mt-2 text-outline">
                                    <span class="font-label-caps font-semibold uppercase tracking-wider text-xs">Referencia:</span> {{ $dir->referencia }}
                                </p>
                            @endif
                        </div>
                    </div>
                </label>
                @endforeach

                <label class="relative block cursor-pointer group">
                    <input type="radio" name="seleccion" value="nueva" wire:model.live="seleccion" class="peer sr-only" id="radio_nueva_direccion" />
                    <div class="h-full bg-surface-container border border-dashed border-outline-variant rounded-xl p-6 transition-all duration-200 hover:bg-surface-variant/50 flex flex-col items-center justify-center min-h-[200px]">
                        <span class="material-symbols-outlined text-outline text-4xl mb-2">add_circle</span>
                        <span class="text-base font-semibold text-primary">Ingresar Nueva</span>
                    </div>
                </label>
            </div>
            @endif

            @error('seleccion')
                <p class="text-xs text-error flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
            @enderror

            <div id="form_nueva_direccion" class="{{ $this->seleccion === 'nueva' ? 'block' : 'hidden' }} bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm mb-8">
                <h2 class="text-base font-bold text-primary mb-6">Detalles de la nueva dirección</h2>
                @include('livewire.partials.direccion-fields')
            </div>

            {{-- Zona de Envío --}}
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm mb-8">
                <h2 class="text-base font-bold text-primary mb-2">Zona de Envío</h2>
                <p class="text-on-surface-variant text-xs mb-4">Selecciona la zona de envío correspondiente para calcular el costo.</p>

                <select name="zona_envio_id" id="zona_envio_id" wire:model="zonaEnvioId" required
                    class="block w-full rounded-md border-outline-variant shadow-sm py-3 pl-3 pr-10 text-sm focus:border-secondary focus:outline-none focus:ring-secondary bg-surface-container-lowest @error('zonaEnvioId') border-error @enderror">
                    <option value="">Seleccione una zona...</option>
                    @foreach($this->zonasEnvio as $zona)
                        <option value="{{ $zona['id'] }}">{{ $zona['nombre'] }} - ${{ number_format($zona['costo'], 2) }}</option>
                    @endforeach
                </select>
                @error('zonaEnvioId')
                    <p class="mt-1 text-xs text-error flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
                @enderror
            </div>

            {{-- Botones de acción --}}
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mt-8 pt-6 border-t border-outline-variant">
                <a href="{{ route('cliente.carrito') }}" wire:navigate class="text-on-surface-variant hover:text-primary font-label-caps text-xs font-semibold uppercase tracking-wide transition-colors flex items-center gap-2 w-full sm:w-auto justify-center">
                    <span class="material-symbols-outlined text-sm">arrow_back</span>
                    Volver al Carrito
                </a>

                <button type="submit" wire:loading.attr="disabled"
                    class="bg-primary text-on-primary font-label-caps text-xs font-semibold uppercase tracking-wide px-8 py-4 rounded-lg hover:bg-primary-container transition-colors shadow-[0_4px_20px_rgba(0,35,73,0.12)] w-full sm:w-auto text-center flex justify-center items-center gap-2">
                    Continuar al Pago
                    <span class="material-symbols-outlined text-sm">arrow_forward</span>
                </button>
            </div>
        </form>
    @else
        {{-- ====================== MODO MI-CUENTA (gestion) ====================== --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-4">
            <div>
                <h3 class="text-base font-bold text-primary">Direcciones de Envío</h3>
                <p class="text-xs text-on-surface-variant mt-0.5">Administra tus direcciones de entrega.</p>
            </div>
            <button type="button" wire:click="abrirNueva"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-on-primary text-xs font-bold uppercase tracking-wider hover:bg-primary-container transition-colors shadow-sm shrink-0">
                <span class="material-symbols-outlined text-[16px]">add</span>
                + Agregar dirección
            </button>
        </div>

        @if($this->direcciones->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8" id="lista-direcciones">
                @foreach($this->direcciones as $dir)
                    @php
                        $iconoAlias = match(mb_strtolower($dir->alias)) {
                            'casa' => 'home',
                            'oficina', 'trabajo' => 'work',
                            'apartamento' => 'apartment',
                            default => 'location_on',
                        };
                    @endphp
                    <div class="card-direccion bg-surface-container-lowest border border-outline-variant rounded-xl p-6 flex flex-col gap-4 ambient-shadow ambient-shadow-hover transition-all duration-300 relative group">
                        <div class="flex justify-between items-start">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-primary text-2xl">{{ $iconoAlias }}</span>
                                <span class="text-base font-semibold text-primary">{{ $dir->alias }}</span>
                                @if($dir->es_predeterminada)
                                    <span class="bg-secondary/10 text-secondary font-label-caps text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-sm">Predeterminada</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button" wire:click="iniciarEdicion({{ $dir->id }})"
                                    class="p-1.5 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container-low transition-colors"
                                    title="Editar">
                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                </button>
                                <button type="button"
                                    data-alias="{{ $dir->alias }}"
                                    data-direccion="{{ $dir->direccion_exacta }}"
                                    @click="window.ModalEliminar.abrir({
                                        livewireEvent: 'eliminar-direccion',
                                        livewireData: { id: {{ $dir->id }} },
                                        nombre: $el.dataset.alias + ' - ' + ($el.dataset.direccion.length > 40 ? $el.dataset.direccion.substring(0, 40) + '...' : $el.dataset.direccion),
                                        id: 'modal-eliminar-direccion'
                                    })"
                                    class="p-1.5 rounded-lg text-on-surface-variant hover:text-error hover:bg-error-container/50 transition-colors"
                                    title="Eliminar">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </div>
                        </div>

                        <div class="space-y-1 text-sm text-on-surface-variant">
                            <p class="font-semibold text-on-background">{{ $dir->nombre_receptor }}</p>
                            <p>{{ $dir->direccion_exacta }}</p>
                            <p>{{ $dir->corregimiento }}, {{ $dir->distrito }}</p>
                            <p>{{ $dir->provincia }}</p>
                            @if($dir->referencia)
                                <p class="text-xs mt-1 text-outline">
                                    <span class="font-label-caps font-semibold uppercase tracking-wider text-xs">Ref:</span> {{ $dir->referencia }}
                                </p>
                            @endif
                        </div>

                        @if(!$dir->es_predeterminada)
                            <button type="button" wire:click="establecerPredeterminada({{ $dir->id }})"
                                class="mt-auto pt-2 border-t border-outline-variant/50 w-full text-center text-xs font-semibold text-secondary hover:text-secondary-container transition-colors py-1.5 rounded-lg hover:bg-surface-container-low">
                                Establecer como predeterminada
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div id="estado-vacio" class="text-center py-12 bg-surface-container-lowest rounded-xl border border-outline-variant ambient-shadow mb-8">
                <span class="material-symbols-outlined text-6xl text-outline-variant mb-4">map</span>
                <h3 class="text-base font-bold text-primary mb-2">No tienes direcciones guardadas</h3>
                <p class="text-on-surface-variant text-sm mb-6">Agrega una dirección para facilitar tus próximas compras.</p>
                <button type="button" wire:click="abrirNueva" id="btn-agregar-vacio"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-primary text-on-primary text-xs font-bold uppercase tracking-wider hover:bg-primary-container transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-[16px]">add</span>
                    + Agregar dirección
                </button>
            </div>
        @endif

        <div id="form-container" class="{{ $this->mostrarFormulario ? 'block' : 'hidden' }} bg-white border border-outline-variant rounded-xl p-6 shadow-sm mb-8">
            <div class="flex items-center gap-2 mb-2">
                <button type="button" wire:click="cerrarFormulario" class="p-1 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container-low transition-colors">
                    <span class="material-symbols-outlined">arrow_back</span>
                </button>
                <h2 id="form-titulo" class="text-xl font-bold text-primary">{{ $this->editandoId ? 'Editar Dirección' : 'Agregar Dirección' }}</h2>
            </div>
            <p id="form-subtitulo" class="text-sm text-on-surface-variant mb-6">{{ $this->editandoId ? 'Actualiza los datos de tu dirección de envío guardada.' : 'Completa los datos de tu dirección de envío.' }}</p>

            <form wire:submit="guardar" novalidate>
                @include('livewire.partials.direccion-fields')

                <div class="flex justify-end gap-3 pt-4 border-t border-outline-variant mt-6">
                    <button type="button" wire:click="cerrarFormulario"
                        class="px-6 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant text-xs font-semibold hover:bg-surface-container-low transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" wire:loading.attr="disabled"
                        class="px-6 py-2.5 rounded-lg bg-primary text-on-primary text-xs font-bold uppercase tracking-wider hover:bg-primary-container transition-colors shadow-sm flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px]">save</span>
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>

        <x-modal-eliminar
            id="modal-eliminar-direccion"
            titulo="¿Eliminar esta dirección?"
            mensaje="Esta dirección dejará de estar disponible para tus pedidos."
            icono="delete"
            textoBoton="Eliminar dirección"
        />
    @endif
</div>
