@extends('layouts.admin')
@section('title', 'Detalle de Factura ' . $factura->numero)

@section('content')
<div class="space-y-6 font-sans">
    <div class="flex items-center justify-between mb-4">
        <a href="{{ route('admin.facturas.index') }}" class="inline-flex items-center text-sm font-medium text-primary hover:text-primary/80">
            <span class="material-symbols-outlined mr-1 text-[18px]">arrow_back</span>
            Volver a facturas
        </a>
    </div>

    <!-- Header de Factura -->
    <div class="card-elevated rounded-xl p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-3">
                Factura {{ $factura->numero }}
                @if($factura->estado === 'emitida')
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border bg-emerald-50 text-emerald-700 border-emerald-200">
                        Emitida
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border bg-slate-100 text-slate-700 border-slate-200">
                        Anulada
                    </span>
                @endif
            </h1>
            <p class="text-sm text-slate-500 mt-1">Emitida el {{ $factura->emitida_en->format('d/m/Y H:i') }} para <span class="font-medium text-slate-700">{{ $factura->usuario->nombre ?? 'Desconocido' }} {{ $factura->usuario->apellido }}</span></p>
        </div>
        
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.facturas.pdf', $factura) }}" class="inline-flex items-center px-4 py-2 bg-slate-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-slate-900 transition">
                <span class="material-symbols-outlined text-[16px] mr-1.5">download</span> Descargar PDF
            </a>
            
            @if($factura->estado === 'emitida')
                <form action="{{ route('admin.facturas.reenviar', $factura) }}" method="POST">
                    @csrf
                    <input type="hidden" name="email_destino" value="{{ $factura->usuario->email }}">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-on-primary uppercase tracking-widest hover:bg-primary-container transition">
                        <span class="material-symbols-outlined text-[16px] mr-1.5">mail</span> Reenviar
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Columna Izquierda: Items -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Items de la Factura -->
            <div class="card-elevated rounded-xl p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-4 border-b border-slate-100 pb-2">Artículos ({{ $factura->pedido->items->count() }})</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead>
                            <tr>
                                <th scope="col" class="px-3 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Producto</th>
                                <th scope="col" class="px-3 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Precio U.</th>
                                <th scope="col" class="px-3 py-3 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider">Cant.</th>
                                <th scope="col" class="px-3 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($factura->pedido->items as $item)
                            <tr>
                                <td class="px-3 py-4">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0 rounded bg-slate-100 overflow-hidden border border-slate-200">
                                            @if($item->producto)
                                                <img src="{{ $item->producto->imagen_url }}" alt="{{ $item->producto->nombre }}" class="h-full w-full object-cover">
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-slate-900">{{ $item->producto->nombre ?? 'Producto Desconocido' }}</div>
                                            @if($item->variante)
                                            <div class="text-xs text-slate-500">
                                                SKU: {{ $item->variante->sku }}
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-right text-sm text-slate-500">
                                    ${{ number_format($item->precio_unitario, 2) }}
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-center text-sm font-medium text-slate-900">
                                    {{ $item->cantidad }}
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-right text-sm font-medium text-slate-900">
                                    ${{ number_format($item->subtotal, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Reenvíos Historial -->
            @if($factura->reenvios->count() > 0)
            <div class="card-elevated rounded-xl p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-6 border-b border-slate-100 pb-2">Historial de Reenvíos</h2>
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        @foreach($factura->reenvios as $reenvio)
                            <li>
                                <div class="relative pb-8">
                                    @if(!$loop->last)
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-slate-200" aria-hidden="true"></span>
                                    @endif
                                    <div class="relative flex space-x-3">
                                        <div>
                                            <span class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center ring-8 ring-white">
                                                <span class="material-symbols-outlined text-slate-500 text-sm">mail</span>
                                            </span>
                                        </div>
                                        <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                            <div>
                                                <p class="text-sm text-slate-900 font-medium">Reenviado a {{ $reenvio->email_destino }}</p>
                                                @if($reenvio->mensaje_personalizado)
                                                    <p class="mt-1 text-sm text-slate-500">"{{ $reenvio->mensaje_personalizado }}"</p>
                                                @endif
                                            </div>
                                            <div class="whitespace-nowrap text-right text-sm text-slate-500 flex flex-col">
                                                <time datetime="{{ $reenvio->enviado_en }}">{{ $reenvio->enviado_en->format('d/m/Y H:i') }}</time>
                                                <span class="text-xs text-slate-400 mt-1">{{ $reenvio->usuario->nombre ?? 'Sistema' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
        </div>

        <!-- Columna Derecha: Resumen y Cliente -->
        <div class="space-y-6">
            <!-- Datos del Cliente -->
            <div class="card-elevated rounded-xl p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-4 border-b border-slate-100 pb-2">Facturar a</h2>
                <div class="mb-4">
                    <p class="font-medium text-slate-900 text-sm">{{ $factura->usuario->nombre ?? 'Desconocido' }} {{ $factura->usuario->apellido }}</p>
                    <p class="text-sm text-slate-500">{{ $factura->usuario->email ?? '' }}</p>
                </div>
                
                @if($factura->pedido->direccionEnvio)
                <h3 class="text-sm font-bold text-slate-900 mb-2 mt-4">Dirección Comercial</h3>
                <address class="text-sm text-slate-600 not-italic space-y-1">
                    <p>{{ $factura->pedido->direccionEnvio->linea1 }}</p>
                    <p>{{ $factura->pedido->direccionEnvio->ciudad }}, {{ $factura->pedido->direccionEnvio->provincia }}</p>
                </address>
                @endif
                
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900 mb-2">Pedido Relacionado</h3>
                    <a href="{{ route('admin.pedidos.detalle', $factura->pedido->id) }}" class="inline-flex items-center text-sm text-primary hover:text-primary/80 font-medium">
                        {{ $factura->pedido->numero_pedido }}
                        <span class="material-symbols-outlined text-[16px] ml-1">open_in_new</span>
                    </a>
                </div>
            </div>

            <!-- Resumen Financiero -->
            <div class="card-elevated rounded-xl p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-4 border-b border-slate-100 pb-2">Resumen Financiero</h2>
                <dl class="space-y-3 text-sm text-slate-600">
                    <div class="flex justify-between">
                        <dt>Subtotal</dt>
                        <dd class="font-medium text-slate-900">${{ number_format($factura->subtotal, 2) }}</dd>
                    </div>
                    @if($factura->descuento > 0)
                    <div class="flex justify-between text-emerald-600">
                        <dt>Descuento</dt>
                        <dd class="font-medium">-${{ number_format($factura->descuento, 2) }}</dd>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <dt>Envío</dt>
                        <dd class="font-medium text-slate-900">${{ number_format($factura->costo_envio, 2) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt>ITBMS ({{ $factura->itbms_tasa }}%)</dt>
                        <dd class="font-medium text-slate-900">${{ number_format($factura->itbms_monto, 2) }}</dd>
                    </div>
                    <div class="flex items-center justify-between border-t border-slate-200 pt-3">
                        <dt class="text-base font-bold text-slate-900">Total Facturado</dt>
                        <dd class="text-lg font-bold text-slate-900">${{ number_format($factura->total, 2) }}</dd>
                    </div>
                </dl>
                <div class="mt-4 pt-4 border-t border-slate-200">
                    <p class="text-sm text-slate-500">
                        <span class="font-medium text-slate-900">Método de pago:</span> 
                        <span class="uppercase font-semibold tracking-wider text-xs">{{ str_replace('_', ' ', $factura->metodo_pago) }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
