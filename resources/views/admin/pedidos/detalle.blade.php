@extends('layouts.admin')
@section('title', 'Detalle de Pedido ' . $pedido->numero_pedido)

@section('content')
<div class="space-y-6 font-sans [&_input]:font-sans [&_select]:font-sans [&_textarea]:font-sans [&_button]:font-sans [&_table]:font-sans">
    <div class="flex items-center justify-between mb-4">
        <a href="{{ route('admin.pedidos.index') }}" class="inline-flex items-center text-sm font-medium text-emerald-600 hover:text-emerald-700">
            <span class="material-symbols-outlined mr-1 text-[18px]">arrow_back</span>
            Volver a pedidos
        </a>
    </div>

    <!-- Header del Pedido -->
    <div class="card-elevated rounded-xl p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-3">
                Pedido {{ $pedido->numero_pedido }}
                @php
                    $ultimoEstado = $pedido->ultimoEstado ? $pedido->ultimoEstado->estado : 'pendiente';
                    $estadoClasses = [
                        'pendiente' => 'bg-slate-100 text-slate-700 border-slate-200',
                        'pago_confirmado' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'pago_rechazado' => 'bg-red-50 text-red-700 border-red-200',
                        'en_preparacion' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'listo_para_envio' => 'bg-teal-50 text-teal-700 border-teal-200',
                        'enviado' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                        'entregado' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'cancelado' => 'bg-red-50 text-red-700 border-red-200',
                    ];
                    $claseEstado = $estadoClasses[$ultimoEstado] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                    $labelEstado = ucfirst(str_replace('_', ' ', $ultimoEstado));
                @endphp
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $claseEstado }}">
                    {{ $labelEstado }}
                </span>
            </h1>
            <p class="text-sm text-slate-500 mt-1">Realizado el {{ $pedido->creado_en->format('d/m/Y H:i') }} por <span class="font-medium text-slate-700">{{ $pedido->usuario->nombre ?? 'Desconocido' }}</span></p>
        </div>
        
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.pedidos.envio', $pedido->id) }}" class="inline-flex items-center px-4 py-2 bg-slate-800 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-slate-900 focus:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 transition ease-in-out duration-150">
                <span class="material-symbols-outlined text-[16px] mr-1.5">local_shipping</span>
                Gestión de Envío
            </a>
            @if($ultimoEstado === 'pendiente' && in_array($pedido->metodo_pago, ['transferencia']))
                <form action="{{ route('admin.pedidos.aprobar-pago', $pedido->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:bg-emerald-700 active:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Aprobar Pago
                    </button>
                </form>
                <button type="button" onclick="document.getElementById('modal-rechazar').classList.remove('hidden')" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Rechazar
                </button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Columna Izquierda: Items y Actualización -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Actualizar Estado -->
            <div class="card-elevated rounded-xl p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-4 border-b border-slate-100 pb-2">Actualizar Estado</h2>
                <form action="{{ route('admin.pedidos.estado', $pedido->id) }}" method="POST" class="flex flex-col sm:flex-row gap-4 items-end">
                    @csrf
                    <div class="w-full sm:w-1/3">
                        <label for="estado" class="block text-sm font-medium text-slate-700 mb-1">Nuevo Estado</label>
                        <select name="estado" id="estado" class="block w-full rounded-md border-slate-300 py-2 pl-3 pr-10 text-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500">
                            <option value="pendiente" {{ $ultimoEstado == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="pago_confirmado" {{ $ultimoEstado == 'pago_confirmado' ? 'selected' : '' }}>Pago Confirmado</option>
                            <option value="en_preparacion" {{ $ultimoEstado == 'en_preparacion' ? 'selected' : '' }}>En Preparación</option>
                            <option value="listo_para_envio" {{ $ultimoEstado == 'listo_para_envio' ? 'selected' : '' }}>Listo para Envío</option>
                            <option value="cancelado" {{ $ultimoEstado == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                        </select>
                    </div>
                    <div class="w-full sm:w-1/2">
                        <label for="comentario" class="block text-sm font-medium text-slate-700 mb-1">Comentario (Opcional)</label>
                        <input type="text" name="comentario" id="comentario" class="block w-full rounded-md border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" placeholder="Ej: Paquete entregado a mensajería">
                    </div>
                    <div class="w-full sm:w-auto">
                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-slate-800 hover:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 transition-colors h-10">
                            Actualizar
                        </button>
                    </div>
                </form>
                <p class="text-[11px] text-slate-500 mt-3 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">info</span>
                    Para marcar el pedido como enviado o entregado, utiliza el botón "Gestión de Envío" en la cabecera.
                </p>
            </div>

            <!-- Items del Pedido -->
            <div class="card-elevated rounded-xl p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-4 border-b border-slate-100 pb-2">Artículos ({{ $pedido->items->count() }})</h2>
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
                            @foreach($pedido->items as $item)
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
                                                @foreach($item->variante->opciones as $opcion)
                                                    {{ $opcion->tipo->nombre }}: {{ $opcion->valor }}@if(!$loop->last), @endif
                                                @endforeach
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

            <!-- Historial -->
            <div class="card-elevated rounded-xl p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-6 border-b border-slate-100 pb-2">Historial del Pedido</h2>
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
                                            <span class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center ring-8 ring-white">
                                                <span class="material-symbols-outlined text-slate-500 text-sm">
                                                    {{ $historial->estado === 'cancelado' ? 'close' : 'check' }}
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
                                            <div class="whitespace-nowrap text-right text-sm text-slate-500 flex flex-col">
                                                <time datetime="{{ $historial->creado_en }}">{{ $historial->creado_en->format('d/m/Y H:i') }}</time>
                                                <span class="text-xs text-slate-400 mt-1">{{ $historial->usuario->nombre ?? 'Sistema' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Resumen y Cliente -->
        <div class="space-y-6">
            
            <!-- Resumen Financiero -->
            <div class="card-elevated rounded-xl p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-4 border-b border-slate-100 pb-2">Resumen Financiero</h2>
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
                        <dt>Envío</dt>
                        <dd class="font-medium text-slate-900">${{ number_format($pedido->costo_envio, 2) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt>ITBMS (7%)</dt>
                        <dd class="font-medium text-slate-900">${{ number_format($pedido->itbms_monto, 2) }}</dd>
                    </div>
                    <div class="flex items-center justify-between border-t border-slate-200 pt-3">
                        <dt class="text-base font-bold text-slate-900">Total Pagado</dt>
                        <dd class="text-lg font-bold text-slate-900">${{ number_format($pedido->total, 2) }}</dd>
                    </div>
                </dl>
                <div class="mt-4 pt-4 border-t border-slate-200">
                    <p class="text-sm text-slate-500 mb-2">
                        <span class="font-medium text-slate-900">Método de pago:</span> 
                        <span class="uppercase font-semibold tracking-wider text-xs">{{ str_replace('_', ' ', $pedido->metodo_pago) }}</span>
                    </p>
                    @if($pedido->comprobante_pago_ruta)
                        <a href="{{ asset('storage/' . $pedido->comprobante_pago_ruta) }}" target="_blank" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                            <span class="material-symbols-outlined text-[18px] mr-1">receipt</span> Ver comprobante
                        </a>
                    @endif
                </div>
            </div>

            <!-- Datos del Cliente & Envío -->
            <div class="card-elevated rounded-xl p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-4 border-b border-slate-100 pb-2">Datos del Cliente</h2>
                <div class="mb-4">
                    <p class="font-medium text-slate-900 text-sm">{{ $pedido->usuario->nombre ?? 'Desconocido' }}</p>
                    <p class="text-sm text-slate-500">{{ $pedido->usuario->email ?? '' }}</p>
                    <p class="text-sm text-slate-500">{{ $pedido->usuario->telefono ?? '' }}</p>
                </div>
                
                @if($pedido->direccion)
                <h3 class="text-sm font-bold text-slate-900 mb-2 mt-4">Dirección de Entrega</h3>
                <address class="text-sm text-slate-600 not-italic space-y-1">
                    <p>{{ $pedido->direccion->nombre_receptor }}</p>
                    <p>{{ $pedido->direccion->direccion_exacta }}</p>
                    <p>{{ $pedido->direccion->corregimiento }}, {{ $pedido->direccion->distrito }}</p>
                    <p>{{ $pedido->direccion->provincia }}</p>
                    @if($pedido->direccion->referencia)
                        <p class="text-slate-500 italic mt-1">Ref: {{ $pedido->direccion->referencia }}</p>
                    @endif
                </address>
                @endif
                
                @if($pedido->zonaEnvio)
                    <div class="mt-3">
                        <span class="inline-flex items-center px-2 py-1 rounded bg-slate-100 text-xs font-medium text-slate-700 border border-slate-200">
                            Zona: {{ $pedido->zonaEnvio->nombre }}
                        </span>
                    </div>
                @endif
            </div>

            @if($pedido->notas_cliente)
            <div class="card-elevated rounded-xl p-6 bg-yellow-50 border-yellow-200">
                <h2 class="text-sm font-bold text-yellow-800 mb-2">Notas del Cliente</h2>
                <p class="text-sm text-yellow-700 italic">"{{ $pedido->notas_cliente }}"</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Modal Rechazar Pago -->
    <div id="modal-rechazar" class="fixed inset-0 z-[100] hidden overflow-y-auto font-sans" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('modal-rechazar').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('admin.pedidos.rechazar-pago', $pedido->id) }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <span class="material-symbols-outlined text-red-600">warning</span>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Rechazar Pago</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">¿Estás seguro que deseas rechazar el pago? Por favor, ingresa el motivo para que el cliente lo sepa.</p>
                                    <textarea name="comentario" rows="3" required class="mt-3 shadow-sm focus:ring-red-500 focus:border-red-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md font-sans" placeholder="Motivo del rechazo..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm font-sans">
                            Rechazar Pago
                        </button>
                        <button type="button" onclick="document.getElementById('modal-rechazar').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm font-sans">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
