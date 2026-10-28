@extends('layouts.admin')
@section('title', 'Nueva Venta')

@section('content')
<div class="space-y-6 font-sans" x-data="posVentas(@js($clientes), @js($productos), @js($zonasEnvio), @js($courierSucursales), @js($zonasCourier))">
    
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

                    <!-- Buscador en tiempo real con lector de código de barras -->
                    <div class="relative mb-4">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="material-symbols-outlined text-slate-400 text-lg">search</span>
                        </span>
                        <input type="text" 
                               id="input-buscar-producto"
                               x-model="busquedaProducto" 
                               @input="paginaProducto = 1"
                               @keydown.enter.prevent="procesarCodigoEscaner(busquedaProducto)"
                               placeholder="Buscar por nombre, SKU o código de barras..." 
                               class="block w-full rounded-lg border-slate-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white py-2 pl-9 pr-24 text-sm focus:border-emerald-500 focus:ring-emerald-500">

                        <!-- Acciones del Buscador: Limpiar y Botón Escáner Cámara -->
                        <div class="absolute inset-y-0 right-0 pr-1.5 flex items-center gap-1">
                            <button type="button" 
                                    x-show="busquedaProducto" 
                                    @click="busquedaProducto = ''; paginaProducto = 1;" 
                                    class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-md transition-colors cursor-pointer"
                                    title="Limpiar búsqueda">
                                <span class="material-symbols-outlined text-[16px]">close</span>
                            </button>
                            <button type="button" 
                                    @click="abrirEscanerCamara()" 
                                    title="Escanear código de barras con la cámara"
                                    class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/60 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition-all cursor-pointer active:scale-95 shadow-2xs">
                                <span class="material-symbols-outlined text-[16px]">barcode_scanner</span>
                                <span class="hidden sm:inline text-[11px] font-bold">Escanear</span>
                            </button>
                        </div>
                    </div>

                    <!-- Listado de catálogo paginado con animación y skeletons suaves 100% estático -->
                    <div class="relative grid grid-cols-1 min-h-[925px]">
                        <!-- Skeletons animados durante carga o cambio de página (ocupan la misma celda de grid) -->
                        <div x-show="cargandoProductos"
                             x-transition:enter="transition-opacity ease-out duration-250"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition-opacity ease-in duration-200"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="col-start-1 row-start-1 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 z-10 w-full">
                            <template x-for="i in 15" :key="i">
                                <div class="p-3 rounded-xl border border-slate-200/80 dark:border-gray-800 bg-white/80 dark:bg-gray-800/60 flex flex-col justify-between animate-pulse h-[175px]">
                                    <div class="flex items-start gap-2.5">
                                        <div class="w-12 h-12 rounded-lg bg-slate-200 dark:bg-gray-700/80 shrink-0"></div>
                                        <div class="min-w-0 flex-1 space-y-2 py-0.5">
                                            <div class="h-3.5 bg-slate-200 dark:bg-gray-700/80 rounded w-4/5"></div>
                                            <div class="h-2.5 bg-slate-200 dark:bg-gray-700/60 rounded w-1/2"></div>
                                            <div class="h-3 bg-emerald-100 dark:bg-emerald-950/40 rounded w-1/3"></div>
                                        </div>
                                    </div>
                                    <!-- Placeholder para selector de variante -->
                                    <div class="mt-2.5 h-[30px] flex items-center">
                                        <div class="h-6 bg-slate-200/60 dark:bg-gray-700/40 rounded-md w-full"></div>
                                    </div>
                                    <!-- Placeholder de botón -->
                                    <div class="mt-2.5 h-8 bg-slate-200/90 dark:bg-gray-700/60 rounded-lg w-full"></div>
                                </div>
                            </template>
                        </div>

                        <!-- Grilla de productos reales (altura uniforme garantizada tanto con o sin variantes) -->
                        <div x-show="!cargandoProductos"
                             x-transition:enter="transition-opacity ease-out duration-250"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition-opacity ease-in duration-200"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="col-start-1 row-start-1 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 w-full">
                            <template x-for="prod in productosPaginados()" :key="prod.id">
                                <div class="p-3 rounded-xl border border-slate-200 dark:border-gray-700/70 hover:border-emerald-500 dark:hover:border-emerald-500/60 bg-slate-50/50 dark:bg-gray-800/40 hover:bg-white dark:hover:bg-gray-800/80 transition-all duration-200 hover:shadow-md flex flex-col justify-between group h-[175px]">
                                    <div class="flex items-start gap-2.5">
                                        <div class="w-12 h-12 rounded-lg bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 overflow-hidden shrink-0 flex items-center justify-center group-hover:scale-105 transition-transform">
                                            <template x-if="prod.imagen">
                                                <img :src="prod.imagen" :alt="prod.nombre" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!prod.imagen">
                                                <span class="material-symbols-outlined text-slate-400 text-xl">image</span>
                                            </template>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h3 class="text-xs font-bold text-slate-800 dark:text-white truncate" :title="prod.nombre" x-text="prod.nombre"></h3>
                                            <template x-if="prod.sku">
                                                <p class="text-[10px] font-mono text-slate-400 dark:text-slate-500 tracking-wider truncate" x-text="'SKU: ' + prod.sku"></p>
                                            </template>
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

                                    <!-- Fila de variante con altura fija h-[30px] para que la tarjeta sea 100% estática -->
                                    <div class="mt-2.5 h-[30px] flex items-center">
                                        <template x-if="prod.variantes && prod.variantes.length > 0">
                                            <select x-model="prod.varianteSeleccionadaId" class="block w-full text-xs rounded-md border-slate-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white py-1 px-2 focus:border-emerald-500 focus:ring-emerald-500">
                                                <option value="">Seleccionar variante...</option>
                                                <template x-for="v in prod.variantes" :key="v.id">
                                                    <option :value="v.id" x-text="(v.nombre || v.sku || 'Variante #' + v.id) + ' ($' + v.precio.toFixed(2) + ' - ' + v.stock + ' disp.)'" :disabled="v.stock <= 0"></option>
                                                </template>
                                            </select>
                                        </template>
                                        <template x-if="!prod.variantes || prod.variantes.length === 0">
                                            <span class="text-[11px] text-slate-400 dark:text-slate-500 italic flex items-center gap-1 pl-0.5">
                                                <span class="material-symbols-outlined text-[13px] text-slate-400">check_circle</span>
                                                Presentación única
                                            </span>
                                        </template>
                                    </div>

                                    <!-- Botón Agregar con altura consistente h-8 -->
                                    <button type="button" 
                                            @click="agregarAlCarrito(prod)" 
                                            :disabled="(prod.variantes && prod.variantes.length > 0 && !prod.varianteSeleccionadaId) || (!prod.variantes?.length && prod.stock <= 0)"
                                            class="mt-2.5 w-full h-8 px-3 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-700 text-white disabled:opacity-50 disabled:cursor-not-allowed transition-all flex items-center justify-center gap-1 active:scale-95 shadow-sm">
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
                    </div>

                    <!-- Barra de Paginación de Productos (estilo consistente con la app) -->
                    <div class="mt-4 pt-3 border-t border-slate-100 dark:border-gray-800 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                        <span x-text="todosProductosFiltrados().length > 0 
                            ? `Mostrando ${((paginaProducto - 1) * porPaginaProducto) + 1} - ${Math.min(paginaProducto * porPaginaProducto, todosProductosFiltrados().length)} de ${todosProductosFiltrados().length}` 
                            : '0 productos'"></span>

                        <div class="flex items-center gap-1.5" x-show="totalPaginasProductos() > 1">
                            <button type="button" 
                                    @click="cambiarPaginaProducto(paginaProducto - 1)" 
                                    :disabled="paginaProducto === 1 || cargandoProductos" 
                                    class="px-2.5 py-1 text-xs font-semibold rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-slate-50 dark:hover:bg-gray-700 text-slate-700 dark:text-slate-300 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-all">
                                Anterior
                            </button>
                            
                            <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300 px-1" x-text="`Pág. ${paginaProducto} de ${totalPaginasProductos()}`"></span>

                            <button type="button" 
                                    @click="cambiarPaginaProducto(paginaProducto + 1)" 
                                    :disabled="paginaProducto === totalPaginasProductos() || cargandoProductos" 
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
                        <button type="button" x-show="items.length > 0" @click="items = []; if (typeof window.mostrarToast === 'function') window.mostrarToast('Lista de productos vaciada', 'info');" class="text-xs text-rose-500 hover:text-rose-700 font-semibold transition-colors">
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
                <div id="seccion-tipo-entrega" class="card-elevated rounded-xl p-5 border border-slate-200/80 dark:border-gray-700/80 bg-white dark:bg-[#181a1b]">
                    <div class="pb-3 mb-4 border-b border-slate-100 dark:border-gray-800 flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                <span>Tipo de Entrega</span>
                                <span class="text-rose-500">*</span>
                            </h2>
                            <p class="text-[11px] text-slate-400 mt-0.5">Selecciona o configura el método de entrega del pedido.</p>
                        </div>
                        <button type="button" 
                                @click="abrirModalEntrega()" 
                                class="inline-flex items-center gap-1 text-xs text-emerald-600 dark:text-emerald-400 font-semibold hover:underline">
                            <span class="material-symbols-outlined text-[16px]">tune</span>
                            <span>Configurar</span>
                        </button>
                    </div>

                    <!-- Botones de Método: Retiro Local, Delivery, Courier -->
                    <div class="grid grid-cols-3 gap-2 mb-4">
                        <button type="button" 
                                @click="seleccionarMetodoEntrega('retiro_local')"
                                :class="tipoEntrega === 'retiro_local' ? 'border-emerald-600 bg-emerald-50/60 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-300 font-bold shadow-xs' : 'border-slate-200 dark:border-gray-700 text-slate-600 dark:text-slate-400 hover:border-slate-300'"
                                class="py-2.5 px-2 rounded-xl border text-center transition-all flex flex-col items-center cursor-pointer">
                            <span class="text-xs">Retiro en Local</span>
                            <span class="text-[10px] text-slate-400 mt-0.5">Gratis ($0.00)</span>
                        </button>

                        <button type="button" 
                                @click="seleccionarMetodoEntrega('delivery')"
                                :class="tipoEntrega === 'delivery' ? 'border-emerald-600 bg-emerald-50/60 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-300 font-bold shadow-xs' : 'border-slate-200 dark:border-gray-700 text-slate-600 dark:text-slate-400 hover:border-slate-300'"
                                class="py-2.5 px-2 rounded-xl border text-center transition-all flex flex-col items-center cursor-pointer">
                            <span class="text-xs">Delivery</span>
                            <span class="text-[10px] text-slate-400 mt-0.5">A Domicilio</span>
                        </button>

                        <button type="button" 
                                @click="seleccionarMetodoEntrega('retiro_courier')"
                                :class="tipoEntrega === 'retiro_courier' ? 'border-[#ff8c69] bg-[#fff5f2] dark:bg-[#331e18] text-[#ff6b3d] font-bold shadow-xs' : 'border-slate-200 dark:border-gray-700 text-slate-600 dark:text-slate-400 hover:border-slate-300'"
                                class="py-2.5 px-2 rounded-xl border text-center transition-all flex flex-col items-center cursor-pointer">
                            <span class="text-xs">Courier</span>
                            <span class="text-[10px] text-slate-400 mt-0.5">Por Sucursal</span>
                        </button>
                    </div>

                    <input type="hidden" name="tipo_entrega" :value="tipoEntrega">
                    <input type="hidden" name="courier_sucursal_id" :value="tipoEntrega === 'retiro_courier' ? courierSucursalId : ''">
                    <input type="hidden" name="zona_envio_id" :value="tipoEntrega === 'delivery' ? zonaEnvioId : ''">
                    <input type="hidden" name="costo_envio" :value="costoEnvio">
                    <input type="hidden" name="direccion_exacta" :value="direccionExacta">

                    <!-- Resumen / Configuración según método -->
                    
                    <!-- 0. SIN SELECCIONAR (POR DEFECTO) -->
                    <div x-show="!tipoEntrega" class="p-4 border-2 border-dashed border-slate-200 dark:border-gray-800 rounded-xl text-center bg-slate-50/50 dark:bg-gray-800/30">
                        <span class="material-symbols-outlined text-slate-400 dark:text-slate-500 text-3xl mb-1">local_shipping</span>
                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">Ningún tipo de entrega seleccionado</p>
                        <p class="text-[11px] text-slate-400 mt-0.5 mb-2.5">Elige arriba entre Retiro en Local, Delivery a Domicilio o Envío por Courier.</p>
                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded-md border border-amber-200 dark:border-amber-800/60">
                            <span class="material-symbols-outlined text-[13px]">warning</span>
                            Obligatorio para procesar la venta
                        </span>
                    </div>

                    <!-- 1. RETIRO EN LOCAL -->
                    <div x-show="tipoEntrega === 'retiro_local'" class="p-3.5 bg-slate-50 dark:bg-gray-800/60 rounded-xl border border-slate-200 dark:border-gray-700 text-xs space-y-1.5">
                        <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-400 font-semibold">
                            <span class="material-symbols-outlined text-[18px]">storefront</span>
                            <span>{{ $retiroLocal['sucursal'] ?? 'Sucursal PYME Panamá' }}</span>
                        </div>
                        <p class="text-slate-600 dark:text-slate-300">{{ $retiroLocal['direccion'] ?? 'Dirección configurada de la empresa' }}</p>
                        @if(!empty($retiroLocal['horario']))
                            <p class="text-[11px] text-slate-400">Horario: {{ $retiroLocal['horario'] }}</p>
                        @endif
                    </div>

                    <!-- 2. DELIVERY A DOMICILIO -->
                    <div x-show="tipoEntrega === 'delivery'" class="space-y-3 pt-1">
                        <template x-if="zonaEnvioId && obtenerZonaDeliverySeleccionada()">
                            <div class="p-3.5 bg-emerald-50/50 dark:bg-emerald-950/20 rounded-xl border border-emerald-200 dark:border-emerald-800 text-xs space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-emerald-700 dark:text-emerald-400 text-xs uppercase tracking-wider flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-[16px]">local_shipping</span>
                                        <span>Delivery: <span x-text="obtenerZonaDeliverySeleccionada().nombre"></span></span>
                                    </span>
                                    <button type="button" @click="abrirModalEntrega('delivery')" class="text-xs text-emerald-700 dark:text-emerald-400 font-semibold hover:underline">
                                        Cambiar
                                    </button>
                                </div>
                                <p class="text-slate-700 dark:text-slate-300 font-medium" x-text="direccionExacta || 'Sin dirección exacta especificada'"></p>
                                <div class="pt-2 border-t border-emerald-200/70 dark:border-emerald-800/60 flex justify-between items-center text-[11px]">
                                    <span class="text-slate-500">Peso: <strong class="text-slate-800 dark:text-slate-200" x-text="calcularPesoTotalLibras() + ' lbs'"></strong></span>
                                    <span class="font-bold text-emerald-800 dark:text-emerald-300" x-text="'Costo: $' + parseFloat(costoEnvio || 0).toFixed(2) + ' USD'"></span>
                                </div>
                            </div>
                        </template>

                        <template x-if="!zonaEnvioId">
                            <div class="p-4 border-2 border-dashed border-emerald-200 dark:border-emerald-900/50 rounded-xl text-center bg-emerald-50/30 dark:bg-emerald-950/10">
                                <span class="material-symbols-outlined text-emerald-600 text-3xl mb-1">home_pin</span>
                                <p class="text-xs font-semibold text-slate-800 dark:text-slate-200">Falta seleccionar la zona de Delivery</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-3">Configura la zona metropolitana y dirección de entrega.</p>
                                <button type="button" 
                                        @click="abrirModalEntrega('delivery')" 
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-colors">
                                    <span class="material-symbols-outlined text-[16px]">add_location_alt</span>
                                    <span>Configurar Delivery</span>
                                </button>
                            </div>
                        </template>
                    </div>

                    <!-- 3. RETIRO EN SUCURSAL DE COURIER -->
                    <div x-show="tipoEntrega === 'retiro_courier'" class="space-y-3 pt-1">
                        <template x-if="courierSucursalId && obtenerSucursalCourierSeleccionada()">
                            <div class="p-3.5 bg-[#fff8f5] dark:bg-[#2a1b16] rounded-xl border border-[#ffd2c2] dark:border-[#52291d] text-xs space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-[#ff6b3d] text-xs uppercase tracking-wider" x-text="'Courier: ' + obtenerSucursalCourierSeleccionada().courier"></span>
                                    <button type="button" @click="abrirModalEntrega('retiro_courier')" class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold hover:underline">
                                        Cambiar
                                    </button>
                                </div>
                                <p class="font-bold text-slate-900 dark:text-white" x-text="obtenerSucursalCourierSeleccionada().sucursal"></p>
                                <p class="text-slate-600 dark:text-slate-300" x-text="obtenerSucursalCourierSeleccionada().direccion"></p>
                                <div class="pt-2 border-t border-[#ffe0d4] dark:border-[#402016] flex justify-between items-center text-[11px]">
                                    <span class="text-slate-500">Peso: <strong class="text-slate-800 dark:text-slate-200" x-text="calcularPesoTotalLibras() + ' lbs'"></strong></span>
                                    <span class="font-bold text-slate-900 dark:text-white" x-text="'Tarifa: $' + parseFloat(costoEnvio || 0).toFixed(2) + ' USD'"></span>
                                </div>
                            </div>
                        </template>

                        <template x-if="!courierSucursalId">
                            <div class="p-4 border-2 border-dashed border-orange-200 dark:border-orange-900/50 rounded-xl text-center bg-orange-50/40 dark:bg-orange-950/20">
                                <span class="material-symbols-outlined text-[#ff6b3d] text-3xl mb-1">local_shipping</span>
                                <p class="text-xs font-semibold text-slate-800 dark:text-slate-200">Falta seleccionar la sucursal de Courier</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-3">Elige entre UnoExpress, Chavale, ServiEntrega u otros.</p>
                                <button type="button" 
                                        @click="abrirModalEntrega('retiro_courier')" 
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#ff6b3d] hover:bg-[#e05a2e] text-white rounded-lg text-xs font-bold transition-colors">
                                    <span class="material-symbols-outlined text-[16px]">add_location_alt</span>
                                    <span>Seleccionar Sucursal</span>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- SECCIÓN 5: FORMA DE PAGO -->
                <div class="card-elevated rounded-xl p-5 border border-slate-200/80 dark:border-gray-700/80 bg-white dark:bg-[#181a1b]">
                    <div class="pb-3 mb-4 border-b border-slate-100 dark:border-gray-800 flex items-center justify-between">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">Forma de Pago</h2>
                        <span class="text-xs font-semibold uppercase text-emerald-600 dark:text-emerald-400" x-text="metodoPago.replace('_', ' ')"></span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                        <!-- Efectivo -->
                        <button type="button" 
                                @click="seleccionarMetodoPago('efectivo')"
                                :class="metodoPago === 'efectivo' ? 'border-emerald-600 bg-emerald-50/60 dark:bg-emerald-950/20 ring-2 ring-emerald-600/20 shadow-xs' : 'border-slate-200 dark:border-gray-700 hover:border-slate-300 dark:hover:border-gray-600 bg-white dark:bg-gray-800/40'"
                                class="py-2.5 px-3 rounded-xl border text-xs transition-all flex items-center justify-center min-h-[64px] h-[64px] overflow-hidden cursor-pointer select-none"
                                title="Pago en Efectivo">
                            <img src="{{ asset('images/efectivo.webp') }}" alt="Efectivo" class="h-9 w-auto max-w-[85%] object-contain" style="max-height: 36px;">
                        </button>

                        <!-- Punto de Venta / Tarjeta (Visa, Mastercard, Clave) -->
                        <button type="button" 
                                @click="seleccionarMetodoPago('punto_venta')"
                                :class="metodoPago === 'punto_venta' ? 'border-emerald-600 bg-emerald-50/60 dark:bg-emerald-950/20 ring-2 ring-emerald-600/20 shadow-xs' : 'border-slate-200 dark:border-gray-700 hover:border-slate-300 dark:hover:border-gray-600 bg-white dark:bg-gray-800/40'"
                                class="py-2 px-2 rounded-xl border text-xs transition-all flex items-center justify-center min-h-[64px] h-[64px] overflow-hidden cursor-pointer select-none"
                                title="Tarjeta de Débito / Crédito / Clave">
                            <div class="flex items-center justify-center gap-1.5 w-full max-w-full px-1 overflow-hidden">
                                <img src="{{ asset('images/visa-logo.png') }}" alt="Visa" class="w-auto object-contain shrink-0" style="height: 13px; max-height: 13px; max-width: 28%;">
                                <img src="{{ asset('images/mastercard-logo.png') }}" alt="Mastercard" class="w-auto object-contain shrink-0" style="height: 18px; max-height: 18px; max-width: 28%;">
                                <img src="{{ asset('images/clave-logo.png') }}" alt="Clave" class="w-auto object-contain rounded-xs shrink-0" style="height: 14px; max-height: 14px; max-width: 34%;">
                            </div>
                        </button>

                        <!-- Yappy -->
                        <button type="button" 
                                @click="seleccionarMetodoPago('yappy')"
                                :class="metodoPago === 'yappy' ? 'border-emerald-600 bg-emerald-50/60 dark:bg-emerald-950/20 ring-2 ring-emerald-600/20 shadow-xs' : 'border-slate-200 dark:border-gray-700 hover:border-slate-300 dark:hover:border-gray-600 bg-white dark:bg-gray-800/40'"
                                class="py-2.5 px-3 rounded-xl border text-xs transition-all flex items-center justify-center min-h-[64px] h-[64px] overflow-hidden cursor-pointer select-none"
                                title="Yappy Comercial">
                            <img src="{{ asset('images/pa-yappy.webp') }}" alt="Yappy" class="h-7 w-auto max-w-[85%] object-contain rounded-md" style="max-height: 28px;">
                        </button>

                        <!-- Transferencia Bancaria / ACH -->
                        <button type="button" 
                                @click="seleccionarMetodoPago('transferencia')"
                                :class="metodoPago === 'transferencia' ? 'border-emerald-600 bg-emerald-50/60 dark:bg-emerald-950/20 ring-2 ring-emerald-600/20 shadow-xs' : 'border-slate-200 dark:border-gray-700 hover:border-slate-300 dark:hover:border-gray-600 bg-white dark:bg-gray-800/40'"
                                class="py-2.5 px-3 rounded-xl border text-xs transition-all flex items-center justify-center min-h-[64px] h-[64px] overflow-hidden cursor-pointer select-none"
                                title="ACH / Transferencia Bancaria">
                            <img src="{{ asset('images/ach.webp') }}" alt="ACH / Transferencia" class="h-9 w-auto max-w-[85%] object-contain" style="max-height: 36px;">
                        </button>

                        <!-- Contra Entrega -->
                        <button type="button" 
                                @click="seleccionarMetodoPago('contra_entrega')"
                                :class="metodoPago === 'contra_entrega' ? 'border-emerald-600 bg-emerald-50/60 dark:bg-emerald-950/20 ring-2 ring-emerald-600/20 shadow-xs' : 'border-slate-200 dark:border-gray-700 hover:border-slate-300 dark:hover:border-gray-600 bg-white dark:bg-gray-800/40'"
                                class="py-2.5 px-3 rounded-xl border text-xs transition-all flex items-center justify-center min-h-[64px] h-[64px] overflow-hidden cursor-pointer select-none col-span-2 sm:col-span-1"
                                title="Pago Contra Entrega">
                            <img src="{{ asset('images/contra-entrega-pago.webp') }}?v=2" alt="Contra Entrega" class="h-10 w-auto max-w-[85%] object-contain" style="max-height: 42px;">
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
                            <span class="font-semibold" x-text="tipoEntrega ? ('$' + parseFloat(costoEnvio || 0).toFixed(2)) : 'Pendiente'"></span>
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
                        <label class="flex items-center gap-2" :class="metodoPago === 'contra_entrega' ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer'">
                            <input type="checkbox" 
                                   name="marcar_pagado" 
                                   value="1" 
                                   x-model="marcarPagado" 
                                   :disabled="metodoPago === 'contra_entrega'"
                                   class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span class="font-semibold text-slate-700 dark:text-slate-300">Marcar como pagado inmediatamente</span>
                        </label>
                        <p x-show="metodoPago === 'contra_entrega'" class="text-[11px] text-amber-600 dark:text-amber-400 pl-6">
                            El cobro se gestionará al momento de la entrega de la orden.
                        </p>

                        <!-- Factura fiscal generada por defecto -->
                        <input type="hidden" name="emitir_factura" value="1">
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
    <template x-teleport="body">
        <div x-show="modalNuevoCliente" 
             x-cloak
             class="fixed inset-0 z-[9999] overflow-y-auto flex items-center justify-center p-3 sm:p-6" 
             aria-labelledby="modal-nuevo-cliente-title" 
             role="dialog" 
             aria-modal="true"
             @keydown.escape.window="cerrarModalNuevoCliente()"
             style="display: none;">
            
            <!-- Backdrop con desenfoque suave a pantalla completa -->
            <div x-show="modalNuevoCliente"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/75 backdrop-blur-xs transition-opacity"
                 @click="cerrarModalNuevoCliente()"></div>

            <div class="relative w-full sm:max-w-md my-auto z-10 flex items-center justify-center">
                <div x-show="modalNuevoCliente"
                     x-transition:enter="ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-[#1e2122] text-left shadow-2xl transition-all w-full border border-slate-200 dark:border-gray-700">
                    
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
                                           class="block w-full rounded-xl border-slate-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white py-2 px-3 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                           required>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                        Apellido
                                    </label>
                                    <input type="text" 
                                           x-model="formModal.apellido" 
                                           class="block w-full rounded-xl border-slate-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white py-2 px-3 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    Correo Electrónico <span class="text-rose-500">*</span>
                                </label>
                                <input type="email" 
                                       x-model="formModal.email" 
                                       class="block w-full rounded-xl border-slate-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white py-2 px-3 text-sm focus:border-emerald-500 focus:ring-emerald-500"
                                       required>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    Teléfono / WhatsApp
                                </label>
                                <input type="tel" 
                                       x-model="formModal.telefono" 
                                       class="block w-full rounded-xl border-slate-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white py-2 px-3 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                        </div>

                        <!-- Pie del modal -->
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
    </template>
    <!-- ========================================== -->
    <!-- MODAL: SELECCIONAR TIPO DE ENTREGA         -->
    <!-- ========================================== -->
    <template x-teleport="body">
        <div x-show="modalEntrega" 
             x-cloak
             class="fixed inset-0 z-[9999] overflow-y-auto flex items-center justify-center p-3 sm:p-6" 
             aria-labelledby="modal-entrega-title" 
             role="dialog" 
             aria-modal="true"
             @keydown.escape.window="cerrarModalEntrega()"
             style="display: none;">
            
            <div x-show="modalEntrega"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/75 backdrop-blur-xs transition-opacity"
                 @click="cerrarModalEntrega()"></div>

        <div class="relative w-full sm:max-w-2xl my-auto z-10 flex items-center justify-center">
            <div x-show="modalEntrega"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-[#1e2122] text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-slate-200 dark:border-gray-700">
                
                <!-- Cabecera del Modal -->
                <div class="px-6 pt-5 pb-4 border-b border-slate-100 dark:border-gray-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-white" id="modal-entrega-title">
                            Selecciona cómo deseas recibir tu pedido
                        </h3>
                    </div>
                    <button type="button" 
                            @click="cerrarModalEntrega()" 
                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-lg transition-colors">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <div class="p-6">
                    <!-- Tabs de métodos -->
                    <div class="flex space-x-1 bg-slate-100 dark:bg-gray-800 p-1 rounded-lg mb-6">
                        <button type="button" 
                                @click="modalMetodoEntrega = 'retiro_local'" 
                                :class="modalMetodoEntrega === 'retiro_local' ? 'bg-white dark:bg-gray-700 text-emerald-600 dark:text-emerald-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-gray-700/50'"
                                class="flex-1 py-2 text-sm font-medium rounded-md transition-colors">
                            Retiro en Local
                        </button>
                        <button type="button" 
                                @click="modalMetodoEntrega = 'delivery'" 
                                :class="modalMetodoEntrega === 'delivery' ? 'bg-white dark:bg-gray-700 text-emerald-600 dark:text-emerald-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-gray-700/50'"
                                class="flex-1 py-2 text-sm font-medium rounded-md transition-colors">
                            Delivery
                        </button>
                        <button type="button" 
                                @click="modalMetodoEntrega = 'retiro_courier'" 
                                :class="modalMetodoEntrega === 'retiro_courier' ? 'bg-white dark:bg-gray-700 text-emerald-600 dark:text-emerald-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-gray-700/50'"
                                class="flex-1 py-2 text-sm font-medium rounded-md transition-colors">
                            Envío por Courier
                        </button>
                    </div>

                    <!-- Contenido según pestaña -->
                    <div class="min-h-[220px]">
                        <!-- PESTAÑA 0: SIN SELECCIÓN EN EL MODAL -->
                        <div x-show="!modalMetodoEntrega" class="py-12 text-center">
                            <span class="material-symbols-outlined text-4xl text-slate-300 dark:text-slate-600 mb-2">touch_app</span>
                            <p class="text-xs sm:text-sm font-semibold text-slate-700 dark:text-slate-300">Selecciona una de las 3 opciones superiores</p>
                            <p class="text-[11px] text-slate-400 mt-1">Elige entre Retiro en Local, Delivery a Domicilio o Envío por Courier para configurar.</p>
                        </div>

                        <!-- TAB 1: RETIRO EN LOCAL -->
                        <div x-show="modalMetodoEntrega === 'retiro_local'">
                            <div class="bg-blue-50 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/50 p-4 rounded-xl mb-4 text-xs sm:text-sm">
                                <h4 class="font-bold text-blue-900 dark:text-blue-300 mb-1 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[18px]">storefront</span>
                                    <span>{{ $retiroLocal['sucursal'] ?? 'Retiro en Sucursal' }}</span>
                                </h4>
                                <p class="text-blue-800 dark:text-blue-300/90 mt-1">Dirección: {{ $retiroLocal['direccion'] ?? 'Dirección configurada de la empresa' }}</p>
                                @if(!empty($retiroLocal['horario']))
                                    <p class="text-blue-800 dark:text-blue-300/90 mt-1">Horario: {{ $retiroLocal['horario'] }}</p>
                                @endif
                                @if(!empty($retiroLocal['telefono']))
                                    <p class="text-blue-800 dark:text-blue-300/90 mt-1">Teléfono: {{ $retiroLocal['telefono'] }}</p>
                                @endif
                                <div class="mt-3 pt-2 border-t border-blue-200 dark:border-blue-800 font-bold text-emerald-700 dark:text-emerald-400">
                                    Costo de Retiro: GRATIS ($0.00)
                                </div>
                            </div>
                        </div>

                        <!-- PESTAÑA 2: ENTREGA A DOMICILIO -->
                        <div x-show="modalMetodoEntrega === 'delivery'" class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    Zona de Envío <span class="text-rose-500">*</span>
                                </label>
                                <select x-model="modalZonaEnvioId" class="block w-full text-xs sm:text-sm rounded-xl border-slate-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white py-2 px-3 focus:border-emerald-500 focus:ring-emerald-500">
                                    <option value="">Selecciona la zona...</option>
                                    <template x-for="z in zonasEnvio" :key="z.id">
                                        <option :value="z.id" x-text="z.nombre + ' (Tarifa base: $' + parseFloat(z.costo).toFixed(2) + ')'"></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                    Dirección Exacta
                                </label>
                                <textarea x-model="modalDireccionExacta" rows="3" placeholder="Calle, número de casa/edificio, corregimiento, punto de referencia..." class="block w-full text-xs sm:text-sm rounded-xl border-slate-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white py-2 px-3 focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                            </div>

                            <div class="p-3 bg-slate-50 dark:bg-gray-800/50 rounded-xl border border-slate-200 dark:border-gray-700 flex justify-between items-center text-xs">
                                <span class="text-slate-500">Peso total de items en venta:</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200 font-mono" x-text="calcularPesoTotalLibras() + ' lbs'"></span>
                            </div>
                        </div>

                        <!-- TAB 3: ENVÍO POR COURIER -->
                        <div x-show="modalMetodoEntrega === 'retiro_courier'" class="space-y-4">
                            <h3 class="text-base font-bold text-emerald-600 dark:text-emerald-400 mb-2">¿En cuál zona?</h3>
                            <div class="flex items-center gap-3">
                                <label class="font-bold text-slate-800 dark:text-slate-200 w-20 text-xs sm:text-sm shrink-0">Zona:</label>
                                <select x-model="modalZonaCourier" @change="alCambiarZonaCourierModal()" class="flex-1 border border-slate-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg p-2 focus:ring-emerald-500 focus:border-emerald-500 text-xs sm:text-sm">
                                    <option value="">Seleccione una zona</option>
                                    <template x-for="z in zonasCourier" :key="z">
                                        <option :value="z" x-text="z"></option>
                                    </template>
                                </select>
                                <span class="text-blue-500 text-xs whitespace-nowrap opacity-60">Ver mapa</span>
                            </div>

                            <h3 class="text-base font-bold text-emerald-600 dark:text-emerald-400 pt-2 mb-2">¿Con cuál courier?</h3>
                            <div class="flex items-center gap-3">
                                <label class="font-bold text-slate-800 dark:text-slate-200 w-20 text-xs sm:text-sm shrink-0">Courier:</label>
                                <select x-model="modalCourier" @change="alCambiarCourierModal()" :disabled="!modalZonaCourier" class="flex-1 border border-slate-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-lg p-2 focus:ring-emerald-500 focus:border-emerald-500 text-xs sm:text-sm disabled:opacity-50">
                                    <option value="">Por favor seleccione</option>
                                    <template x-for="c in couriersDisponiblesEnModal()" :key="c">
                                        <option :value="c" x-text="c"></option>
                                    </template>
                                </select>
                            </div>

                            <h3 class="text-base font-bold text-emerald-600 dark:text-emerald-400 pt-2 mb-2">¿En cuál sucursal?</h3>
                            <div class="space-y-2.5 max-h-56 overflow-y-auto pr-1">
                                <template x-if="!modalZonaCourier || !modalCourier">
                                    <div class="bg-cyan-50 dark:bg-cyan-950/30 border border-cyan-100 dark:border-cyan-900/50 text-cyan-800 dark:text-cyan-300 p-4 rounded-lg text-xs sm:text-sm">
                                        Por Favor selecciona el Courier
                                    </div>
                                </template>

                                <template x-if="modalZonaCourier && modalCourier">
                                    <div class="space-y-2">
                                        <template x-for="suc in sucursalesDisponiblesEnModal()" :key="suc.id">
                                            <label class="block cursor-pointer relative">
                                                <input type="radio" name="modal_sucursal_radio" :value="suc.id" x-model="modalSucursalId" class="peer sr-only" />
                                                <div class="p-3.5 border-2 rounded-xl transition-all"
                                                     :class="modalSucursalId == suc.id ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/30 shadow-xs' : 'border-slate-200 dark:border-gray-700 hover:border-slate-300 dark:hover:border-gray-600'">
                                                    <div>
                                                        <div class="font-bold text-slate-900 dark:text-white text-xs sm:text-sm mb-0.5" x-text="suc.sucursal"></div>
                                                        <div class="text-xs text-slate-500 dark:text-slate-400" x-text="suc.direccion"></div>
                                                        <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold mt-1" x-text="'Tarifa: $' + parseFloat(suc.tarifa_uno_hasta_7lb).toFixed(2) + ' USD (hasta 7 lbs)'"></div>
                                                    </div>
                                                </div>
                                            </label>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Pie del modal -->
                    <div class="mt-6 pt-4 border-t border-slate-100 dark:border-gray-800 flex justify-end gap-3">
                        <button type="button" 
                                @click="cerrarModalEntrega()" 
                                class="px-5 py-2.5 text-xs sm:text-sm font-semibold text-slate-600 dark:text-slate-300 bg-slate-100 dark:bg-gray-800 hover:bg-slate-200 dark:hover:bg-gray-700 rounded-xl transition-colors">
                            Cancelar
                        </button>
                        <button type="button" 
                                @click="confirmarModalEntrega()" 
                                class="px-5 py-2.5 text-xs sm:text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition-colors">
                            Confirmar Selección
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>
</div>

@push('scripts')
<script>
    function posVentas(clientes, productos, zonasEnvio, courierSucursales, zonasCourier) {
        return {
            clientes: clientes || [],
            productos: (productos || []).map(p => ({ ...p, varianteSeleccionadaId: '', peso: parseFloat(p.peso || 0) })),
            zonasEnvio: zonasEnvio || [],
            courierSucursales: courierSucursales || [],
            zonasCourier: zonasCourier || [],

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

            // Estado de Entrega y Logística (por defecto ninguno seleccionado)
            tipoEntrega: '',
            zonaEnvioId: '',
            courierSucursalId: '',
            costoEnvio: 0.00,
            direccionExacta: '',

            // Estado del modal de entrega
            modalEntrega: false,
            modalMetodoEntrega: '',
            modalZonaCourier: '',
            modalCourier: '',
            modalSucursalId: '',
            modalZonaEnvioId: '',
            modalDireccionExacta: '',

            busquedaProducto: '',
            paginaProducto: 1,
            porPaginaProducto: 15,
            cargandoProductos: true,
            items: [],

            metodoPago: 'efectivo',
            descuentoManual: 0.00,
            marcarPagado: true,
            emitirFactura: true,
            notasInternas: '',
            procesando: false,

            seleccionarMetodoPago(metodo) {
                this.metodoPago = metodo;
                if (metodo === 'contra_entrega') {
                    this.marcarPagado = false;
                } else {
                    this.marcarPagado = true;
                }
            },

            // Métodos del modal de entrega
            abrirModalEntrega(metodo) {
                this.modalMetodoEntrega = metodo || this.tipoEntrega || '';
                this.modalZonaEnvioId = this.zonaEnvioId;
                this.modalDireccionExacta = this.direccionExacta;
                this.modalSucursalId = this.courierSucursalId;

                if (this.courierSucursalId) {
                    const suc = this.courierSucursales.find(s => s.id == this.courierSucursalId);
                    if (suc) {
                        this.modalZonaCourier = suc.zona;
                        this.modalCourier = suc.courier;
                    }
                } else {
                    this.modalZonaCourier = this.modalZonaCourier || '';
                    this.modalCourier = this.modalCourier || '';
                }

                this.modalEntrega = true;
            },

            cerrarModalEntrega() {
                this.modalEntrega = false;
            },

            confirmarModalEntrega() {
                if (!this.modalMetodoEntrega) {
                    if (typeof window.mostrarToast === 'function') {
                        window.mostrarToast('Por favor selecciona un Tipo de Entrega en las pestañas superiores.', 'warning');
                    } else {
                        alert('Por favor selecciona un Tipo de Entrega en las pestañas superiores.');
                    }
                    return;
                }

                this.tipoEntrega = this.modalMetodoEntrega;

                if (this.tipoEntrega === 'retiro_local') {
                    this.courierSucursalId = '';
                    this.costoEnvio = 0.00;
                } else if (this.tipoEntrega === 'delivery') {
                    if (!this.modalZonaEnvioId) {
                        if (typeof window.mostrarToast === 'function') {
                            window.mostrarToast('Por favor selecciona una zona de Delivery.', 'warning');
                        } else {
                            alert('Por favor selecciona una zona de Delivery.');
                        }
                        return;
                    }
                    this.zonaEnvioId = this.modalZonaEnvioId;
                    this.direccionExacta = this.modalDireccionExacta;
                    this.courierSucursalId = '';
                    this.recalcularEnvio();
                } else if (this.tipoEntrega === 'retiro_courier') {
                    if (!this.modalSucursalId) {
                        if (typeof window.mostrarToast === 'function') {
                            window.mostrarToast('Por favor selecciona una sucursal de Courier.', 'warning');
                        } else {
                            alert('Por favor selecciona una sucursal de Courier.');
                        }
                        return;
                    }
                    this.courierSucursalId = this.modalSucursalId;
                    this.recalcularEnvio();
                }

                this.modalEntrega = false;
            },

            seleccionarMetodoEntrega(metodo) {
                this.tipoEntrega = metodo;
                if (metodo === 'retiro_local') {
                    this.costoEnvio = 0.00;
                    this.courierSucursalId = '';
                } else if (metodo === 'retiro_courier' && !this.courierSucursalId) {
                    this.abrirModalEntrega('retiro_courier');
                } else if (metodo === 'delivery' && !this.zonaEnvioId) {
                    this.abrirModalEntrega('delivery');
                } else {
                    this.recalcularEnvio();
                }
            },

            alCambiarZonaDelivery() {
                this.recalcularEnvio();
            },

            alCambiarZonaCourierModal() {
                this.modalCourier = '';
                this.modalSucursalId = '';
            },

            alCambiarCourierModal() {
                this.modalSucursalId = '';
            },

            couriersDisponiblesEnModal() {
                if (!this.modalZonaCourier) return [];
                const unicos = new Set();
                this.courierSucursales.forEach(s => {
                    if (s.zona === this.modalZonaCourier) {
                        unicos.add(s.courier);
                    }
                });
                return Array.from(unicos);
            },

            sucursalesDisponiblesEnModal() {
                if (!this.modalZonaCourier || !this.modalCourier) return [];
                return this.courierSucursales.filter(s => s.zona === this.modalZonaCourier && s.courier === this.modalCourier);
            },

            obtenerZonaDeliverySeleccionada() {
                if (!this.zonaEnvioId) return null;
                return this.zonasEnvio.find(z => z.id == this.zonaEnvioId) || null;
            },

            obtenerSucursalCourierSeleccionada() {
                if (!this.courierSucursalId) return null;
                return this.courierSucursales.find(s => s.id == this.courierSucursalId) || null;
            },

            // Cálculo dinámico de peso acumulado en libras
            calcularPesoTotalLibras() {
                let pesoTotalKg = 0;
                for (const item of this.items) {
                    const p = this.productos.find(pr => pr.id === item.producto_id);
                    const pesoUnitario = item.peso || (p ? p.peso : 0) || 0;
                    pesoTotalKg += (pesoUnitario * item.cantidad);
                }
                return Math.ceil(pesoTotalKg * 2.20462);
            },

            // Recalcular tarifa de envío según método y peso de items
            recalcularEnvio() {
                if (this.tipoEntrega === 'retiro_local') {
                    this.costoEnvio = 0.00;
                    return;
                }

                const pesoLbs = this.calcularPesoTotalLibras();

                if (this.tipoEntrega === 'delivery') {
                    if (!this.zonaEnvioId) {
                        this.costoEnvio = 0.00;
                        return;
                    }
                    const z = this.zonasEnvio.find(item => item.id == this.zonaEnvioId);
                    if (!z) {
                        this.costoEnvio = 0.00;
                        return;
                    }
                    const baseCosto = parseFloat(z.costo || 0);
                    if (pesoLbs > 10) {
                        const extra = (pesoLbs - 10) * 0.50;
                        this.costoEnvio = parseFloat((baseCosto + extra).toFixed(2));
                    } else {
                        this.costoEnvio = parseFloat(baseCosto.toFixed(2));
                    }
                    return;
                }

                if (this.tipoEntrega === 'retiro_courier') {
                    if (!this.courierSucursalId) {
                        this.costoEnvio = 0.00;
                        return;
                    }
                    const suc = this.courierSucursales.find(s => s.id == this.courierSucursalId);
                    if (!suc) {
                        this.costoEnvio = 0.00;
                        return;
                    }
                    const baseCosto = parseFloat(suc.tarifa_uno_hasta_7lb || 0);
                    if (pesoLbs > 7) {
                        const extra = (pesoLbs - 7) * 0.50;
                        this.costoEnvio = parseFloat((baseCosto + extra).toFixed(2));
                    } else {
                        this.costoEnvio = parseFloat(baseCosto.toFixed(2));
                    }
                    return;
                }
            },

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
                        if (typeof window.mostrarToast === 'function') {
                            window.mostrarToast(`Cliente "${data.cliente.nombre}" registrado y seleccionado`, 'success');
                        }
                    }
                } catch (err) {
                    this.errorModalCliente = err.message || 'Error de conexión al registrar cliente.';
                } finally {
                    this.guardandoCliente = false;
                }
            },

            init() {
                this.iniciarLectorPistola();
                // Transición suave y serena al cargar la vista para apreciar el skeleton
                setTimeout(() => {
                    this.cargandoProductos = false;
                }, 450);
            },

            iniciarLectorPistola() {
                let buffer = '';
                let lastTime = 0;

                window.addEventListener('keydown', (e) => {
                    const active = document.activeElement;
                    const isFormInput = active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.tagName === 'SELECT');
                    const isProductSearch = active && active.id === 'input-buscar-producto';

                    // Si está escribiendo en campos de cliente o notas, ignorar pistola
                    if (isFormInput && !isProductSearch) return;

                    const now = Date.now();
                    if (now - lastTime > 150) {
                        buffer = '';
                    }
                    lastTime = now;

                    if (e.key === 'Enter') {
                        if (buffer.length >= 3) {
                            e.preventDefault();
                            this.procesarCodigoEscaner(buffer);
                            buffer = '';
                        }
                    } else if (e.key.length === 1) {
                        buffer += e.key;
                    }
                });
            },

            abrirEscanerCamara() {
                if (window.ModalEscaner && typeof window.ModalEscaner.abrir === 'function') {
                    window.ModalEscaner.abrir((codigo) => {
                        this.procesarCodigoEscaner(codigo);
                    });
                } else {
                    if (typeof window.mostrarToast === 'function') {
                        window.mostrarToast('El escáner de cámara no está disponible.', 'warning');
                    } else {
                        alert('El escáner de cámara no está disponible.');
                    }
                }
            },

            procesarCodigoEscaner(codigo) {
                if (!codigo || !codigo.trim()) return;
                const raw = codigo.trim();
                const q = raw.toLowerCase();
                this.busquedaProducto = raw;
                this.paginaProducto = 1;

                // 1. Buscar coincidencia exacta por SKU de producto o variante
                let productoEncontrado = null;
                let varianteEncontrada = null;

                for (const p of this.productos) {
                    if (p.sku && p.sku.toLowerCase() === q) {
                        productoEncontrado = p;
                        break;
                    }
                    if (p.variantes && p.variantes.length > 0) {
                        const vMatch = p.variantes.find(v => v.sku && v.sku.toLowerCase() === q);
                        if (vMatch) {
                            productoEncontrado = p;
                            varianteEncontrada = vMatch;
                            break;
                        }
                    }
                }

                if (productoEncontrado) {
                    if (varianteEncontrada) {
                        productoEncontrado.varianteSeleccionadaId = varianteEncontrada.id;
                        this.agregarAlCarrito(productoEncontrado);
                    } else if (productoEncontrado.variantes && productoEncontrado.variantes.length > 0) {
                        if (typeof window.mostrarToast === 'function') {
                            window.mostrarToast(`Producto "${productoEncontrado.nombre}" detectado. Selecciona una variante.`, 'info');
                        }
                    } else {
                        this.agregarAlCarrito(productoEncontrado);
                    }
                } else {
                    const filtrados = this.todosProductosFiltrados();
                    if (filtrados.length === 1) {
                        const unico = filtrados[0];
                        if (!unico.variantes || unico.variantes.length === 0) {
                            this.agregarAlCarrito(unico);
                        } else {
                            if (typeof window.mostrarToast === 'function') {
                                window.mostrarToast(`"${unico.nombre}" encontrado. Selecciona una variante.`, 'info');
                            }
                        }
                    } else if (filtrados.length === 0) {
                        if (typeof window.mostrarToast === 'function') {
                            window.mostrarToast(`Código "${raw}" no encontrado en catálogo.`, 'error');
                        }
                    } else {
                        if (typeof window.mostrarToast === 'function') {
                            window.mostrarToast(`Se encontraron ${filtrados.length} productos coincidentes con "${raw}".`, 'info');
                        }
                    }
                }
            },

            // Métodos de catálogo y paginación (15 items por página)
            todosProductosFiltrados() {
                const q = this.busquedaProducto.trim().toLowerCase();
                if (!q) return this.productos;
                return this.productos.filter(p => {
                    if (p.nombre && p.nombre.toLowerCase().includes(q)) return true;
                    if (p.sku && p.sku.toLowerCase().includes(q)) return true;
                    if (p.variantes && p.variantes.some(v => (v.sku && v.sku.toLowerCase().includes(q)) || (v.nombre && v.nombre.toLowerCase().includes(q)))) return true;
                    return false;
                });
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
                if (nueva >= 1 && nueva <= max && nueva !== this.paginaProducto && !this.cargandoProductos) {
                    this.cargandoProductos = true;
                    setTimeout(() => {
                        this.paginaProducto = nueva;
                        setTimeout(() => {
                            this.cargandoProductos = false;
                        }, 60);
                    }, 350);
                }
            },

            agregarAlCarrito(prod) {
                let varianteId = null;
                let varianteNombre = null;
                let precio = prod.precio;
                let stock = prod.stock;
                let peso = prod.peso || 0;

                if (prod.variantes && prod.variantes.length > 0) {
                    if (!prod.varianteSeleccionadaId) {
                        if (typeof window.mostrarToast === 'function') {
                            window.mostrarToast('Por favor selecciona una variante antes de agregar.', 'warning');
                        } else {
                            alert('Por favor selecciona una variante antes de agregar.');
                        }
                        return;
                    }
                    const v = prod.variantes.find(item => item.id == prod.varianteSeleccionadaId);
                    if (!v) return;
                    varianteId = v.id;
                    varianteNombre = v.nombre;
                    precio = v.precio;
                    stock = v.stock;
                    peso = v.peso || prod.peso || 0;
                }

                if (stock <= 0) {
                    if (typeof window.mostrarToast === 'function') {
                        window.mostrarToast('El producto seleccionado no cuenta con inventario disponible.', 'error');
                    } else {
                        alert('El producto seleccionado no cuenta con inventario disponible.');
                    }
                    return;
                }

                const key = prod.id + '_' + (varianteId || 'base');
                const index = this.items.findIndex(i => i.key === key);
                const nombreCompleto = prod.nombre + (varianteNombre ? ' (' + varianteNombre + ')' : '');

                if (index >= 0) {
                    if (this.items[index].cantidad + 1 > stock) {
                        if (typeof window.mostrarToast === 'function') {
                            window.mostrarToast('Stock máximo alcanzado (' + stock + ' disp.).', 'warning');
                        } else {
                            alert('No puedes agregar más unidades del stock disponible (' + stock + ').');
                        }
                        return;
                    }
                    this.items[index].cantidad += 1;

                    if (typeof window.mostrarToast === 'function') {
                        window.mostrarToast(`+1 "${nombreCompleto}" agregado (Total: ${this.items[index].cantidad})`, 'success');
                    }
                } else {
                    this.items.push({
                        key: key,
                        producto_id: prod.id,
                        variante_id: varianteId,
                        nombre: prod.nombre,
                        variante_nombre: varianteNombre,
                        precio: precio,
                        peso: peso,
                        cantidad: 1,
                        stock_disponible: stock,
                        imagen: prod.imagen
                    });

                    if (typeof window.mostrarToast === 'function') {
                        window.mostrarToast(`"${nombreCompleto}" agregado a la venta`, 'success');
                    }
                }

                this.recalcularEnvio();
            },

            cambiarCantidad(item, delta) {
                const nueva = item.cantidad + delta;
                if (nueva <= 0) return;
                if (nueva > item.stock_disponible) {
                    if (typeof window.mostrarToast === 'function') {
                        window.mostrarToast('Stock máximo alcanzado (' + item.stock_disponible + ' disp.).', 'warning');
                    } else {
                        alert('Stock máximo alcanzado (' + item.stock_disponible + ').');
                    }
                    return;
                }
                item.cantidad = nueva;
                this.recalcularEnvio();
            },

            validarCantidad(item) {
                if (item.cantidad <= 0 || isNaN(item.cantidad)) {
                    item.cantidad = 1;
                }
                if (item.cantidad > item.stock_disponible) {
                    item.cantidad = item.stock_disponible;
                    if (typeof window.mostrarToast === 'function') {
                        window.mostrarToast('Cantidad ajustada al stock disponible (' + item.stock_disponible + ').', 'warning');
                    } else {
                        alert('Cantidad ajustada al stock disponible (' + item.stock_disponible + ').');
                    }
                }
                this.recalcularEnvio();
            },

            eliminarItem(index) {
                const eliminado = this.items[index];
                this.items.splice(index, 1);
                if (eliminado && typeof window.mostrarToast === 'function') {
                    const nombre = eliminado.nombre + (eliminado.variante_nombre ? ' (' + eliminado.variante_nombre + ')' : '');
                    window.mostrarToast(`"${nombre}" removido de la venta`, 'info');
                }
                this.recalcularEnvio();
            },

            actualizarCostoZona() {
                this.recalcularEnvio();
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
                    if (typeof window.mostrarToast === 'function') {
                        window.mostrarToast('Debes agregar al menos un producto para registrar la venta.', 'warning');
                    } else {
                        alert('Debes agregar al menos un producto para registrar la venta.');
                    }
                    return;
                }

                if (!this.clienteSeleccionado) {
                    e.preventDefault();
                    if (typeof window.mostrarToast === 'function') {
                        window.mostrarToast('Por favor selecciona o crea un cliente para la venta.', 'warning');
                    } else {
                        alert('Por favor selecciona o crea un cliente para la venta.');
                    }
                    return;
                }

                if (!this.tipoEntrega) {
                    e.preventDefault();
                    if (typeof window.mostrarToast === 'function') {
                        window.mostrarToast('Por favor selecciona un Tipo de Entrega (Retiro en Local, Delivery o Courier).', 'warning');
                    } else {
                        alert('Por favor selecciona un Tipo de Entrega (Retiro en Local, Delivery o Courier).');
                    }
                    const sec = document.getElementById('seccion-tipo-entrega');
                    if (sec) sec.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }

                if (this.tipoEntrega === 'delivery' && !this.zonaEnvioId) {
                    e.preventDefault();
                    if (typeof window.mostrarToast === 'function') {
                        window.mostrarToast('Por favor selecciona una zona de Delivery.', 'warning');
                    } else {
                        alert('Por favor selecciona una zona de Delivery.');
                    }
                    this.abrirModalEntrega('delivery');
                    return;
                }

                if (this.tipoEntrega === 'retiro_courier' && !this.courierSucursalId) {
                    e.preventDefault();
                    if (typeof window.mostrarToast === 'function') {
                        window.mostrarToast('Por favor selecciona una sucursal de Courier.', 'warning');
                    } else {
                        alert('Por favor selecciona una sucursal de Courier.');
                    }
                    this.abrirModalEntrega('retiro_courier');
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
