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
<x-cliente.perfil.layout active="pedidos">
    <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 mb-6">
        <div>
            <h3 class="text-base font-bold text-primary">Historial de Pedidos</h3>
            <p class="text-xs text-on-surface-variant mt-0.5">Consulta el estado y detalle de tus pedidos anteriores.</p>
        </div>
        
        <form method="GET" action="{{ route('cliente.perfil.pedidos.index') }}" class="flex flex-col sm:flex-row gap-3 w-full xl:w-auto shrink-0 items-start sm:items-center" x-data="{ submitTimeout: null, autoSubmit() { clearTimeout(this.submitTimeout); this.submitTimeout = setTimeout(() => this.$el.closest('form').submit(), 500); } }">
            
            <!-- Filtro Estado (Pills) -->
            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto pb-1 sm:pb-0">
                <a href="{{ route('cliente.perfil.pedidos.index', array_merge(request()->except('estado', 'page'), ['estado' => ''])) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ !request('estado') ? 'bg-primary text-on-primary shadow-sm' : 'bg-surface-container-lowest border border-outline-variant text-on-surface-variant hover:bg-surface-dim' }}">
                    Todos
                </a>
                <a href="{{ route('cliente.perfil.pedidos.index', array_merge(request()->except('estado', 'page'), ['estado' => 'pendiente'])) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ request('estado') === 'pendiente' ? 'bg-tertiary text-on-tertiary shadow-sm' : 'bg-surface-container-lowest border border-outline-variant text-on-surface-variant hover:bg-surface-dim' }}">
                    Pendientes
                </a>
                <a href="{{ route('cliente.perfil.pedidos.index', array_merge(request()->except('estado', 'page'), ['estado' => 'entregado'])) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ request('estado') === 'entregado' ? 'bg-secondary text-on-secondary shadow-sm' : 'bg-surface-container-lowest border border-outline-variant text-on-surface-variant hover:bg-surface-dim' }}">
                    Completados
                </a>
            </div>

            <!-- Buscador -->
            <div class="relative w-full sm:w-64">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline-variant text-[20px]">search</span>
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar pedido #..." @input="autoSubmit()"
                    class="w-full pl-10 pr-4 py-2 bg-surface-container-lowest border border-outline-variant rounded-xl text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all placeholder:text-on-surface-variant/50">
            </div>
            
            @if(request('estado'))
                <input type="hidden" name="estado" value="{{ request('estado') }}">
            @endif
            <button type="submit" class="hidden">Buscar</button>
        </form>
    </div>

    @if($pedidos->count() > 0)
        <!-- Desktop Table -->
        <div class="hidden md:block bg-surface-container-lowest border border-outline-variant rounded-xl overflow-x-auto ambient-shadow">
            <table class="w-full text-left min-w-[800px]">
                <thead class="bg-surface-container-low border-b border-outline-variant">
                    <tr>
                        <th class="py-4 px-6 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">Pedido / Producto</th>
                        <th class="py-4 px-6 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">Fecha</th>
                        <th class="py-4 px-6 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">Total</th>
                        <th class="py-4 px-6 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider">Estado</th>
                        <th class="py-4 px-6 text-[11px] font-bold text-on-surface-variant uppercase tracking-wider text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/50">
                    @foreach($pedidos as $pedido)
                        @php
                            $ultimoEstado = $pedido->ultimoEstado ? $pedido->ultimoEstado->estado : 'pendiente';
                            $configEstado = match($ultimoEstado) {
                                'entregado' => [ 'chip_bg' => 'bg-secondary/10', 'chip_text' => 'text-secondary', 'icon' => 'check_circle' ],
                                'pendiente', 'pago_confirmado', 'en_preparacion' => [ 'chip_bg' => 'bg-tertiary-container/20', 'chip_text' => 'text-tertiary', 'icon' => 'schedule' ],
                                'cancelado', 'devolucion_solicitada', 'pago_rechazado' => [ 'chip_bg' => 'bg-primary/10', 'chip_text' => 'text-primary', 'icon' => 'flag' ],
                                'reembolsado' => [ 'chip_bg' => 'bg-slate-100', 'chip_text' => 'text-slate-800', 'icon' => 'currency_exchange' ],
                                default => [ 'chip_bg' => 'bg-blue-100', 'chip_text' => 'text-blue-700', 'icon' => 'local_shipping' ]
                            };
                            $labelEstado = ucfirst(str_replace('_', ' ', $ultimoEstado));
                        @endphp
                        <tr class="hover:bg-surface-dim/30 transition-colors">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 rounded-lg border border-outline-variant overflow-hidden bg-surface-container-low shrink-0 flex items-center justify-center">
                                        @php
                                            $primerItem = $pedido->items->first();
                                            $imagenUrl = null;
                                            if ($primerItem) {
                                                if ($primerItem->variante && $primerItem->variante->imagen_ruta) {
                                                    $imagenUrl = asset('storage/'.$primerItem->variante->imagen_ruta);
                                                } elseif ($primerItem->producto && $primerItem->producto->imagenes->first()) {
                                                    $imagenUrl = asset($primerItem->producto->imagenes->first()->ruta);
                                                }
                                            }
                                        @endphp
                                        @if($imagenUrl)
                                            <img src="{{ $imagenUrl }}" alt="Producto" class="w-full h-full object-cover">
                                        @else
                                            <span class="material-symbols-outlined text-outline">image</span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-primary">#{{ $pedido->numero_pedido }}</div>
                                        <div class="text-[11px] text-on-surface-variant mt-0.5 max-w-[200px] lg:max-w-[300px] truncate">
                                            {{ $primerItem?->producto?->nombre ?? 'Producto Desconocido' }} 
                                            @if($pedido->items->count() > 1)
                                                <span class="font-bold text-secondary">y {{ $pedido->items->count() - 1 }} más</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-sm text-on-surface-variant font-medium">{{ $pedido->creado_en->format('d/m/Y') }}</td>
                            <td class="py-4 px-6 text-sm font-bold text-primary">${{ number_format($pedido->total, 2) }}</td>
                            <td class="py-4 px-6">
                                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full {{ $configEstado['chip_bg'] }} {{ $configEstado['chip_text'] }} font-label-caps text-[11px] font-bold tracking-wider uppercase">
                                    <span class="material-symbols-outlined text-[14px]">{{ $configEstado['icon'] }}</span>
                                    {{ $labelEstado }}
                                </div>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <a href="{{ route('cliente.perfil.pedidos.detalle', $pedido->id) }}" wire:navigate class="inline-flex items-center gap-1.5 px-4 py-2 border border-outline-variant hover:border-primary text-on-surface-variant hover:text-primary rounded-lg text-[11px] font-bold tracking-wider uppercase transition-colors">
                                    Ver Detalle
                                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards -->
        <div class="grid grid-cols-1 md:hidden gap-6">
            @foreach($pedidos as $pedido)
                @php
                    $ultimoEstado = $pedido->ultimoEstado ? $pedido->ultimoEstado->estado : 'pendiente';
                    $configEstado = match($ultimoEstado) {
                        'entregado' => [ 'bar' => 'bg-secondary/80', 'chip_bg' => 'bg-secondary/10', 'chip_text' => 'text-secondary', 'icon' => 'check_circle', ],
                        'pendiente', 'pago_confirmado', 'en_preparacion' => [ 'bar' => 'bg-tertiary-container', 'chip_bg' => 'bg-tertiary-container/20', 'chip_text' => 'text-tertiary', 'icon' => 'schedule', ],
                        'cancelado', 'devolucion_solicitada', 'pago_rechazado' => [ 'bar' => 'bg-primary', 'chip_bg' => 'bg-primary/10', 'chip_text' => 'text-primary', 'icon' => 'flag', ],
                        'reembolsado' => [ 'bar' => 'bg-slate-700', 'chip_bg' => 'bg-slate-100', 'chip_text' => 'text-slate-800', 'icon' => 'currency_exchange', ],
                        default => [ 'bar' => 'bg-blue-500', 'chip_bg' => 'bg-blue-100', 'chip_text' => 'text-blue-700', 'icon' => 'local_shipping', ]
                    };
                    $labelEstado = ucfirst(str_replace('_', ' ', $ultimoEstado));
                @endphp

                <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 flex flex-col gap-4 ambient-shadow ambient-shadow-hover transition-all duration-300 relative overflow-hidden group">
                    <div class="absolute top-0 left-0 w-full h-1 {{ $configEstado['bar'] }}"></div>

                    <div class="flex justify-between items-start">
                        <div>
                            <span class="font-label-caps text-xs text-on-surface-variant uppercase tracking-wider font-bold">Pedido #</span>
                            <div class="text-sm font-bold text-primary mt-1">{{ $pedido->numero_pedido }}</div>
                        </div>

                        <div class="px-3 py-1 rounded-full {{ $configEstado['chip_bg'] }} {{ $configEstado['chip_text'] }} font-label-caps text-[11px] font-bold tracking-wider flex items-center gap-1 uppercase">
                            <span class="material-symbols-outlined text-[14px]">{{ $configEstado['icon'] }}</span>
                            {{ $labelEstado }}
                        </div>
                    </div>

                    <div class="flex flex-col gap-2 py-4 border-y border-outline-variant/50">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-on-surface-variant">Fecha</span>
                            <span class="text-xs font-semibold text-primary">{{ $pedido->creado_en->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-on-surface-variant">Artículos</span>
                            <span class="text-xs font-semibold text-primary">{{ $pedido->items->sum('cantidad') }} {{ $pedido->items->sum('cantidad') == 1 ? 'artículo' : 'artículos' }}</span>
                        </div>
                    </div>

                    <div class="flex justify-between items-end mt-auto">
                        <div>
                            <span class="font-label-caps text-[10px] uppercase font-bold tracking-wider text-on-surface-variant">Total</span>
                            <div class="text-base font-bold text-primary mt-1">${{ number_format($pedido->total, 2) }}</div>
                        </div>
                        <a href="{{ route('cliente.perfil.pedidos.detalle', $pedido->id) }}" wire:navigate class="border border-primary text-primary bg-transparent hover:bg-primary/5 rounded-lg px-4 py-2 font-label-caps text-[11px] font-bold tracking-wider uppercase transition-colors flex items-center gap-2">
                            Ver Detalle
                            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        @if($pedidos->hasPages())
        <div class="mt-8 flex justify-center items-center gap-2">
            @if($pedidos->onFirstPage())
                <span class="p-2 border border-outline-variant rounded-lg text-on-surface-variant opacity-50 cursor-not-allowed">
                    <span class="material-symbols-outlined">chevron_left</span>
                </span>
            @else
                <a href="{{ $pedidos->previousPageUrl() }}" wire:navigate class="p-2 border border-outline-variant rounded-lg text-on-surface-variant hover:bg-surface-dim transition-colors">
                    <span class="material-symbols-outlined">chevron_left</span>
                </a>
            @endif

            <span class="font-label-caps text-xs font-bold tracking-wider text-on-surface px-4 uppercase">
                Página {{ $pedidos->currentPage() }} de {{ $pedidos->lastPage() }}
            </span>

            @if($pedidos->hasMorePages())
                <a href="{{ $pedidos->nextPageUrl() }}" wire:navigate class="p-2 border border-outline-variant rounded-lg text-on-surface-variant hover:bg-surface-dim transition-colors">
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
            <span class="material-symbols-outlined text-6xl text-outline-variant mb-4">shopping_bag</span>
            <h3 class="text-base font-bold text-primary mb-2">Aún no tienes pedidos</h3>
            <p class="text-on-surface-variant text-sm mb-6">Explora nuestro catálogo y encuentra los mejores productos.</p>
            <a href="{{ route('cliente.catalogo') }}" wire:navigate class="inline-flex justify-center items-center gap-2 rounded-lg border border-transparent bg-secondary px-6 py-2.5 text-sm font-bold uppercase tracking-wider text-on-secondary shadow-sm hover:bg-secondary-container hover:text-on-secondary-container transition-colors font-label-caps">
                <span class="material-symbols-outlined text-[18px]">storefront</span>
                Ir a la tienda
            </a>
        </div>
    @endif
</x-cliente.perfil.layout>
@endsection
