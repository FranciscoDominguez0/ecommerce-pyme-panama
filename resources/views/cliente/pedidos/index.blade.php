<x-app-layout>
    <div class="bg-slate-50 min-h-screen py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-slate-900 mb-8 font-sans">Mis Pedidos</h1>

            @if($pedidos->count() > 0)
                <div class="space-y-6">
                    @foreach($pedidos as $pedido)
                        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4 transition-shadow hover:shadow-md">
                            
                            <div class="flex-1 space-y-2">
                                <div class="flex items-center gap-3">
                                    <h2 class="text-lg font-bold text-slate-900 font-sans">Pedido #{{ $pedido->numero_pedido }}</h2>
                                    @php
                                        $estadoClasses = [
                                            'pendiente' => 'bg-slate-100 text-slate-700',
                                            'pago_confirmado' => 'bg-blue-100 text-blue-700',
                                            'en_preparacion' => 'bg-amber-100 text-amber-700',
                                            'enviado' => 'bg-indigo-100 text-indigo-700',
                                            'entregado' => 'bg-emerald-100 text-emerald-700',
                                            'cancelado' => 'bg-red-100 text-red-700',
                                        ];
                                        $ultimoEstado = $pedido->ultimoEstado ? $pedido->ultimoEstado->estado : 'pendiente';
                                        $claseEstado = $estadoClasses[$ultimoEstado] ?? 'bg-slate-100 text-slate-700';
                                        $labelEstado = ucfirst(str_replace('_', ' ', $ultimoEstado));
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $claseEstado }}">
                                        {{ $labelEstado }}
                                    </span>
                                </div>
                                <p class="text-sm text-slate-500">Realizado el {{ $pedido->creado_en->format('d/m/Y \a \l\a\s H:i') }}</p>
                                <p class="text-sm font-medium text-slate-700">Total: ${{ number_format($pedido->total, 2) }}</p>
                            </div>
                            
                            <div class="flex flex-col sm:flex-row gap-3">
                                <a href="{{ route('cliente.pedidos.detalle', $pedido->id) }}" class="inline-flex justify-center items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                                    Ver Detalle
                                </a>
                                @if($ultimoEstado === 'entregado')
                                    <button class="inline-flex justify-center items-center rounded-lg border border-transparent bg-slate-800 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-900 transition-colors">
                                        Comprar de Nuevo
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-8">
                    {{ $pedidos->links() }}
                </div>
            @else
                <div class="text-center py-16 bg-white rounded-xl border border-slate-200 shadow-sm">
                    <span class="material-symbols-outlined text-6xl text-slate-300 mb-4">shopping_bag</span>
                    <h3 class="text-xl font-medium text-slate-900 font-sans mb-2">Aún no tienes pedidos</h3>
                    <p class="text-slate-500 mb-6">Explora nuestro catálogo y encuentra los mejores productos.</p>
                    <a href="{{ route('cliente.catalogo') }}" class="inline-flex justify-center rounded-lg border border-transparent bg-emerald-600 px-6 py-3 text-base font-medium text-white shadow-sm hover:bg-emerald-700 transition-colors">
                        Ir a la tienda
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
