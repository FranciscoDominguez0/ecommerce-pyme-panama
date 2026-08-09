@extends('layouts.cliente')

@section('title', 'Resumen y Confirmación')

@section('content')
<div class="flex-grow pt-8 pb-12 px-4 md:px-16 max-w-7xl mx-auto w-full">
    <!-- Progress Indicator -->
    <div class="mb-12 flex justify-center w-full max-w-3xl mx-auto">
        <div class="flex items-center w-full relative">
            <!-- Step 1: Address (Completed) -->
            <div class="flex flex-col items-center relative z-10 w-1/3">
                <a href="{{ route('cliente.checkout.direccion') }}" class="w-8 h-8 rounded-full bg-secondary text-on-secondary flex items-center justify-center mb-2 hover:bg-secondary-container hover:text-on-secondary-container transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">check</span>
                </a>
                <span class="font-label-caps text-xs font-semibold uppercase tracking-wide text-secondary text-center">Dirección</span>
            </div>
            <!-- Connector 1-2 -->
            <div class="absolute top-4 left-[16.6%] right-[50%] h-[2px] bg-secondary -z-10"></div>
            <!-- Step 2: Payment (Completed) -->
            <div class="flex flex-col items-center relative z-10 w-1/3">
                <a href="{{ route('cliente.checkout.pago') }}" class="w-8 h-8 rounded-full bg-secondary text-on-secondary flex items-center justify-center mb-2 hover:bg-secondary-container hover:text-on-secondary-container transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">check</span>
                </a>
                <span class="font-label-caps text-xs font-semibold uppercase tracking-wide text-secondary text-center">Pago</span>
            </div>
            <!-- Connector 2-3 -->
            <div class="absolute top-4 left-[50%] right-[16.6%] h-[2px] bg-secondary -z-10"></div>
            <!-- Step 3: Confirmation (Active) -->
            <div class="flex flex-col items-center relative z-10 w-1/3">
                <div class="w-8 h-8 rounded-full bg-primary text-on-primary flex items-center justify-center font-numeric-data text-xl font-semibold mb-2 shadow-[0_4px_20px_rgba(0,35,73,0.12)]">3</div>
                <span class="font-label-caps text-xs font-semibold uppercase tracking-wide text-primary text-center">Confirmación</span>
            </div>
        </div>
    </div>

    <div class="mb-8">
        <h1 class="font-headline-md text-2xl font-bold text-primary mb-2 md:text-4xl md:mb-2">Resumen de tu Pedido</h1>
        <p class="text-on-surface-variant font-body-md text-base">Revisa los detalles antes de confirmar tu compra.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Detalles del pedido (Left Column) -->
        <div class="lg:col-span-8 space-y-6">
            
            <!-- Items del Carrito -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm">
                <h2 class="font-headline-md text-xl font-semibold text-primary mb-4 pb-2 border-b border-outline-variant">Productos</h2>
                <ul role="list" class="-my-6 divide-y divide-outline-variant/50">
                    @foreach($carrito->items as $item)
                        <li class="flex py-6">
                            <div class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-lg border border-outline-variant bg-surface-container-low p-1">
                                <img src="{{ $item->producto->imagen_url }}" alt="{{ $item->producto->nombre }}" class="h-full w-full object-contain object-center">
                            </div>

                            <div class="ml-4 flex flex-1 flex-col justify-center">
                                <div>
                                    <div class="flex justify-between font-headline-md text-lg font-semibold text-primary">
                                        <h3>{{ $item->producto->nombre }}</h3>
                                        <p class="ml-4 font-numeric-data">${{ number_format($item->subtotal, 2) }}</p>
                                    </div>
                                    @if($item->variante)
                                        <p class="mt-1 font-body-md text-sm text-on-surface-variant">
                                            @foreach($item->variante->opciones as $opcion)
                                                <span class="font-semibold">{{ $opcion->tipo->nombre }}:</span> {{ $opcion->valor }}@if(!$loop->last) | @endif
                                            @endforeach
                                        </p>
                                    @endif
                                </div>
                                <div class="flex flex-1 items-end justify-between font-body-md text-sm mt-2">
                                    <p class="text-outline">Cant: <span class="font-semibold text-on-surface-variant">{{ $item->cantidad }}</span></p>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Dirección -->
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm flex flex-col">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-xl">location_on</span>
                            <h2 class="font-headline-md text-lg font-semibold text-primary">Envío a</h2>
                        </div>
                        <a href="{{ route('cliente.checkout.direccion') }}" class="font-label-caps text-[10px] uppercase font-bold tracking-wider text-secondary hover:text-secondary-container transition-colors">Editar</a>
                    </div>
                    <address class="font-body-md text-sm text-on-surface-variant not-italic flex-1">
                        <p class="font-semibold text-on-background mb-1">{{ $direccion->nombre_receptor }}</p>
                        <p>{{ $direccion->direccion_exacta }}</p>
                        <p>{{ $direccion->corregimiento }}, {{ $direccion->distrito }}</p>
                        <p>{{ $direccion->provincia }}</p>
                        @if($zonaEnvio)
                            <p class="mt-3 text-xs font-semibold text-secondary bg-secondary/10 inline-block px-2 py-1 rounded">Zona: {{ $zonaEnvio->nombre }}</p>
                        @endif
                    </address>
                </div>

                <!-- Método de Pago -->
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-sm flex flex-col">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-primary text-xl">payments</span>
                            <h2 class="font-headline-md text-lg font-semibold text-primary">Pago</h2>
                        </div>
                        <a href="{{ route('cliente.checkout.pago') }}" class="font-label-caps text-[10px] uppercase font-bold tracking-wider text-secondary hover:text-secondary-container transition-colors">Editar</a>
                    </div>
                    <div class="font-body-md text-sm text-on-surface-variant flex items-center gap-3">
                        @if($metodoPago === 'stripe')
                            <span class="material-symbols-outlined text-3xl text-outline">credit_card</span>
                            <span class="font-semibold text-on-background">Tarjeta de Crédito / Débito</span>
                        @elseif($metodoPago === 'yappy')
                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center border border-slate-200 p-1 shrink-0">
                                <img src="{{ asset('images/pa-yappy.webp') }}" alt="Yappy" class="max-w-full max-h-full object-contain">
                            </div>
                            <span class="font-semibold text-on-background">Yappy</span>
                        @elseif($metodoPago === 'transferencia')
                            <span class="material-symbols-outlined text-3xl text-outline">account_balance</span>
                            <div>
                                <p class="font-semibold text-on-background">Transferencia ACH</p>
                                @if(session('checkout_comprobante_ruta'))
                                <p class="text-xs text-secondary mt-1 flex items-center gap-1 font-semibold">
                                    <span class="material-symbols-outlined text-[14px]">done</span> Comprobante adjunto
                                </p>
                                @endif
                            </div>
                        @elseif($metodoPago === 'contra_entrega')
                            <span class="material-symbols-outlined text-3xl text-outline">local_shipping</span>
                            <span class="font-semibold text-on-background">Pago Contra Entrega</span>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        <!-- Resumen de Costos (Right Column) -->
        <div class="lg:col-span-4">
            <form action="{{ route('cliente.checkout.procesar') }}" method="POST">
                @csrf
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 shadow-[0_4px_20px_rgba(0,35,73,0.05)] sticky top-24">
                    <h2 class="font-headline-md text-xl font-semibold text-primary mb-6 pb-2 border-b border-outline-variant flex items-center justify-between">
                        <span>Resumen</span>
                        <span class="material-symbols-outlined text-secondary">receipt_long</span>
                    </h2>
                    
                    <dl class="space-y-4 font-body-md text-sm text-on-surface-variant mb-6">
                        <div class="flex justify-between items-center">
                            <dt>Subtotal</dt>
                            <dd class="font-semibold text-on-background font-numeric-data">${{ number_format($totales['subtotal'], 2) }}</dd>
                        </div>

                        @if($totales['descuento'] > 0)
                        <div class="flex justify-between items-center text-secondary bg-secondary/10 px-3 py-2 rounded-lg border border-secondary/20">
                            <dt class="flex items-center gap-1.5 font-semibold">
                                <span class="material-symbols-outlined text-[16px]">sell</span>
                                Descuento
                                @if($carrito->cupon)
                                    <span class="font-label-caps uppercase tracking-wider ml-1">({{ $carrito->cupon->codigo }})</span>
                                @endif
                            </dt>
                            <dd class="font-bold font-numeric-data">-${{ number_format($totales['descuento'], 2) }}</dd>
                        </div>
                        @endif

                        <div class="flex justify-between items-center">
                            <dt>Envío</dt>
                            <dd class="font-semibold text-on-background font-numeric-data">${{ number_format($totales['costo_envio'], 2) }}</dd>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <dt>Impuestos (7% ITBMS)</dt>
                            <dd class="font-semibold text-on-background font-numeric-data">${{ number_format($totales['itbms_monto'], 2) }}</dd>
                        </div>

                        <div class="flex items-end justify-between border-t border-outline-variant pt-4 mt-4">
                            <dt>
                                <span class="block font-label-caps text-xs font-semibold uppercase tracking-wider text-outline mb-0.5">Total a Pagar</span>
                            </dt>
                            <dd class="font-numeric-data text-3xl font-extrabold text-primary tracking-tight">${{ number_format($totales['total'], 2) }}</dd>
                        </div>
                    </dl>

                    <div class="mb-6">
                        <label for="notas_cliente" class="block font-label-caps text-[10px] font-bold uppercase tracking-wider text-on-surface-variant mb-2">Notas del Pedido (Opcional)</label>
                        <textarea name="notas_cliente" id="notas_cliente" rows="2" class="block w-full rounded-md border-outline-variant shadow-sm focus:border-secondary focus:ring-secondary font-body-md text-sm bg-surface-container-lowest" placeholder="Ej: Entregar en la puerta blanca..."></textarea>
                    </div>

                    <button type="submit" id="btn-confirmar-pedido" class="w-full bg-secondary text-on-secondary font-label-caps text-xs font-semibold uppercase tracking-wide py-4 px-4 rounded-lg hover:bg-secondary-container transition-colors shadow-[0_4px_20px_rgba(0,35,73,0.12)] flex justify-center items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">check_circle</span>
                        Confirmar Pedido
                    </button>
                    
                    <p class="mt-4 font-body-md text-[11px] text-center text-outline flex items-center justify-center gap-1.5">
                        <span class="material-symbols-outlined text-[14px]">lock</span>
                        Proceso seguro y encriptado
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form[action="{{ route(\'cliente.checkout.procesar\') }}"]');
        if (form) {
            form.addEventListener('submit', function(e) {
                const btn = document.getElementById('btn-confirmar-pedido');
                if (btn) {
                    if (btn.disabled) {
                        e.preventDefault();
                        return;
                    }
                    btn.disabled = true;
                    // Cambiar el contenido del botón para mostrar estado de carga
                    btn.innerHTML = '<span class="material-symbols-outlined text-[18px] animate-spin">progress_activity</span> Procesando...';
                    btn.classList.add('opacity-75', 'cursor-not-allowed');
                }
            });
        }
    });
</script>
@endpush
