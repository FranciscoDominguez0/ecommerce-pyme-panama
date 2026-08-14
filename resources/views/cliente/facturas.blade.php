@extends('layouts.cliente')

@section('title', 'Mis Facturas')

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
<x-cliente.perfil.layout active="facturas">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-4">
        <div>
            <h3 class="text-base font-bold text-primary">Historial de Facturas</h3>
            <p class="text-xs text-on-surface-variant mt-0.5">Consulta el historial de pagos y comprobantes de tus compras.</p>
        </div>
    </div>

    @if($facturas->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($facturas as $factura)
                @php
                    $configEstado = match($factura->estado) {
                        'emitida' => [
                            'bar' => 'bg-secondary/80',
                            'chip_bg' => 'bg-secondary/10',
                            'chip_text' => 'text-secondary',
                            'icon' => 'check_circle',
                        ],
                        'anulada' => [
                            'bar' => 'bg-primary',
                            'chip_bg' => 'bg-primary/10',
                            'chip_text' => 'text-primary',
                            'icon' => 'cancel',
                        ],
                        default => [
                            'bar' => 'bg-tertiary-container',
                            'chip_bg' => 'bg-tertiary-container/20',
                            'chip_text' => 'text-tertiary',
                            'icon' => 'schedule',
                        ]
                    };
                    $labelEstado = ucfirst(str_replace('_', ' ', $factura->estado));
                @endphp

                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 flex flex-col gap-4 ambient-shadow ambient-shadow-hover transition-all duration-300 relative overflow-hidden group {{ $factura->estado === 'anulada' ? 'opacity-75' : '' }}">
                    <div class="absolute top-0 left-0 w-full h-1 {{ $configEstado['bar'] }}"></div>

                    <div class="flex justify-between items-start">
                        <div>
                            <span class="font-label-caps text-xs text-on-surface-variant uppercase tracking-wider font-bold">Factura #</span>
                            <div class="text-sm font-bold text-primary mt-1 {{ $factura->estado === 'anulada' ? 'line-through' : '' }}">{{ $factura->numero }}</div>
                        </div>

                        <div class="px-3 py-1 rounded-full {{ $configEstado['chip_bg'] }} {{ $configEstado['chip_text'] }} font-label-caps text-[11px] font-bold tracking-wider flex items-center gap-1 uppercase">
                            <span class="material-symbols-outlined text-[14px]">{{ $configEstado['icon'] }}</span>
                            {{ $labelEstado }}
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 py-4 border-y border-outline-variant/50">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-on-surface-variant">Fecha de Emisión</span>
                            <span class="text-xs font-semibold text-primary">{{ $factura->emitida_en->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-on-surface-variant">Subtotal</span>
                            <span class="text-xs font-semibold text-primary">${{ number_format($factura->subtotal, 2) }}</span>
                        </div>
                    </div>

                    <div class="flex justify-between items-end mt-auto">
                        <div>
                            <span class="font-label-caps text-[10px] uppercase font-bold tracking-wider text-on-surface-variant">Total</span>
                            <div class="text-base font-bold text-primary mt-1 {{ $factura->estado === 'anulada' ? 'line-through text-on-surface-variant' : '' }}">${{ number_format($factura->total, 2) }}</div>
                        </div>
                        <a href="{{ route('cliente.facturas.pdf', $factura) }}" class="border border-primary text-primary bg-transparent hover:bg-primary/5 rounded-lg px-4 py-2 font-label-caps text-[11px] font-bold tracking-wider uppercase transition-colors flex items-center gap-2">
                            Descargar PDF
                            <span class="material-symbols-outlined text-[16px]">download</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        @if($facturas->hasPages())
        <div class="mt-8 flex justify-center items-center gap-2">
            @if($facturas->onFirstPage())
                <span class="p-2 border border-outline-variant rounded-lg text-on-surface-variant opacity-50 cursor-not-allowed">
                    <span class="material-symbols-outlined">chevron_left</span>
                </span>
            @else
                <a href="{{ $facturas->previousPageUrl() }}" wire:navigate class="p-2 border border-outline-variant rounded-lg text-on-surface-variant hover:bg-surface-dim transition-colors">
                    <span class="material-symbols-outlined">chevron_left</span>
                </a>
            @endif

            <span class="font-label-caps text-xs font-bold tracking-wider text-on-surface px-4 uppercase">
                Página {{ $facturas->currentPage() }} de {{ $facturas->lastPage() }}
            </span>

            @if($facturas->hasMorePages())
                <a href="{{ $facturas->nextPageUrl() }}" wire:navigate class="p-2 border border-outline-variant rounded-lg text-on-surface-variant hover:bg-surface-dim transition-colors">
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
        <div class="text-center py-12 bg-surface-container-lowest rounded-xl border border-outline-variant ambient-shadow">
            <span class="material-symbols-outlined text-6xl text-outline-variant mb-4">receipt_long</span>
            <h3 class="text-base font-bold text-primary mb-2">Aún no tienes facturas</h3>
            <p class="text-on-surface-variant text-sm mb-6">Cuando realices una compra, tus facturas aparecerán aquí.</p>
            <a href="{{ route('cliente.catalogo') }}" wire:navigate class="inline-flex justify-center items-center gap-2 rounded-lg border border-transparent bg-secondary px-6 py-2.5 text-sm font-bold uppercase tracking-wider text-on-secondary shadow-sm hover:bg-secondary-container hover:text-on-secondary-container transition-colors font-label-caps">
                <span class="material-symbols-outlined text-[18px]">storefront</span>
                Ir a la tienda
            </a>
        </div>
    @endif
</x-cliente.perfil.layout>
@endsection