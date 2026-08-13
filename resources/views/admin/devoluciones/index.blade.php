@extends('layouts.admin')
@section('title', 'Gestión de Devoluciones')

@section('content')
<div x-data="{ 
        modalAbierto: false, 
        devolucionActiva: null,
        
        abrirModal(devolucion) {
            this.devolucionActiva = devolucion;
            this.modalAbierto = true;
            document.body.style.overflow = 'hidden';
        },
        cerrarModal() {
            this.modalAbierto = false;
            setTimeout(() => { this.devolucionActiva = null; }, 300);
            document.body.style.overflow = '';
        }
    }" class="space-y-6 font-sans">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Gestión de Devoluciones</h1>
            <p class="text-sm text-slate-500 mt-1">Administra y procesa las solicitudes de devolución de clientes.</p>
        </div>
    </div>

    <!-- Filters & Actions -->
    <div class="card-elevated rounded-xl p-4 flex flex-col md:flex-row gap-4 items-center justify-between">
        <div class="flex flex-wrap gap-2 w-full md:w-auto">
            <a href="{{ route('admin.devoluciones.index') }}" wire:navigate class="px-4 py-2 {{ request('estado') ? 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50' : 'bg-slate-100 text-slate-900 font-bold' }} rounded-lg text-xs tracking-wider uppercase transition-colors">Todos</a>
            <a href="{{ route('admin.devoluciones.index', ['estado' => 'pendiente']) }}" wire:navigate class="px-4 py-2 {{ request('estado') === 'pendiente' ? 'bg-slate-100 text-slate-900 font-bold' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50' }} rounded-lg text-xs tracking-wider uppercase transition-colors">Pendientes</a>
            <a href="{{ route('admin.devoluciones.index', ['estado' => 'aprobada']) }}" wire:navigate class="px-4 py-2 {{ request('estado') === 'aprobada' ? 'bg-slate-100 text-slate-900 font-bold' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50' }} rounded-lg text-xs tracking-wider uppercase transition-colors">Aprobadas</a>
            <a href="{{ route('admin.devoluciones.index', ['estado' => 'rechazada']) }}" wire:navigate class="px-4 py-2 {{ request('estado') === 'rechazada' ? 'bg-slate-100 text-slate-900 font-bold' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50' }} rounded-lg text-xs tracking-wider uppercase transition-colors">Rechazadas</a>
        </div>
        
        <form action="{{ route('admin.devoluciones.index') }}" method="GET" class="w-full md:w-auto">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">search</span>
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por pedido o cliente..." class="w-full md:w-64 pl-10 pr-4 py-2 bg-white border border-slate-200 rounded-lg text-sm focus:outline-none focus:border-slate-400 focus:ring-1 focus:ring-slate-400 transition-all">
                @if(request('estado'))
                    <input type="hidden" name="estado" value="{{ request('estado') }}">
                @endif
            </div>
        </form>
    </div>

    @if($devoluciones->isEmpty())
        <!-- Empty State (Screen 4) -->
        <div class="bg-white rounded-xl border border-slate-200 p-12 flex flex-col items-center justify-center text-center">
            <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-6">
                <span class="material-symbols-outlined text-4xl text-slate-300">assignment_return</span>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-2">No hay devoluciones</h3>
            <p class="text-slate-500 max-w-md mx-auto">No se encontraron solicitudes de devolución con los filtros actuales. Todas las solicitudes de clientes aparecerán aquí.</p>
            @if(request('buscar') || request('estado'))
                <a href="{{ route('admin.devoluciones.index') }}" wire:navigate class="mt-6 inline-flex items-center gap-2 px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-colors text-sm font-medium">
                    Limpiar Filtros
                </a>
            @endif
        </div>
    @else
        <!-- Table (Screen 2) -->
        <div class="card-elevated rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">ID Devolución</th>
                            <th class="px-6 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Pedido</th>
                            <th class="px-6 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Cliente</th>
                            <th class="px-6 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Motivo</th>
                            <th class="px-6 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($devoluciones as $dev)
                        <tr class="hover:bg-slate-50/50 transition-colors cursor-pointer group" 
                            @click="abrirModal({
                                id: '{{ $dev->id }}',
                                pedido_id: '{{ $dev->pedido->id }}',
                                numero_pedido: '{{ $dev->pedido->numero_pedido }}',
                                estado: '{{ $dev->estado }}',
                                motivo: '{{ ucfirst($dev->motivo) }}',
                                descripcion: '{{ addslashes(str_replace(PHP_EOL, " ", $dev->descripcion)) }}',
                                foto_url: '{{ $dev->foto_evidencia_ruta ? asset('storage/'.$dev->foto_evidencia_ruta) : '' }}',
                                cliente_nombre: '{{ addslashes($dev->usuario->nombre . ' ' . $dev->usuario->apellido) }}',
                                cliente_email: '{{ $dev->usuario->email }}',
                                cliente_tel: '{{ $dev->usuario->telefono ?? "No provisto" }}',
                                admin_comentario: '{{ addslashes(str_replace(PHP_EOL, " ", $dev->comentario_admin ?? "")) }}',
                                items: [
                                    @foreach($dev->pedido->items as $item)
                                    {
                                        nombre: '{{ addslashes($item->producto->nombre) }}',
                                        sku: '{{ $item->variante->sku ?? $item->producto->sku }}',
                                        precio: '{{ number_format($item->precio_unitario, 2) }}',
                                        cantidad: '{{ $item->cantidad }}',
                                        imagen: '{{ $item->variante?->imagen_ruta ? asset('storage/'.$item->variante->imagen_ruta) : ($item->producto->imagenes->first() ? $item->producto->imagen_url : '') }}'
                                    }@if(!$loop->last),@endif
                                    @endforeach
                                ]
                            })">
                            <td class="px-6 py-4 font-mono text-sm font-bold text-slate-900">DEV-{{ str_pad($dev->id, 4, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-6 py-4 text-sm text-slate-500">{{ $dev->pedido->numero_pedido }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-slate-900">{{ $dev->usuario->nombre }} {{ $dev->usuario->apellido }}</td>
                            <td class="px-6 py-4 text-sm text-slate-500">{{ ucfirst($dev->motivo) }}</td>
                            <td class="px-6 py-4 text-sm text-slate-500">{{ $dev->creado_en->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">
                                @if($dev->estado === 'pendiente')
                                    <span class="inline-block px-2.5 py-1 bg-amber-50 text-amber-700 rounded-full text-[10px] font-bold uppercase tracking-wider">Pendiente</span>
                                @elseif($dev->estado === 'aprobada')
                                    <span class="inline-block px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-full text-[10px] font-bold uppercase tracking-wider">Aprobada</span>
                                @else
                                    <span class="inline-block px-2.5 py-1 bg-red-50 text-red-700 rounded-full text-[10px] font-bold uppercase tracking-wider">Rechazada</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button class="text-slate-500 hover:text-slate-900 transition-colors text-[11px] font-bold uppercase tracking-wider group-hover:text-emerald-600">Ver Detalles</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($devoluciones->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $devoluciones->links() }}
                </div>
            @endif
        </div>
    @endif

    <!-- Side Modal (Detalles) -->
    <div x-show="modalAbierto" style="display: none;" class="fixed inset-0 z-50 flex justify-end" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
        
        <!-- Background backdrop -->
        <div x-show="modalAbierto" 
             x-transition:enter="ease-in-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in-out duration-300" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" 
             @click="cerrarModal()"></div>

        <!-- Slide-over panel -->
        <div x-show="modalAbierto" 
             x-transition:enter="transform transition ease-in-out duration-300 sm:duration-500" 
             x-transition:enter-start="translate-x-full" 
             x-transition:enter-end="translate-x-0" 
             x-transition:leave="transform transition ease-in-out duration-300 sm:duration-500" 
             x-transition:leave-start="translate-x-0" 
             x-transition:leave-end="translate-x-full" 
             class="pointer-events-auto w-full md:max-w-lg flex flex-col bg-white h-full shadow-2xl relative">
            
            <template x-if="devolucionActiva">
                <div class="flex flex-col h-full">
                    <!-- Header -->
                    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                        <div>
                            <h3 class="text-xl font-bold text-slate-900" x-text="'Devolución #DEV-' + String(devolucionActiva.id).padStart(4, '0')"></h3>
                            <p class="text-xs text-slate-500 font-bold uppercase tracking-wider mt-1">
                                Pedido asociado: <a :href="'{{ url('admin/pedidos') }}/' + devolucionActiva.pedido_id" class="text-emerald-600 hover:underline" x-text="devolucionActiva.numero_pedido"></a>
                            </p>
                        </div>
                        <button @click="cerrarModal()" class="text-slate-400 hover:text-slate-700 transition-colors p-2 rounded-full hover:bg-slate-200 focus:outline-none">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="p-6 overflow-y-auto flex-1 bg-white">
                        
                        <!-- Info Cliente -->
                        <div class="mb-6">
                            <h4 class="text-[11px] font-bold text-slate-500 mb-2 uppercase tracking-wider">Información del Cliente</h4>
                            <div class="bg-slate-50 p-4 rounded-lg border border-slate-100">
                                <p class="font-bold text-sm text-slate-900" x-text="devolucionActiva.cliente_nombre"></p>
                                <p class="text-xs text-slate-500 mt-1" x-text="devolucionActiva.cliente_email"></p>
                                <p class="text-xs text-slate-500 mt-1" x-text="devolucionActiva.cliente_tel"></p>
                            </div>
                        </div>

                        <!-- Productos Involucrados (Asumimos pedido completo por limitación actual) -->
                        <div class="mb-6">
                            <h4 class="text-[11px] font-bold text-slate-500 mb-2 uppercase tracking-wider">Productos del Pedido (Afectados)</h4>
                            <div class="flex flex-col gap-3">
                                <template x-for="item in devolucionActiva.items">
                                    <div class="flex items-center gap-4 bg-slate-50 p-3 rounded-lg border border-slate-100">
                                        <div class="w-12 h-12 rounded bg-white border border-slate-200 overflow-hidden flex items-center justify-center shrink-0">
                                            <template x-if="item.imagen">
                                                <img :src="item.imagen" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!item.imagen">
                                                <span class="material-symbols-outlined text-slate-300 text-sm">image</span>
                                            </template>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-bold text-sm text-slate-900 truncate" x-text="item.nombre"></p>
                                            <p class="text-[11px] text-slate-500 font-mono" x-text="'SKU: ' + item.sku"></p>
                                            <p class="font-mono text-sm font-bold text-slate-900 mt-0.5" x-text="'$' + item.precio + ' (x' + item.cantidad + ')'"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Motivo y Descripción -->
                        <div class="mb-6">
                            <h4 class="text-[11px] font-bold text-slate-500 mb-2 uppercase tracking-wider">Motivo y Descripción</h4>
                            <div class="bg-slate-50 p-4 rounded-lg border border-slate-100">
                                <span class="inline-block px-2.5 py-1 bg-slate-200 text-slate-700 rounded-md text-[10px] font-bold uppercase tracking-wider mb-3" x-text="devolucionActiva.motivo"></span>
                                <p class="text-sm text-slate-700 italic border-l-2 border-slate-300 pl-3" x-text="'&quot;' + devolucionActiva.descripcion + '&quot;'"></p>
                            </div>
                        </div>

                        <!-- Evidencia -->
                        <template x-if="devolucionActiva.foto_url">
                            <div class="mb-6">
                                <h4 class="text-[11px] font-bold text-slate-500 mb-2 uppercase tracking-wider">Evidencia Fotográfica</h4>
                                <a :href="devolucionActiva.foto_url" target="_blank" class="block w-full h-48 rounded-lg border border-slate-200 overflow-hidden hover:opacity-90 transition-opacity">
                                    <img :src="devolucionActiva.foto_url" class="w-full h-full object-contain bg-slate-50">
                                </a>
                            </div>
                        </template>

                        <!-- Resolución Admin -->
                        <div class="mb-6">
                            <h4 class="text-[11px] font-bold text-slate-500 mb-2 uppercase tracking-wider">Resolución Administrativa</h4>
                            
                            <template x-if="devolucionActiva.estado === 'pendiente'">
                                <div>
                                    <label class="block text-xs text-slate-500 mb-2">Comentario del administrador (Obligatorio para rechazar)</label>
                                    <textarea id="admin-comment-input" class="w-full p-3 bg-white border border-slate-300 rounded-lg focus:ring-1 focus:ring-slate-900 focus:border-slate-900 text-sm text-slate-900 resize-none" placeholder="Ingrese detalles de la resolución..." rows="3"></textarea>
                                </div>
                            </template>

                            <template x-if="devolucionActiva.estado !== 'pendiente'">
                                <div class="bg-slate-50 p-4 rounded-lg border border-slate-100">
                                    <p class="text-sm text-slate-700" x-text="devolucionActiva.admin_comentario || 'Sin comentarios.'"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <template x-if="devolucionActiva.estado === 'pendiente'">
                        <div class="p-4 border-t border-slate-100 bg-slate-50 flex gap-3">
                            <form :action="'{{ url('admin/devoluciones') }}/' + devolucionActiva.id + '/rechazar'" method="POST" class="flex-1" onsubmit="this.querySelector('input[name=comentario_admin]').value = document.getElementById('admin-comment-input').value">
                                @csrf
                                <input type="hidden" name="comentario_admin" value="">
                                <button type="submit" class="w-full py-2.5 px-4 bg-red-100 hover:bg-red-200 text-red-800 text-[11px] font-bold uppercase tracking-wider rounded-lg transition-colors text-center border border-red-200">
                                    Rechazar Devolución
                                </button>
                            </form>
                            
                            <form :action="'{{ url('admin/devoluciones') }}/' + devolucionActiva.id + '/aprobar'" method="POST" class="flex-1" onsubmit="this.querySelector('input[name=comentario_admin]').value = document.getElementById('admin-comment-input').value">
                                @csrf
                                <input type="hidden" name="comentario_admin" value="">
                                <button type="submit" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold uppercase tracking-wider rounded-lg transition-colors text-center shadow-sm">
                                    Aprobar Devolución
                                </button>
                            </form>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>

</div>
@endsection
