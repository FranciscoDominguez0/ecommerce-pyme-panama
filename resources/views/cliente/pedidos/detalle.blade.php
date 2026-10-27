@extends('layouts.cliente')

@section('title', 'Rastreo del Pedido ' . $pedido->numero_pedido)

@section('content')
<div id="area-detalle-web" class="min-h-[calc(100vh-160px)] bg-slate-50/70 dark:bg-[#121415] py-8 sm:py-10">
    <div class="w-full max-w-4xl lg:max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 sm:space-y-8">

        {{-- Animación de compra confirmada (si viene desde checkout) --}}
        @if(session('pedido_creado_animacion'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/95 backdrop-blur-md" x-transition.opacity.duration.500ms>
            <div class="bg-white dark:bg-[#1f2327] rounded-3xl p-8 sm:p-10 flex flex-col items-center justify-center max-w-sm w-full mx-4 shadow-2xl text-center">
                <div class="relative w-24 h-24 mb-5 text-emerald-500">
                    <svg viewBox="0 0 100 100" class="w-full h-full drop-shadow-md" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="50" cy="50" r="45" stroke-dasharray="300" stroke-dashoffset="300" style="animation: animCircle 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;"></circle>
                        <path d="M 30 50 L 45 65 L 70 35" stroke-dasharray="100" stroke-dashoffset="100" style="animation: animCheck 0.5s cubic-bezier(0.65, 0, 0.45, 1) forwards; animation-delay: 0.4s;"></path>
                    </svg>
                </div>
                <style>
                    @keyframes animCircle { to { stroke-dashoffset: 0; } }
                    @keyframes animCheck { to { stroke-dashoffset: 0; } }
                </style>
                <h2 class="text-xl font-extrabold text-slate-900 dark:text-white mb-1.5">¡Pedido Confirmado!</h2>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Tu orden ha sido registrada exitosamente en nuestro sistema.</p>
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
                            }{{ $loop->last ? '' : ',' }}
                            @endforeach
                        ]
                    });
                }
            })();
        </script>
        @endif

        @php
            $ultimoEstado = $pedido->ultimoEstado?->estado ?? 'pendiente';
            $pasoActual = $pedido->config_estado['progress_step'];
            $progresoPct = $pedido->config_estado['progress_pct'];
        @endphp

        {{-- Encabezado Principal --}}
        <div>
            <a href="{{ route('cliente.perfil.pedidos.index') }}" wire:navigate 
               class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors uppercase tracking-wider mb-2.5">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Volver a mis pedidos</span>
            </a>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Rastreo de tu Pedido</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Consulta el avance logístico, detalles de envío y artículos incluidos en tu compra.
            </p>
        </div>

        {{-- Banner de Reembolso (si aplica) --}}
        @if($ultimoEstado === 'reembolsado' || (float)$pedido->monto_reembolsado > 0)
        <div class="p-5 rounded-2xl bg-slate-100 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 flex items-start gap-4">
            <span class="material-symbols-outlined text-slate-700 dark:text-slate-300 text-2xl shrink-0 mt-0.5">currency_exchange</span>
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Reembolso Procesado</h3>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 leading-relaxed">
                    Este pedido fue reembolsado por un monto de <span class="font-bold text-slate-900 dark:text-white">${{ number_format($pedido->monto_reembolsado > 0 ? $pedido->monto_reembolsado : $pedido->total, 2) }} USD</span> a tu método de pago original.
                </p>
            </div>
        </div>
        @endif

        {{-- CARD 1: Pedido Actual y Botones de Acción (Sin badge al lado del número) --}}
        <div class="bg-white dark:bg-[#1f2327] rounded-3xl border border-slate-200/90 dark:border-slate-800 p-6 sm:p-8 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">Pedido {{ $pedido->numero_pedido }}</h2>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[15px] text-slate-400">calendar_today</span>
                        <span>Realizado el {{ $pedido->creado_en->translatedFormat('d \d\e F, Y') }} a las {{ $pedido->creado_en->format('h:i A') }}</span>
                    </p>
                </div>

                {{-- Acciones a la derecha en español --}}
                <div class="flex flex-wrap items-center gap-2.5 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100 dark:border-slate-800">
                    @if($pedido->factura)
                        <a href="{{ route('cliente.facturas.pdf', $pedido->factura->id) }}" target="_blank"
                           class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 hover:border-emerald-600 hover:text-emerald-600 transition-colors shadow-2xs">
                            <span class="material-symbols-outlined text-[16px]">download</span>
                            <span>Descargar Factura</span>
                        </a>

                        <button type="button" onclick="imprimirReciboPdf('{{ route('cliente.facturas.pdf', [$pedido->factura->id, 'modo' => 'inline']) }}')"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 hover:border-emerald-600 hover:text-emerald-600 transition-colors shadow-2xs cursor-pointer">
                            <span class="material-symbols-outlined text-[16px]">print</span>
                            <span>Imprimir Recibo</span>
                        </button>
                    @endif

                    @if(in_array($ultimoEstado, ['pago_confirmado', 'en_preparacion', 'listo_para_envio', 'enviado', 'en_transito', 'entregado']) && $ultimoEstado !== 'reembolsado')
                        @if(!$pedido->devolucion)
                            <a href="{{ route('cliente.perfil.pedidos.devolucion.create', $pedido->id) }}" wire:navigate
                               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs font-bold text-slate-700 dark:text-slate-200 hover:border-emerald-600 hover:text-emerald-600 transition-colors shadow-2xs">
                                <span class="material-symbols-outlined text-[16px]">assignment_return</span>
                                <span>Solicitar Devolución</span>
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- CARD 2: Progreso de Entrega (Delivery Progress con barra superior gruesa y 4 hitos en español) --}}
        <div class="bg-white dark:bg-[#1f2327] rounded-3xl border border-slate-200/90 dark:border-slate-800 p-6 sm:p-8 shadow-xs">
            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">Progreso de Entrega</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                @if($ultimoEstado === 'entregado')
                    Entrega finalizada con éxito el {{ $hitosFechas['entregado'] ? $hitosFechas['entregado']->format('d/m/Y') : $pedido->creado_en->format('d/m/Y') }}
                @else
                    Fecha estimada de entrega: {{ $pedido->creado_en->addDays(3)->format('d/m/Y') }}
                @endif
            </p>

            {{-- Barra de progreso horizontal superior gruesa --}}
            <div class="mt-6 mb-8 w-full bg-slate-200 dark:bg-slate-800 h-2 sm:h-2.5 rounded-full overflow-hidden">
                <div class="h-full bg-slate-900 dark:bg-emerald-500 rounded-full transition-all duration-700" 
                     style="width: {{ $progresoPct }}%;"></div>
            </div>

            {{-- 4 Hitos debajo de la barra con círculos e iconos en español --}}
            <div class="grid grid-cols-4 gap-2 text-center">
                {{-- 1. Realizado --}}
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-emerald-500 text-white flex items-center justify-center shadow-xs">
                        <span class="material-symbols-outlined text-[20px] sm:text-[22px]">check</span>
                    </div>
                    <span class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white mt-2.5">Realizado</span>
                    <span class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">{{ $hitosFechas['creado']?->format('d M') }}</span>
                </div>

                {{-- 2. Confirmado --}}
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full {{ $pasoActual >= 2 ? 'bg-emerald-500 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 border border-slate-200 dark:border-slate-700' }} flex items-center justify-center">
                        <span class="material-symbols-outlined text-[20px] sm:text-[22px]">{{ $pasoActual >= 2 ? 'check' : 'inventory_2' }}</span>
                    </div>
                    <span class="text-xs sm:text-sm font-bold {{ $pasoActual >= 2 ? 'text-slate-900 dark:text-white' : 'text-slate-400 dark:text-slate-500' }} mt-2.5">Confirmado</span>
                    <span class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">{{ $hitosFechas['confirmado'] ? $hitosFechas['confirmado']->format('d M') : '—' }}</span>
                </div>

                {{-- 3. En camino --}}
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full {{ $pasoActual >= 3 ? 'bg-emerald-500 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 border border-slate-200 dark:border-slate-700' }} flex items-center justify-center">
                        <span class="material-symbols-outlined text-[20px] sm:text-[22px]">{{ $pasoActual >= 3 ? 'check' : 'local_shipping' }}</span>
                    </div>
                    <span class="text-xs sm:text-sm font-bold {{ $pasoActual >= 3 ? 'text-slate-900 dark:text-white' : 'text-slate-400 dark:text-slate-500' }} mt-2.5">En camino</span>
                    <span class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">{{ $hitosFechas['enviado'] ? $hitosFechas['enviado']->format('d M') : '—' }}</span>
                </div>

                {{-- 4. Entregado --}}
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full {{ $pasoActual >= 4 ? 'bg-emerald-500 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-400 border border-slate-200 dark:border-slate-700' }} flex items-center justify-center">
                        <span class="material-symbols-outlined text-[20px] sm:text-[22px]">{{ $pasoActual >= 4 ? 'check' : 'package_2' }}</span>
                    </div>
                    <span class="text-xs sm:text-sm font-bold {{ $pasoActual >= 4 ? 'text-slate-900 dark:text-white' : 'text-slate-400 dark:text-slate-500' }} mt-2.5">Entregado</span>
                    <span class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">{{ $hitosFechas['entregado'] ? $hitosFechas['entregado']->format('d M') : '—' }}</span>
                </div>
            </div>
        </div>

        {{-- CARD 3: Historial del Pedido (Order Timeline vertical) --}}
        <div class="bg-white dark:bg-[#1f2327] rounded-3xl border border-slate-200/90 dark:border-slate-800 p-6 sm:p-8 shadow-xs">
            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white pb-4 mb-6 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-600 text-[22px]">history</span>
                <span>Historial del Pedido</span>
            </h3>

            @if($estadosOrdenados->isNotEmpty())
                <div class="relative pl-6 sm:pl-8 space-y-7 before:content-[''] before:absolute before:left-3 sm:before:left-4 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200 dark:before:bg-slate-800">
                    @foreach($estadosOrdenados as $historial)
                        @php
                            $esActual = $loop->last;
                            $nombreEstado = ucfirst(str_replace('_', ' ', $historial->estado));
                        @endphp
                        <div class="relative">
                            {{-- Checkmark verde --}}
                            <div class="absolute -left-[27px] sm:-left-[31px] w-6 h-6 rounded-full {{ $esActual ? 'bg-emerald-600 text-white ring-4 ring-emerald-100 dark:ring-emerald-950/60' : 'bg-emerald-500 text-white' }} flex items-center justify-center z-10 shadow-xs">
                                <span class="material-symbols-outlined text-[14px]">check</span>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">{{ $nombreEstado }}</h4>
                                    @if($esActual)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300/40">Actual</span>
                                    @endif
                                </div>
                                <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">
                                    {{ $historial->creado_en->translatedFormat('d M, Y') }} · {{ $historial->creado_en->format('h:i A') }}
                                </span>
                            </div>

                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 leading-relaxed">
                                {{ $historial->comentario ?? 'Estado registrado correctamente en el sistema.' }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-slate-500 dark:text-slate-400">Aún no hay actualizaciones registradas para este pedido.</p>
            @endif

            {{-- Botón para confirmar recepción si el paquete está en camino --}}
            @if(in_array($ultimoEstado, ['enviado', 'en_transito']))
                <div class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 bg-emerald-50/60 dark:bg-emerald-950/30 p-4 sm:p-5 rounded-2xl border border-emerald-200/80 dark:border-emerald-800/50">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-emerald-600 text-2xl">mark_email_read</span>
                        <div>
                            <h4 class="text-xs sm:text-sm font-bold text-emerald-900 dark:text-emerald-300">¿Ya recibiste tu compra?</h4>
                            <p class="text-[11px] text-emerald-700 dark:text-emerald-400">Confirma la entrega para completar el proceso de despacho.</p>
                        </div>
                    </div>
                    <form action="{{ route('cliente.perfil.pedidos.confirmar-recepcion', $pedido->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs transition-colors cursor-pointer">
                            <span class="material-symbols-outlined text-[16px]">thumb_up</span>
                            <span>Confirmar Recepción</span>
                        </button>
                    </form>
                </div>
            @endif
        </div>

        {{-- CARD 4: Información de Rastreo --}}
        <div class="bg-white dark:bg-[#1f2327] rounded-3xl border border-slate-200/90 dark:border-slate-800 p-6 sm:p-8 shadow-xs">
            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white pb-4 mb-4 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-600 text-[22px]">local_shipping</span>
                <span>Información de Rastreo</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 bg-slate-50 dark:bg-slate-800/40 p-5 rounded-2xl border border-slate-200/70 dark:border-slate-800">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Empresa Logística</span>
                    <p class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-100 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-emerald-600 text-[18px]">verified</span>
                        <span>Logística PyME Panamá</span>
                    </p>
                    <span class="text-[11px] text-slate-500 font-mono mt-0.5 block">Guía: #{{ $pedido->numero_pedido }}</span>
                </div>

                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Estado de Envío</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold {{ $pedido->config_estado['badge'] }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $pedido->config_estado['dot'] }}"></span>
                        <span>{{ $pedido->config_estado['label'] }}</span>
                    </span>
                </div>

                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Última Actualización</span>
                    <p class="text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-100">
                        {{ $pedido->ultimoEstado?->creado_en?->format('d/m/Y · h:i A') ?? 'Reciente' }}
                    </p>
                </div>

                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Zona de Destino</span>
                    <p class="text-xs sm:text-sm font-semibold text-slate-800 dark:text-slate-100">
                        {{ $pedido->zonaEnvio?->nombre ?? ($pedido->direccion?->provincia ?? 'Panamá') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- CARD 5: Productos del Pedido con Comentarios y Reseñas Incorporadas Profesionalemente --}}
        <div class="bg-white dark:bg-[#1f2327] rounded-3xl border border-slate-200/90 dark:border-slate-800 p-4 sm:p-8 shadow-xs">
            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white pb-3 sm:pb-4 mb-5 sm:mb-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-emerald-600 text-[22px]">shopping_bag</span>
                    <span>Productos del Pedido</span>
                </span>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold">{{ $totalArticulos }} {{ $totalArticulos == 1 ? 'artículo' : 'artículos' }}</span>
            </h3>

            {{-- Listado de productos --}}
            <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
                @foreach($pedido->items as $item)
                    @php
                        $valoracion = isset($valoracionesPorProducto) ? $valoracionesPorProducto->get($item->producto_id) : null;
                    @endphp

                    <div class="py-4 sm:py-5 first:pt-0 last:pb-0 flex flex-col gap-4">
                        {{-- Fila del producto: Imagen, info y precio totalmente adaptativo a móviles --}}
                        <div class="flex items-start gap-3.5 sm:gap-4">
                            {{-- Miniatura del producto fija y proporcionada --}}
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 bg-slate-50 dark:bg-slate-800/60 overflow-hidden flex items-center justify-center shrink-0">
                                @if($item->imagen_url)
                                    <img src="{{ $item->imagen_url }}" alt="{{ $item->producto?->nombre ?? 'Producto' }}" class="w-full h-full object-cover">
                                @else
                                    <span class="material-symbols-outlined text-slate-400 text-2xl">inventory_2</span>
                                @endif
                            </div>

                            {{-- Información del producto y precios --}}
                            <div class="flex-1 min-w-0 flex flex-col sm:flex-row sm:items-start justify-between gap-1.5 sm:gap-4">
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white leading-snug">
                                        @if($item->producto && $item->producto->slug)
                                            <a href="{{ route('cliente.producto.detalle', $item->producto->slug) }}" wire:navigate class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                                                {{ $item->producto->nombre }}
                                            </a>
                                        @elseif($item->producto)
                                            <span>{{ $item->producto->nombre }}</span>
                                        @else
                                            <span>Producto no disponible</span>
                                        @endif
                                    </h4>
                                    
                                    @if($item->variante && $item->variante->opciones->isNotEmpty())
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex flex-wrap gap-x-2.5 gap-y-0.5">
                                            @foreach($item->variante->opciones as $opcion)
                                                <span>{{ $opcion->tipo->nombre }}: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $opcion->valor }}</span></span>
                                            @endforeach
                                        </p>
                                    @endif

                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                        Cantidad: <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $item->cantidad }}</span>
                                    </p>
                                </div>

                                {{-- Precio: en móvil se alinea con claridad y en desktop a la derecha --}}
                                <div class="mt-1 sm:mt-0 flex sm:flex-col items-baseline sm:items-end justify-between sm:justify-start gap-1 sm:text-right shrink-0 pt-0.5 sm:pt-0 border-t sm:border-0 border-slate-100 dark:border-slate-800/60">
                                    <span class="text-sm sm:text-base font-black text-slate-900 dark:text-white block">
                                        ${{ number_format($item->subtotal, 2) }}
                                    </span>
                                    @if($item->cantidad > 1)
                                        <span class="text-[11px] sm:text-xs text-slate-400 block sm:mt-0.5">
                                            ${{ number_format($item->precio_unitario, 2) }} c/u
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- SECCIÓN PROFESIONAL DE COMENTARIOS Y VALORACIÓN --}}
                        @if($item->producto)
                            @if($ultimoEstado === 'entregado')
                                {{-- Pedido entregado: el cliente PUEDE valorar o editar su valoración --}}
                                <div x-data="{
                                    formularioAbierto: false,
                                    calificacion: {{ $valoracion?->calificacion ?? 5 }},
                                    hoverCalificacion: 0,
                                    etiquetas: {1: 'Muy malo', 2: 'Malo', 3: 'Regular', 4: 'Bueno', 5: '¡Excelente!'}
                                }" class="w-full bg-slate-50/70 dark:bg-slate-800/40 border border-slate-200/70 dark:border-slate-800 rounded-2xl p-4 sm:p-5 mt-1">
                                    
                                    @if($valoracion)
                                        {{-- Valoración ya registrada --}}
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                            <div class="space-y-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-800 dark:text-emerald-400">
                                                        <span class="material-symbols-outlined text-[15px]">verified</span>
                                                        <span>Tu opinión y valoración:</span>
                                                    </span>
                                                    <div class="flex items-center text-amber-400">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <span class="material-symbols-outlined text-[18px] {{ $i <= $valoracion->calificacion ? 'text-amber-400' : 'text-slate-300 dark:text-slate-600' }}"
                                                                  style="{{ $i <= $valoracion->calificacion ? "font-variation-settings: 'FILL' 1;" : "font-variation-settings: 'FILL' 0;" }}">star</span>
                                                        @endfor
                                                    </div>
                                                    <span class="text-xs font-bold text-slate-600 dark:text-slate-400">({{ $valoracion->calificacion }}/5)</span>
                                                </div>

                                                @if($valoracion->titulo)
                                                    <h5 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200">{{ $valoracion->titulo }}</h5>
                                                @endif
                                                @if($valoracion->comentario)
                                                    <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed">{{ $valoracion->comentario }}</p>
                                                @endif
                                                <p class="text-[10px] text-slate-400">Publicado el {{ $valoracion->creado_en->translatedFormat('d M, Y') }}</p>
                                            </div>

                                            <button type="button" @click="formularioAbierto = !formularioAbierto"
                                                    class="self-start sm:self-auto inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:border-emerald-600 transition-colors shrink-0">
                                                <span class="material-symbols-outlined text-[14px]">edit</span>
                                                <span x-text="formularioAbierto ? 'Ocultar edición' : 'Editar opinión'">Editar opinión</span>
                                            </button>
                                        </div>
                                    @else
                                        {{-- Aún no ha calificado --}}
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3" x-show="!formularioAbierto">
                                            <div>
                                                <h5 class="text-xs sm:text-sm font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5">
                                                    <span class="material-symbols-outlined text-amber-400 text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                                    <span>¿Recibiste este producto? ¡Valora su calidad!</span>
                                                </h5>
                                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                                    Comparte tu experiencia con otros compradores sobre la calidad de este artículo.
                                                </p>
                                            </div>

                                            <button type="button" @click="formularioAbierto = true"
                                                    class="self-start sm:self-auto inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-colors shadow-2xs cursor-pointer shrink-0">
                                                <span class="material-symbols-outlined text-[16px]">rate_review</span>
                                                <span>Escribir opinión</span>
                                            </button>
                                        </div>
                                    @endif

                                    {{-- Formulario interactivo para registrar o editar valoración --}}
                                    <div x-show="formularioAbierto" x-cloak class="mt-4 pt-4 border-t border-slate-200/80 dark:border-slate-700 space-y-4" x-transition>
                                        <div class="flex items-center justify-between">
                                            <h5 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                                                <span class="material-symbols-outlined text-amber-500 text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
                                                <span>{{ $valoracion ? 'Modificar tu opinión' : 'Escribir comentario y valoración' }}</span>
                                            </h5>
                                            <button type="button" @click="formularioAbierto = false" class="text-slate-400 hover:text-slate-600 text-xs">
                                                <span class="material-symbols-outlined text-[18px]">close</span>
                                            </button>
                                        </div>

                                        <form action="{{ route('cliente.perfil.pedidos.valorar', $pedido->id) }}" method="POST" class="space-y-3.5">
                                             @csrf
                                            <input type="hidden" name="producto_id" value="{{ $item->producto_id }}">
                                            <input type="hidden" name="calificacion" :value="calificacion">

                                            {{-- Estrellas amarillas tocables --}}
                                            <div>
                                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tu puntuación</label>
                                                <div class="flex items-center gap-3">
                                                    <div class="flex items-center gap-1 select-none" @mouseleave="hoverCalificacion = 0">
                                                        <template x-for="i in 5" :key="i">
                                                            <button type="button" 
                                                                    @mouseenter="hoverCalificacion = i" 
                                                                    @touchstart.prevent="calificacion = i; hoverCalificacion = 0"
                                                                    @pointerdown="calificacion = i; hoverCalificacion = 0"
                                                                    @click.prevent="calificacion = i; hoverCalificacion = 0" 
                                                                    class="p-1 touch-manipulation hover:scale-125 transition-transform cursor-pointer"
                                                                    :title="etiquetas[i]">
                                                                <span class="material-symbols-outlined text-[30px] leading-none transition-all"
                                                                      :class="(hoverCalificacion ? i <= hoverCalificacion : i <= calificacion) ? 'text-amber-400' : 'text-slate-300 dark:text-slate-600'"
                                                                      :style="(hoverCalificacion ? i <= hoverCalificacion : i <= calificacion) ? 'font-variation-settings: \'FILL\' 1;' : 'font-variation-settings: \'FILL\' 0;'">
                                                                    star
                                                                </span>
                                                            </button>
                                                        </template>
                                                    </div>
                                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 px-2.5 py-0.5 rounded-full bg-slate-200/80 dark:bg-slate-700"
                                                          x-text="etiquetas[hoverCalificacion || calificacion]"></span>
                                                </div>
                                            </div>

                                            <div>
                                                <label for="titulo-{{ $item->id }}" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                    Título de la reseña <span class="text-slate-400 font-normal">(Opcional)</span>
                                                </label>
                                                <input type="text" id="titulo-{{ $item->id }}" name="titulo" maxlength="120"
                                                       value="{{ old('titulo', $valoracion?->titulo) }}"
                                                       placeholder="Ej: Excelente calidad, 100% recomendado"
                                                       class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 py-2.5 px-3.5 focus:ring-emerald-500 focus:border-emerald-500">
                                            </div>

                                            <div>
                                                <label for="comentario-{{ $item->id }}" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                                    Comentario detallado <span class="text-slate-400 font-normal">(Opcional)</span>
                                                </label>
                                                <textarea id="comentario-{{ $item->id }}" name="comentario" rows="3" maxlength="1000"
                                                          placeholder="Escribe tu opinión sobre el producto, funciones, diseño o experiencia de compra..."
                                                          class="w-full text-xs sm:text-sm rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 py-2.5 px-3.5 focus:ring-emerald-500 focus:border-emerald-500">{{ old('comentario', $valoracion?->comentario) }}</textarea>
                                            </div>

                                            <div class="flex items-center justify-end gap-2.5 pt-2 border-t border-slate-200/80 dark:border-slate-700">
                                                <button type="button" @click="formularioAbierto = false" class="px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-400 hover:bg-slate-100 transition-colors">
                                                    Cancelar
                                                </button>
                                                <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-colors shadow-2xs cursor-pointer">
                                                    {{ $valoracion ? 'Actualizar opinión' : 'Publicar opinión' }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                </div>
                            @endif
                        @endif

                    </div>
                @endforeach
            </div>

            {{-- Desglose y Resumen de Totales al pie de los productos --}}
            <div class="pt-6 mt-6 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-end justify-between gap-6">
                <div class="text-xs text-slate-500 dark:text-slate-400">
                    <span class="font-bold text-slate-700 dark:text-slate-300">{{ $totalArticulos }} artículos</span> incluidos en esta compra.
                </div>

                <div class="space-y-2 text-right w-full sm:w-72">
                    <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400">
                        <span>Subtotal</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-300">${{ number_format($pedido->subtotal, 2) }}</span>
                    </div>

                    @if($pedido->descuento > 0)
                    <div class="flex justify-between text-xs text-emerald-600 dark:text-emerald-400">
                        <span>
                            Descuento
                            @if($pedido->cupon)
                                ({{ $pedido->cupon->codigo }})
                            @endif
                        </span>
                        <span class="font-bold">-${{ number_format($pedido->descuento, 2) }}</span>
                    </div>
                    @endif

                    <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400">
                        <span>Envío</span>
                        @if((float) $pedido->costo_envio === 0.0)
                            <span class="font-semibold text-emerald-600">Gratis</span>
                        @else
                            <span class="font-semibold text-slate-700 dark:text-slate-300">${{ number_format($pedido->costo_envio, 2) }}</span>
                        @endif
                    </div>

                    <div class="flex justify-between text-xs text-slate-500 dark:text-slate-400">
                        <span>ITBMS (7%)</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-300">${{ number_format($pedido->itbms_monto, 2) }}</span>
                    </div>

                    <div class="pt-2.5 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center">
                        <span class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">Total</span>
                        <span class="text-lg sm:text-xl font-black text-slate-900 dark:text-white">${{ number_format($pedido->total, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- CARDS 6 y 7: Dirección de Entrega y Método de Pago con Logos Reales (Visa, Mastercard, Stripe, Yappy) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            
            {{-- Tarjeta: Dirección de Entrega --}}
            <div class="bg-white dark:bg-[#1f2327] rounded-3xl border border-slate-200/90 dark:border-slate-800 p-6 sm:p-8 shadow-xs flex flex-col justify-between">
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white pb-3.5 mb-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-600 text-[20px]">location_on</span>
                        <span>Dirección de Entrega</span>
                    </h3>

                    @if($pedido->direccion)
                        <div class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 space-y-1.5">
                            <p class="font-bold text-slate-900 dark:text-white text-sm sm:text-base">{{ $pedido->direccion->nombre_receptor }}</p>
                            <p>{{ $pedido->direccion->direccion_exacta }}</p>
                            <p>{{ $pedido->direccion->corregimiento }}, {{ $pedido->direccion->distrito }}</p>
                            <p>{{ $pedido->direccion->provincia }}, Panamá</p>
                            @if($pedido->direccion->telefono)
                                <p class="text-slate-500 pt-1.5 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[15px]">call</span>
                                    <span>+507 {{ $pedido->direccion->telefono }}</span>
                                </p>
                            @endif
                        </div>
                    @else
                        <div class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 space-y-1.5">
                            <p class="font-bold text-slate-900 dark:text-white text-sm sm:text-base">Retiro en Local</p>
                            <p class="font-medium text-slate-800 dark:text-slate-200">{{ $retiroLocal['sucursal'] ?? 'Sucursal de PyME Panamá' }}</p>
                            <p>{{ $retiroLocal['direccion'] ?? 'Entrega presencial en instalaciones.' }}</p>
                            @if(!empty($retiroLocal['horario']))
                                <p class="text-xs text-slate-500">Horario: {{ $retiroLocal['horario'] }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Tarjeta: Método de Pago con Imagen de Marca Real --}}
            <div class="bg-white dark:bg-[#1f2327] rounded-3xl border border-slate-200/90 dark:border-slate-800 p-6 sm:p-8 shadow-xs flex flex-col justify-between">
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white pb-3.5 mb-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-600 text-[20px]">payment</span>
                        <span>Método de Pago</span>
                    </h3>

                    <div class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 space-y-3">
                        @if($pedido->metodo_pago === 'stripe')
                            <div class="flex items-center gap-3.5">
                                <div class="w-14 h-9 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-white/95 p-1 flex items-center justify-center overflow-hidden shrink-0 shadow-2xs">
                                    <img src="{{ $pedido->info_tarjeta['logo'] }}" alt="{{ $pedido->info_tarjeta['nombre'] }}" class="max-h-full max-w-full object-contain">
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white text-sm sm:text-base">Tarjeta de Crédito / Débito</p>
                                    <p class="text-xs text-slate-500 font-mono mt-0.5">
                                        {{ $pedido->info_tarjeta['nombre'] }} @if($pedido->info_tarjeta['last4']) · Terminación •••• {{ $pedido->info_tarjeta['last4'] }} @endif
                                    </p>
                                </div>
                            </div>
                        @elseif($pedido->metodo_pago === 'yappy')
                            <div class="flex items-center gap-3.5">
                                <div class="w-14 h-9 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-white/95 p-1 flex items-center justify-center overflow-hidden shrink-0 shadow-2xs">
                                    <img src="{{ asset('images/pa-yappy.webp') }}" alt="Yappy" class="max-h-full max-w-full object-contain">
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white text-sm sm:text-base">Yappy Comercial</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Pago verificado al instante</p>
                                </div>
                            </div>
                        @elseif($pedido->metodo_pago === 'transferencia')
                            <div class="flex items-center gap-3.5">
                                <div class="w-14 h-9 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-1 flex items-center justify-center shrink-0 shadow-2xs">
                                    <span class="material-symbols-outlined text-emerald-600 text-[24px]">account_balance</span>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white text-sm sm:text-base">Transferencia Bancaria</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Banco General / ACH</p>
                                </div>
                            </div>
                        @elseif($pedido->metodo_pago === 'punto_venta')
                            <div class="flex items-center gap-3.5">
                                <div class="h-9 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-white/95 px-2 py-1 flex items-center justify-center gap-1.5 shrink-0 shadow-2xs">
                                    <img src="{{ asset('images/visa-logo.png') }}" alt="Visa" class="h-3.5 w-auto object-contain">
                                    <img src="{{ asset('images/mastercard-logo.png') }}" alt="Mastercard" class="h-4 w-auto object-contain">
                                    <img src="{{ asset('images/clave-logo.png') }}" alt="Clave" class="h-3.5 w-auto object-contain rounded-xs">
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white text-sm sm:text-base">Punto de Venta</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Tarjeta / Clave en Tienda Física</p>
                                </div>
                            </div>
                        @elseif($pedido->metodo_pago === 'efectivo')
                            <div class="flex items-center gap-3.5">
                                <div class="w-14 h-9 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-white/95 p-1 flex items-center justify-center shrink-0 shadow-2xs">
                                    <img src="{{ asset('images/efectivo.webp') }}" alt="Efectivo" class="max-h-full max-w-full object-contain">
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white text-sm sm:text-base">Pago en Efectivo</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Pago directo en caja</p>
                                </div>
                            </div>
                        @else
                            <div class="flex items-center gap-3.5">
                                <div class="w-14 h-9 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-1 flex items-center justify-center shrink-0 shadow-2xs">
                                    <span class="material-symbols-outlined text-emerald-600 text-[24px]">payments</span>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900 dark:text-white text-sm sm:text-base">Pago Contra Entrega</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Al recibir tu paquete</p>
                                </div>
                            </div>
                        @endif

                        <div class="pt-2.5 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center text-xs sm:text-sm">
                            <span class="text-slate-500">Monto abonado:</span>
                            <span class="font-extrabold text-slate-900 dark:text-white">${{ number_format($pedido->total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Experiencia y Opinión de Compra (Modal Inteligente Post-Compra) --}}
        <div x-data="{
            modalAbierto: false,
            evaluado: {{ $evaluacionPrevia ? 'true' : 'false' }},
            puntuacion: {{ $evaluacionPrevia?->puntuacion_facilidad ?? 5 }},
            hoverPuntuacion: 0,
            comentario: '{{ addslashes($evaluacionPrevia?->comentario ?? '') }}',
            enviando: false,
            etiquetas: {
                1: 'Malo',
                2: 'Regular',
                3: 'Bueno',
                4: 'Muy Bueno',
                5: '¡Excelente!'
            },
            init() {
                @if($abrirAuto && !$evaluacionPrevia)
                    setTimeout(() => {
                        this.modalAbierto = true;
                    }, 3800);
                @endif
            },
            abrirModal() {
                this.modalAbierto = true;
            },
            cerrarModal() {
                this.modalAbierto = false;
            },
            enviar() {
                if (this.enviando) return;
                this.enviando = true;

                fetch('{{ route('cliente.perfil.pedidos.usabilidad', $pedido->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        puntuacion_facilidad: this.puntuacion,
                        comentario: this.comentario,
                        tiempo_checkout_segundos: {{ session('pedido_checkout_duracion') ? (int) session('pedido_checkout_duracion') : 'null' }},
                        dispositivo: window.innerWidth < 768 ? 'Mobile' : 'Desktop'
                    })
                })
                .then(res => res.json())
                .then(data => {
                    this.enviando = false;
                    if (data.success) {
                        this.evaluado = true;
                        this.cerrarModal();
                        if (window.mostrarToast) {
                            window.mostrarToast('success', '¡Gracias por compartir tu opinión!');
                        }
                    }
                })
                .catch(err => {
                    this.enviando = false;
                    if (window.mostrarToast) {
                        window.mostrarToast('error', 'No se pudo registrar tu calificación.');
                    }
                });
            }
        }">


            <template x-if="!evaluado">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 rounded-3xl bg-white dark:bg-[#1f2327] border border-slate-200/90 dark:border-slate-800 shadow-xs">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-500 flex items-center justify-center shrink-0 border border-amber-100 dark:border-amber-800/60 shadow-2xs">
                            <span class="material-symbols-outlined text-[24px]" style="font-variation-settings: 'FILL' 1;">hotel_class</span>
                        </div>
                        <div>
                            <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white">¿Qué tal estuvo tu compra hoy?</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Tu opinión nos ayuda a ofrecerte siempre el mejor servicio.</p>
                        </div>
                    </div>
                    <button type="button" @click="abrirModal()" class="self-start sm:self-auto px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition-all shadow-xs cursor-pointer flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">rate_review</span>
                        <span>Calificar compra</span>
                    </button>
                </div>
            </template>

            <!-- Modal Emergente Post-Compra (Estilo Grandes Empresas) -->
            <div x-show="modalAbierto" style="display: none;" class="fixed inset-0 z-[120] flex items-center justify-center p-4" x-transition.opacity.duration.250ms>
                <!-- Backdrop oscuro con blur -->
                <div class="fixed inset-0 bg-slate-950/65 backdrop-blur-sm transition-opacity" @click="cerrarModal()"></div>

                <!-- Contenedor del Modal -->
                <div class="relative bg-white dark:bg-[#1f2327] rounded-3xl p-6 sm:p-8 max-w-sm w-full shadow-2xl border border-slate-200/80 dark:border-slate-800 text-center z-10"
                     @click.stop
                     x-transition:enter="transition ease-out duration-250"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-3"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 translate-y-3">

                    <!-- Botón cerrar (X) -->
                    <button type="button" @click="cerrarModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-full transition-colors cursor-pointer" title="Cerrar">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>

                    <!-- Formulario de calificación -->
                    <div class="space-y-4">
                            <div class="w-14 h-14 mx-auto rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-500 flex items-center justify-center border border-amber-100 dark:border-amber-800/60 shadow-2xs">
                                <span class="material-symbols-outlined text-[32px]" style="font-variation-settings: 'FILL' 1;">hotel_class</span>
                            </div>

                            <div>
                                <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">¿Qué tal estuvo tu compra hoy?</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    Califica cómo sentiste el proceso de compra.
                                </p>
                            </div>

                            <!-- Estrellas interactivas doradas -->
                            <div class="py-1 space-y-1">
                                <div class="flex items-center justify-center gap-1.5">
                                    <template x-for="i in 5" :key="i">
                                        <button type="button"
                                                @mouseenter="hoverPuntuacion = i"
                                                @mouseleave="hoverPuntuacion = 0"
                                                @click="puntuacion = i"
                                                class="p-1 touch-manipulation hover:scale-125 transition-transform cursor-pointer"
                                                :title="etiquetas[i]">
                                            <span class="material-symbols-outlined text-[34px] sm:text-[38px] transition-colors"
                                                  :class="(hoverPuntuacion ? i <= hoverPuntuacion : i <= puntuacion) ? 'text-amber-400' : 'text-slate-200 dark:text-slate-700'"
                                                  :style="(hoverPuntuacion ? i <= hoverPuntuacion : i <= puntuacion) ? 'font-variation-settings: \'FILL\' 1;' : 'font-variation-settings: \'FILL\' 0;'">
                                                star
                                            </span>
                                        </button>
                                    </template>
                                </div>
                                <div class="text-xs font-bold text-amber-600 dark:text-amber-400 h-5"
                                     x-text="etiquetas[hoverPuntuacion || puntuacion]"></div>
                            </div>

                            <!-- Comentario opcional -->
                            <div class="text-left space-y-1.5 pt-1">
                                <label for="modal-opinion-comentario" class="block text-[11px] font-bold text-slate-700 dark:text-slate-300">
                                    ¿Algún comentario o sugerencia? <span class="text-slate-400 font-normal">(Opcional)</span>
                                </label>
                                <textarea id="modal-opinion-comentario" x-model="comentario" rows="2" maxlength="500"
                                          placeholder="Cuéntanos qué te gustó o qué podemos mejorar..."
                                          class="w-full text-xs rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-3 focus:bg-white focus:ring-emerald-500 focus:border-emerald-500 transition-all resize-none"></textarea>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="space-y-2 pt-2">
                                <button type="button" @click="enviar()" :disabled="enviando"
                                        class="w-full py-3 px-4 rounded-full bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold transition-all shadow-xs cursor-pointer disabled:opacity-50 flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-[17px] animate-spin" x-show="enviando" style="display: none;">progress_activity</span>
                                    <span x-text="enviando ? 'Enviando...' : 'Enviar Calificación'"></span>
                                </button>
                                <button type="button" @click="cerrarModal()" class="w-full py-1.5 text-xs font-medium text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors cursor-pointer">
                                    Ahora no
                                </button>
                            </div>
                        </div>

                </div>
            </div>

        </div>

        {{-- CARD 8: ¿Necesitas ayuda con tu pedido? (Footer Card del ejemplo en español) --}}
        <div class="bg-white dark:bg-[#1f2327] rounded-3xl border border-slate-200/90 dark:border-slate-800 p-8 sm:p-10 text-center shadow-xs">
            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white mb-1.5">¿Necesitas ayuda con tu pedido?</h3>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto mb-5">
                Nuestro equipo de soporte técnico y atención está listo para asistirte en todo momento.
            </p>
            <div class="flex flex-wrap items-center justify-center gap-3.5">
                <a href="https://wa.me/50760000000?text=Hola,%20tengo%20una%20consulta%20sobre%20mi%20pedido%20{{ $pedido->numero_pedido }}" target="_blank"
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200 hover:border-emerald-600 hover:text-emerald-600 transition-colors shadow-2xs">
                    <span class="material-symbols-outlined text-[18px]">support_agent</span>
                    <span>Contactar Soporte</span>
                </a>
                <a href="{{ route('cliente.catalogo') }}" wire:navigate
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold transition-colors shadow-2xs">
                    <span class="material-symbols-outlined text-[18px]">storefront</span>
                    <span>Seguir Comprando</span>
                </a>
            </div>
        </div>

    </div>
</div>

<script>
    // Imprime el recibo PDF directamente en la misma página sin abrir una pestaña externa
    window.imprimirReciboPdf = function(urlPdf) {
        let iframe = document.getElementById('iframe-impresion-recibo');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'iframe-impresion-recibo';
            iframe.style.position = 'fixed';
            iframe.style.right = '0';
            iframe.style.bottom = '0';
            iframe.style.width = '0';
            iframe.style.height = '0';
            iframe.style.border = '0';
            iframe.style.visibility = 'hidden';
            document.body.appendChild(iframe);
        }

        iframe.onload = function() {
            try {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            } catch (e) {
                // Si la política de seguridad del navegador bloquea print() entre frames, abrir la URL directamente
                window.location.href = urlPdf;
            }
        };

        iframe.src = urlPdf;
    };
</script>
@endsection

