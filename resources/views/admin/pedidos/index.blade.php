@extends('layouts.admin')
@section('title', 'Gestión de Pedidos')

@section('content')
<div class="space-y-6 font-sans">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Gestión de Pedidos</h1>
            <p class="text-sm text-slate-500 mt-1">Administra todas las órdenes de la tienda.</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card-elevated rounded-xl p-4 sm:p-5">
        <form action="{{ route('admin.pedidos.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4">
            <div class="w-full sm:w-64">
                <label for="estado" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Filtrar por Estado</label>
                <select name="estado" id="estado" class="block w-full rounded-md border-slate-300 py-2 pl-3 pr-10 text-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500" onchange="this.form.submit()">
                    <option value="todos" {{ request('estado') === 'todos' ? 'selected' : '' }}>Todos los pedidos</option>
                    <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="pago_confirmado" {{ request('estado') === 'pago_confirmado' ? 'selected' : '' }}>Pago Confirmado</option>
                    <option value="pago_rechazado" {{ request('estado') === 'pago_rechazado' ? 'selected' : '' }}>Pago Rechazado</option>
                    <option value="en_preparacion" {{ request('estado') === 'en_preparacion' ? 'selected' : '' }}>En Preparación</option>
                    <option value="listo_para_envio" {{ request('estado') === 'listo_para_envio' ? 'selected' : '' }}>Listo para Envío</option>
                    <option value="enviado" {{ request('estado') === 'enviado' ? 'selected' : '' }}>Enviado</option>
                    <option value="entregado" {{ request('estado') === 'entregado' ? 'selected' : '' }}>Entregado</option>
                    <option value="cancelado" {{ request('estado') === 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                    <option value="devolucion_solicitada" {{ request('estado') === 'devolucion_solicitada' ? 'selected' : '' }}>Devolución Solicitada</option>
                </select>
            </div>
            <div class="w-full sm:w-64">
                <label for="q" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Buscar</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-outlined text-slate-400 text-lg">search</span>
                    </span>
                    <input type="text" name="q" id="q" value="{{ request('q') }}" placeholder="N° Pedido o Cliente" class="block w-full rounded-md border-slate-300 py-2 pl-9 pr-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500">
                </div>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-slate-900 text-white rounded-md px-4 py-2 text-sm font-medium hover:bg-slate-800 transition-colors h-[38px]">
                    Buscar
                </button>
            </div>
            @if((request('estado') && request('estado') !== 'todos') || request('q'))
            <div class="flex items-end mb-1">
                <a href="{{ route('admin.pedidos.index') }}" class="inline-flex items-center text-sm text-slate-500 hover:text-slate-700">
                    <span class="material-symbols-outlined text-[18px] mr-1">close</span> Limpiar
                </a>
            </div>
            @endif
        </form>
    </div>

    <!-- Tabla -->
    <div class="card-elevated rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">N° Pedido</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Cliente</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Total</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado Actual</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    @forelse($pedidos as $pedido)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-bold text-slate-900">{{ $pedido->numero_pedido }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold text-xs uppercase shrink-0">
                                    {{ substr($pedido->usuario->nombre ?? 'U', 0, 1) }}
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-slate-900">{{ $pedido->usuario->nombre ?? 'Desconocido' }}</p>
                                    <p class="text-xs text-slate-500">{{ $pedido->usuario->email ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                            {{ $pedido->creado_en->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
                            ${{ number_format($pedido->total, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $estadoClasses = [
                                    'pendiente' => 'bg-slate-100 text-slate-700 border-slate-200',
                                    'pago_confirmado' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'pago_rechazado' => 'bg-red-50 text-red-700 border-red-200',
                                    'en_preparacion' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'listo_para_envio' => 'bg-teal-50 text-teal-700 border-teal-200',
                                    'enviado' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                    'entregado' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'cancelado' => 'bg-red-50 text-red-700 border-red-200',
                                    'devolucion_solicitada' => 'bg-orange-50 text-orange-700 border-orange-200',
                                ];
                                $ultimoEstado = $pedido->ultimoEstado ? $pedido->ultimoEstado->estado : 'pendiente';
                                $claseEstado = $estadoClasses[$ultimoEstado] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                                $labelEstado = ucfirst(str_replace('_', ' ', $ultimoEstado));
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $claseEstado }}">
                                {{ $labelEstado }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.pedidos.detalle', $pedido->id) }}" class="text-emerald-600 hover:text-emerald-900 bg-emerald-50 hover:bg-emerald-100 p-2 rounded-md transition-colors inline-flex items-center" title="Ver Detalle">
                                <span class="material-symbols-outlined text-[18px]">visibility</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                            <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">inbox</span>
                            <p>No se encontraron pedidos con los filtros actuales.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($pedidos->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50">
                {{ $pedidos->links('vendor.pagination.admin-tailwind') }}
            </div>
        @endif
    </div>
</div>
@endsection
