@extends('layouts.admin')
@section('title', 'Gestión de Facturas')

@section('content')
<div class="space-y-6 font-sans">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Gestión de Facturas</h1>
            <p class="text-sm text-slate-500 mt-1">Administra, descarga y reenvía las facturas generadas por el sistema.</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card-elevated rounded-xl p-4 sm:p-5">
        <form action="{{ route('admin.facturas.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4">
            <div class="w-full sm:w-64">
                <label for="numero" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Buscar por N°</label>
                <input type="text" name="numero" id="numero" value="{{ request('numero') }}" placeholder="Ej: FAC-000001" class="block w-full rounded-md border-slate-300 py-2 px-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500" onchange="this.form.submit()">
            </div>
            
            <div class="w-full sm:w-64">
                <label for="cliente" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Buscar por Cliente</label>
                <input type="text" name="cliente" id="cliente" value="{{ request('cliente') }}" placeholder="Nombre o email" class="block w-full rounded-md border-slate-300 py-2 px-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500" onchange="this.form.submit()">
            </div>

            <div class="w-full sm:w-64">
                <label for="estado" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Filtrar por Estado</label>
                <select name="estado" id="estado" class="block w-full rounded-md border-slate-300 py-2 pl-3 pr-10 text-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500" onchange="this.form.submit()">
                    <option value="" {{ request('estado') === null ? 'selected' : '' }}>Todas las facturas</option>
                    <option value="emitida" {{ request('estado') === 'emitida' ? 'selected' : '' }}>Emitida</option>
                    <option value="anulada" {{ request('estado') === 'anulada' ? 'selected' : '' }}>Anulada</option>
                </select>
            </div>

            <div class="w-full sm:w-48">
                <label for="emitida_desde" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Emitida desde</label>
                <input type="date" name="emitida_desde" id="emitida_desde" value="{{ request('emitida_desde') }}" class="block w-full rounded-md border-slate-300 py-2 px-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500" onchange="this.form.submit()">
            </div>

            <div class="w-full sm:w-48">
                <label for="emitida_hasta" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Emitida hasta</label>
                <input type="date" name="emitida_hasta" id="emitida_hasta" value="{{ request('emitida_hasta') }}" class="block w-full rounded-md border-slate-300 py-2 px-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500" onchange="this.form.submit()">
            </div>

            @if(request()->anyFilled(['estado', 'numero', 'cliente', 'emitida_desde', 'emitida_hasta']))
            <div class="flex items-end mb-1">
                <a href="{{ route('admin.facturas.index') }}" class="inline-flex items-center text-sm text-slate-500 hover:text-slate-700">
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
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Factura</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Cliente</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Total</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha de Emisión</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    @forelse($facturas as $factura)
                    <tr class="hover:bg-slate-50 transition-colors {{ $factura->estado === 'anulada' ? 'opacity-75' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-bold {{ $factura->estado === 'anulada' ? 'text-slate-500 line-through' : 'text-slate-900' }}">{{ $factura->numero }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold text-xs uppercase shrink-0">
                                    {{ substr($factura->usuario->nombre ?? 'U', 0, 1) }}
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium {{ $factura->estado === 'anulada' ? 'text-slate-500' : 'text-slate-900' }}">{{ $factura->usuario->nombre ?? 'Desconocido' }} {{ $factura->usuario->apellido }}</p>
                                    <p class="text-xs text-slate-500">{{ $factura->usuario->email ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $factura->estado === 'anulada' ? 'text-slate-500 line-through' : 'text-slate-900' }}">
                            ${{ number_format($factura->total, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                            {{ $factura->emitida_en->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($factura->estado === 'emitida')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border bg-emerald-50 text-emerald-700 border-emerald-200">
                                    Emitida
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border bg-slate-100 text-slate-700 border-slate-200">
                                    Anulada
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.facturas.pdf', $factura) }}" class="text-primary hover:text-primary/80 bg-primary/10 hover:bg-primary/20 p-2 rounded-md transition-colors inline-flex items-center" title="Descargar PDF">
                                    <span class="material-symbols-outlined text-[18px]">download</span>
                                </a>
                                <a href="{{ route('admin.facturas.show', $factura) }}" class="text-primary hover:text-primary/80 bg-primary/10 hover:bg-primary/20 p-2 rounded-md transition-colors inline-flex items-center" title="Ver Detalle">
                                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                                </a>
                                @if($factura->estado === 'emitida')
                                <form action="{{ route('admin.facturas.reenviar', $factura) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="email_destino" value="{{ $factura->usuario->email }}">
                                    <button type="submit" class="text-primary hover:text-primary/80 bg-primary/10 hover:bg-primary/20 p-2 rounded-md transition-colors inline-flex items-center relative" title="Reenviar">
                                        <span class="material-symbols-outlined text-[18px]">mail</span>
                                        @if($factura->reenvios()->count() > 0)
                                        <span class="absolute -top-1 -right-1 flex h-3 w-3">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-secondary opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-3 w-3 bg-secondary"></span>
                                        </span>
                                        @endif
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                            <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">receipt_long</span>
                            <p>No se encontraron facturas registradas.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($facturas->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50">
                {{ $facturas->links('vendor.pagination.admin-tailwind') }}
            </div>
        @endif
    </div>
</div>
@endsection