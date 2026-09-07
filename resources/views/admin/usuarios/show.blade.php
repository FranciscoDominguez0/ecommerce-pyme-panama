@extends('layouts.admin')

@section('title', 'Perfil de Usuario: ' . $usuario->nombre_completo)

@section('content')
<div class="space-y-6 font-sans">
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-4">
        <a href="{{ route('admin.usuarios.index') }}" class="inline-flex items-center text-sm font-medium text-emerald-600 hover:text-emerald-700">
            <span class="material-symbols-outlined mr-1 text-[18px]">arrow_back</span>
            Volver a usuarios
        </a>
    </div>

    <div class="card-elevated rounded-xl p-6 flex flex-col md:flex-row md:items-start justify-between gap-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-3">
                Perfil de Usuario
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Visualizando detalles y actividad de {{ $usuario->nombre_completo }}
            </p>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.usuarios.edit', $usuario->id) }}" class="inline-flex items-center px-4 py-2 bg-slate-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-slate-900 transition">
                <span class="material-symbols-outlined text-[16px] mr-1.5">edit</span>
                Editar Usuario
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Columna Izquierda: Info Personal -->
        <div class="flex flex-col gap-6">
            <!-- Tarjeta de Perfil -->
            <div class="card-elevated rounded-xl p-6 flex flex-col items-center text-center">
                @if($usuario->foto_perfil_ruta)
                    <div class="h-24 w-24 rounded-full bg-slate-200 overflow-hidden border-4 border-white shadow-md mb-4">
                        <img alt="{{ $usuario->nombre }}" class="w-full h-full object-cover" src="{{ asset($usuario->foto_perfil_ruta) }}"/>
                    </div>
                @else
                    <div class="h-24 w-24 rounded-full bg-slate-100 overflow-hidden border-4 border-white shadow-md mb-4 flex items-center justify-center text-slate-400 font-bold text-3xl uppercase">
                        {{ $usuario->iniciales }}
                    </div>
                @endif
                
                <h3 class="text-xl font-bold text-slate-900">{{ $usuario->nombre_completo }}</h3>
                <p class="text-sm text-slate-500 mb-4">{{ $usuario->email }}</p>
                
                <div class="flex flex-wrap gap-2 justify-center mb-4">
                    @if(isset($usuario->activo) && !$usuario->activo)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 uppercase tracking-wide">
                            Inactivo
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 uppercase tracking-wide">
                            Activo
                        </span>
                    @endif
                    
                    @foreach($usuario->roles as $rol)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 uppercase tracking-wide">
                            {{ $rol->name }}
                        </span>
                    @endforeach
                </div>
                
                <div class="w-full pt-4 border-t border-slate-100 flex flex-col gap-3 text-left">
                    <div class="flex items-center gap-3 text-sm text-slate-600">
                        <span class="material-symbols-outlined text-slate-400 text-lg">calendar_today</span>
                        <span>Registrado el {{ $usuario->creado_en->format('d M Y') }}</span>
                    </div>
                    @if($usuario->telefono)
                    <div class="flex items-center gap-3 text-sm text-slate-600">
                        <span class="material-symbols-outlined text-slate-400 text-lg">call</span>
                        <span>{{ $usuario->telefono }}</span>
                    </div>
                    @endif
                    @if($usuario->google_id)
                    <div class="flex items-center gap-3 text-sm text-slate-600">
                        <span class="material-symbols-outlined text-slate-400 text-lg">account_circle</span>
                        <span>Cuenta vinculada con Google</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Tarjeta de Direcciones -->
            <div class="card-elevated rounded-xl p-6">
                <h3 class="text-lg font-bold text-slate-900 mb-4 border-b border-slate-100 pb-2 flex items-center gap-2">
                    <span class="material-symbols-outlined text-slate-700">location_on</span>
                    Direcciones Guardadas
                </h3>
                
                @if($usuario->direcciones->isEmpty())
                    <div class="p-6 text-center">
                        <span class="material-symbols-outlined text-slate-200 text-4xl mb-2">location_off</span>
                        <p class="text-sm text-slate-500 italic">No hay direcciones registradas.</p>
                    </div>
                @else
                    <div class="flex flex-col gap-4">
                        @foreach($usuario->direcciones as $direccion)
                            <div class="p-4 bg-slate-50 rounded-lg border border-slate-100 relative">
                                @if($direccion->es_predeterminada)
                                    <span class="absolute top-3 right-3 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-700 uppercase tracking-wide">
                                        Principal
                                    </span>
                                @endif
                                <p class="text-sm font-semibold text-slate-900 flex items-center gap-1.5 mb-1">
                                    <span class="material-symbols-outlined text-[16px] text-slate-400">home</span>
                                    {{ $direccion->alias ?? 'Dirección' }}
                                </p>
                                <address class="text-xs text-slate-500 not-italic space-y-0.5 mt-2">
                                    <p class="text-slate-700 font-medium mb-1">{{ $direccion->nombre_receptor }}</p>
                                    <p class="leading-relaxed">
                                        {{ collect([$direccion->direccion_exacta, $direccion->corregimiento, $direccion->distrito, $direccion->provincia])->filter()->map(fn($item) => trim($item))->unique()->implode(', ') }}
                                    </p>
                                    @if($direccion->referencia)
                                        <p class="text-slate-400 italic mt-1 font-medium">Ref: {{ $direccion->referencia }}</p>
                                    @endif
                                </address>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Columna Derecha: Estadísticas y Tablas -->
        <div class="lg:col-span-2 flex flex-col gap-6">
            <!-- Stats Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="card-elevated rounded-xl p-6 flex flex-col justify-center">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Total Pedidos</span>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
                            <span class="material-symbols-outlined text-[24px]">shopping_bag</span>
                        </div>
                        <span class="font-extrabold text-3xl text-slate-900">{{ $totalPedidos }}</span>
                    </div>
                </div>
                
                <div class="card-elevated rounded-xl p-6 flex flex-col justify-center">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Total Gastado</span>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
                            <span class="material-symbols-outlined text-[24px]">payments</span>
                        </div>
                        <span class="font-extrabold text-3xl text-slate-900">${{ number_format($totalGastado, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Pedidos Recientes -->
            <div class="card-elevated rounded-xl overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <span class="material-symbols-outlined text-slate-700">receipt_long</span>
                        Pedidos Recientes
                    </h2>
                    @if($usuario->pedidos->count() > 0)
                        <a href="{{ route('admin.pedidos.index', ['usuario_id' => $usuario->id]) }}" class="inline-flex items-center text-xs font-semibold text-emerald-600 hover:text-emerald-700">
                            Ver todos <span class="material-symbols-outlined text-[16px] ml-0.5">chevron_right</span>
                        </a>
                    @endif
                </div>
                
                @if($usuario->pedidos->isEmpty())
                    <div class="p-12 text-center flex flex-col items-center">
                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-slate-300 text-3xl">receipt_long</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 mb-1">Sin historial de pedidos</h3>
                        <p class="text-sm text-slate-500 max-w-sm">Este usuario aún no ha realizado ninguna compra en la tienda.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-white border-b border-slate-200">
                                    <th class="py-4 px-6 text-[10px] font-bold text-slate-500 uppercase tracking-wider">ID / Fecha</th>
                                    <th class="py-4 px-6 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Estado</th>
                                    <th class="py-4 px-6 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total</th>
                                    <th class="py-4 px-6 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach($usuario->pedidos as $pedido)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="py-4 px-6">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-bold text-slate-900">#{{ str_pad($pedido->numero_pedido ?? $pedido->id, 5, '0', STR_PAD_LEFT) }}</span>
                                                <span class="text-xs text-slate-500 mt-0.5">{{ $pedido->creado_en->format('d/m/Y H:i') }}</span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6">
                                            @php
                                                $estadoStr = $pedido->ultimoEstado->estado ?? 'pendiente';
                                                $estadoClasses = match(strtolower($estadoStr)) {
                                                    'completado', 'entregado' => 'bg-emerald-100 text-emerald-700',
                                                    'pendiente' => 'bg-amber-100 text-amber-700',
                                                    'procesando', 'en_preparacion' => 'bg-blue-100 text-blue-700',
                                                    'cancelado', 'rechazado', 'problema_entrega' => 'bg-red-100 text-red-700',
                                                    'enviado', 'en_transito' => 'bg-indigo-100 text-indigo-700',
                                                    'listo_para_envio' => 'bg-teal-100 text-teal-700',
                                                    'pago_confirmado' => 'bg-blue-100 text-blue-700',
                                                    'reembolsado' => 'bg-slate-100 text-slate-800 border-slate-300 border',
                                                    default => 'bg-slate-100 text-slate-700'
                                                };
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide {{ $estadoClasses }}">
                                                {{ str_replace('_', ' ', $estadoStr) }}
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-sm font-bold text-slate-900">${{ number_format($pedido->total, 2) }}</td>
                                        <td class="py-4 px-6 text-right">
                                            <a href="{{ route('admin.pedidos.detalle', $pedido->id) }}" class="inline-flex items-center px-3 py-1.5 bg-white border border-slate-200 rounded-md text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm">
                                                Ver detalle
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
