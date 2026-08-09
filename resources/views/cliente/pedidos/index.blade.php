@extends('layouts.cliente')

@section('title', 'Mis Pedidos')

@push('styles')
<style>
    /* Utilities */
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .ambient-shadow {
        box-shadow: 0px 4px 20px rgba(0, 35, 73, 0.05);
    }
    .ambient-shadow-hover:hover {
        box-shadow: 0px 12px 32px rgba(0, 35, 73, 0.12);
    }
</style>
@endpush

@section('content')
<main class="flex-grow pt-8 pb-12 px-4 md:px-16 w-full max-w-[1200px] mx-auto">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
        <div>
            <h1 class="font-headline-md text-3xl font-bold text-primary mb-2 md:text-4xl">Mis Pedidos</h1>
            <p class="font-body-md text-on-surface-variant text-base">Revisa y rastrea tus compras recientes.</p>
        </div>
        <!-- Quick Filters -->
        <div class="flex gap-2 overflow-x-auto pb-2 -mx-4 px-4 md:mx-0 md:px-0 md:pb-0 hide-scrollbar">
            <a href="{{ route('cliente.pedidos.index') }}" class="whitespace-nowrap px-4 py-2 rounded-full {{ !request('estado') ? 'bg-primary text-on-primary' : 'bg-surface-container-lowest text-on-surface hover:bg-surface-dim border border-outline-variant' }} font-label-caps text-xs font-bold tracking-wider transition-colors uppercase">Todos</a>
            <a href="{{ route('cliente.pedidos.index', ['estado' => 'pendiente']) }}" class="whitespace-nowrap px-4 py-2 rounded-full {{ request('estado') === 'pendiente' ? 'bg-primary text-on-primary' : 'bg-surface-container-lowest text-on-surface hover:bg-surface-dim border border-outline-variant' }} font-label-caps text-xs font-bold tracking-wider transition-colors uppercase">Pendientes</a>
            <a href="{{ route('cliente.pedidos.index', ['estado' => 'entregado']) }}" class="whitespace-nowrap px-4 py-2 rounded-full {{ request('estado') === 'entregado' ? 'bg-primary text-on-primary' : 'bg-surface-container-lowest text-on-surface hover:bg-surface-dim border border-outline-variant' }} font-label-caps text-xs font-bold tracking-wider transition-colors uppercase">Completados</a>
        </div>
    </div>
    
    @if($pedidos->count() > 0)
        <!-- Orders Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($pedidos as $pedido)
                @php
                    $ultimoEstado = $pedido->ultimoEstado ? $pedido->ultimoEstado->estado : 'pendiente';
                    
                    // Configuramos los colores y el ícono según el estado
                    $configEstado = match($ultimoEstado) {
                        'entregado' => [
                            'bar' => 'bg-secondary/80',
                            'chip_bg' => 'bg-secondary/10',
                            'chip_text' => 'text-secondary',
                            'icon' => 'check_circle',
                        ],
                        'pendiente', 'pago_confirmado', 'en_preparacion' => [
                            'bar' => 'bg-tertiary-container',
                            'chip_bg' => 'bg-tertiary-container/20',
                            'chip_text' => 'text-tertiary',
                            'icon' => 'schedule',
                        ],
                        'cancelado', 'devolucion_solicitada', 'pago_rechazado' => [
                            'bar' => 'bg-primary',
                            'chip_bg' => 'bg-primary/10',
                            'chip_text' => 'text-primary',
                            'icon' => 'flag',
                        ],
                        default => [
                            'bar' => 'bg-blue-500',
                            'chip_bg' => 'bg-blue-100',
                            'chip_text' => 'text-blue-700',
                            'icon' => 'local_shipping',
                        ]
                    };
                    $labelEstado = ucfirst(str_replace('_', ' ', $ultimoEstado));
                @endphp
                
                <!-- Order Card -->
                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 flex flex-col gap-4 ambient-shadow ambient-shadow-hover transition-all duration-300 relative overflow-hidden group">
                    <!-- Status Bar Top -->
                    <div class="absolute top-0 left-0 w-full h-1 {{ $configEstado['bar'] }}"></div>
                    
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="font-label-caps text-xs text-on-surface-variant uppercase tracking-wider font-bold">Pedido #</span>
                            <div class="font-numeric-data text-lg font-bold text-primary mt-1">{{ $pedido->numero_pedido }}</div>
                        </div>
                        
                        <!-- Status Chip -->
                        <div class="px-3 py-1 rounded-full {{ $configEstado['chip_bg'] }} {{ $configEstado['chip_text'] }} font-label-caps text-[11px] font-bold tracking-wider flex items-center gap-1 uppercase">
                            <span class="material-symbols-outlined text-[14px]" data-icon="{{ $configEstado['icon'] }}">{{ $configEstado['icon'] }}</span>
                            {{ $labelEstado }}
                        </div>
                    </div>
                    
                    <div class="flex flex-col gap-2 py-4 border-y border-outline-variant/50">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-on-surface-variant">Fecha</span>
                            <span class="font-numeric-data font-semibold text-primary">{{ $pedido->creado_en->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-on-surface-variant">Artículos</span>
                            <span class="font-semibold text-primary">{{ $pedido->items->sum('cantidad') }} {{ $pedido->items->sum('cantidad') == 1 ? 'artículo' : 'artículos' }}</span>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-end mt-auto">
                        <div>
                            <span class="font-label-caps text-[10px] uppercase font-bold tracking-wider text-on-surface-variant">Total</span>
                            <div class="font-headline-md text-xl text-primary font-bold mt-1">${{ number_format($pedido->total, 2) }}</div>
                        </div>
                        <a href="{{ route('cliente.pedidos.detalle', $pedido->id) }}" class="border border-primary text-primary bg-transparent hover:bg-primary/5 rounded-lg px-4 py-2 font-label-caps text-[11px] font-bold tracking-wider uppercase transition-colors flex items-center gap-2">
                            Ver Detalle
                            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Pagination -->
        @if($pedidos->hasPages())
        <div class="mt-8 flex justify-center items-center gap-2">
            @if($pedidos->onFirstPage())
                <span class="p-2 border border-outline-variant rounded-lg text-on-surface-variant opacity-50 cursor-not-allowed">
                    <span class="material-symbols-outlined">chevron_left</span>
                </span>
            @else
                <a href="{{ $pedidos->previousPageUrl() }}" class="p-2 border border-outline-variant rounded-lg text-on-surface-variant hover:bg-surface-dim transition-colors">
                    <span class="material-symbols-outlined">chevron_left</span>
                </a>
            @endif

            <span class="font-label-caps text-xs font-bold tracking-wider text-on-surface px-4 uppercase">
                Página {{ $pedidos->currentPage() }} de {{ $pedidos->lastPage() }}
            </span>

            @if($pedidos->hasMorePages())
                <a href="{{ $pedidos->nextPageUrl() }}" class="p-2 border border-outline-variant rounded-lg text-on-surface-variant hover:bg-surface-dim transition-colors">
                    <span class="material-symbols-outlined">chevron_right</span>
                </a>
            @else
                <span class="p-2 border border-outline-variant rounded-lg text-on-surface-variant opacity-50 cursor-not-allowed">
                    <span class="material-symbols-outlined">chevron_right</span>
                </span>
            @endif
        </div>
        @endif
    @else
        <!-- No orders state -->
        <div class="text-center py-16 bg-surface-container-lowest rounded-xl border border-outline-variant ambient-shadow">
            <span class="material-symbols-outlined text-6xl text-outline-variant mb-4">shopping_bag</span>
            <h3 class="text-xl font-bold text-primary font-headline-md mb-2">Aún no tienes pedidos</h3>
            <p class="text-on-surface-variant font-body-md text-sm mb-6">Explora nuestro catálogo y encuentra los mejores productos.</p>
            <a href="{{ route('cliente.catalogo') }}" class="inline-flex justify-center items-center gap-2 rounded-lg border border-transparent bg-secondary px-6 py-3 text-sm font-bold uppercase tracking-wider text-on-secondary shadow-sm hover:bg-secondary-container hover:text-on-secondary-container transition-colors font-label-caps">
                <span class="material-symbols-outlined text-[18px]">storefront</span>
                Ir a la tienda
            </a>
        </div>
    @endif
</main>
@endsection
