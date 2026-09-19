@extends('layouts.admin')
@section('title', 'Gestión de Pedidos')

@section('content')
<div class="space-y-6 font-sans">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Gestión de Pedidos</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Administra todas las órdenes de la tienda.</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card-elevated rounded-xl p-4 sm:p-5">
        <form action="{{ route('admin.pedidos.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4">
            <div class="w-full sm:w-64">
                <label for="estado" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Filtrar por Estado</label>
                <select name="estado" id="estado" class="block w-full rounded-md border-slate-300 dark:border-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-white py-2 pl-3 pr-10 text-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500" onchange="this.form.submit()">
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
                    <option value="reembolsado" {{ request('estado') === 'reembolsado' ? 'selected' : '' }}>Reembolsado</option>
                </select>
            </div>
            <div class="w-full sm:w-64">
                <label for="q" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Buscar</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-symbols-outlined text-slate-400 text-lg">search</span>
                    </span>
                    <input type="text" name="q" id="q" value="{{ request('q') }}" placeholder="N° Pedido o Cliente" class="block w-full rounded-md border-slate-300 dark:border-gray-700 dark:border-gray-600 dark:bg-gray-800 dark:text-white py-2 pl-9 pr-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500">
                </div>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-slate-900 text-white rounded-md px-4 py-2 text-sm font-medium hover:bg-slate-800 transition-colors h-[38px]">
                    Buscar
                </button>
            </div>
            @if((request('estado') && request('estado') !== 'todos') || request('q'))
            <div class="flex items-end mb-1">
                <a href="{{ route('admin.pedidos.index') }}" class="inline-flex items-center text-sm text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:text-slate-300 dark:hover:text-slate-200">
                    <span class="material-symbols-outlined text-[18px] mr-1">close</span> Limpiar
                </a>
            </div>
            @endif
        </form>
    </div>

    <!-- Tabla -->
    <div class="card-elevated rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-700">
                <thead class="bg-slate-50 dark:bg-[#181a1b] border-b border-slate-200 dark:border-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">N° Pedido</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Cliente</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Fecha</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Estado Actual</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-transparent divide-y divide-slate-100 dark:divide-gray-700/50">
                    @forelse($pedidos as $pedido)
                    @php
                        $ultimoEstado = $pedido->ultimoEstado ? $pedido->ultimoEstado->estado : 'pendiente';
                        $esNuevo = in_array($ultimoEstado, ['pendiente', 'pago_confirmado']);
                    @endphp
                    <tr class="hover:bg-slate-50 dark:bg-transparent dark:hover:bg-gray-700/30 dark:bg-transparent dark:hover:bg-gray-700/50 transition-colors {{ $esNuevo ? 'bg-emerald-50/40 dark:bg-transparent border-l-4 border-l-emerald-500' : 'border-l-4 border-l-transparent' }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2.5">
                                <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $pedido->numero_pedido }}</span>
                                <x-badge-nuevo :condicion="$esNuevo" />
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                @if($pedido->usuario && $pedido->usuario->foto_perfil_ruta)
                                    <img src="{{ str_starts_with($pedido->usuario->foto_perfil_ruta, 'http') ? $pedido->usuario->foto_perfil_ruta : asset(ltrim($pedido->usuario->foto_perfil_ruta, '/')) }}" alt="{{ $pedido->usuario->nombre }}" class="h-8 w-8 rounded-full object-cover shrink-0 border border-slate-200 dark:border-gray-700">
                                @else
                                    <div class="h-8 w-8 rounded-full bg-slate-200 dark:bg-gray-700 flex items-center justify-center text-slate-600 dark:text-slate-400 dark:text-slate-300 font-bold text-xs uppercase shrink-0">
                                        {{ substr($pedido->usuario->nombre ?? 'U', 0, 1) }}
                                    </div>
                                @endif
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $pedido->usuario->nombre ?? 'Desconocido' }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $pedido->usuario->email ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600 dark:text-slate-400">
                            {{ $pedido->creado_en->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900 dark:text-white">
                            ${{ number_format($pedido->total, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $estadoClasses = [
                                    'pendiente' => 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-gray-700 dark:border-slate-700',
                                    'pago_confirmado' => 'bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-800',
                                    'pago_rechazado' => 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800',
                                    'en_preparacion' => 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-800',
                                    'listo_para_envio' => 'bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400 border-teal-200 dark:border-teal-800',
                                    'enviado' => 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400 border-indigo-200 dark:border-indigo-800',
                                    'entregado' => 'bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800',
                                    'cancelado' => 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800',
                                    'devolucion_solicitada' => 'bg-orange-50 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400 border-orange-200 dark:border-orange-800',
                                    'reembolsado' => 'bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-gray-100 dark:text-slate-300 border-slate-300 dark:border-gray-700 dark:border-slate-600',
                                ];
                                $ultimoEstado = $pedido->ultimoEstado ? $pedido->ultimoEstado->estado : 'pendiente';
                                $claseEstado = $estadoClasses[$ultimoEstado] ?? 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-gray-700 dark:border-slate-700';
                                $labelEstado = ucfirst(str_replace('_', ' ', $ultimoEstado));
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $claseEstado }}">
                                {{ $labelEstado }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.pedidos.detalle', $pedido->id) }}" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-900 dark:hover:text-emerald-300 bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 p-2 rounded-md transition-colors inline-flex items-center" title="Ver Detalle">
                                <span class="material-symbols-outlined text-[18px]">visibility</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500 dark:text-slate-400">
                            <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600 dark:text-slate-400 mb-2">inbox</span>
                            <p>No se encontraron pedidos con los filtros actuales.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($pedidos->total() > 0)
            <div class="px-6 py-4 border-t border-slate-200 dark:border-gray-700 bg-slate-50/50 dark:bg-transparent">
                {{ $pedidos->links('vendor.pagination.admin-tailwind') }}
            </div>
        @endif
    </div>
</div>
@endsection


