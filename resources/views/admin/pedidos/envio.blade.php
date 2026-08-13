@extends('layouts.admin')
@section('title', 'Gestión de Envío - Pedido ' . $pedido->numero_pedido)

@section('content')
<div class="space-y-6 font-sans">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
        <a href="{{ route('admin.pedidos.detalle', $pedido->id) }}" class="inline-flex items-center text-sm font-medium text-emerald-600 hover:text-emerald-700">
            <span class="material-symbols-outlined mr-1 text-[18px]">arrow_back</span>
            Volver al Pedido
        </a>
    </div>

    <div class="flex items-center gap-4 mb-6">
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Pedido {{ $pedido->numero_pedido }}</h1>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 uppercase tracking-wider">
            <span class="material-symbols-outlined text-[16px]">package</span>
            @if($pedido->envio)
                Actualizar Envío
            @else
                Nuevo Envío
            @endif
        </span>
    </div>

    <!-- Visual Progress Tracker -->
    @php
        $estadoActual = $pedido->ultimoEstado?->estado ?? 'pendiente';
        $pasoActual = 1;
        if ($estadoActual === 'enviado') $pasoActual = 2;
        if ($estadoActual === 'en_transito') $pasoActual = 3;
        if ($estadoActual === 'entregado') $pasoActual = 4;
        
        $esProblema = ($estadoActual === 'problema_entrega');
    @endphp

    <div class="card-elevated rounded-xl p-6 mb-6">
        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-6">Estado de la Entrega</h3>
        
        @if($esProblema)
            <div class="flex items-center gap-3 p-4 bg-red-50 text-red-700 rounded-lg border border-red-100">
                <span class="material-symbols-outlined text-red-500 text-3xl">error</span>
                <div>
                    <p class="font-bold">Problema de Entrega Reportado</p>
                    <p class="text-sm opacity-90 mt-0.5">La entrega se ha pausado debido a un inconveniente. Revisa los detalles y actualiza el estado cuando se resuelva.</p>
                </div>
            </div>
        @else
            <div class="flex flex-col md:flex-row justify-between items-center relative">
                <!-- Línea de conexión de fondo -->
                <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-slate-100 hidden md:block z-0 rounded-full"></div>
                <!-- Línea de progreso (verde) -->
                <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-emerald-500 hidden md:block z-0 transition-all duration-500 rounded-full" 
                     style="width: {{ ($pasoActual - 1) * 33.33 }}%;"></div>

                @php
                    $pasos = [
                        1 => ['label' => 'Preparando', 'icon' => 'inventory_2'],
                        2 => ['label' => 'Enviado', 'icon' => 'outbox'],
                        3 => ['label' => 'En Tránsito', 'icon' => 'local_shipping'],
                        4 => ['label' => 'Entregado', 'icon' => 'task_alt'],
                    ];
                @endphp

                @foreach($pasos as $num => $paso)
                    <div class="relative z-10 flex flex-col items-center gap-2 {{ $num > $pasoActual ? 'opacity-50 grayscale' : '' }}">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center border-4 border-white shadow-sm {{ $num <= $pasoActual ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-500' }} transition-colors duration-300">
                            <span class="material-symbols-outlined text-[20px]">{{ $paso['icon'] }}</span>
                        </div>
                        <span class="text-xs font-bold uppercase tracking-wider {{ $num <= $pasoActual ? 'text-emerald-700' : 'text-slate-500' }}">
                            {{ $paso['label'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="mt-8 pt-6 border-t border-slate-100 flex flex-wrap gap-3 justify-center">
            @if($estadoActual !== 'entregado')
                @if($pasoActual < 2)
                <form action="{{ route('admin.pedidos.envio.estado', $pedido->id) }}" method="POST">
                    @csrf <input type="hidden" name="nuevo_estado" value="enviado">
                    <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-[11px] font-bold uppercase tracking-wider hover:bg-slate-700 transition-colors">Marcar como Enviado</button>
                </form>
                @endif
                
                @if($pasoActual >= 2 && $pasoActual < 3)
                <form action="{{ route('admin.pedidos.envio.estado', $pedido->id) }}" method="POST">
                    @csrf <input type="hidden" name="nuevo_estado" value="en_transito">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-[11px] font-bold uppercase tracking-wider hover:bg-blue-700 transition-colors">Marcar En Tránsito</button>
                </form>
                @endif
                
                @if($pasoActual >= 2)
                <form action="{{ route('admin.pedidos.envio.estado', $pedido->id) }}" method="POST">
                    @csrf <input type="hidden" name="nuevo_estado" value="entregado">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-[11px] font-bold uppercase tracking-wider hover:bg-emerald-700 transition-colors">Marcar como Entregado</button>
                </form>
                @endif

                <form action="{{ route('admin.pedidos.envio.estado', $pedido->id) }}" method="POST">
                    @csrf <input type="hidden" name="nuevo_estado" value="problema_entrega">
                    <button type="submit" class="px-4 py-2 bg-white border border-red-200 text-red-600 rounded-lg text-[11px] font-bold uppercase tracking-wider hover:bg-red-50 transition-colors">Reportar Problema</button>
                </form>
            @else
                <span class="px-4 py-2 bg-emerald-50 text-emerald-700 rounded-lg text-[11px] font-bold uppercase tracking-wider border border-emerald-100">
                    <span class="material-symbols-outlined text-[14px] align-middle mr-1">check_circle</span>
                    Proceso de entrega completado exitosamente
                </span>
            @endif
        </div>
    </div>

    <!-- Layout Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Columna Izquierda: Info Cliente y Productos -->
        <div class="lg:col-span-2 flex flex-col gap-6">
            
            <!-- Cliente -->
            <div class="card-elevated rounded-xl p-6">
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3 mb-4">
                    <span class="material-symbols-outlined text-slate-500">person</span>
                    Información del Cliente
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Nombre Completo</span>
                        <span class="text-sm font-medium text-slate-900">{{ $pedido->usuario->nombre }} {{ $pedido->usuario->apellido }}</span>
                    </div>
                    <div>
                        <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Contacto</span>
                        <span class="text-sm text-slate-900 block">{{ $pedido->usuario->telefono ?? 'N/A' }}</span>
                        <span class="text-xs text-slate-500">{{ $pedido->usuario->email }}</span>
                    </div>
                    <div class="md:col-span-2">
                        <span class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Dirección de Entrega</span>
                        <p class="text-sm text-slate-800">
                            {{ $pedido->direccion->direccion_linea1 }}<br>
                            @if($pedido->direccion->direccion_linea2)
                                {{ $pedido->direccion->direccion_linea2 }}<br>
                            @endif
                            {{ $pedido->direccion->corregimiento }}, {{ $pedido->direccion->distrito }}, {{ $pedido->direccion->provincia }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Productos -->
            <div class="card-elevated rounded-xl overflow-hidden">
                <div class="p-6 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <span class="material-symbols-outlined text-slate-500">inventory_2</span>
                        Productos del Pedido
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Producto</th>
                                <th class="px-6 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-center w-24">Cant.</th>
                                <th class="px-6 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right w-32">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($pedido->items as $item)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-12 bg-slate-100 rounded-lg overflow-hidden border border-slate-200 flex items-center justify-center flex-shrink-0">
                                                @php
                                                    $imgUrl = null;
                                                    if ($item->variante && $item->variante->imagen_ruta) {
                                                        $imgUrl = asset('storage/' . $item->variante->imagen_ruta);
                                                    } else if ($item->producto && $item->producto->imagenes->isNotEmpty()) {
                                                        $imgUrl = $item->producto->imagen_url;
                                                    }
                                                @endphp
                                                
                                                @if($imgUrl)
                                                    <img src="{{ $imgUrl }}" class="w-full h-full object-cover">
                                                @else
                                                    <span class="material-symbols-outlined text-slate-400">image</span>
                                                @endif
                                            </div>
                                            <div>
                                                <span class="text-sm font-bold text-slate-900 block">{{ $item->producto->nombre }}</span>
                                                @if($item->variante)
                                                    <span class="text-xs text-slate-500 mt-0.5 block">
                                                        {{ $item->variante->opciones->map(fn($o) => $o->valor)->join(' / ') }}
                                                    </span>
                                                @endif
                                                <span class="text-[11px] text-slate-400 font-mono mt-0.5 block">SKU: {{ $item->variante->sku ?? $item->producto->sku }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center font-mono text-sm text-slate-900">{{ $item->cantidad }}</td>
                                    <td class="px-6 py-4 text-right font-mono text-sm font-bold text-slate-900">${{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Columna Derecha: Formulario de Envío -->
        <div class="lg:col-span-1">
            <div class="card-elevated rounded-xl shadow-sm sticky top-24">
                <div class="p-6 border-b border-slate-100 bg-slate-50 rounded-t-xl">
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <span class="material-symbols-outlined text-slate-700">local_shipping</span>
                        Gestión de Envío
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">Configure los detalles logísticos para despachar el pedido.</p>
                </div>
                
                @php
                    $empresaDB = $pedido->envio->empresa_mensajeria ?? '';
                    $metodoEnvioDefault = old('metodo_envio', '');
                    $empresaMensajeriaDefault = old('empresa_mensajeria', '');
                    
                    if (!$metodoEnvioDefault && $empresaDB) {
                        $partes = explode(' - ', $empresaDB, 2);
                        $metodoEnvioDefault = $partes[0];
                        $empresaMensajeriaDefault = $partes[1] ?? '';
                    }
                    if (!$metodoEnvioDefault) {
                        $metodoEnvioDefault = 'Company Delivery';
                    }
                @endphp
                <form action="{{ route('admin.pedidos.envio.update', $pedido->id) }}" method="POST" class="p-6 flex flex-col gap-5" x-data="{ metodoEnvio: '{{ $metodoEnvioDefault }}' }">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5" for="metodo_envio">Método de Envío <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select class="w-full bg-white border {{ $errors->has('metodo_envio') ? 'border-red-400' : 'border-slate-200' }} rounded-lg py-2 px-3 pr-10 text-sm text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-1 focus:ring-slate-900 appearance-none transition-all" 
                                    id="metodo_envio" name="metodo_envio" x-model="metodoEnvio" required>
                                <option value="Company Delivery">Entrega Propia (Driver)</option>
                                <option value="Courier Service">Mensajería Local (Courier)</option>
                                <option value="External Delivery">Servicio Externo (Fletes, etc.)</option>
                                <option value="Store Pickup">Retiro en Tienda</option>
                            </select>
                            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">expand_more</span>
                        </div>
                        @error('metodo_envio')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div x-show="metodoEnvio !== 'Store Pickup' && metodoEnvio !== 'Company Delivery'">
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5" for="empresa_mensajeria">Empresa de Mensajería / Courier</label>
                        <input class="w-full bg-white border {{ $errors->has('empresa_mensajeria') ? 'border-red-400' : 'border-slate-200' }} rounded-lg py-2 px-3 text-sm text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-1 focus:ring-slate-900 transition-all" 
                               id="empresa_mensajeria" name="empresa_mensajeria" type="text" placeholder="Ej: Fletes Chavale, UnoExpress..."
                               value="{{ $empresaMensajeriaDefault }}">
                        @error('empresa_mensajeria')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div x-show="metodoEnvio !== 'Store Pickup'">
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5" for="numero_guia">Número de Guía / Referencia</label>
                        <input class="w-full bg-white border {{ $errors->has('numero_guia') ? 'border-red-400' : 'border-slate-200' }} rounded-lg py-2 px-3 text-sm text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-1 focus:ring-slate-900 transition-all font-mono" 
                               id="numero_guia" name="numero_guia" placeholder="Opcional" type="text"
                               value="{{ old('numero_guia', $pedido->envio->numero_guia ?? '') }}">
                        @error('numero_guia')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5" for="fecha_estimada_entrega">Fecha Estimada de Entrega</label>
                        <div class="relative flex items-center">
                            <span class="material-symbols-outlined absolute left-3 text-slate-400 text-[18px]">calendar_today</span>
                            <input class="w-full pl-9 bg-white border {{ $errors->has('fecha_estimada_entrega') ? 'border-red-400' : 'border-slate-200' }} rounded-lg py-2 px-3 text-sm text-slate-900 focus:outline-none focus:border-slate-900 focus:ring-1 focus:ring-slate-900 transition-all" 
                                   id="fecha_estimada_entrega" name="fecha_estimada_entrega" type="date"
                                   value="{{ old('fecha_estimada_entrega', $pedido->envio?->fecha_estimada_entrega?->format('Y-m-d') ?? '') }}">
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-100 flex flex-col gap-2">
                        <button type="submit" class="w-full bg-slate-900 text-white py-2.5 px-4 rounded-lg text-sm font-bold hover:bg-slate-800 transition-colors focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            Guardar Info. de Envío
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
