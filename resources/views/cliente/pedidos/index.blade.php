@extends('layouts.cliente')

@section('title', 'Mis Pedidos')

@section('content')
<x-cliente.perfil.layout active="pedidos">
    {{-- Encabezado y barra de filtros --}}
    <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 mb-6">
        <div>
            <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white">Mis Pedidos</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Consulta el estado, historial y detalle de todas tus compras.</p>
        </div>
        
        <form method="GET" action="{{ route('cliente.perfil.pedidos.index') }}" class="flex flex-col sm:flex-row gap-3 w-full xl:w-auto shrink-0 items-start sm:items-center" x-data="{ submitTimeout: null, autoSubmit() { clearTimeout(this.submitTimeout); this.submitTimeout = setTimeout(() => this.$el.closest('form').submit(), 500); } }">
            
            {{-- Filtros de estado por pestañas tipo pill --}}
            <div class="flex items-center gap-1.5 p-1 bg-slate-100 dark:bg-slate-800/60 rounded-xl border border-slate-200/80 dark:border-slate-700/60 w-full sm:w-auto overflow-x-auto">
                <a href="{{ route('cliente.perfil.pedidos.index', array_merge(request()->except('estado', 'page'), ['estado' => ''])) }}" wire:navigate
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all whitespace-nowrap {{ !request('estado') ? 'bg-white dark:bg-slate-700 text-emerald-600 dark:text-emerald-400 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }}">
                    Todos
                </a>
                <a href="{{ route('cliente.perfil.pedidos.index', array_merge(request()->except('estado', 'page'), ['estado' => 'pendiente'])) }}" wire:navigate
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all whitespace-nowrap {{ request('estado') === 'pendiente' ? 'bg-white dark:bg-slate-700 text-emerald-600 dark:text-emerald-400 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }}">
                    Pendientes
                </a>
                <a href="{{ route('cliente.perfil.pedidos.index', array_merge(request()->except('estado', 'page'), ['estado' => 'entregado'])) }}" wire:navigate
                   class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all whitespace-nowrap {{ request('estado') === 'entregado' ? 'bg-white dark:bg-slate-700 text-emerald-600 dark:text-emerald-400 shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }}">
                    Completados
                </a>
            </div>

            {{-- Buscador en tiempo real --}}
            <div class="relative w-full sm:w-64">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por pedido o producto..." @input="autoSubmit()"
                    class="w-full pl-9 pr-4 py-2 bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 outline-none transition-all">
            </div>
            
            @if(request('estado'))
                <input type="hidden" name="estado" value="{{ request('estado') }}">
            @endif
            <button type="submit" class="hidden">Buscar</button>
        </form>
    </div>

    {{-- Listado de pedidos en formato Cards --}}
    @if($pedidos->count() > 0)
        <div class="flex flex-col gap-4">
            @foreach($pedidos as $pedido)
                @php
                    $ultimoEstado = $pedido->ultimoEstado ? $pedido->ultimoEstado->estado : 'pendiente';
                    $configEstado = match($ultimoEstado) {
                        'entregado' => [
                            'label' => 'Entregado',
                            'dot' => 'bg-emerald-500',
                            'badge' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/60',
                        ],
                        'enviado' => [
                            'label' => 'Enviado',
                            'dot' => 'bg-blue-500',
                            'badge' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 border border-blue-200/80 dark:border-blue-800/60',
                        ],
                        'en_transito' => [
                            'label' => 'En tránsito',
                            'dot' => 'bg-blue-500',
                            'badge' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 border border-blue-200/80 dark:border-blue-800/60',
                        ],
                        'listo_para_envio' => [
                            'label' => 'Listo para envío',
                            'dot' => 'bg-blue-500',
                            'badge' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400 border border-blue-200/80 dark:border-blue-800/60',
                        ],
                        'en_preparacion' => [
                            'label' => 'En preparación',
                            'dot' => 'bg-amber-500',
                            'badge' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200/80 dark:border-amber-800/60',
                        ],
                        'pago_confirmado' => [
                            'label' => 'Pago confirmado',
                            'dot' => 'bg-teal-500',
                            'badge' => 'bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-400 border border-teal-200/80 dark:border-teal-800/60',
                        ],
                        'cancelado' => [
                            'label' => 'Cancelado',
                            'dot' => 'bg-rose-500',
                            'badge' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200/80 dark:border-rose-800/60',
                        ],
                        'reembolsado' => [
                            'label' => 'Reembolsado',
                            'dot' => 'bg-slate-400',
                            'badge' => 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700',
                        ],
                        'devolucion_solicitada' => [
                            'label' => 'Devolución solicitada',
                            'dot' => 'bg-amber-500',
                            'badge' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200/80 dark:border-amber-800/60',
                        ],
                        default => [
                            'label' => 'Pendiente',
                            'dot' => 'bg-amber-500',
                            'badge' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200/80 dark:border-amber-800/60',
                        ]
                    };

                    $primerItem = $pedido->items->first();
                    $totalArticulos = $pedido->items->sum('cantidad');
                    $otrosItemsCount = $pedido->items->count() - 1;
                @endphp

                {{-- Tarjeta individual de pedido con diseño corporativo / e-commerce de alto nivel --}}
                <div class="bg-white dark:bg-[#1f2327] rounded-3xl border border-slate-200/90 dark:border-slate-800 p-5 sm:p-6 transition-all duration-200 hover:shadow-md hover:border-slate-300 dark:hover:border-slate-700 flex flex-col gap-4">
                    
                    {{-- Encabezado de la tarjeta: Número, Fecha y Estado (Wrap limpio en móviles) --}}
                    <div class="flex items-center justify-between gap-3 pb-3.5 border-b border-slate-100 dark:border-slate-800/80">
                        <div class="flex items-center gap-2 sm:gap-2.5 min-w-0">
                            <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-[20px] shrink-0">receipt_long</span>
                            <span class="text-xs sm:text-sm font-extrabold text-slate-900 dark:text-white font-mono tracking-tight truncate">#{{ $pedido->numero_pedido }}</span>
                            <span class="text-slate-300 dark:text-slate-700 text-xs hidden xs:inline">•</span>
                            <span class="text-[11px] sm:text-xs text-slate-500 dark:text-slate-400 whitespace-nowrap hidden xs:inline">
                                {{ $pedido->creado_en->format('d/m/Y') }}
                            </span>
                        </div>

                        {{-- Estado en pill redondeada --}}
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold shrink-0 {{ $configEstado['badge'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $configEstado['dot'] }}"></span>
                            <span>{{ $configEstado['label'] }}</span>
                        </div>
                    </div>

                    {{-- Cuerpo de la tarjeta: 1 Imagen destacada con indicador numérico + Nombre + Cantidad + Total + Botón --}}
                    @php
                        $primerImg = null;
                        if ($primerItem?->variante && $primerItem->variante->imagen_ruta) {
                            $primerImg = asset('storage/' . $primerItem->variante->imagen_ruta);
                        } elseif ($primerItem?->producto) {
                            $primerImg = $primerItem->producto->imagen_url;
                        }
                    @endphp

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        
                        {{-- Producto Principal (1 sola imagen clara + badge N productos si compró 20) --}}
                        <div class="flex items-center gap-3.5 sm:gap-4 min-w-0 flex-1">
                            {{-- Contenedor de la imagen destacada --}}
                            <div class="relative w-16 h-16 sm:w-18 sm:h-18 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/90 dark:border-slate-700/80 overflow-hidden shrink-0 flex items-center justify-center p-1">
                                @if($primerImg)
                                    <img src="{{ $primerImg }}" alt="{{ $primerItem->producto?->nombre ?? 'Producto' }}" class="w-full h-full object-contain">
                                @else
                                    <span class="material-symbols-outlined text-slate-400 text-2xl">inventory_2</span>
                                @endif

                                {{-- Indicador superpuesto si compró varios artículos --}}
                                @if($otrosItemsCount > 0)
                                    <span class="absolute bottom-1 right-1 bg-slate-900/90 text-white text-[10px] font-black px-1.5 py-0.5 rounded-md shadow-xs backdrop-blur-xs">
                                        +{{ $otrosItemsCount }}
                                    </span>
                                @endif
                            </div>

                            {{-- Información del artículo y fecha en móviles --}}
                            <div class="min-w-0 flex-1">
                                <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white line-clamp-1 leading-snug">
                                    {{ $primerItem?->producto?->nombre ?? 'Pedido registrado' }}
                                </h4>
                                
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    <span>
                                        {{ $totalArticulos }} {{ $totalArticulos == 1 ? 'artículo' : 'artículos' }}
                                        @if($otrosItemsCount > 0)
                                            <span class="text-slate-400">({{ $otrosItemsCount + 1 }} productos)</span>
                                        @endif
                                    </span>
                                    <span class="text-slate-300 dark:text-slate-700 xs:hidden">•</span>
                                    <span class="xs:hidden">{{ $pedido->creado_en->format('d/m/Y') }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Total y Botón de acción (Mantiene orden limpio en mobile) --}}
                        <div class="flex items-center justify-between sm:justify-end gap-4 pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-100 dark:border-slate-800/60 shrink-0">
                            <div class="text-left sm:text-right">
                                <span class="block text-[10px] uppercase font-bold tracking-wider text-slate-400 dark:text-slate-500 leading-none mb-1">Total abonado</span>
                                <span class="text-base sm:text-lg font-black text-slate-900 dark:text-white leading-none">${{ number_format($pedido->total, 2) }}</span>
                            </div>

                            <a href="{{ route('cliente.perfil.pedidos.detalle', $pedido->id) }}" wire:navigate
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] transition-all shadow-xs cursor-pointer shrink-0">
                                <span>Ver detalle</span>
                                <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                            </a>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>

        {{-- Paginación --}}
        @if($pedidos->hasPages())
            <div class="mt-8 flex justify-center items-center gap-2">
                @if($pedidos->onFirstPage())
                    <span class="p-2 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-400 opacity-50 cursor-not-allowed">
                        <span class="material-symbols-outlined text-[18px]">chevron_left</span>
                    </span>
                @else
                    <a href="{{ $pedidos->previousPageUrl() }}" wire:navigate class="p-2 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <span class="material-symbols-outlined text-[18px]">chevron_left</span>
                    </a>
                @endif

                <span class="text-xs font-bold text-slate-600 dark:text-slate-400 px-3 uppercase tracking-wider">
                    Página {{ $pedidos->currentPage() }} de {{ $pedidos->lastPage() }}
                </span>

                @if($pedidos->hasMorePages())
                    <a href="{{ $pedidos->nextPageUrl() }}" wire:navigate class="p-2 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                    </a>
                @else
                    <span class="p-2 border border-slate-200 dark:border-slate-800 rounded-xl text-slate-400 opacity-50 cursor-not-allowed">
                        <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                    </span>
                @endif
            </div>
        @endif
    @else
        {{-- Estado vacío --}}
        <div class="text-center py-16 px-4 bg-white dark:bg-[#1f2327] rounded-2xl border border-slate-200 dark:border-slate-800">
            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <span class="material-symbols-outlined text-3xl">shopping_bag</span>
            </div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1.5">No se encontraron pedidos</h3>
            <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm max-w-sm mx-auto mb-6">
                @if(request('buscar') || request('estado'))
                    No hay pedidos que coincidan con los filtros aplicados. Intenta con otra búsqueda.
                @else
                    Aún no has realizado ninguna compra en nuestra tienda.
                @endif
            </p>
            <a href="{{ route('cliente.catalogo') }}" wire:navigate class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold shadow-sm transition-colors">
                <span class="material-symbols-outlined text-[18px]">storefront</span>
                <span>Explorar catálogo</span>
            </a>
        </div>
    @endif
</x-cliente.perfil.layout>
@endsection
