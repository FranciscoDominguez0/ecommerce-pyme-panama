@extends('layouts.admin')
@section('title', 'Nueva Venta')

@section('content')
<div class="space-y-6 font-sans" x-data="posVentas(@js($clientes), @js($productos), @js($zonasEnvio))">
    
    <!-- Encabezado -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Nueva Venta</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Registra una orden de mostrador o venta directa.</p>
        </div>
        <div>
            <a href="{{ route('admin.pedidos.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-slate-300 dark:border-gray-700 hover:bg-slate-50 dark:hover:bg-gray-800 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-semibold transition-all">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Volver a Pedidos</span>
            </a>
        </div>
    </div>

    <!-- Formulario Principal -->
    <form action="{{ route('admin.pedidos.guardar-manual') }}" method="POST" id="form-pos" @submit="prepararEnvio($event)">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- ========================================== -->
            <!-- COLUMNA IZQUIERDA: CLIENTE Y PRODUCTOS (7 cols) -->
            <!-- ========================================== -->
            <div class="lg:col-span-7 space-y-6">

                <!-- SECCIÓN 1: CLIENTE -->
                <div class="card-elevated rounded-xl p-5 border border-slate-200/80 dark:border-gray-700/80 bg-white dark:bg-[#181a1b]">
                    <div class="relative" @click.outside="mostrarListaClientes = false">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                            Cliente <span class="text-rose-500">*</span>
                        </label>
                        
                        <div class="relative">
                            <input type="text" 
                                   x-model="busquedaCliente" 
                                   @focus="mostrarListaClientes = true"
                                   @input="mostrarListaClientes = true"
                                   placeholder="Escribe para buscar..." 
                                   class="block w-full rounded-xl border-slate-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white py-2.5 pl-3.5 pr-10 text-sm focus:border-emerald-500 focus:ring-emerald-500 transition-all placeholder-slate-400">
                            
                            <!-- Botón limpiar X (como en la imagen del usuario) -->
                            <button type="button" 
                                    x-show="busquedaCliente || clienteSeleccionado" 
                                    @click="quitarCliente()" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors"
                                    title="Limpiar selección">
                                <span class="material-symbols-outlined text-[18px]">close</span>
                            </button>
                        </div>

                        <!-- Dropdown de Clientes + Opción '+ Nuevo Cliente' fijada abajo (idéntico al diseño del usuario) -->
                        <div x-show="mostrarListaClientes" 
                             x-transition 
                             class="absolute z-30 left-0 right-0 mt-1 max-h-64 flex flex-col bg-white dark:bg-[#1f2223] rounded-xl shadow-xl border border-slate-200 dark:border-gray-700">
                            
                            <!-- Lista desplazable de clientes existentes (máx 15 DOM) -->
                            <div class="overflow-y-auto divide-y divide-slate-100 dark:divide-gray-800 flex-1">
                                <template x-for="cli in clientesFiltrados()" :key="cli.id">
                                    <button type="button" 
                                            @click="seleccionarCliente(cli)" 
                                            class="w-full text-left px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-gray-800/70 transition-colors flex items-center justify-between group">
                                        <div>
                                            <p class="text-sm font-medium text-slate-800 dark:text-slate-200 group-hover:text-emerald-600 dark:group-hover:text-emerald-400" x-text="cli.nombre + ' ' + (cli.apellido || '')"></p>
                                            <p class="text-xs text-slate-400" x-text="cli.email"></p>
                                        </div>
                                        <span class="text-xs text-slate-400 font-mono" x-text="cli.telefono || ''"></span>
                                    </button>
                                </template>

                                <template x-if="clientesFiltrados().length === 0 && busquedaCliente.trim()">
                                    <div class="px-4 py-3 text-xs text-slate-400 text-center">
                                        No se encontraron clientes con "<span x-text="busquedaCliente"></span>".
                                    </div>
                                </template>
                            </div>

                            <!-- Botón '+ Nuevo Cliente' fijado al pie del dropdown -->
                            <div class="shrink-0 bg-white dark:bg-[#1f2223] border-t border-slate-100 dark:border-gray-800 rounded-b-xl">
                                <button type="button" 
                                        @click="abrirModalNuevoCliente()" 
                                        class="w-full text-left px-4 py-3 text-slate-800 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 hover:text-emerald-700 dark:hover:text-emerald-400 font-semibold text-xs flex items-center gap-2 transition-colors">
                                    <span class="material-symbols-outlined text-[18px] text-slate-600 dark:text-slate-400">add</span>
                                    <span>Nuevo Cliente</span>
                                </button>
                            </div>
                        </div>

                        <!-- Feedback visual si hay cliente seleccionado -->
                        <div x-show="clienteSeleccionado" class="mt-2.5 flex items-center justify-between p-2.5 rounded-lg bg-emerald-50/70 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/40 text-xs">
                            <div class="flex items-center gap-2 truncate">
                                <span class="material-symbols-outlined text-emerald-600 text-[16px]">check_circle</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200" x-text="clienteSeleccionado ? clienteSeleccionado.nombre + ' ' + (clienteSeleccionado.apellido || '') : ''"></span>
                                <span class="text-slate-500 dark:text-slate-400 truncate" x-text="clienteSeleccionado ? '(' + clienteSeleccionado.email + ')' : ''"></span>
                            </div>
                            <span class="text-[10px] uppercase font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-100 dark:bg-emerald-900/40 px-1.5 py-0.5 rounded">Seleccionado</span>
                        </div>
                    </div>

                    <!-- Campos ocultos requeridos para el envío -->
                    <input type="hidden" name="tipo_cliente" value="existente">
                    <input type="hidden" name="usuario_id" :value="clienteSeleccionado ? clienteSeleccionado.id : ''">
                </div>

                <!-- SECCIÓN 2: CATÁLOGO DE PRODUCTOS -->
                <div class="card-elevated rounded-xl p-5 border border-slate-200/80 dark:border-gray-700/80 bg-white dark:bg-[#181a1b]">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-gray-800">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">Catálogo de Productos</h2>
                        <span class="text-xs text-slate-400" x-text="todosProductosFiltrados().length + ' disponibles'"></span>
                    </div>

                    <!-- Buscador en tiempo real -->
                    <div class="relative mb-4">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-slate-400 text-lg">search</span>
                        </span>
                        <input type="text" 
                               x-model="busquedaProducto" 
                               @input="paginaProducto = 1"
                               placeholder="Buscar producto por nombre..." 
                               class="block w-full rounded-lg border-slate-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white py-2 pl-9 pr-3 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>

                    <!-- Listado de catálogo paginado -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                        <template x-for="prod in productosPaginados()" :key="prod.id">
                            <div class="p-3 rounded-xl border border-slate-200 dark:border-gray-700/70 hover:border-emerald-500 dark:hover:border-emerald-500/60 bg-slate-50/50 dark:bg-gray-800/40 transition-all flex flex-col justify-between group">
                                <div class="flex items-start gap-2.5">
                                    <div class="w-12 h-12 rounded-lg bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 overflow-hidden shrink-0 flex items-center justify-center">
                                        <template x-if="prod.imagen">
                                            <img :src="prod.imagen" :alt="prod.nombre" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!prod.imagen">
                                            <span class="material-symbols-outlined text-slate-400 text-xl">image</span>
                                        </template>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-xs font-bold text-slate-800 dark:text-white truncate" :title="prod.nombre" x-text="prod.nombre"></h3>
                                        <p class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 mt-0.5" x-text="'$' + prod.precio.toFixed(2)"></p>
                                        
                                        <!-- Stock o variantes -->
                                        <template x-if="prod.variantes && prod.variantes.length > 0">
                                            <span class="inline-block mt-1 text-[10px] px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300 font-semibold" x-text="prod.variantes.length + ' variantes'"></span>
                                        </template>
                                        <template x-if="!prod.variantes || prod.variantes.length === 0">
                                            <span class="inline-block mt-1 text-[10px] px-1.5 py-0.5 rounded font-semibold" 
                                                  :class="prod.stock > 0 ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400' : 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400'"
                                                  x-text="prod.stock > 0 ? prod.stock + ' en stock' : 'Agotado'"></span>
                                        </template>
                                    </div>
                                </div>

                                <!-- Selector de variante si tiene variantes -->
                                <template x-if="prod.variantes && prod.variantes.length > 0">
                                    <div class="mt-2.5">
                                        <select x-model="prod.varianteSeleccionadaId" class="block w-full text-xs rounded-md border-slate-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white py-1 px-2 focus:border-emerald-500 focus:ring-emerald-500">
                                            <option value="">Seleccionar variante...</option>
                                            <template x-for="v in prod.variantes" :key="v.id">
                                                <option :value="v.id" x-text="v.nombre + ' ($' + v.precio.toFixed(2) + ' - ' + v.stock + ' disp.)'" :disabled="v.stock <= 0"></option>
                                            </template>
                                        </select>
                                    </div>
                                </template>

                                <!-- Botón Agregar -->
                                <button type="button" 
                                        @click="agregarAlCarrito(prod)" 
                                        :disabled="(prod.variantes && prod.variantes.length > 0 && !prod.varianteSeleccionadaId) || (!prod.variantes?.length && prod.stock <= 0)"
                                        class="mt-2.5 w-full py-1.5 px-3 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-1 active:scale-95 shadow-sm">
                                    <span class="material-symbols-outlined text-[16px]">add_shopping_cart</span>
                                    <span>Agregar</span>
                                </button>
                            </div>
                        </template>

                        <template x-if="todosProductosFiltrados().length === 0">
                            <div class="col-span-full py-8 text-center text-slate-400 dark:text-slate-500 text-xs">
                                No se encontraron productos con ese término.
                            </div>
                        </template>
                    </div>

                    <!-- Barra de Paginación de Productos (estilo consistente con la app) -->
                    <div class="mt-4 pt-3 border-t border-slate-100 dark:border-gray-800 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                        <span x-text="todosProductosFiltrados().length > 0 
                            ? `Mostrando ${((paginaProducto - 1) * porPaginaProducto) + 1} - ${Math.min(paginaProducto * porPaginaProducto, todosProductosFiltrados().length)} de ${todosProductosFiltrados().length}` 
                            : '0 productos'"></span>

                        <div class="flex items-center gap-1.5" x-show="totalPaginasProductos() > 1">
                            <button type="button" 
                                    @click="cambiarPaginaProducto(paginaProducto - 1)" 
                                    :disabled="paginaProducto === 1" 
                                    class="px-2.5 py-1 text-xs font-semibold rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-slate-50 dark:hover:bg-gray-700 text-slate-700 dark:text-slate-300 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-all">
                                Anterior
                            </button>
                            
                            <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300 px-1" x-text="`Pág. ${paginaProducto} de ${totalPaginasProductos()}`"></span>

                            <button type="button" 
                                    @click="cambiarPaginaProducto(paginaProducto + 1)" 
                                    :disabled="paginaProducto === totalPaginasProductos()" 
                                    class="px-2.5 py-1 text-xs font-semibold rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-slate-50 dark:hover:bg-gray-700 text-slate-700 dark:text-slate-300 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-all">
                                Siguiente
                            </button>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 3: DETALLE DE ITEMS EN LA VENTA -->
                <div class="card-elevated rounded-xl p-5 border border-slate-200/80 dark:border-gray-700/80 bg-white dark:bg-[#181a1b]">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100 dark:border-gray-800">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">Productos en la Venta</h2>
                        <button type="button" x-show="items.length > 0" @click="items = []" class="text-xs text-rose-500 hover:text-rose-700 font-semibold transition-colors">
                            Vaciar lista
                        </button>
                    </div>

                    <!-- Lista de items seleccionados -->
                    <div class="divide-y divide-slate-100 dark:divide-gray-800">
                        <template x-for="(item, index) in items" :key="item.key">
                            <div class="py-3 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 overflow-hidden shrink-0 flex items-center justify-center">
                                        <template x-if="item.imagen">
                                            <img :src="item.imagen" :alt="item.nombre" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!item.imagen">
                                            <span class="material-symbols-outlined text-slate-400 text-lg">image</span>
                                        </template>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-bold text-slate-900 dark:text-white truncate" x-text="item.nombre"></p>
                                        <template x-if="item.variante_nombre">
                                            <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-medium" x-text="item.variante_nombre"></p>
                                        </template>
                                        <p class="text-xs font-semibold text-slate-600 dark:text-slate-300" x-text="'$' + item.precio.toFixed(2) + ' c/u'"></p>
                                    </div>
                                </div>

                                <!-- Control de cantidad -->
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <button type="button" @click="cambiarCantidad(item, -1)" class="w-7 h-7 rounded-md bg-slate-100 dark:bg-gray-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-slate-700 dark:text-slate-200 flex items-center justify-center text-sm font-bold transition-all">
                                        -
                                    </button>
                                    <input type="number" 
                                           x-model.number="item.cantidad" 
                                           @change="validarCantidad(item)"
                                           min="1" 
                                           :max="item.stock_disponible"
                                           class="w-12 text-center text-xs font-bold py-1 px-1 rounded-md border-slate-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-emerald-500 focus:border-emerald-500 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                    <button type="button" @click="cambiarCantidad(item, 1)" class="w-7 h-7 rounded-md bg-slate-100 dark:bg-gray-800 hover:bg-slate-200 dark:hover:bg-gray-700 text-slate-700 dark:text-slate-200 flex items-center justify-center text-sm font-bold transition-all">
                                        +
                                    </button>
                                </div>

                                <!-- Subtotal del Item y Quitar -->
                                <div class="text-right shrink-0 min-w-[70px]">
                                    <p class="text-xs font-bold text-slate-900 dark:text-white" x-text="'$' + (item.precio * item.cantidad).toFixed(2)"></p>
                                    <button type="button" @click="eliminarItem(index)" class="text-[11px] text-rose-500 hover:text-rose-700 transition-colors">
                                        Quitar
                                    </button>
                                </div>
                            </div>
                        </template>

                        <template x-if="items.length === 0">
                            <div class="py-10 text-center">
                                <span class="material-symbols-outlined text-slate-300 dark:text-slate-600 text-4xl mb-2">shopping_cart</span>
                                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">No hay productos agregados a la venta.</p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Selecciona productos desde el catálogo superior.</p>
                            </div>
                        </template>
                    </div>
                </div>

            </div>

            <!-- ========================================== -->
            <!-- COLUMNA DERECHA: PAGO, ENTREGA Y TOTAL (5 cols) -->
            <!-- ========================================== -->
            <div class="lg:col-span-5 space-y-6">

                <!-- SECCIÓN 4: ENTREGA Y LOGÍSTICA -->
                <div class="card-elevated rounded-xl p-5 border border-slate-200/80 dark:border-gray-700/80 bg-white dark:bg-[#181a1b]">
                    <div class="pb-3 mb-4 border-b border-slate-100 dark:border-gray-800">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">Tipo de Entrega</h2>
                    </div>

                    <div class="grid grid-cols-2 gap-2 mb-4">
                        <button type="button" 
                                @click="tipoEntrega = 'retiro_local'; costoEnvio = 0"
                                :class="tipoEntrega === 'retiro_local' ? 'border-emerald-600 bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-300 font-bold' : 'border-slate-200 dark:border-gray-700 text-slate-600 dark:text-slate-400 hover:border-slate-300'"
                                class="py-2.5 px-3 rounded-xl border text-center transition-all flex flex-col items-center">
                            <span class="text-xs">Retiro en Mostrador</span>
                            <span class="text-[10px] text-slate-400 mt-0.5">Gratis ($0.00)</span>
                        </button>

                        <button type="button" 
                                @click="tipoEntrega = 'envio_domicilio'"
                                :class="tipoEntrega === 'envio_domicilio' ? 'border-emerald-600 bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-300 font-bold' : 'border-slate-200 dark:border-gray-700 text-slate-600 dark:text-slate-400 hover:border-slate-300'"
                                class="py-2.5 px-3 rounded-xl border text-center transition-all flex flex-col items-center">
                            <span class="text-xs">Envío a Domicilio</span>
                            <span class="text-[10px] text-slate-400 mt-0.5">Mensajería</span>
                        </button>
                    </div>

                    <input type="hidden" name="tipo_entrega" :value="tipoEntrega">

                    <!-- Opciones de envío a domicilio -->
                    <div x-show="tipoEntrega === 'envio_domicilio'" class="space-y-3 pt-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Zona de Envío</label>
                            <select name="zona_envio_id" x-model="zonaEnvioId" @change="actualizarCostoZona()" class="block w-full text-xs rounded-lg border-slate-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white py-2 px-3 focus:border-emerald-500 focus:ring-emerald-500">
                                <option value="">Seleccionar zona...</option>
                                <template x-for="z in zonasEnvio" :key="z.id">
                                    <option :value="z.id" x-text="z.nombre + ' ($' + parseFloat(z.tarifa_base).toFixed(2) + ')'"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Costo de Envío ($)</label>
                            <input type="number" step="0.01" min="0" name="costo_envio" x-model.number="costoEnvio" class="block w-full text-xs rounded-lg border-slate-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white py-2 px-3 focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Dirección Exacta</label>
                            <textarea name="direccion_exacta" x-model="direccionExacta" rows="2" placeholder="Calle, casa/edificio, corregimiento..." class="block w-full text-xs rounded-lg border-slate-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white py-2 px-3 focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 5: MÉTODO DE PAGO -->
                <div class="card-elevated rounded-xl p-5 border border-slate-200/80 dark:border-gray-700/80 bg-white dark:bg-[#181a1b]">
                    <div class="pb-3 mb-4 border-b border-slate-100 dark:border-gray-800">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">Forma de Pago</h2>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        <!-- Efectivo -->
                        <button type="button" 
                                @click="metodoPago = 'efectivo'"
                                :class="metodoPago === 'efectivo' ? 'border-emerald-600 bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-300 font-bold' : 'border-slate-200 dark:border-gray-700 text-slate-600 dark:text-slate-400 hover:border-slate-300'"
                                class="py-2.5 px-3 rounded-xl border text-xs text-center transition-all">
                            Efectivo
                        </button>

                        <!-- Punto de Venta / Tarjeta -->
                        <button type="button" 
                                @click="metodoPago = 'punto_venta'"
                                :class="metodoPago === 'punto_venta' ? 'border-emerald-600 bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-300 font-bold' : 'border-slate-200 dark:border-gray-700 text-slate-600 dark:text-slate-400 hover:border-slate-300'"
                                class="py-2.5 px-3 rounded-xl border text-xs text-center transition-all">
                            Terminal POS
                        </button>

                        <!-- Yappy -->
                        <button type="button" 
                                @click="metodoPago = 'yappy'"
                                :class="metodoPago === 'yappy' ? 'border-emerald-600 bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-300 font-bold' : 'border-slate-200 dark:border-gray-700 text-slate-600 dark:text-slate-400 hover:border-slate-300'"
                                class="py-2.5 px-3 rounded-xl border text-xs text-center transition-all">
                            Yappy
                        </button>

                        <!-- Transferencia Bancaria / ACH -->
                        <button type="button" 
                                @click="metodoPago = 'transferencia'"
                                :class="metodoPago === 'transferencia' ? 'border-emerald-600 bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-300 font-bold' : 'border-slate-200 dark:border-gray-700 text-slate-600 dark:text-slate-400 hover:border-slate-300'"
                                class="py-2.5 px-3 rounded-xl border text-xs text-center transition-all">
                            ACH / Banco
                        </button>

                        <!-- Contra Entrega -->
                        <button type="button" 
                                @click="metodoPago = 'contra_entrega'"
                                :class="metodoPago === 'contra_entrega' ? 'border-emerald-600 bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-300 font-bold' : 'border-slate-200 dark:border-gray-700 text-slate-600 dark:text-slate-400 hover:border-slate-300'"
                                class="py-2.5 px-3 rounded-xl border text-xs text-center transition-all col-span-2 sm:col-span-1">
                            Contra Entrega
                        </button>
                    </div>

                    <input type="hidden" name="metodo_pago" :value="metodoPago">
                </div>

                <!-- SECCIÓN 6: RESUMEN ECONÓMICO Y CONFIRMACIÓN -->
                <div class="card-elevated rounded-xl p-5 border border-slate-200/80 dark:border-gray-700/80 bg-white dark:bg-[#181a1b] space-y-4">
                    <div class="pb-3 border-b border-slate-100 dark:border-gray-800">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">Resumen de Venta</h2>
                    </div>

                    <!-- Desglose de totales -->
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Subtotal Items</span>
                            <span class="font-bold text-slate-900 dark:text-white" x-text="'$' + calcularSubtotal().toFixed(2)"></span>
                        </div>

                        <!-- Descuento manual -->
                        <div class="flex items-center justify-between gap-2 pt-1">
                            <span class="text-slate-600 dark:text-slate-400">Descuento ($)</span>
                            <div class="w-24">
                                <input type="number" 
                                       step="0.01" 
                                       min="0" 
                                       name="descuento_manual" 
                                       x-model.number="descuentoManual" 
                                       class="block w-full text-right text-xs rounded-md border-slate-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white py-1 px-2 focus:ring-emerald-500 focus:border-emerald-500">
                            </div>
                        </div>

                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>Costo de Envío</span>
                            <span class="font-semibold" x-text="'$' + parseFloat(costoEnvio || 0).toFixed(2)"></span>
                        </div>

                        <div class="flex justify-between text-slate-600 dark:text-slate-400">
                            <span>ITBMS (7%)</span>
                            <span class="font-semibold" x-text="'$' + calcularITBMS().toFixed(2)"></span>
                        </div>

                        <div class="pt-3 border-t border-slate-200 dark:border-gray-800 flex justify-between items-baseline">
                            <span class="text-sm font-bold text-slate-900 dark:text-white">Total a Cobrar</span>
                            <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400" x-text="'$' + calcularTotal().toFixed(2)"></span>
                        </div>
                    </div>

                    <!-- Opciones de Estado y Factura -->
                    <div class="pt-3 border-t border-slate-100 dark:border-gray-800 space-y-2.5 text-xs">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="marcar_pagado" value="1" x-model="marcarPagado" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="font-semibold text-slate-700 dark:text-slate-300">Marcar como pagado inmediatamente</span>
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="emitir_factura" value="1" x-model="emitirFactura" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span class="font-semibold text-slate-700 dark:text-slate-300">Generar factura fiscal de mostrador</span>
                        </label>
                    </div>

                    <!-- Nota interna sencilla (opcional) -->
                    <div class="pt-2 border-t border-slate-100 dark:border-gray-800">
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Notas internas (opcional)</label>
                        <input type="text" 
                               name="notas_internas" 
                               x-model="notasInternas" 
                               placeholder="Detalles u observaciones de la venta..." 
                               class="block w-full text-xs rounded-xl border-slate-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white py-2 px-3 focus:ring-emerald-500 focus:border-emerald-500 placeholder-slate-400">
                    </div>

                    <!-- Botón Confirmar Venta -->
                    <div class="pt-3">
                        <button type="submit" 
                                :disabled="items.length === 0 || procesando"
                                class="w-full py-3 px-4 rounded-xl text-sm font-bold bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-md shadow-emerald-600/20 flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[20px]" :class="procesando ? 'animate-spin' : ''" x-text="procesando ? 'progress_activity' : 'check_circle'"></span>
                            <span x-text="procesando ? 'Procesando Venta...' : 'Confirmar y Registrar Venta'"></span>
                        </button>
                    </div>
                </div>

            </div>

        </div>
    </form>

    <!-- ========================================== -->
    <!-- MODAL: REGISTRAR NUEVO CLIENTE RÁPIDO      -->
    <!-- ========================================== -->
    <div x-show="modalNuevoCliente" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-nuevo-cliente-title" 
         role="dialog" 
         aria-modal="true"
         @keydown.escape.window="cerrarModalNuevoCliente()">
        
        <!-- Backdrop con desenfoque suave -->
        <div x-show="modalNuevoCliente"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity"
             @click="cerrarModalNuevoCliente()"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="modalNuevoCliente"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-[#1e2122] text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-200 dark:border-gray-700">
                
                <!-- Cabecera del Modal -->
                <div class="px-6 pt-5 pb-4 border-b border-slate-100 dark:border-gray-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white" id="modal-nuevo-cliente-title">
                            Nuevo Cliente
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Completa los datos para registrarlo y seleccionarlo.
                        </p>
                    </div>
                    <button type="button" 
                            @click="cerrarModalNuevoCliente()" 
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg transition-colors">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <!-- Formulario Modal -->
                <form @submit.prevent="guardarNuevoClienteModal()">
                    <div class="p-6 space-y-4">
                        <!-- Alerta de Error en el Modal -->
                        <template x-if="errorModalCliente">
                            <div class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-900/50 text-rose-700 dark:text-rose-400 text-xs flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px]">error</span>
                                <span x-text="errorModalCliente"></span>
                            </div>
                        </template>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    Nombre <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       x-model="formModal.nombre" 
                                       placeholder="Ej. Juan" 
                                       class="block w-full rounded-xl border-slate-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white py-2 px-3 text-sm focus:border-emerald-500 focus:ring-emerald-500 placeholder-slate-400"
                                       required>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    Apellido
                                </label>
                                <input type="text" 
                                       x-model="formModal.apellido" 
                                       placeholder="Ej. Pérez" 
                                       class="block w-full rounded-xl border-slate-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white py-2 px-3 text-sm focus:border-emerald-500 focus:ring-emerald-500 placeholder-slate-400">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Correo Electrónico <span class="text-rose-500">*</span>
                            </label>
                            <input type="email" 
                                   x-model="formModal.email" 
                                   placeholder="cliente@ejemplo.com" 
                                   class="block w-full rounded-xl border-slate-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white py-2 px-3 text-sm focus:border-emerald-500 focus:ring-emerald-500 placeholder-slate-400"
                                   required>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Teléfono / WhatsApp
                            </label>
                            <input type="tel" 
                                   x-model="formModal.telefono" 
                                   placeholder="+507 6000-0000" 
                                   class="block w-full rounded-xl border-slate-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white py-2 px-3 text-sm focus:border-emerald-500 focus:ring-emerald-500 placeholder-slate-400">
                        </div>
                    </div>

                    <!-- Footer del Modal -->
                    <div class="px-6 py-4 bg-slate-50 dark:bg-[#181a1b] border-t border-slate-100 dark:border-gray-800 flex items-center justify-end gap-3 rounded-b-2xl">
                        <button type="button" 
                                @click="cerrarModalNuevoCliente()" 
                                :disabled="guardandoCliente"
                                class="px-4 py-2 border border-slate-300 dark:border-gray-700 hover:bg-slate-100 dark:hover:bg-gray-800 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-semibold transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" 
                                :disabled="guardandoCliente"
                                class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-xs transition-colors disabled:opacity-50">
                            <span x-show="guardandoCliente" class="animate-spin material-symbols-outlined text-[16px]">progress_activity</span>
                            <span x-text="guardandoCliente ? 'Guardando...' : 'Guardar Cliente'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function posVentas(clientes, productos, zonasEnvio) {
        return {
            clientes: clientes || [],
            productos: (productos || []).map(p => ({ ...p, varianteSeleccionadaId: '' })),
            zonasEnvio: zonasEnvio || [],

            clienteSeleccionado: null,
            busquedaCliente: '',
            mostrarListaClientes: false,

            // Estado del modal de nuevo cliente
            modalNuevoCliente: false,
            guardandoCliente: false,
            errorModalCliente: '',
            formModal: {
                nombre: '',
                apellido: '',
                email: '',
                telefono: ''
            },

            busquedaProducto: '',
            paginaProducto: 1,
            porPaginaProducto: 15,
            items: [],

            tipoEntrega: 'retiro_local',
            zonaEnvioId: '',
            costoEnvio: 0.00,
            direccionExacta: '',

            metodoPago: 'efectivo',
            descuentoManual: 0.00,
            marcarPagado: true,
            emitirFactura: true,
            notasInternas: '',
            procesando: false,

            // Filtro de clientes (máximo 15 items DOM)
            clientesFiltrados() {
                const q = this.busquedaCliente.trim().toLowerCase();
                if (!q) return this.clientes.slice(0, 15);
                return this.clientes.filter(c => {
                    const full = (c.nombre + ' ' + (c.apellido || '') + ' ' + c.email).toLowerCase();
                    return full.includes(q);
                }).slice(0, 15);
            },

            seleccionarCliente(cli) {
                this.clienteSeleccionado = cli;
                this.busquedaCliente = cli.nombre + (cli.apellido ? ' ' + cli.apellido : '');
                this.mostrarListaClientes = false;
            },

            quitarCliente() {
                this.clienteSeleccionado = null;
                this.busquedaCliente = '';
            },

            abrirModalNuevoCliente() {
                this.mostrarListaClientes = false;
                this.errorModalCliente = '';
                this.formModal = {
                    nombre: (this.busquedaCliente && !this.clienteSeleccionado) ? this.busquedaCliente.trim() : '',
                    apellido: '',
                    email: '',
                    telefono: ''
                };
                this.modalNuevoCliente = true;
            },

            cerrarModalNuevoCliente() {
                this.modalNuevoCliente = false;
                this.errorModalCliente = '';
            },

            async guardarNuevoClienteModal() {
                this.errorModalCliente = '';
                if (!this.formModal.nombre.trim()) {
                    this.errorModalCliente = 'El nombre del cliente es obligatorio.';
                    return;
                }
                if (!this.formModal.email.trim() || !this.formModal.email.includes('@')) {
                    this.errorModalCliente = 'Por favor ingresa un correo electrónico válido.';
                    return;
                }

                this.guardandoCliente = true;

                try {
                    const response = await fetch("{{ route('admin.pedidos.cliente-rapido') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.formModal)
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Error al registrar el cliente.'));
                    }

                    if (data.success && data.cliente) {
                        const idx = this.clientes.findIndex(c => c.id === data.cliente.id);
                        if (idx >= 0) {
                            this.clientes[idx] = data.cliente;
                        } else {
                            this.clientes.unshift(data.cliente);
                        }
                        this.seleccionarCliente(data.cliente);
                        this.cerrarModalNuevoCliente();
                    }
                } catch (err) {
                    this.errorModalCliente = err.message || 'Error de conexión al registrar cliente.';
                } finally {
                    this.guardandoCliente = false;
                }
            },

            // Métodos de catálogo y paginación (15 items por página)
            todosProductosFiltrados() {
                const q = this.busquedaProducto.trim().toLowerCase();
                if (!q) return this.productos;
                return this.productos.filter(p => p.nombre.toLowerCase().includes(q));
            },

            totalPaginasProductos() {
                return Math.max(1, Math.ceil(this.todosProductosFiltrados().length / this.porPaginaProducto));
            },

            productosPaginados() {
                const todos = this.todosProductosFiltrados();
                const inicio = (this.paginaProducto - 1) * this.porPaginaProducto;
                return todos.slice(inicio, inicio + this.porPaginaProducto);
            },

            cambiarPaginaProducto(nueva) {
                const max = this.totalPaginasProductos();
                if (nueva >= 1 && nueva <= max) {
                    this.paginaProducto = nueva;
                }
            },

            agregarAlCarrito(prod) {
                let varianteId = null;
                let varianteNombre = null;
                let precio = prod.precio;
                let stock = prod.stock;

                if (prod.variantes && prod.variantes.length > 0) {
                    if (!prod.varianteSeleccionadaId) return;
                    const v = prod.variantes.find(item => item.id == prod.varianteSeleccionadaId);
                    if (!v) return;
                    varianteId = v.id;
                    varianteNombre = v.nombre;
                    precio = v.precio;
                    stock = v.stock;
                }

                if (stock <= 0) {
                    alert('El producto seleccionado no cuenta con inventario disponible.');
                    return;
                }

                const key = prod.id + '_' + (varianteId || 'base');
                const index = this.items.findIndex(i => i.key === key);

                if (index >= 0) {
                    if (this.items[index].cantidad + 1 > stock) {
                        alert('No puedes agregar más unidades del stock disponible (' + stock + ').');
                        return;
                    }
                    this.items[index].cantidad += 1;
                } else {
                    this.items.push({
                        key: key,
                        producto_id: prod.id,
                        variante_id: varianteId,
                        nombre: prod.nombre,
                        variante_nombre: varianteNombre,
                        precio: precio,
                        cantidad: 1,
                        stock_disponible: stock,
                        imagen: prod.imagen
                    });
                }
            },

            cambiarCantidad(item, delta) {
                const nueva = item.cantidad + delta;
                if (nueva <= 0) return;
                if (nueva > item.stock_disponible) {
                    alert('Stock máximo alcanzado (' + item.stock_disponible + ').');
                    return;
                }
                item.cantidad = nueva;
            },

            validarCantidad(item) {
                if (item.cantidad <= 0 || isNaN(item.cantidad)) {
                    item.cantidad = 1;
                }
                if (item.cantidad > item.stock_disponible) {
                    item.cantidad = item.stock_disponible;
                    alert('Cantidad ajustada al stock disponible (' + item.stock_disponible + ').');
                }
            },

            eliminarItem(index) {
                this.items.splice(index, 1);
            },

            actualizarCostoZona() {
                if (!this.zonaEnvioId) {
                    this.costoEnvio = 0.00;
                    return;
                }
                const z = this.zonasEnvio.find(item => item.id == this.zonaEnvioId);
                this.costoEnvio = z ? parseFloat(z.tarifa_base) : 0.00;
            },

            calcularSubtotal() {
                return this.items.reduce((acc, i) => acc + (i.precio * i.cantidad), 0);
            },

            calcularBaseImponible() {
                const sub = this.calcularSubtotal();
                const desc = parseFloat(this.descuentoManual || 0);
                return Math.max(0, sub - desc);
            },

            calcularITBMS() {
                const base = this.calcularBaseImponible();
                return parseFloat((base * 0.07).toFixed(2));
            },

            calcularTotal() {
                const base = this.calcularBaseImponible();
                const itbms = this.calcularITBMS();
                const envio = parseFloat(this.costoEnvio || 0);
                return parseFloat((base + itbms + envio).toFixed(2));
            },

            prepararEnvio(e) {
                if (this.items.length === 0) {
                    e.preventDefault();
                    alert('Debes agregar al menos un producto para registrar la venta.');
                    return;
                }

                if (!this.clienteSeleccionado) {
                    e.preventDefault();
                    alert('Por favor selecciona o crea un cliente para la venta.');
                    return;
                }

                this.procesando = true;

                // Crear los campos ocultos de items dinámicamente antes de enviar
                const form = e.target;
                
                // Limpiar items previos si hubiese
                form.querySelectorAll('input[name^="items["]').forEach(el => el.remove());

                this.items.forEach((item, idx) => {
                    this.crearHiddenInput(form, `items[${idx}][producto_id]`, item.producto_id);
                    if (item.variante_id) {
                        this.crearHiddenInput(form, `items[${idx}][variante_id]`, item.variante_id);
                    }
                    this.crearHiddenInput(form, `items[${idx}][cantidad]`, item.cantidad);
                    this.crearHiddenInput(form, `items[${idx}][precio_unitario]`, item.precio);
                });
            },

            crearHiddenInput(form, name, value) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                form.appendChild(input);
            }
        };
    }
</script>
@endpush
@endsection
