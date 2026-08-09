<x-app-layout>
    <div class="bg-slate-50 min-h-screen py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="mb-8">
                <a href="{{ route('cliente.pedidos.index') }}" class="inline-flex items-center text-sm font-medium text-emerald-600 hover:text-emerald-700">
                    <span class="material-symbols-outlined mr-1 text-[18px]">arrow_back</span>
                    Volver a mis pedidos
                </a>
            </div>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900 font-sans">Pedido #{{ $pedido->numero_pedido }}</h1>
                    <p class="text-sm text-slate-500 mt-1">Realizado el {{ $pedido->creado_en->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    @php
                        $ultimoEstado = $pedido->ultimoEstado ? $pedido->ultimoEstado->estado : 'pendiente';
                        $estadoClasses = [
                            'pendiente' => 'bg-slate-100 text-slate-700',
                            'pago_confirmado' => 'bg-blue-100 text-blue-700',
                            'pago_rechazado' => 'bg-red-100 text-red-700',
                            'en_preparacion' => 'bg-amber-100 text-amber-700',
                            'listo_para_envio' => 'bg-teal-100 text-teal-700',
                            'enviado' => 'bg-indigo-100 text-indigo-700',
                            'entregado' => 'bg-emerald-100 text-emerald-700',
                            'cancelado' => 'bg-red-100 text-red-700',
                            'devolucion_solicitada' => 'bg-orange-100 text-orange-700',
                        ];
                        $claseEstado = $estadoClasses[$ultimoEstado] ?? 'bg-slate-100 text-slate-700';
                        $labelEstado = ucfirst(str_replace('_', ' ', $ultimoEstado));
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $claseEstado }}">
                        {{ $labelEstado }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Columna Principal -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Línea de Tiempo de Estados -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                        <h2 class="text-lg font-semibold text-slate-800 mb-6 font-sans">Historial del Pedido</h2>
                        <div class="flow-root">
                            <ul role="list" class="-mb-8">
                                @foreach($pedido->estados as $index => $historial)
                                    <li>
                                        <div class="relative pb-8">
                                            @if(!$loop->last)
                                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-slate-200" aria-hidden="true"></span>
                                            @endif
                                            <div class="relative flex space-x-3">
                                                <div>
                                                    <span class="h-8 w-8 rounded-full bg-emerald-100 flex items-center justify-center ring-8 ring-white">
                                                        <span class="material-symbols-outlined text-emerald-600 text-sm">
                                                            {{ $historial->estado === 'entregado' ? 'done_all' : 'check' }}
                                                        </span>
                                                    </span>
                                                </div>
                                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                    <div>
                                                        <p class="text-sm text-slate-900 font-medium">{{ ucfirst(str_replace('_', ' ', $historial->estado)) }}</p>
                                                        @if($historial->comentario)
                                                            <p class="mt-1 text-sm text-slate-500">{{ $historial->comentario }}</p>
                                                        @endif
                                                    </div>
                                                    <div class="whitespace-nowrap text-right text-sm text-slate-500">
                                                        <time datetime="{{ $historial->creado_en }}">{{ $historial->creado_en->format('d/m/Y H:i') }}</time>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <!-- Items -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                        <h2 class="text-lg font-semibold text-slate-800 mb-4 font-sans">Artículos ({{ $pedido->items->count() }})</h2>
                        <div class="space-y-4">
                            @foreach($pedido->items as $item)
                                <div class="flex items-center gap-4 border-b border-slate-100 pb-4 last:border-0 last:pb-0">
                                    <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded border border-slate-200">
                                        @if($item->producto)
                                            <img src="{{ $item->producto->imagen_url }}" alt="{{ $item->producto->nombre }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="h-full w-full bg-slate-100 flex items-center justify-center">
                                                <span class="material-symbols-outlined text-slate-400 text-sm">image</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="text-sm font-medium text-slate-900">{{ $item->producto ? $item->producto->nombre : 'Producto no disponible' }}</h3>
                                        @if($item->variante)
                                            <p class="text-xs text-slate-500 mt-0.5">
                                                @foreach($item->variante->opciones as $opcion)
                                                    {{ $opcion->tipo->nombre }}: {{ $opcion->valor }}@if(!$loop->last), @endif
                                                @endforeach
                                            </p>
                                        @endif
                                        <p class="text-xs text-slate-500 mt-1">Cant: {{ $item->cantidad }} x ${{ number_format($item->precio_unitario, 2) }}</p>
                                    </div>
                                    <div class="text-sm font-medium text-slate-900">
                                        ${{ number_format($item->subtotal, 2) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Columna Lateral -->
                <div class="space-y-6">
                    
                    <!-- Resumen de Costos -->
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                        <h2 class="text-lg font-semibold text-slate-800 mb-4 font-sans">Resumen</h2>
                        <dl class="space-y-3 text-sm text-slate-600">
                            <div class="flex justify-between">
                                <dt>Subtotal</dt>
                                <dd class="font-medium text-slate-900">${{ number_format($pedido->subtotal, 2) }}</dd>
                            </div>
                            @if($pedido->descuento > 0)
                            <div class="flex justify-between text-emerald-600">
                                <dt>Descuento</dt>
                                <dd class="font-medium">-${{ number_format($pedido->descuento, 2) }}</dd>
                            </div>
                            @endif
                            <div class="flex justify-between">
                                <dt>Costo de Envío</dt>
                                <dd class="font-medium text-slate-900">${{ number_format($pedido->costo_envio, 2) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt>Impuestos (7%)</dt>
                                <dd class="font-medium text-slate-900">${{ number_format($pedido->itbms_monto, 2) }}</dd>
                            </div>
                            <div class="flex items-center justify-between border-t border-slate-200 pt-3">
                                <dt class="text-base font-bold text-slate-900">Total</dt>
                                <dd class="text-lg font-bold text-slate-900">${{ number_format($pedido->total, 2) }}</dd>
                            </div>
                        </dl>
                        <div class="mt-4 pt-4 border-t border-slate-200">
                            <p class="text-sm text-slate-500">
                                <span class="font-medium text-slate-900">Método de pago:</span> 
                                {{ ucfirst(str_replace('_', ' ', $pedido->metodo_pago)) }}
                            </p>
                        </div>
                    </div>

                    <!-- Dirección de Envío -->
                    @if($pedido->direccion)
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                        <h2 class="text-lg font-semibold text-slate-800 mb-4 font-sans">Dirección de Envío</h2>
                        <address class="text-sm text-slate-600 not-italic space-y-1">
                            <p class="font-medium text-slate-900">{{ $pedido->direccion->nombre_receptor }}</p>
                            <p>{{ $pedido->direccion->direccion_exacta }}</p>
                            <p>{{ $pedido->direccion->corregimiento }}, {{ $pedido->direccion->distrito }}</p>
                            <p>{{ $pedido->direccion->provincia }}</p>
                            @if($pedido->direccion->referencia)
                                <p class="mt-2 text-slate-500 italic">Ref: {{ $pedido->direccion->referencia }}</p>
                            @endif
                            @if($pedido->zonaEnvio)
                                <p class="mt-2 text-xs font-semibold text-emerald-600 bg-emerald-50 inline-block px-2 py-1 rounded">Zona: {{ $pedido->zonaEnvio->nombre }}</p>
                            @endif
                        </address>
                    </div>
                    @endif

                    <!-- Notas -->
                    @if($pedido->notas_cliente)
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                        <h2 class="text-sm font-semibold text-slate-800 mb-2 font-sans">Notas Adicionales</h2>
                        <p class="text-sm text-slate-600 italic">"{{ $pedido->notas_cliente }}"</p>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
