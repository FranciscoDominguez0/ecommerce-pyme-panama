@extends('layouts.cliente')

@section('title', 'Detalle del Pedido')

@section('content')
<div class="min-h-[calc(100vh-160px)] bg-surface/50 py-6 md:py-8">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
    
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
    <script>
        (function() {
            if (window.gtagEcommerce) {
                window.gtagEcommerce.purchase({
                    id: '{{ $pedido->numero_pedido ?? $pedido->id }}',
                    total: {{ number_format((float)$pedido->total, 2, '.', '') }},
                    tax: {{ number_format((float)($pedido->itbms_monto ?? 0), 2, '.', '') }},
                    shipping: {{ number_format((float)($pedido->costo_envio ?? 0), 2, '.', '') }},
                    coupon: '{{ $pedido->cupon?->codigo ?? "" }}',
                    items: [
                        @foreach($pedido->items as $item)
                        {
                            item_id: '{{ $item->producto_id }}',
                            item_name: @json($item->producto?->nombre ?? 'Producto'),
                            price: {{ number_format((float)$item->precio_unitario, 2, '.', '') }},
                            quantity: {{ (int)$item->cantidad }}
                        }@if(!$loop->last),@endif
                        @endforeach
                    ]
                });
            }
        })();
    </script>
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
            'reembolsado' => [
                'badge_bg' => 'bg-slate-100',
                'badge_text' => 'text-slate-800',
                'icon' => 'currency_exchange',
                'label' => 'Reembolsado',
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
            <div class="flex items-center gap-3">
                <h1 class="text-xl md:text-2xl font-bold text-primary">Pedido {{ $pedido->numero_pedido }}</h1>
            </div>
            <p class="text-sm text-on-surface-variant mt-1">Realizado el {{ $pedido->creado_en->translatedFormat('d \d\e F, Y') }} a las {{ $pedido->creado_en->format('h:i A') }}</p>
        </div>
        <div class="flex flex-col items-end gap-3">
            @if($pedido->factura)
                <a href="{{ route('cliente.facturas.pdf', $pedido->factura->id) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-surface text-primary border border-primary text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-primary/5 transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-[16px]">receipt_long</span>
                    Descargar Recibo
                </a>
            @endif

            @if(in_array($ultimoEstado, ['pago_confirmado', 'en_preparacion', 'listo_para_envio', 'enviado', 'en_transito', 'entregado']) && $ultimoEstado !== 'reembolsado')
                @if(!$pedido->devolucion)
                    <a href="{{ route('cliente.perfil.pedidos.devolucion.create', $pedido->id) }}" wire:navigate class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-white text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-primary-container transition-colors shadow-sm">
                        <span class="material-symbols-outlined text-[16px]">assignment_return</span>
                        Solicitar Devolución / Reembolso
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

    @if($ultimoEstado === 'reembolsado' || (float)$pedido->monto_reembolsado > 0)
        <div class="mb-8 p-5 rounded-2xl bg-slate-100 border border-slate-200 text-slate-800 flex items-start gap-4 shadow-xs">
            <span class="material-symbols-outlined text-slate-700 text-3xl shrink-0 mt-0.5">currency_exchange</span>
            <div>
                <h3 class="text-base font-bold text-slate-900">Reembolso Procesado</h3>
                <p class="text-sm text-slate-600 mt-1 leading-relaxed">
                    Este pedido fue reembolsado por un monto total de <span class="font-bold text-slate-900">${{ number_format($pedido->monto_reembolsado > 0 ? $pedido->monto_reembolsado : $pedido->total, 2) }} USD</span> a tu método de pago original con <img src="{{ asset('images/stripe.webp') }}" alt="Stripe" class="h-4 inline-block align-text-bottom ml-1">.
                </p>
            </div>
        </div>
    @endif

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
                            $esReembolso = $historial->estado === 'reembolsado';
                            
                            if ($esProblema) {
                                $dotClass = $esActual ? 'bg-red-500 animate-pulse' : 'bg-red-500';
                                $textClass = 'text-red-600';
                            } elseif ($esReembolso) {
                                $dotClass = $esActual ? 'bg-slate-700 animate-pulse' : 'bg-slate-700';
                                $textClass = 'text-slate-800 font-semibold';
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

            <!-- Banner de valoración cuando el pedido está entregado -->
            @if($ultimoEstado === 'entregado')
            <div class="bg-gradient-to-r from-emerald-500/10 via-teal-500/10 to-emerald-500/5 border border-emerald-200/80 dark:border-emerald-500/20 rounded-2xl p-5 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xs">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-xs shrink-0">
                        <span class="material-symbols-outlined text-[22px]">hotel_class</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">¡Tu pedido ha sido entregado exitosamente!</h3>
                        <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">
                            Tu opinión ayuda a otros compradores. Valora con estrellas tus productos recibidos a continuación.
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Artículos del pedido -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 md:p-8">
                <h2 class="text-sm font-bold text-primary">Artículos del pedido</h2>
                <div class="space-y-6">
                    @foreach($pedido->items as $item)
                    @php
                        $valoracion = isset($valoracionesPorProducto) ? $valoracionesPorProducto->get($item->producto_id) : null;
                    @endphp
                    <div class="flex flex-col items-start gap-4 {{ !$loop->last ? 'pb-6 border-b border-outline-variant/50' : '' }}">
                        <div class="flex flex-col sm:flex-row items-start gap-4 w-full">
                            <div class="w-20 h-20 sm:w-24 sm:h-24 flex-shrink-0 rounded-lg border border-outline-variant/30 overflow-hidden bg-surface-container-low">
                                @if($item->producto)
                                    <img src="{{ $item->producto->imagen_url }}" alt="{{ $item->producto->nombre }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <span class="material-symbols-outlined text-outline-variant">image</span>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="flex-1 min-w-0 w-full flex flex-col sm:flex-row justify-between gap-4">
                                <!-- Info Producto -->
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="text-sm font-semibold text-on-surface line-clamp-2">
                                            {{ $item->producto?->nombre ?? 'Producto no disponible' }}
                                        </h3>
                                    </div>
                                    @if($item->variante && $item->variante->opciones->isNotEmpty())
                                        <p class="text-on-surface-variant text-sm mt-1">
                                            @foreach($item->variante->opciones as $opcion)
                                                <span class="inline-block mr-2">{{ $opcion->tipo->nombre }}: <span class="font-medium text-on-surface">{{ $opcion->valor }}</span></span>
                                            @endforeach
                                        </p>
                                    @endif
                                    
                                    <!-- Precio y Cantidad Mobile -->
                                    <div class="mt-2 sm:hidden flex items-center justify-between">
                                        <p class="text-on-surface-variant text-sm">{{ $item->cantidad }} x ${{ number_format($item->precio_unitario, 2) }}</p>
                                        <p class="text-sm font-bold text-primary">${{ number_format($item->subtotal, 2) }}</p>
                                    </div>
                                </div>
                                
                                <!-- Precio y Cantidad Desktop -->
                                <div class="hidden sm:flex flex-col items-end gap-1 min-w-[120px]">
                                    <p class="text-sm font-bold text-primary">${{ number_format($item->subtotal, 2) }}</p>
                                    <p class="text-on-surface-variant text-xs">{{ $item->cantidad }} x ${{ number_format($item->precio_unitario, 2) }} c/u</p>
                                </div>
                            </div>
                        </div>

                        <!-- Sección de Valoración para productos entregados -->
                        @if($ultimoEstado === 'entregado' && $item->producto)
                        <div x-data="{
                            abierto: false,
                            calificacion: {{ $valoracion?->calificacion ?? 5 }},
                            hoverCalificacion: 0,
                            etiquetas: {1: 'Muy malo', 2: 'Malo', 3: 'Regular', 4: 'Bueno', 5: '¡Excelente!'}
                        }" class="w-full pt-3 border-t border-slate-100 dark:border-gray-700/60">

                            @if($valoracion)
                                <!-- Valoración registrada por el cliente -->
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-emerald-50/60 dark:bg-emerald-500/5 border border-emerald-100 dark:border-emerald-500/20 rounded-xl p-3.5">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-800 dark:text-emerald-400">
                                                <span class="material-symbols-outlined text-[15px]">verified</span>
                                                <span>Tu valoración</span>
                                            </span>
                                            <div class="flex items-center text-amber-400">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <span class="material-symbols-outlined star-filled text-[17px] {{ $i <= $valoracion->calificacion ? 'text-amber-400' : 'text-slate-300 dark:text-slate-600' }}"
                                                          style="{{ $i <= $valoracion->calificacion ? "font-variation-settings: 'FILL' 1;" : "font-variation-settings: 'FILL' 0;" }}">star</span>
                                                @endfor
                                            </div>
                                            <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400">({{ $valoracion->calificacion }}/5)</span>
                                        </div>

                                        @if($valoracion->titulo)
                                            <h4 class="text-xs font-bold text-slate-800 dark:text-gray-200">{{ $valoracion->titulo }}</h4>
                                        @endif

                                        @if($valoracion->comentario)
                                            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">{{ $valoracion->comentario }}</p>
                                        @endif

                                        <p class="text-[10px] text-slate-400">Publicado el {{ $valoracion->creado_en->translatedFormat('d M, Y') }}</p>
                                    </div>

                                    <button type="button" 
                                            @click="abierto = !abierto" 
                                            class="self-start sm:self-auto inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-slate-200 dark:border-gray-700 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-white dark:hover:bg-[#181a1b] hover:border-emerald-500 transition-colors shrink-0">
                                        <span class="material-symbols-outlined text-[14px]">edit</span>
                                        <span x-text="abierto ? 'Cerrar edición' : 'Editar opinión'">Editar opinión</span>
                                    </button>
                                </div>
                            @else
                                <!-- Botón para abrir el formulario de valoración -->
                                <div class="flex items-center justify-between" x-show="!abierto">
                                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-amber-400 text-[18px]" style="font-variation-settings: 'FILL' 1;">hotel_class</span>
                                        <span>¿Recibiste este artículo? ¡Valora su calidad!</span>
                                    </span>
                                    <button type="button" 
                                            @click="abierto = true" 
                                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 transition-all shadow-2xs">
                                        <span class="material-symbols-outlined text-[15px]">rate_review</span>
                                        <span>Valorar producto</span>
                                    </button>
                                </div>
                            @endif

                            <!-- Formulario interactivo para registrar o editar valoración -->
                            <div x-show="abierto" x-cloak class="mt-3 bg-slate-50 dark:bg-transparent/60 border border-slate-200 dark:border-gray-700 rounded-2xl p-4 sm:p-5 space-y-4" x-transition>
                                <div class="flex items-center justify-between border-b border-slate-200/80 dark:border-gray-700 pb-3">
                                    <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-amber-500 text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                        <span>{{ $valoracion ? 'Modificar valoración' : 'Escribir tu valoración' }}</span>
                                    </h4>
                                    <button type="button" @click="abierto = false" class="text-slate-400 hover:text-slate-600 text-xs">
                                        <span class="material-symbols-outlined text-[18px]">close</span>
                                    </button>
                                </div>

                                <form action="{{ route('cliente.perfil.pedidos.valorar', $pedido->id) }}" method="POST" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="producto_id" value="{{ $item->producto_id }}">
                                    <input type="hidden" name="calificacion" :value="calificacion">

                                    <!-- Selector interactivo de estrellas tocándolas y con relleno amarillo -->
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                                            Calificación general <span class="text-rose-500">*</span>
                                        </label>
                                        <div class="flex items-center gap-3">
                                            <div class="flex items-center gap-1 select-none" @mouseleave="hoverCalificacion = 0">
                                                <template x-for="i in 5" :key="i">
                                                    <button type="button" 
                                                            @mouseenter="hoverCalificacion = i" 
                                                            @touchstart.prevent="calificacion = i; hoverCalificacion = 0"
                                                            @pointerdown="calificacion = i; hoverCalificacion = 0"
                                                            @click.prevent="calificacion = i; hoverCalificacion = 0" 
                                                            class="p-1 sm:p-1.5 touch-manipulation hover:scale-125 active:scale-95 transition-transform focus:outline-none cursor-pointer"
                                                            :title="etiquetas[i]">
                                                        <span class="material-symbols-outlined text-[32px] sm:text-[36px] transition-all leading-none"
                                                              :class="(hoverCalificacion ? i <= hoverCalificacion : i <= calificacion) ? 'text-amber-400 star-filled drop-shadow-xs' : 'text-slate-300 dark:text-slate-600'"
                                                              :style="(hoverCalificacion ? i <= hoverCalificacion : i <= calificacion) ? 'font-variation-settings: \'FILL\' 1;' : 'font-variation-settings: \'FILL\' 0;'">
                                                            star
                                                        </span>
                                                    </button>
                                                </template>
                                            </div>
                                            <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-slate-100 dark:bg-[#181a1b] text-slate-700 dark:text-slate-300"
                                                  x-text="etiquetas[hoverCalificacion || calificacion]"></span>
                                        </div>
                                    </div>

                                    <!-- Título de la reseña -->
                                    <div>
                                        <label for="titulo-{{ $item->id }}" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                            Título de tu opinión <span class="text-slate-400 font-normal">(Opcional)</span>
                                        </label>
                                        <input type="text" 
                                               id="titulo-{{ $item->id }}" 
                                               name="titulo" 
                                               maxlength="120"
                                               value="{{ old('titulo', $valoracion?->titulo) }}"
                                               placeholder="Ej: Excelente calidad, justo lo que esperaba"
                                               class="w-full text-xs rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-[#121415] text-slate-800 dark:text-gray-100 py-2 px-3 focus:ring-emerald-500 focus:border-emerald-500">
                                    </div>

                                    <!-- Comentario sanitizado -->
                                    <div x-data="{ caracteres: {{ mb_strlen($valoracion?->comentario ?? '') }} }">
                                        <div class="flex items-center justify-between mb-1">
                                            <label for="comentario-{{ $item->id }}" class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                                                Comentario detallado <span class="text-slate-400 font-normal">(Opcional)</span>
                                            </label>
                                            <span class="text-[10px] text-slate-400"><span x-text="caracteres"></span> / 1000</span>
                                        </div>
                                        <textarea id="comentario-{{ $item->id }}" 
                                                  name="comentario" 
                                                  rows="3" 
                                                  maxlength="1000"
                                                  @input="caracteres = $el.value.length"
                                                  placeholder="Cuéntanos sobre el producto, funcionamiento o tu experiencia de uso..."
                                                  class="w-full text-xs rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-[#121415] text-slate-800 dark:text-gray-100 py-2 px-3 focus:ring-emerald-500 focus:border-emerald-500">{{ old('comentario', $valoracion?->comentario) }}</textarea>
                                    </div>

                                    <!-- Botones de acción -->
                                    <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-200/80 dark:border-gray-700">
                                        <button type="button" @click="abierto = false" class="px-3.5 py-1.5 rounded-xl border border-slate-200 dark:border-gray-700 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:bg-transparent transition-colors">
                                            Cancelar
                                        </button>
                                        <button type="submit" class="px-4 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer">
                                            <span class="material-symbols-outlined text-[15px]">send</span>
                                            <span>{{ $valoracion ? 'Actualizar valoración' : 'Publicar valoración' }}</span>
                                        </button>
                                    </div>
                                </form>
                            </div>

                        </div>
                        @endif

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
                        @if((float) $pedido->costo_envio === 0.0)
                            <span class="font-semibold text-emerald-600">Gratis</span>
                        @else
                            <span class="font-numeric-data font-semibold">${{ number_format($pedido->costo_envio, 2) }}</span>
                        @endif
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
                @if($ultimoEstado === 'reembolsado' || (float)$pedido->monto_reembolsado > 0)
                    <div class="flex justify-between items-center bg-slate-100 px-3 py-2 rounded-lg border border-slate-200 text-slate-800 font-semibold text-xs mb-6">
                        <span class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm text-slate-600">currency_exchange</span>
                            Monto Reembolsado
                        </span>
                        <span class="font-numeric-data font-bold text-rose-600">-${{ number_format($pedido->monto_reembolsado > 0 ? $pedido->monto_reembolsado : $pedido->total, 2) }}</span>
                    </div>
                @endif
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
                    @if($pedido->metodo_pago === 'stripe')
                        @php
                            $detallesTarjeta = $pedido->detalles_tarjeta;
                            $stripeBrand = strtolower($detallesTarjeta['tarjeta_marca'] ?? '');
                            $stripeLast4 = $detallesTarjeta['tarjeta_last4'] ?? null;
                            $brandLabels = [
                                'visa' => 'Visa',
                                'mastercard' => 'Mastercard',
                                'amex' => 'American Express',
                                'discover' => 'Discover',
                                'diners' => 'Diners Club',
                                'jcb' => 'JCB',
                                'unionpay' => 'UnionPay',
                            ];
                            $nombreMarca = $brandLabels[$stripeBrand] ?? (!empty($stripeBrand) ? ucfirst($stripeBrand) : 'Tarjeta de crédito / débito');
                        @endphp
                        <div class="shrink-0 shadow-2xs rounded-md overflow-hidden border border-slate-200">
                            <x-tarjeta-marca-logo :brand="$stripeBrand" class="w-12 h-8 block" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-semibold text-on-surface">{{ $nombreMarca }}</p>
                                @if($stripeLast4)
                                    <span class="font-mono text-xs text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">•••• {{ $stripeLast4 }}</span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                                Pago seguro en tiempo real vía 
                                <img src="{{ asset('images/stripe.webp') }}" alt="Stripe" class="h-4 object-contain">
                            </p>
                        </div>
                    @elseif($pedido->metodo_pago === 'yappy')
                        <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center border border-slate-200 p-1 shrink-0">
                            <img src="{{ asset('images/pa-yappy.webp') }}" alt="Yappy" class="max-w-full max-h-full object-contain">
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-on-surface">{{ $pagoInfo['label'] }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">Pago verificado vía Yappy</p>
                        </div>
                    @elseif($pedido->metodo_pago === 'transferencia')
                        <span class="material-symbols-outlined text-2xl text-on-surface-variant">account_balance</span>
                        <div>
                            <p class="text-sm font-semibold text-on-surface">{{ $pagoInfo['label'] }}</p>
                            @if($pedido->comprobante_pago_ruta)
                                <p class="text-xs text-secondary mt-0.5 flex items-center gap-1 font-semibold">
                                    <span class="material-symbols-outlined text-[14px]">done</span> Comprobante adjunto
                                </p>
                            @else
                                <p class="text-xs text-slate-500 mt-0.5">Pendiente de comprobante</p>
                            @endif
                        </div>
                    @else
                        <span class="material-symbols-outlined text-2xl text-on-surface-variant">{{ $pagoInfo['icon'] }}</span>
                        <div>
                            <p class="text-sm font-semibold text-on-surface">{{ $pagoInfo['label'] }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">Pago registrado al recibir el pedido</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Dirección o Courier de envío -->
            @php
                $notas = json_decode($pedido->notas_internas, true);
                $metodoEntrega = $notas['metodo_entrega'] ?? ($pedido->direccion ? 'delivery' : 'retiro_local');
            @endphp

            @if($pedido->direccion)
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 md:p-8">
                <h3 class="font-label-caps text-[10px] font-bold tracking-wider text-on-surface-variant uppercase mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">location_on</span>
                    {{ (float) $pedido->costo_envio === 0.0 ? 'Datos del cliente' : 'Dirección de envío' }}
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
            @elseif($metodoEntrega === 'retiro_courier' && isset($notas['courier_sucursal_id']))
                @php
                    $sucursal = \App\Models\CourierSucursal::find($notas['courier_sucursal_id']);
                @endphp
                @if($sucursal)
                    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 md:p-8">
                        <h3 class="font-label-caps text-[10px] font-bold tracking-wider text-on-surface-variant uppercase mb-4 flex items-center gap-2 text-orange-600">
                            <span class="material-symbols-outlined text-sm">store</span>
                            Retiro en Sucursal Courier
                        </h3>
                        <div class="text-sm text-on-surface space-y-1">
                            <p class="font-semibold">{{ $sucursal->courier }} - {{ $sucursal->sucursal }}</p>
                            <p>{{ $sucursal->direccion }}</p>
                            <p>{{ $sucursal->zona }}</p>
                            @if($sucursal->telefono)
                                <p class="text-on-surface-variant mt-2 text-sm">Tel: {{ $sucursal->telefono }}</p>
                            @endif
                        </div>
                    </div>
                @endif
            @elseif($metodoEntrega === 'retiro_local')
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 md:p-8">
                    <h3 class="font-label-caps text-[10px] font-bold tracking-wider text-on-surface-variant uppercase mb-4 flex items-center gap-2 text-emerald-600">
                        <span class="material-symbols-outlined text-sm">storefront</span>
                        Retiro en Local PYME PANAMÁ
                    </h3>
                    <div class="text-sm text-on-surface space-y-1">
                        <p class="font-semibold">Sucursal Principal</p>
                        <p class="text-on-surface-variant">Retiro gratuito en nuestras instalaciones.</p>
                    </div>
                </div>
            @endif

            <!-- Datos de Contacto del Pedido -->
            @if(isset($notas['contacto']))
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 md:p-8">
                <h3 class="font-label-caps text-[10px] font-bold tracking-wider text-on-surface-variant uppercase mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">contact_mail</span>
                    Datos de Contacto
                </h3>
                <div class="text-sm text-on-surface space-y-1">
                    <p class="font-semibold">{{ current(explode(' ', $notas['contacto']['nombre'] ?? '')) }} {{ current(explode(' ', $notas['contacto']['apellido'] ?? '')) }}</p>
                    <p>{{ $notas['contacto']['email'] ?? '' }}</p>
                    <p>{{ $notas['contacto']['telefono1'] ?? '' }}</p>
                    @if(!empty($notas['contacto']['telefono2']))
                        <p>{{ $notas['contacto']['telefono2'] }}</p>
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
