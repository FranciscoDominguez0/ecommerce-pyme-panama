{{-- resources/views/admin/auditoria/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Auditoría del Sistema - PayMe')

@section('content')
<div class="space-y-6 font-sans" x-data="auditoriaModal()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Auditoría del Sistema</h1>
            <p class="text-sm text-slate-500 mt-1">Monitorea y revisa las acciones realizadas en el sistema.</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card-elevated rounded-xl p-4 sm:p-5">
        <form action="{{ route('admin.auditoria.index') }}" method="GET" class="flex flex-col sm:flex-row gap-4">
            <div class="w-full sm:w-64">
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Usuario</label>
                <select name="usuario_id" class="block w-full rounded-md border-slate-300 py-2 pl-3 pr-10 text-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500" onchange="this.form.submit()">
                    <option value="">Todos los usuarios</option>
                    @foreach($usuarios as $user)
                        <option value="{{ $user->id }}" {{ request('usuario_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->nombre }} {{ $user->apellido }}
                            @if($user->hasRole(['super_admin', 'admin', 'Admin', 'Superadmin']))
                                (Admin)
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="w-full sm:w-48">
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Módulo</label>
                <select name="modulo" class="block w-full rounded-md border-slate-300 py-2 pl-3 pr-10 text-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500" onchange="this.form.submit()">
                    <option value="">Todos los módulos</option>
                    @foreach($modulos as $mod)
                        <option value="{{ $mod }}" {{ request('modulo') == $mod ? 'selected' : '' }}>{{ $mod }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full sm:w-48">
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Acción</label>
                <select name="accion" class="block w-full rounded-md border-slate-300 py-2 pl-3 pr-10 text-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500" onchange="this.form.submit()">
                    <option value="">Todas las acciones</option>
                    @foreach($acciones as $act)
                        <option value="{{ $act }}" {{ request('accion') == $act ? 'selected' : '' }}>{{ ucfirst($act) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full sm:w-48">
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Desde Fecha</label>
                <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}" class="block w-full rounded-md border-slate-300 py-2 px-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-emerald-500" onchange="this.form.submit()">
            </div>

            @if(request()->anyFilled(['usuario_id', 'modulo', 'accion', 'fecha_inicio']))
            <div class="flex items-end mb-1">
                <a href="{{ route('admin.auditoria.index') }}" class="inline-flex items-center text-sm text-slate-500 hover:text-slate-700">
                    <span class="material-symbols-outlined text-[18px] mr-1">close</span> Limpiar
                </a>
            </div>
            @endif
        </form>
    </div>

    <!-- Tabla -->
    <div class="card-elevated rounded-xl overflow-hidden">
        @if($logs->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha / Hora</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Usuario</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Módulo</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Acción</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Descripción</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    @foreach($logs as $log)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 font-medium">
                            {{ $log->creado_en->format('d M Y, H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                @if($log->usuario)
                                    <div class="h-8 w-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold text-xs uppercase shrink-0">
                                        {{ substr($log->usuario->nombre, 0, 1) }}{{ substr($log->usuario->apellido, 0, 1) }}
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-slate-900">
                                            {{ $log->usuario->nombre }}
                                            @if($log->usuario->hasRole(['super_admin', 'admin', 'Admin', 'Superadmin']))
                                                <span class="inline-flex items-center ml-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200">ADMIN</span>
                                            @endif
                                        </p>
                                    </div>
                                @else
                                    <div class="h-8 w-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 font-bold text-xs uppercase shrink-0">
                                        S
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-slate-500 italic">Sistema / Anónimo</p>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 font-medium">
                            {{ $log->modulo }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $color = match($log->accion) {
                                    'creado' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'actualizado' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'eliminado' => 'bg-red-50 text-red-700 border-red-200',
                                    default => 'bg-blue-50 text-blue-700 border-blue-200'
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $color }}">
                                {{ ucfirst($log->accion) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600 truncate max-w-[200px]" title="{{ $log->descripcion }}">
                            {{ $log->descripcion }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button @click="abrirDetalle({{ $log->id }})" class="text-primary hover:text-primary/80 bg-primary/10 hover:bg-primary/20 p-2 rounded-md transition-colors inline-flex items-center w-[34px] h-[34px] justify-center" title="Ver Detalle">
                                <span class="material-symbols-outlined text-[18px]">visibility</span>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50">
                {{ $logs->links('vendor.pagination.admin-tailwind') }}
            </div>
        @endif
        @else
        <div class="px-6 py-12 text-center text-slate-500">
            <span class="material-symbols-outlined text-4xl text-slate-300 mb-2">manage_search</span>
            <p>No se encontraron registros de auditoría.</p>
        </div>
        @endif
    </div>

    <!-- Modal Detalle de Auditoría -->
    <div x-show="modalOpen" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div x-show="modalOpen" x-transition.opacity class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-sm" @click="cerrarDetalle()"></div>

            <div x-show="modalOpen" x-transition.scale.origin.bottom class="relative inline-block w-full max-w-4xl p-6 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl">
                
                <!-- Skeleton Loader -->
                <div x-show="cargando" class="animate-pulse space-y-4">
                    <div class="h-6 bg-slate-200 rounded w-1/4 mb-6"></div>
                    <div class="grid grid-cols-2 gap-4"><div class="h-40 bg-slate-100 rounded"></div><div class="h-40 bg-slate-100 rounded"></div></div>
                </div>

                <!-- Contenido Modal -->
                <div x-show="!cargando && logDetalle">
                    <div class="flex justify-between items-start mb-5 pb-4 border-b border-slate-100">
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Detalle de Registro</h3>
                            <p class="text-sm text-slate-500 mt-1" x-text="logDetalle?.descripcion"></p>
                        </div>
                        <button @click="cerrarDetalle()" class="text-slate-400 hover:text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-md p-1.5 transition">
                            <span class="material-symbols-outlined text-[20px]">close</span>
                        </button>
                    </div>

                    <!-- Meta info -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 bg-slate-50 p-4 rounded-xl text-sm border border-slate-100">
                        <div><span class="block text-slate-500 text-[10px] uppercase font-bold tracking-wider mb-1">Módulo</span><span class="font-semibold text-slate-900" x-text="logDetalle?.modulo"></span></div>
                        <div><span class="block text-slate-500 text-[10px] uppercase font-bold tracking-wider mb-1">Acción</span><span class="font-semibold text-slate-900 capitalize" x-text="logDetalle?.accion"></span></div>
                        <div><span class="block text-slate-500 text-[10px] uppercase font-bold tracking-wider mb-1">IP</span><span class="font-mono text-slate-900" x-text="logDetalle?.ip || 'N/A'"></span></div>
                        <div><span class="block text-slate-500 text-[10px] uppercase font-bold tracking-wider mb-1">Navegador</span><span class="text-slate-700 truncate block" :title="logDetalle?.agente_usuario" x-text="logDetalle?.agente_usuario || 'N/A'"></span></div>
                    </div>

                    <!-- Comparación Diff -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Valor Anterior -->
                        <div class="border border-red-200 rounded-xl overflow-hidden flex flex-col bg-white">
                            <div class="bg-red-50 border-b border-red-100 px-4 py-2 text-[11px] font-bold text-red-800 uppercase tracking-wider flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px]">remove</span> Valor Anterior
                            </div>
                            <div class="p-4 overflow-x-auto flex-1">
                                <pre class="text-[12px] font-mono text-slate-700 whitespace-pre-wrap break-all" x-text="formatearJSON(logDetalle?.valor_anterior)"></pre>
                            </div>
                        </div>

                        <!-- Valor Nuevo -->
                        <div class="border border-emerald-200 rounded-xl overflow-hidden flex flex-col bg-white">
                            <div class="bg-emerald-50 border-b border-emerald-100 px-4 py-2 text-[11px] font-bold text-emerald-800 uppercase tracking-wider flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px]">add</span> Valor Nuevo
                            </div>
                            <div class="p-4 overflow-x-auto flex-1">
                                <pre class="text-[12px] font-mono text-slate-700 whitespace-pre-wrap break-all" x-text="formatearJSON(logDetalle?.valor_nuevo)"></pre>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('auditoriaModal', () => ({
            modalOpen: false,
            logDetalle: null,
            cargando: false,
            abrirDetalle(id) {
                this.cargando = true;
                this.modalOpen = true;
                fetch(`/admin/auditoria/${id}`)
                    .then(res => res.json())
                    .then(data => {
                        this.logDetalle = data;
                        this.cargando = false;
                    });
            },
            cerrarDetalle() {
                this.modalOpen = false;
                setTimeout(() => this.logDetalle = null, 300);
            },
            formatearJSON(obj) {
                if (!obj) return 'Sin datos (N/A)';
                return JSON.stringify(obj, null, 2);
            }
        }));
    });
</script>
@endsection
