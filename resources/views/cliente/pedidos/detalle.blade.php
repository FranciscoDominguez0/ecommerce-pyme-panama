@extends('layouts.cliente')

@section('title', 'Detalle del Pedido')

@section('content')
<div class="h-[calc(100vh-160px)] overflow-y-auto bg-surface/50 py-6 md:py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    @if(session('pedido_creado_animacion'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/95 backdrop-blur-md" x-transition.opacity.duration.500ms>
        <div class="bg-surface rounded-3xl p-10 flex flex-col items-center justify-center max-w-sm w-full mx-4 shadow-2xl" x-show="show" x-transition:enter="transition ease-out duration-500 delay-100" x-transition:enter-start="opacity-0 scale-50 translate-y-10" x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
            <!-- SVG Checkmark Animado -->
            <div class="relative w-28 h-28 mb-6 text-secondary">
                <svg viewBox="0 0 100 100" class="w-full h-full drop-shadow-lg" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="50" cy="50" r="45" stroke-dasharray="300" stroke-dashoffset="300" style="animation: animCircle 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;"></circle>
                    <path d="M 30 50 L 45 65 L 70 35" stroke-dasharray="100" stroke-dashoffset="100" style="animation: animCheck 0.5s cubic-bezier(0.65, 0, 0.45, 1) forwards; animation-delay: 0.4s;"></path>
                </svg>
            </div>
            <style>
                @keyframes animCircle { to { stroke-dashoffset: 0; } }
                @keyframes animCheck { to { stroke-dashoffset: 0; } }
            </style>
            
            <h2 class="text-2xl font-bold text-on-surface mb-2 text-center" x-show="show" x-transition:enter="transition ease-out duration-500 delay-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">¡Pedido Confirmado!</h2>
            <p class="text-on-surface-variant text-center" x-show="show" x-transition:enter="transition ease-out duration-500 delay-400" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">Tu orden ha sido procesada exitosamente.</p>
        </div>
    </div>
    @endif

    @php
        $ultimoEstado = $pedido->ultimoEstado?->estado ?? 'pendiente';

        $configEstado = match($ultimoEstado) {
            'entregado' => [
                'badge_bg' => 'bg-secondary/10',
                'badge_text' => 'text-secondary',
                'icon' => 'check_circle',
                'label' => 'Entregado',
            ],
            'enviado' => [
                'badge_bg' => 'bg-secondary/10',
                'badge_text' => 'text-secondary',
                'icon' => 'local_shipping',
                'label' => 'En tránsito',
            ],
            'pendiente', 'pago_confirmado', 'en_preparacion', 'listo_para_envio' => [
                'badge_bg' => 'bg-tertiary-container/20',
                'badge_text' => 'text-tertiary',
                'icon' => 'schedule',
                'label' => ucfirst(str_replace('_', ' ', $ultimoEstado)),
            ],
            'cancelado', 'pago_rechazado', 'devolucion_solicitada' => [
                'badge_bg' => 'bg-primary/10',
                'badge_text' => 'text-primary',
                'icon' => 'flag',
                'label' => ucfirst(str_replace('_', ' ', $ultimoEstado)),
            ],
            default => [
                'badge_bg' => 'bg-surface-container-high',
                'badge_text' => 'text-on-surface',
                'icon' => 'info',
                'label' => ucfirst(str_replace('_', ' ', $ultimoEstado)),
            ],
        };

        $metodosPago = [
            'stripe' => ['icon' => 'credit_card', 'label' => 'Tarjeta de crédito / débito'],
            'yappy' => ['icon' => 'yappy', 'label' => 'Yappy'],
            'transferencia' => ['icon' => 'account_balance', 'label' => 'Transferencia bancaria'],
            'contra_entrega' => ['icon' => 'local_shipping', 'label' => 'Pago contra entrega'],
        ];
        $pagoInfo = $metodosPago[$pedido->metodo_pago] ?? ['icon' => 'payment', 'label' => ucfirst(str_replace('_', ' ', $pedido->metodo_pago))];
    @endphp

    <!-- Encabezado -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <a href="{{ route('cliente.perfil.pedidos.index') }}" wire:navigate class="flex items-center gap-2 text-on-surface-variant hover:text-primary transition-colors font-label-caps text-xs font-bold tracking-wider uppercase mb-2">
                <span class="material-symbols-outlined text-sm">arrow_back</span>
                Volver a mis pedidos
            </a>
            <h1 class="text-lg sm:text-xl font-bold text-primary">Pedido {{ $pedido->numero_pedido }}</h1>
            <p class="text-on-surface-variant text-sm mt-1">
                Realizado el {{ $pedido->creado_en->translatedFormat('d M, Y') }} a las {{ $pedido->creado_en->format('h:i A') }}
            </p>
        </div>
        <div class="flex flex-col items-end gap-3">
            @if($pedido->factura)
                <a href="{{ route('cliente.facturas.pdf', $pedido->factura->id) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-surface text-primary border border-primary text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-primary/5 transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-[16px]">receipt_long</span>
                    Descargar Recibo
                </a>
            @endif

            @if(in_array($ultimoEstado, ['entregado', 'enviado']))
                @php
                    $tieneDevolucion = \App\Models\Devolucion::where('pedido_id', $pedido->id)->exists();
                @endphp
                @if(!$tieneDevolucion)
                    <a href="{{ route('cliente.perfil.pedidos.devolucion.create', $pedido->id) }}" wire:navigate class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-primary-container transition-colors shadow-sm">
                        <span class="material-symbols-outlined text-[16px]">assignment_return</span>
                        Solicitar Devolución
                    </a>
                @else
                    <span class="inline-flex items-center gap-2 px-4 py-2 bg-surface-container-high text-on-surface-variant text-xs font-bold uppercase tracking-wider rounded-lg border border-outline-variant/30">
                        <span class="material-symbols-outlined text-[16px]">info</span>
                        Devolución Solicitada
                    </span>
                @endif
            @endif
        </div>
    </div>

    <!-- Grid principal -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        <!-- Columna izquierda -->
        <div class="col-span-1 md:col-span-8 flex flex-col gap-6">

            <!-- Estado y línea de tiempo -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 md:p-8 shadow-[0_4px_20px_rgba(0,35,73,0.05)]">
                <div class="flex justify-between items-center mb-6 pb-4 border-b border-outline-variant/50">
                    <h2 class="text-sm font-bold text-primary">Estado del envío</h2>
                    <span class="{{ $configEstado['badge_bg'] }} {{ $configEstado['badge_text'] }} px-3 py-1 rounded-full font-label-caps text-xs font-bold tracking-wider flex items-center gap-2 uppercase">
                        <span class="material-symbols-outlined text-sm">{{ $configEstado['icon'] }}</span>
                        {{ $configEstado['label'] }}
                    </span>
                </div>

                @if($pedido->zonaEnvio || $pedido->numero_pedido)
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8 bg-surface-container-low p-4 rounded-lg">
                    @if($pedido->zonaEnvio)
                    <div>
                        <p class="font-label-caps text-[10px] font-bold tracking-wider text-on-surface-variant uppercase mb-1">Zona de envío</p>
                        <p class="text-sm font-semibold text-on-surface">{{ $pedido->zonaEnvio->nombre }}</p>
                    </div>
                    @endif
                    <div>
                        <p class="font-label-caps text-[10px] font-bold tracking-wider text-on-surface-variant uppercase mb-1">Número de pedido</p>
                        <p class="text-sm font-semibold text-on-surface">{{ $pedido->numero_pedido }}</p>
                    </div>
                    <div>
                        <p class="font-label-caps text-[10px] font-bold tracking-wider text-on-surface-variant uppercase mb-1">Costo de envío</p>
                        <p class="text-sm font-semibold text-secondary">${{ number_format($pedido->costo_envio, 2) }}</p>
                    </div>
                </div>
                @endif

                @if($estadosOrdenados->isNotEmpty())
                <div class="relative pl-6 border-l-2 border-surface-variant space-y-6">
                    @foreach($estadosOrdenados as $historial)
                        @php
                            $esActual = $loop->last;
                            $esProblema = $historial->estado === 'problema_entrega';
                            
                            if ($esProblema) {
                                $dotClass = $esActual ? 'bg-red-500 animate-pulse' : 'bg-red-500';
                                $textClass = 'text-red-600';
                            } else {
                                $dotClass = $esActual
                                    ? 'bg-tertiary-container animate-pulse'
                                    : 'bg-secondary';
                                $textClass = $esActual ? 'text-tertiary' : 'text-on-surface';
                            }
                        @endphp
                        <div class="relative">
                            <div class="absolute -left-[31px] w-4 h-4 rounded-full {{ $dotClass }} border-4 border-surface-container-lowest"></div>
                            <div class="flex flex-col">
                                <p class="text-sm font-semibold {{ $textClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $historial->estado)) }}
                                </p>
                                <p class="text-sm text-on-surface-variant mt-1">
                                    {{ $historial->creado_en->translatedFormat('d M, Y') }} · {{ $historial->creado_en->format('h:i A') }}
                                    @if($historial->comentario)
                                        <br><span class="text-xs opacity-90 mt-1 block">{{ $historial->comentario }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
                @else
                <p class="text-on-surface-variant text-sm">Aún no hay actualizaciones registradas para este pedido.</p>
                @endif

                @if(in_array($ultimoEstado, ['enviado', 'en_transito']))
                <div class="mt-8 p-6 bg-emerald-50 rounded-xl border border-emerald-200 flex flex-col items-center text-center">
                    <span class="material-symbols-outlined text-emerald-600 text-3xl mb-2">inventory_2</span>
                    <h3 class="text-lg font-bold text-emerald-900 mb-1">¿Ya recibiste tu pedido?</h3>
                    <p class="text-sm text-emerald-700 mb-4 max-w-md">Ayúdanos a confirmar que el paquete llegó correctamente a tus manos.</p>
                    <form action="{{ route('cliente.perfil.pedidos.confirmar-recepcion', $pedido->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 text-white rounded-full font-bold shadow hover:bg-emerald-700 transition-colors">
                            <span class="material-symbols-outlined text-sm">thumb_up</span>
                            Sí, ya recibí mi pedido
                        </button>
                    </form>
                </div>
                @endif
            </div>

            <!-- Artículos del pedido -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 md:p-8">
                <h2 class="text-sm font-bold text-primary">Artículos del pedido</h2>
                <div class="space-y-6">
                    @foreach($pedido->items as $item)
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 {{ !$loop->last ? 'pb-6 border-b border-outline-variant/50' : '' }}">
                        <div class="w-24 h-24 flex-shrink-0 rounded-lg border border-outline-variant/30 overflow-hidden bg-surface-container-low">
                            @if($item->producto)
                                <img src="{{ $item->producto->imagen_url }}" alt="{{ $item->producto->nombre }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <span class="material-symbols-outlined text-outline-variant">image</span>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-semibold text-on-surface">
                                {{ $item->producto?->nombre ?? 'Producto no disponible' }}
                            </h3>
                            @if($item->variante && $item->variante->opciones->isNotEmpty())
                                <p class="text-on-surface-variant text-sm mt-1">
                                    @foreach($item->variante->opciones as $opcion)
                                        {{ $opcion->tipo->nombre }}: {{ $opcion->valor }}@if(!$loop->last), @endif
                                    @endforeach
                                </p>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-on-surface">${{ number_format($item->precio_unitario, 2) }}</p>
                            <p class="text-on-surface-variant text-xs mt-1">Cant: {{ $item->cantidad }}</p>
                        </div>
                        <div class="sm:w-24 text-right">
                            <p class="text-sm font-bold text-primary">${{ number_format($item->subtotal, 2) }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Columna derecha -->
        <div class="col-span-1 md:col-span-4 flex flex-col gap-6">

            <!-- Resumen -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 md:p-8 shadow-[0_4px_20px_rgba(0,35,73,0.05)]">
                <h2 class="text-sm font-bold text-primary mb-6">Resumen del pedido</h2>
                <div class="space-y-2 mb-6">
                    <div class="flex justify-between text-on-surface-variant text-sm">
                        <span>Subtotal ({{ $totalArticulos }} {{ $totalArticulos === 1 ? 'artículo' : 'artículos' }})</span>
                        <span class="font-numeric-data font-semibold">${{ number_format($pedido->subtotal, 2) }}</span>
                    </div>
                    @if($pedido->descuento > 0)
                    <div class="flex justify-between text-secondary text-sm">
                        <span>Descuento</span>
                        <span class="font-numeric-data font-semibold">-${{ number_format($pedido->descuento, 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-on-surface-variant text-sm">
                        <span>Envío</span>
                        <span class="font-numeric-data font-semibold">${{ number_format($pedido->costo_envio, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-on-surface-variant text-sm">
                        <span>ITBMS (7%)</span>
                        <span class="font-numeric-data font-semibold">${{ number_format($pedido->itbms_monto, 2) }}</span>
                    </div>
                </div>
                <div class="pt-4 border-t border-outline-variant/50 flex justify-between items-center mb-6">
                    <span class="text-sm font-bold text-on-surface">Total</span>
                    <span class="text-base font-bold text-primary">${{ number_format($pedido->total, 2) }}</span>
                </div>
                <a href="{{ route('cliente.catalogo') }}" wire:navigate class="block w-full py-3 bg-secondary text-on-secondary rounded-lg font-label-caps text-xs font-bold tracking-wider uppercase text-center shadow-sm hover:bg-on-secondary-container transition-colors">
                    Seguir comprando
                </a>
            </div>

            <!-- Método de pago -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 md:p-8">
                <h3 class="font-label-caps text-[10px] font-bold tracking-wider text-on-surface-variant uppercase mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">payment</span>
                    Método de pago
                </h3>
                <div class="flex items-center gap-3">
                    @if($pedido->metodo_pago === 'yappy')
                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center border border-slate-200 p-1 shrink-0">
                        <img src="{{ asset('images/pa-yappy.webp') }}" alt="Yappy" class="max-w-full max-h-full object-contain">
                    </div>
                    @else
                    <span class="material-symbols-outlined text-2xl text-on-surface-variant">{{ $pagoInfo['icon'] }}</span>
                    @endif
                    <div>
                        <p class="text-sm font-semibold text-on-surface">{{ $pagoInfo['label'] }}</p>
                        @if($pedido->metodo_pago === 'transferencia' && $pedido->comprobante_pago_ruta)
                            <p class="text-sm text-on-surface-variant">Comprobante adjunto</p>
                        @else
                            <p class="text-sm text-on-surface-variant">Pago registrado al confirmar el pedido</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Dirección de envío -->
            @if($pedido->direccion)
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 md:p-8">
                <h3 class="font-label-caps text-[10px] font-bold tracking-wider text-on-surface-variant uppercase mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">location_on</span>
                    Dirección de envío
                </h3>
                <div class="text-sm text-on-surface space-y-1">
                    <p class="font-semibold">{{ $pedido->direccion->nombre_receptor }}</p>
                    <p>{{ $pedido->direccion->direccion_exacta }}</p>
                    <p>{{ $pedido->direccion->corregimiento }}, {{ $pedido->direccion->distrito }}</p>
                    <p>{{ $pedido->direccion->provincia }}</p>
                    @if($pedido->direccion->referencia)
                        <p class="text-on-surface-variant mt-2 text-sm">Ref: {{ $pedido->direccion->referencia }}</p>
                    @endif
                </div>
            </div>
            @endif

            @if($pedido->notas_cliente)
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 md:p-8">
                <h3 class="font-label-caps text-[10px] font-bold tracking-wider text-on-surface-variant uppercase mb-3">Notas adicionales</h3>
                <p class="text-sm text-on-surface-variant italic">"{{ $pedido->notas_cliente }}"</p>
            </div>
            @endif
        </div>
    </div>
    </div>
</div>
@endsection
