@extends('layouts.cliente')

@section('title', 'Dirección de Envío')

@section('content')
<div class="flex-grow pt-8 pb-12 px-4 md:px-16 max-w-7xl mx-auto w-full">
    <!-- Progress Indicator -->
    <div class="mb-12 flex justify-center w-full max-w-3xl mx-auto">
        <div class="flex items-center w-full relative">
            <!-- Step 1: Address (Active) -->
            <div class="flex flex-col items-center relative z-10 w-1/3">
                <div class="w-8 h-8 rounded-full bg-primary text-on-primary flex items-center justify-center font-numeric-data text-xl font-semibold mb-2 shadow-[0_4px_20px_rgba(0,35,73,0.12)]">1</div>
                <span class="font-label-caps text-xs font-semibold uppercase tracking-wide text-primary text-center">Dirección</span>
            </div>
            <!-- Connector 1-2 -->
            <div class="absolute top-4 left-[16.6%] right-[50%] h-[2px] bg-outline-variant -z-10"></div>
            <!-- Step 2: Payment (Pending) -->
            <div class="flex flex-col items-center relative z-10 w-1/3">
                <div class="w-8 h-8 rounded-full bg-surface-container border-2 border-outline-variant text-outline flex items-center justify-center font-numeric-data text-xl font-semibold mb-2">2</div>
                <span class="font-label-caps text-xs font-semibold uppercase tracking-wide text-on-surface-variant text-center">Pago</span>
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
        <h1 class="font-headline-md text-2xl font-bold text-primary mb-2 md:text-4xl md:mb-2">Seleccione su dirección de envío</h1>
        <p class="text-on-surface-variant font-body-md text-base">Elija una dirección guardada o agregue una nueva para su entrega.</p>
    </div>

    <form action="{{ route('cliente.checkout.guardar-direccion') }}" method="POST" class="max-w-4xl mx-auto space-y-8">
        @csrf

        <!-- Saved Addresses Grid -->
        @if($direcciones->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            @foreach($direcciones as $dir)
            <label class="relative block cursor-pointer group">
                <input type="radio" name="direccion_id" value="{{ $dir->id }}" class="peer sr-only" {{ $loop->first ? 'checked' : '' }} />
                <div class="h-full bg-surface-container-lowest border border-outline-variant rounded-xl p-6 transition-all duration-200 peer-checked:border-secondary peer-checked:shadow-[0_4px_20px_rgba(0,35,73,0.05)] hover:shadow-[0_4px_20px_rgba(0,35,73,0.05)]">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-2xl">
                                {{ strtolower($dir->alias) == 'casa' ? 'home' : (strtolower($dir->alias) == 'oficina' ? 'work' : 'location_on') }}
                            </span>
                            <span class="font-headline-md text-lg font-semibold text-primary">{{ $dir->alias }}</span>
                            @if($loop->first)
                                <span class="bg-secondary/10 text-secondary font-label-caps text-[10px] uppercase font-bold tracking-wider px-2 py-1 rounded-sm ml-2">Predeterminada</span>
                            @endif
                        </div>
                        <div class="w-5 h-5 rounded-full border-2 border-outline-variant flex items-center justify-center peer-checked:group-[]:border-secondary peer-checked:group-[]:bg-secondary transition-colors">
                            <span class="material-symbols-outlined text-on-secondary text-[14px] opacity-0 peer-checked:group-[]:opacity-100">check</span>
                        </div>
                    </div>
                    <div class="space-y-1 text-on-surface-variant font-body-md text-base">
                        <p class="font-semibold text-on-background">{{ $dir->nombre_receptor }}</p>
                        <p>{{ $dir->direccion_exacta }}</p>
                        <p>{{ $dir->corregimiento }}, {{ $dir->distrito }}</p>
                        <p>{{ $dir->provincia }}</p>
                        @if($dir->referencia)
                            <p class="text-sm mt-2 text-outline">
                                <span class="font-label-caps font-semibold uppercase tracking-wider text-xs">Referencia:</span> {{ $dir->referencia }}
                            </p>
                        @endif
                    </div>
                </div>
            </label>
            @endforeach
            
            <label class="relative block cursor-pointer group">
                <input type="radio" name="direccion_id" value="" class="peer sr-only" id="radio_nueva_direccion" />
                <div class="h-full bg-surface-container border border-dashed border-outline-variant rounded-xl p-6 transition-all duration-200 hover:bg-surface-variant/50 flex flex-col items-center justify-center min-h-[200px]">
                    <span class="material-symbols-outlined text-outline text-4xl mb-2">add_circle</span>
                    <span class="font-headline-md text-lg font-semibold text-primary">Ingresar Nueva</span>
                </div>
            </label>
        </div>
        @else
            <input type="hidden" name="direccion_id" value="" id="radio_nueva_direccion" checked>
        @endif

        <div id="form_nueva_direccion" class="{{ $direcciones->count() > 0 ? 'hidden' : 'block' }} bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm mb-8">
            <h2 class="font-headline-md text-xl font-semibold text-primary mb-6">Detalles de la nueva dirección</h2>
            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                <div>
                    <label for="alias" class="block text-sm font-semibold text-on-surface-variant mb-1">Alias (Ej: Casa, Trabajo)</label>
                    <input type="text" name="alias" id="alias" class="block w-full rounded-md border-outline-variant shadow-sm focus:border-secondary focus:ring-secondary sm:text-sm bg-surface-container-lowest">
                </div>

                <div>
                    <label for="nombre_receptor" class="block text-sm font-semibold text-on-surface-variant mb-1">Nombre de quien recibe</label>
                    <input type="text" name="nombre_receptor" id="nombre_receptor" class="block w-full rounded-md border-outline-variant shadow-sm focus:border-secondary focus:ring-secondary sm:text-sm bg-surface-container-lowest">
                </div>

                <div>
                    <label for="provincia" class="block text-sm font-semibold text-on-surface-variant mb-1">Provincia</label>
                    <input type="text" name="provincia" id="provincia" class="block w-full rounded-md border-outline-variant shadow-sm focus:border-secondary focus:ring-secondary sm:text-sm bg-surface-container-lowest">
                </div>

                <div>
                    <label for="distrito" class="block text-sm font-semibold text-on-surface-variant mb-1">Distrito</label>
                    <input type="text" name="distrito" id="distrito" class="block w-full rounded-md border-outline-variant shadow-sm focus:border-secondary focus:ring-secondary sm:text-sm bg-surface-container-lowest">
                </div>
                
                <div>
                    <label for="corregimiento" class="block text-sm font-semibold text-on-surface-variant mb-1">Corregimiento</label>
                    <input type="text" name="corregimiento" id="corregimiento" class="block w-full rounded-md border-outline-variant shadow-sm focus:border-secondary focus:ring-secondary sm:text-sm bg-surface-container-lowest">
                </div>

                <div class="sm:col-span-2">
                    <label for="direccion_exacta" class="block text-sm font-semibold text-on-surface-variant mb-1">Dirección Exacta</label>
                    <textarea name="direccion_exacta" id="direccion_exacta" rows="3" class="block w-full rounded-md border-outline-variant shadow-sm focus:border-secondary focus:ring-secondary sm:text-sm bg-surface-container-lowest"></textarea>
                </div>
                
                <div class="sm:col-span-2">
                    <label for="referencia" class="block text-sm font-semibold text-on-surface-variant mb-1">Referencia (Opcional)</label>
                    <input type="text" name="referencia" id="referencia" class="block w-full rounded-md border-outline-variant shadow-sm focus:border-secondary focus:ring-secondary sm:text-sm bg-surface-container-lowest">
                </div>
            </div>
        </div>

        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm mb-8">
            <h2 class="font-headline-md text-xl font-semibold text-primary mb-2">Zona de Envío</h2>
            <p class="text-on-surface-variant font-body-md text-sm mb-4">Selecciona la zona de envío correspondiente para calcular el costo.</p>
            
            <select name="zona_envio_id" id="zona_envio_id" required class="block w-full rounded-md border-outline-variant shadow-sm py-3 pl-3 pr-10 text-base focus:border-secondary focus:outline-none focus:ring-secondary font-body-md bg-surface-container-lowest">
                <option value="">Seleccione una zona...</option>
                @foreach($zonasEnvio as $zona)
                    <option value="{{ $zona->id }}">{{ $zona->nombre }} - ${{ number_format($zona->costo, 2) }}</option>
                @endforeach
            </select>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 mt-8 pt-6 border-t border-outline-variant">
            <a href="{{ route('cliente.carrito') }}" class="text-on-surface-variant hover:text-primary font-label-caps text-xs font-semibold uppercase tracking-wide transition-colors flex items-center gap-2 w-full sm:w-auto justify-center">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                Volver al Carrito
            </a>
            
            <button type="submit" class="bg-primary text-on-primary font-label-caps text-xs font-semibold uppercase tracking-wide px-8 py-4 rounded-lg hover:bg-primary-container transition-colors shadow-[0_4px_20px_rgba(0,35,73,0.12)] w-full sm:w-auto text-center flex justify-center items-center gap-2">
                Continuar al Pago
                <span class="material-symbols-outlined text-sm">arrow_forward</span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const radios = document.querySelectorAll('input[name="direccion_id"]');
        const formNueva = document.getElementById('form_nueva_direccion');
        
        if (formNueva) {
            const inputsNueva = formNueva.querySelectorAll('input[type="text"], textarea');

            radios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if(this.value === '') {
                        formNueva.classList.remove('hidden');
                        inputsNueva.forEach(input => input.required = true);
                        if(document.getElementById('referencia')) document.getElementById('referencia').required = false;
                    } else {
                        formNueva.classList.add('hidden');
                        inputsNueva.forEach(input => input.required = false);
                    }
                });
            });
        }
    });
</script>
@endpush
@endsection
