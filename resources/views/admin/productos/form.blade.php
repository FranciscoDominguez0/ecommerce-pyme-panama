@extends('layouts.admin')

@section('title', ($esEdicion ?? false) ? 'Editar Producto' : 'Nuevo Producto')

@section('breadcrumbs')
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <a href="{{ route('admin.productos.index') }}"
        class="text-slate-500 hover:text-slate-900 transition-colors">Productos</a>
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <span class="font-bold text-slate-900 truncate">{{ ($esEdicion ?? false) ? 'Editar' : 'Nuevo' }}</span>
@endsection

@section('content')
    <div class="space-y-5 w-full min-w-0 max-w-full">

        <!-- Formulario Principal -->
        @if($esEdicion ?? false)
            <form id="form-producto" method="POST" action="{{ route('admin.productos.update', $id ?? $producto->id) }}"
                enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')
        @else
                <form id="form-producto" method="POST" action="{{ route('admin.productos.store') }}"
                    enctype="multipart/form-data" class="space-y-5">
                    @csrf
            @endif

                <!-- Header con Acciones Principales -->
                <div
                    class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-slate-200/80 pb-3">
                    <div>
                        <div class="flex items-center gap-2.5">
                            <div
                                class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-br from-emerald-500/20 to-teal-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-600 shadow-xs shrink-0">
                                <span
                                    class="material-symbols-outlined text-[22px]">{{ ($esEdicion ?? false) ? 'edit_note' : 'add_box' }}</span>
                            </div>
                            <div>
                                <h1 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight leading-tight">
                                    {{ ($esEdicion ?? false) ? 'Editar Registro de Artículo' : 'Nuevo Registro de Artículo' }}
                                </h1>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Ficha técnica del producto, precios, inventario y variantes.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-wrap">
                        <a href="{{ route('admin.productos.index') }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl text-xs font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-all shadow-xs">
                            <span class="material-symbols-outlined text-[17px] text-slate-400">arrow_back</span>
                            <span>Volver</span>
                        </a>

                        @if(($esEdicion ?? false) && !empty($producto->slug))
                            <a href="{{ route('cliente.producto.detalle', $producto->slug) }}" target="_blank"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 transition-all shadow-xs">
                                <span class="material-symbols-outlined text-[17px]">visibility</span>
                                <span>Ver en Tienda</span>
                            </a>
                        @endif

                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-1.5 sm:px-5 sm:py-2 bg-slate-900 hover:bg-slate-800 rounded-xl text-xs font-bold text-white shadow-sm transition-all transform active:scale-95">
                            <span class="material-symbols-outlined text-[17px]">check_circle</span>
                            <span>{{ ($esEdicion ?? false) ? 'Guardar Cambios' : 'Publicar Artículo' }}</span>
                        </button>
                    </div>
                </div>

                <!-- Barra Superior de Pestañas (Registro de Artículo style) -->
                <div class="bg-white border border-slate-200/90 rounded-2xl p-1.5 shadow-xs overflow-x-auto no-scrollbar">
                    <div class="flex items-center gap-1 min-w-max">
                        <button type="button" id="btn-tab-datos-generales" onclick="cambiarTabProducto('datos-generales')"
                            class="btn-tab-producto inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all bg-slate-900 text-white shadow-xs">
                            <span class="material-symbols-outlined text-[16px]">info</span>
                            <span>DATOS GENERALES</span>
                        </button>

                        <button type="button" id="btn-tab-precios-inventario"
                            onclick="cambiarTabProducto('precios-inventario')"
                            class="btn-tab-producto inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all bg-white text-slate-600 hover:bg-slate-100">
                            <span class="material-symbols-outlined text-[16px]">payments</span>
                            <span>PRECIOS E INVENTARIO</span>
                        </button>

                        <button type="button" id="btn-tab-descripcion" onclick="cambiarTabProducto('descripcion')"
                            class="btn-tab-producto inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all bg-white text-slate-600 hover:bg-slate-100">
                            <span class="material-symbols-outlined text-[16px]">description</span>
                            <span>DESCRIPCIÓN DETALLADA</span>
                        </button>

                        <button type="button" id="btn-tab-imagenes" onclick="cambiarTabProducto('imagenes')"
                            class="btn-tab-producto inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all bg-white text-slate-600 hover:bg-slate-100">
                            <span class="material-symbols-outlined text-[16px]">photo_library</span>
                            <span>IMÁGENES</span>
                        </button>

                        <button type="button" id="btn-tab-variantes" onclick="cambiarTabProducto('variantes')"
                            class="btn-tab-producto inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all bg-white text-slate-600 hover:bg-slate-100">
                            <span class="material-symbols-outlined text-[16px]">style</span>
                            <span>VARIANTES</span>
                        </button>

                        <button type="button" id="btn-tab-ver-todo" onclick="cambiarTabProducto('ver-todo')"
                            class="btn-tab-producto inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition-all bg-white text-slate-500 hover:bg-slate-100 border-l border-slate-200 ml-auto">
                            <span class="material-symbols-outlined text-[16px]">view_agenda</span>
                            <span>VER TODO</span>
                        </button>
                    </div>
                </div>

                <!-- ── CONTENIDO DE PESTAÑAS ── -->

                <!-- Pestaña 1: DATOS GENERALES -->
                <div id="tab-panel-datos-generales" class="tab-contenido-producto space-y-5">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

                        <!-- Columna Izquierda: Datos de Ficha de Artículo (8 cols) -->
                        <div class="lg:col-span-8 space-y-5">
                            <div class="card-elevated p-5 sm:p-6 rounded-2xl space-y-4">
                                <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                                    <span class="material-symbols-outlined text-emerald-600 text-[20px]">info</span>
                                    <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Información del
                                        Artículo</h2>
                                </div>

                                <div class="space-y-4">
                                    <!-- Fila 1: Nombre del Producto (8 cols) + SKU Base (4 cols) -->
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                        <div class="md:col-span-8">
                                            <label for="nombre"
                                                class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                                Nombre del Producto <span class="text-rose-500">*</span>
                                            </label>
                                            <input type="text" id="nombre" name="nombre" required
                                                value="{{ old('nombre', $producto->nombre ?? '') }}"
                                                placeholder="Ej. Enrutador Inalámbrico TP-Link ARCHER AX23"
                                                class="input-panama w-full text-sm rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 py-2.5 px-3 font-medium">
                                        </div>

                                        <div class="md:col-span-4">
                                            <label for="sku"
                                                class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                                SKU Base <span class="text-rose-500">*</span>
                                            </label>
                                            <input type="text" id="sku" name="sku" required
                                                value="{{ old('sku', $producto->sku ?? '') }}" placeholder="PRDO-13"
                                                class="input-panama w-full text-xs font-mono uppercase rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 py-2.5 px-3">
                                        </div>
                                    </div>

                                    <!-- Fila 2: Slug (8 cols) + Modelo Técnico (4 cols) -->
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                        <div class="md:col-span-8">
                                            <div class="flex items-center justify-between mb-1">
                                                <label for="slug"
                                                    class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                                                    Slug / Enlace Permanente <span class="text-rose-500">*</span>
                                                </label>
                                                <button type="button" onclick="regenerarSlugDesdeNombre()"
                                                    class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 hover:text-emerald-800 transition-colors cursor-pointer group">
                                                    <span id="icono-autorenew-slug"
                                                        class="material-symbols-outlined text-[15px] group-hover:rotate-180 transition-transform duration-300">autorenew</span>
                                                    <span>Regenerar desde nombre</span>
                                                </button>
                                            </div>
                                            <div class="relative flex items-center">
                                                <span
                                                    class="absolute left-3 text-xs text-slate-400 pointer-events-none select-none font-mono hidden sm:inline">tutienda.com/producto/</span>
                                                <input type="text" id="slug" name="slug" required
                                                    value="{{ old('slug', $producto->slug ?? '') }}"
                                                    placeholder="enrutador-inalambrico-tp-link-archer-ax23"
                                                    class="input-panama w-full sm:pl-44 pr-3 py-2 text-xs font-mono rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 text-slate-800">
                                            </div>
                                        </div>

                                        <div class="md:col-span-4">
                                            <label for="modelo"
                                                class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                                Modelo Técnico
                                            </label>
                                            <input type="text" id="modelo" name="modelo"
                                                value="{{ old('modelo', $producto->modelo ?? '') }}"
                                                placeholder="ARCHER AX23"
                                                class="input-panama w-full text-xs font-mono rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 py-2.5 px-3">
                                            <p class="text-[10px] text-slate-400 mt-1">
                                                Modelo de fábrica exacto.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Fila 3: Categoría y Marca -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <!-- Categoría -->
                                        <div>
                                            <label
                                                class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                                Categoría <span class="text-rose-500">*</span>
                                            </label>
                                            <input type="hidden" id="input-categoria-valor" name="categoria_id" required
                                                value="{{ old('categoria_id', $producto->categoria_id ?? '') }}">

                                            <div id="contenedor-categoria-card" class="relative">
                                                <div id="card-categoria-activa"
                                                    class="hidden items-center justify-between p-1.5 bg-slate-50 border border-slate-200 rounded-xl hover:border-slate-300 transition-all h-[44px]">
                                                    <div class="flex items-center gap-2 min-w-0">
                                                        <div id="display-categoria-logo"
                                                            class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center shrink-0 overflow-hidden p-0.5 shadow-2xs">
                                                            <span
                                                                class="material-symbols-outlined text-[18px]">category</span>
                                                        </div>
                                                        <div class="min-w-0">
                                                            <div id="display-categoria-nombre"
                                                                class="text-xs font-bold text-slate-900 truncate">Redes
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center gap-0.5 shrink-0">
                                                        <button type="button" onclick="abrirModalCategorias()"
                                                            class="px-2 py-1 text-[11px] font-semibold text-slate-700 hover:bg-slate-200 rounded-md transition-colors cursor-pointer flex items-center gap-0.5">
                                                            <span
                                                                class="material-symbols-outlined text-[13px]">sync_alt</span>
                                                            <span>Cambiar</span>
                                                        </button>
                                                        <button type="button" onclick="deseleccionarCategoria()"
                                                            class="p-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-md transition-colors cursor-pointer"
                                                            title="Quitar categoría">
                                                            <span class="material-symbols-outlined text-[15px]">close</span>
                                                        </button>
                                                    </div>
                                                </div>

                                                <button type="button" id="btn-abrir-modal-categorias"
                                                    onclick="abrirModalCategorias()"
                                                    class="w-full flex items-center justify-between px-3 bg-slate-50 hover:bg-slate-100/80 border border-dashed border-slate-300 hover:border-slate-400 rounded-xl transition-all group text-left cursor-pointer h-[44px]">
                                                    <div class="flex items-center gap-2">
                                                        <span
                                                            class="material-symbols-outlined text-slate-400 group-hover:text-slate-700 text-[18px]">category</span>
                                                        <span class="text-xs font-medium text-slate-600">Seleccionar
                                                            Categoría...</span>
                                                    </div>
                                                    <span
                                                        class="px-2 py-0.5 rounded-md bg-white border border-slate-200 text-slate-600 text-[10px] font-bold flex items-center gap-1 shadow-2xs group-hover:border-slate-300">
                                                        <span class="material-symbols-outlined text-[12px]">search</span>
                                                        <span>Explorar</span>
                                                    </span>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Marca -->
                                        <div>
                                            <label
                                                class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                                Marca (Fabricante)
                                            </label>
                                            <input type="hidden" id="input-marca-valor" name="marca"
                                                value="{{ old('marca', $producto->brand->name ?? ($producto->marca ?? '')) }}">

                                            <div id="contenedor-marca-card" class="relative">
                                                <div id="card-marca-activa"
                                                    class="hidden items-center justify-between p-1.5 bg-slate-50 border border-slate-200 rounded-xl hover:border-slate-300 transition-all h-[44px]">
                                                    <div class="flex items-center gap-2 min-w-0">
                                                        <div id="display-marca-logo"
                                                            class="w-9 h-7 bg-white border border-slate-200/80 rounded-md flex items-center justify-center p-0.5 shadow-2xs shrink-0 overflow-hidden">
                                                        </div>
                                                        <div class="min-w-0">
                                                            <div id="display-marca-nombre"
                                                                class="text-xs font-bold text-slate-900 truncate">Adata
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center gap-0.5 shrink-0">
                                                        <button type="button" onclick="abrirModalMarcas()"
                                                            class="px-2 py-1 text-[11px] font-semibold text-slate-700 hover:bg-slate-200 rounded-md transition-colors cursor-pointer flex items-center gap-0.5">
                                                            <span
                                                                class="material-symbols-outlined text-[13px]">sync_alt</span>
                                                            <span>Cambiar</span>
                                                        </button>
                                                        <button type="button" onclick="deseleccionarMarca()"
                                                            class="p-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-md transition-colors cursor-pointer"
                                                            title="Quitar marca">
                                                            <span class="material-symbols-outlined text-[15px]">close</span>
                                                        </button>
                                                    </div>
                                                </div>

                                                <button type="button" id="btn-abrir-modal-marcas"
                                                    onclick="abrirModalMarcas()"
                                                    class="w-full flex items-center justify-between px-3 bg-slate-50 hover:bg-slate-100/80 border border-dashed border-slate-300 hover:border-slate-400 rounded-xl transition-all group text-left cursor-pointer h-[44px]">
                                                    <div class="flex items-center gap-2">
                                                        <span
                                                            class="material-symbols-outlined text-slate-400 group-hover:text-slate-700 text-[18px]">verified</span>
                                                        <span class="text-xs font-medium text-slate-600">Seleccionar
                                                            Marca...</span>
                                                    </div>
                                                    <span
                                                        class="px-2 py-0.5 rounded-md bg-white border border-slate-200 text-slate-600 text-[10px] font-bold flex items-center gap-1 shadow-2xs group-hover:border-slate-300">
                                                        <span class="material-symbols-outlined text-[12px]">search</span>
                                                        <span>Explorar</span>
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Derecha: Estado & Visibilidad (4 cols) -->
                        <div class="lg:col-span-4 space-y-5">
                            <div class="card-elevated p-5 rounded-2xl space-y-4">
                                <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                                    <span class="material-symbols-outlined text-emerald-600 text-[20px]">toggle_on</span>
                                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Estado &
                                        Visibilidad</h3>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label for="activo"
                                            class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                            Estado del Producto
                                        </label>
                                        <select id="activo" name="activo"
                                            class="input-panama w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 py-2.5 px-3 font-semibold">
                                            <option value="1" @selected(old('activo', $producto->activo ?? true) == true)>
                                                Activo (Visible en tienda)</option>
                                            <option value="0" @selected(old('activo', $producto->activo ?? true) == false)>
                                                Inactivo (Borrador oculto)</option>
                                        </select>
                                    </div>

                                    <div class="space-y-2.5">
                                        <!-- Switch Destacado -->
                                        <div
                                            class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                                            <div class="space-y-0.5">
                                                <p class="text-xs font-bold text-slate-800 flex items-center gap-1">
                                                    <span
                                                        class="material-symbols-outlined text-amber-500 text-[16px]">star</span>
                                                    <span>Destacado</span>
                                                </p>
                                                <p class="text-[9px] text-slate-500">Sección destacados</p>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="destacado" value="1" class="sr-only peer"
                                                    @checked(old('destacado', $producto->destacado ?? false))>
                                                <div
                                                    class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-amber-500">
                                                </div>
                                            </label>
                                        </div>

                                        <!-- Switch ITBMS 7% -->
                                        <div
                                            class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                                            <div class="space-y-0.5">
                                                <p class="text-xs font-bold text-slate-800 flex items-center gap-1">
                                                    <span
                                                        class="material-symbols-outlined text-emerald-600 text-[16px]">receipt_long</span>
                                                    <span>ITBMS (7%)</span>
                                                </p>
                                                <p class="text-[9px] text-slate-500">Impuesto Panamá</p>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" name="aplica_itbms" value="1" class="sr-only peer"
                                                    @checked(old('aplica_itbms', $producto->aplica_itbms ?? true))>
                                                <div
                                                    class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600">
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Pestaña 2: PRECIOS E INVENTARIO -->
                <div id="tab-panel-precios-inventario" class="tab-contenido-producto space-y-5 hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

                        <!-- Precios & Rentabilidad (7 Cols) -->
                        <div class="lg:col-span-7 space-y-5">
                            <div class="card-elevated p-5 sm:p-6 rounded-2xl space-y-4">
                                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-emerald-600 text-[20px]">payments</span>
                                        <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Precios &
                                            Rentabilidad</h2>
                                    </div>
                                    <span
                                        class="text-[11px] font-semibold text-slate-500 bg-slate-100 px-2.5 py-0.5 rounded-full">USD
                                        ($)</span>
                                </div>

                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <!-- Precio Base -->
                                        <div>
                                            <label for="precio"
                                                class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                                Precio Base <span class="text-rose-500">*</span>
                                            </label>
                                            <div class="relative flex items-center">
                                                <span class="absolute left-3 text-xs font-bold text-slate-400">$</span>
                                                <input type="number" step="0.01" min="0" id="precio" name="precio" required
                                                    oninput="calcularMargen()"
                                                    value="{{ old('precio', $producto->precio ?? '134.04') }}"
                                                    placeholder="134.04"
                                                    class="input-panama w-full pl-7 pr-3 py-2.5 text-xs font-bold text-slate-900 rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20">
                                            </div>
                                        </div>

                                        <!-- Precio Oferta -->
                                        <div>
                                            <label for="precio_oferta"
                                                class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                                Precio Oferta
                                            </label>
                                            <div class="relative flex items-center">
                                                <span class="absolute left-3 text-xs font-bold text-slate-400">$</span>
                                                <input type="number" step="0.01" min="0" id="precio_oferta"
                                                    name="precio_oferta" oninput="calcularMargen()"
                                                    value="{{ old('precio_oferta', $producto->precio_oferta ?? '') }}"
                                                    placeholder="Opcional"
                                                    class="input-panama w-full pl-7 pr-3 py-2.5 text-xs font-bold text-emerald-700 rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Costo Unitario -->
                                    <div>
                                        <label for="costo_unitario"
                                            class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                            Costo Unitario (Privado)
                                        </label>
                                        <div class="relative flex items-center">
                                            <span class="absolute left-3 text-xs font-bold text-slate-400">$</span>
                                            <input type="number" step="0.01" min="0" id="costo_unitario"
                                                name="costo_unitario" oninput="calcularMargen()"
                                                value="{{ old('costo_unitario', $producto->costo_unitario ?? '0.00') }}"
                                                placeholder="0.00"
                                                class="input-panama w-full pl-7 pr-3 py-2.5 text-xs font-mono rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20">
                                        </div>
                                    </div>

                                    <!-- Indicator Margen -->
                                    <div id="badge-margen"
                                        class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 flex flex-col gap-1 text-xs">
                                        <div class="flex items-center gap-1.5 text-slate-600">
                                            <span
                                                class="material-symbols-outlined text-[16px] text-emerald-600">trending_up</span>
                                            <span class="font-medium text-[11px]">Rentabilidad calculada estimada:</span>
                                        </div>
                                        <div class="flex items-center justify-between pt-0.5">
                                            <span id="margen-monto" class="font-bold text-slate-900 text-sm">+$0.00 por
                                                unidad</span>
                                            <span id="margen-porcentaje"
                                                class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">0%
                                                margen</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Inventario & Stock (5 Cols) -->
                        <div class="lg:col-span-5 space-y-5">
                            <div class="card-elevated p-5 sm:p-6 rounded-2xl space-y-4">
                                <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                                    <span class="material-symbols-outlined text-emerald-600 text-[20px]">warehouse</span>
                                    <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Inventario & Stock
                                    </h2>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="stock"
                                            class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                            Stock Total
                                        </label>
                                        <div class="relative flex items-center">
                                            <input type="number" id="stock" name="stock" min="0"
                                                value="{{ old('stock', $producto->stock ?? 12) }}"
                                                class="input-panama w-full text-xs font-bold rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 py-2.5 px-3">
                                        </div>
                                    </div>

                                    <div>
                                        <label for="stock_minimo"
                                            class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1 flex items-center justify-between">
                                            <span>Mínimo Alerta</span>
                                            <span class="material-symbols-outlined text-[14px] text-amber-500"
                                                title="Alerta de stock bajo">warning</span>
                                        </label>
                                        <input type="number" id="stock_minimo" name="stock_minimo" min="0"
                                            value="{{ old('stock_minimo', $producto->stock_minimo ?? 3) }}"
                                            class="input-panama w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 py-2.5 px-3">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Pestaña 3: DESCRIPCIÓN DETALLADA -->
                <div id="tab-panel-descripcion" class="tab-contenido-producto space-y-5 hidden">
                    <div class="card-elevated p-5 sm:p-6 rounded-2xl space-y-4">
                        <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                            <span class="material-symbols-outlined text-emerald-600 text-[20px]">description</span>
                            <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Descripciones &
                                Especificaciones</h2>
                        </div>

                        <div class="space-y-4">
                            <!-- Descripción Corta -->
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label for="descripcion_corta"
                                        class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                                        Descripción Corta (Resumen)
                                    </label>
                                    <span id="contador-desc-corta" class="text-[10px] text-slate-400">0 / 180
                                        caracteres</span>
                                </div>
                                <textarea id="descripcion_corta" name="descripcion_corta" rows="2" maxlength="180"
                                    oninput="actualizarContador(this, 'contador-desc-corta', 180)"
                                    placeholder="Router WiFi 6 de alta velocidad con cobertura de hasta 150 m²..."
                                    class="input-panama w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 p-3">{{ old('descripcion_corta', $producto->descripcion_corta ?? '') }}</textarea>
                            </div>

                            <!-- Descripción Detallada -->
                            <div>
                                <label for="descripcion"
                                    class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                    Descripción Detallada & Especificaciones
                                </label>

                                <!-- Barra de herramientas simulada -->
                                <div
                                    class="flex items-center gap-1 bg-slate-50 border border-slate-200 border-b-0 rounded-t-xl px-3 py-1.5 text-slate-500 text-xs">
                                    <button type="button" class="p-1 hover:bg-slate-200 rounded font-bold">B</button>
                                    <button type="button"
                                        class="p-1 hover:bg-slate-200 rounded italic font-serif">I</button>
                                    <span class="text-slate-300 mx-1">|</span>
                                    <button type="button"
                                        class="p-1 hover:bg-slate-200 rounded material-symbols-outlined text-[16px]">format_list_bulleted</button>
                                    <button type="button"
                                        class="p-1 hover:bg-slate-200 rounded material-symbols-outlined text-[16px]">link</button>
                                    <button type="button"
                                        class="p-1 hover:bg-slate-200 rounded material-symbols-outlined text-[16px]">image</button>
                                </div>
                                <textarea id="descripcion" name="descripcion" rows="8"
                                    placeholder="Escribe la descripción completa, características técnicas y especificaciones..."
                                    class="input-panama w-full text-xs rounded-b-xl rounded-t-none border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 p-3">{{ old('descripcion', $producto->descripcion ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pestaña 4: IMÁGENES -->
                <div id="tab-panel-imagenes" class="tab-contenido-producto space-y-5 hidden">
                    @include('admin.productos.imagenes', ['imagenes' => $producto->imagenes ?? collect()])
                </div>

                <!-- Pestaña 5: VARIANTES -->
                <div id="tab-panel-variantes" class="tab-contenido-producto space-y-5 hidden">
                    @include('admin.productos.variantes', [
                        'variantes' => $producto->variantes ?? collect(),
                        'tiposVariante' => $tiposVariante ?? collect()
                    ])
                </div>

            </form>

    </div>

    <!-- MODAL DE SELECCIÓN DE MARCAS REUTILIZABLE -->
    <x-modal-busqueda id="modal-marcas" titulo="Seleccionar Marca" subtitulo="Busca o elige el fabricante del producto"
        icono="verified" placeholder="Filtrar marca (ej. Lenovo, HP, ASUS, Apple, TP-Link, Adata)..." :porPagina="15" />

    <!-- MODAL DE SELECCIÓN DE CATEGORÍAS REUTILIZABLE -->
    <x-modal-busqueda id="modal-categorias" titulo="Explorar Categorías"
        subtitulo="Busca y asigna la categoría del producto" icono="category"
        placeholder="Buscar por nombre o palabra clave..." :porPagina="15" />

    <script>
        /* Cambio de Pestañas de la Ficha de Producto con Persistencia al Recargar */
        function cambiarTabProducto(tabId) {
            const paneles = document.querySelectorAll('.tab-contenido-producto');
            const botones = document.querySelectorAll('.btn-tab-producto');

            if (tabId === 'ver-todo') {
                paneles.forEach(p => p.classList.remove('hidden'));
            } else {
                paneles.forEach(p => p.classList.add('hidden'));
                const target = document.getElementById('tab-panel-' + tabId);
                if (target) target.classList.remove('hidden');
            }

            botones.forEach(btn => {
                btn.classList.remove('bg-slate-900', 'text-white', 'shadow-xs');
                btn.classList.add('bg-white', 'text-slate-600', 'hover:bg-slate-100');
            });

            const activeBtn = document.getElementById('btn-tab-' + tabId);
            if (activeBtn) {
                activeBtn.classList.remove('bg-white', 'text-slate-600', 'hover:bg-slate-100');
                activeBtn.classList.add('bg-slate-900', 'text-white', 'shadow-xs');
            }

            // Persistir tab activo en localStorage y URL Hash
            if (tabId && tabId !== 'ver-todo') {
                localStorage.setItem('producto_tab_activo', tabId);
                if (history.replaceState) {
                    history.replaceState(null, null, '#' + tabId);
                } else {
                    location.hash = '#' + tabId;
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            let tabApertura = 'generales';

            // 1. Prioridad: Hash en la URL (#variantes, #imagenes, etc.)
            const hash = (window.location.hash || '').replace('#', '').trim();
            if (hash) {
                const btnTarget = document.getElementById('btn-tab-' + hash);
                if (btnTarget) {
                    tabApertura = hash;
                }
            } else {
                // 2. Segunda opción: localStorage
                const tabGuardado = localStorage.getItem('producto_tab_activo');
                if (tabGuardado) {
                    const btnTarget = document.getElementById('btn-tab-' + tabGuardado);
                    if (btnTarget) {
                        tabApertura = tabGuardado;
                    }
                }
            }

            cambiarTabProducto(tabApertura);
        });

        function regenerarSlugDesdeNombre() {
            const nombreInput = document.getElementById('nombre');
            const slugInput = document.getElementById('slug');
            const icono = document.getElementById('icono-autorenew-slug');

            if (!nombreInput || !slugInput) return;

            if (icono) {
                icono.classList.add('rotate-180', 'text-emerald-500');
                setTimeout(() => icono.classList.remove('rotate-180', 'text-emerald-500'), 500);
            }

            const texto = nombreInput.value.trim();
            if (texto) {
                const slugGenerado = texto
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/[^a-z0-9 -]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');

                slugInput.value = slugGenerado;
            }
        }

        function actualizarContador(textarea, idContador, max) {
            const el = document.getElementById(idContador);
            if (el) {
                el.textContent = `${textarea.value.length} / ${max} caracteres`;
            }
        }

        function calcularMargen() {
            const precio = parseFloat(document.getElementById('precio').value) || 0;
            const costo = parseFloat(document.getElementById('costo_unitario').value) || 0;
            const montoEl = document.getElementById('margen-monto');
            const pctEl = document.getElementById('margen-porcentaje');

            const margen = precio - costo;
            const porcentaje = precio > 0 ? ((margen / precio) * 100).toFixed(0) : 0;

            if (montoEl) montoEl.textContent = `${margen >= 0 ? '+' : ''}$${margen.toFixed(2)} por unidad`;
            if (pctEl) {
                pctEl.textContent = `${porcentaje}% margen`;
                if (margen >= 0) {
                    pctEl.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800';
                } else {
                    pctEl.className = 'px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800';
                }
            }
        }

        const marcasData = {!! json_encode($marcasData ?? []) !!};
        const categoriasData = {!! json_encode($categoriasData ?? []) !!};

        let activeTabMarcas = 'todas';

        function getLogoHtmlForBrand(brand) {
            if (brand && brand.url) {
                return `<img src="${brand.url}" alt="${brand.nombre}" class="h-5 max-h-6 max-w-[70px] object-contain select-none">`;
            }
            if (brand && brand.slug === 'apple') {
                return `<svg class="h-5 w-5 text-black fill-current" viewBox="0 0 170 170"><path d="M150.37 130.25c-2.45 5.66-5.35 10.87-8.71 15.66-4.58 6.53-8.33 11.05-11.22 13.56-4.48 4.12-9.28 6.23-14.42 6.35-3.69 0-8.14-1.05-13.32-3.18-5.19-2.12-9.97-3.17-14.34-3.17-4.58 0-9.49 1.05-14.75 3.17-5.26 2.13-9.5 3.24-12.74 3.35-4.35.13-9.16-1.9-14.42-6.08-3.7-3.04-7.7-7.88-12-14.52-6.55-10.13-11.49-21.36-14.81-33.7-3.32-12.33-4.99-23.77-4.99-34.3 0-14.61 3.73-26.68 11.2-36.21 7.46-9.53 16.73-14.39 27.79-14.57 4.89 0 10.36 1.34 16.4 4.02 6.04 2.68 9.77 4.07 11.19 4.17 1.12 0 5.09-1.52 11.91-4.57 6.83-3.04 12.63-4.37 17.41-3.99 13.25 1.13 23.36 5.86 30.34 14.18-11.83 7.15-17.61 16.89-17.33 29.2.29 9.69 4.15 17.65 11.58 23.87 7.43 6.22 16.27 9.78 26.51 10.68-2.6 7.82-5.74 15.65-9.41 23.49zm-38.64-106.8c0-7.39 2.65-14.18 7.95-20.36 5.3-6.19 11.75-9.83 19.34-10.93.9 4.02 1.35 7.82 1.35 11.4 0 7.39-2.78 14.35-8.33 20.88-5.55 6.53-12.31 10.23-20.31 11.1-.38-3.9-.38-7.93 0-12.09z"/></svg>`;
            }
            const name = brand ? brand.nombre : 'Marca';
            return `<span class="px-1.5 py-0.5 rounded bg-slate-900 text-white text-[9px] font-black uppercase tracking-wider">${name}</span>`;
        }

        function getImageHtmlForCategory(cat) {
            if (cat && cat.imagen_ruta) {
                let img = cat.imagen_ruta.trim().replace(/\\/g, '/');

                // 1. Si es código SVG inline
                if (img.startsWith('<svg') || img.includes('</svg>')) {
                    return `<div class="w-full h-full flex items-center justify-center svg-container">${img}</div>`;
                }

                // 2. Si es nombre de ícono de Material Symbols (ej: 'category', 'devices', 'laptop')
                if (!img.includes('/') && !img.includes('.') && img.length < 40) {
                    return `<span class="material-symbols-outlined text-[16px]">${img}</span>`;
                }

                // 3. Si es una ruta de imagen en disco (limpiar public/ y barras invertidas)
                let src = img;
                if (!src.startsWith('http://') && !src.startsWith('https://') && !src.startsWith('data:image')) {
                    src = src.replace(/^\/?public\//i, '/');
                    if (!src.startsWith('/')) {
                        src = '/' + src;
                    }
                }

                return `<img src="${src}" alt="${cat.nombre}" class="w-full h-full object-cover rounded-lg" onerror="this.onerror=null; this.parentElement.innerHTML='<span class=\\'material-symbols-outlined text-[16px]\\'>category</span>';">`;
            }
            return `<span class="material-symbols-outlined text-[15px]">category</span>`;
        }

        /* Modal Categorías Logic (Integrado con ModalBuscador - Paginado 15 items) */
        document.addEventListener('DOMContentLoaded', function () {
            if (window.ModalBuscador) {
                window.ModalBuscador.init('modal-categorias', {
                    items: categoriasData,
                    porPagina: 15,
                    emptyText: 'No se encontraron categorías para',
                    render: (cat) => {
                        const idActual = (document.getElementById('input-categoria-valor')?.value || '').toString().trim();
                        const isSelected = idActual && (cat.id.toString() === idActual);

                        const card = document.createElement('div');
                        card.className = `p-2.5 rounded-xl border transition-all cursor-pointer flex items-center justify-between group ${isSelected
                            ? 'bg-emerald-50/90 border-emerald-400 ring-2 ring-emerald-500/20 shadow-xs'
                            : 'bg-white border-slate-200/80 hover:border-slate-400 hover:shadow-xs'
                            }`;
                        card.onclick = () => seleccionarCategoria(cat.id, cat.nombre);

                        card.innerHTML = `
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg ${isSelected ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 group-hover:bg-slate-900 group-hover:text-white'} flex items-center justify-center shrink-0 transition-colors overflow-hidden p-0.5 shadow-2xs">
                                        ${getImageHtmlForCategory(cat)}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold ${isSelected ? 'text-emerald-950' : 'text-slate-800 group-hover:text-slate-900'} truncate">${cat.nombre}</div>
                                    </div>
                                </div>
                                ${isSelected ? '<span class="material-symbols-outlined text-emerald-600 text-[18px]">check_circle</span>' : ''}
                            `;
                        return card;
                    }
                });

                window.ModalBuscador.init('modal-marcas', {
                    items: marcasData,
                    porPagina: 15,
                    emptyText: 'No se encontró la marca',
                    render: (brand) => {
                        const valorActual = (document.getElementById('input-marca-valor')?.value || '').trim().toLowerCase();
                        const isSelected = valorActual && (brand.nombre.toLowerCase() === valorActual || (brand.slug && brand.slug.toLowerCase() === valorActual));

                        const card = document.createElement('div');
                        card.className = `p-2 rounded-xl border transition-all cursor-pointer text-center flex items-center justify-between group ${isSelected
                            ? 'bg-emerald-50/90 border-emerald-400 ring-2 ring-emerald-500/20 shadow-xs'
                            : 'bg-white border-slate-200/80 hover:border-slate-400 hover:shadow-xs'
                            }`;
                        card.onclick = () => seleccionarMarca(brand.nombre);

                        card.innerHTML = `
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center font-bold text-xs shrink-0 overflow-hidden">
                                        ${getLogoHtmlForBrand(brand)}
                                    </div>
                                    <span class="text-xs font-bold ${isSelected ? 'text-emerald-950' : 'text-slate-800'} truncate">${brand.nombre}</span>
                                </div>
                                ${isSelected ? '<span class="material-symbols-outlined text-emerald-600 text-[16px]">check_circle</span>' : ''}
                            `;
                        return card;
                    }
                });
            }
        });

        function abrirModalCategorias() {
            if (window.ModalBuscador) window.ModalBuscador.abrir('modal-categorias');
        }

        function cerrarModalCategorias() {
            if (window.ModalBuscador) window.ModalBuscador.cerrar('modal-categorias');
        }

        function seleccionarCategoria(id, nombre) {
            const input = document.getElementById('input-categoria-valor');
            if (input) {
                input.value = id;
            }
            actualizarUICategoria(id, nombre);
            cerrarModalCategorias();
        }

        function deseleccionarCategoria() {
            const input = document.getElementById('input-categoria-valor');
            if (input) {
                input.value = '';
            }
            actualizarUICategoria('', '');
        }

        function actualizarUICategoria(id, nombre) {
            const cardActiva = document.getElementById('card-categoria-activa');
            const btnAbrir = document.getElementById('btn-abrir-modal-categorias');
            const displayNombre = document.getElementById('display-categoria-nombre');

            const idStr = (id || '').toString().trim();

            if (!idStr) {
                if (cardActiva) cardActiva.classList.add('hidden'), cardActiva.classList.remove('flex');
                if (btnAbrir) btnAbrir.classList.remove('hidden');
                return;
            }

            const catEncontrada = categoriasData.find(c => c.id.toString() === idStr);
            const displayLogo = document.getElementById('display-categoria-logo');

            if (displayNombre) {
                displayNombre.textContent = catEncontrada ? catEncontrada.nombre : (nombre || 'Categoría');
            }

            if (displayLogo) {
                displayLogo.innerHTML = catEncontrada ? getImageHtmlForCategory(catEncontrada) : '<span class="material-symbols-outlined text-[18px]">category</span>';
            }

            if (btnAbrir) btnAbrir.classList.add('hidden');
            if (cardActiva) {
                cardActiva.classList.remove('hidden');
                cardActiva.classList.add('flex');
            }
        }

        /* Modal Marcas Logic */
        function abrirModalMarcas() {
            if (window.ModalBuscador) window.ModalBuscador.abrir('modal-marcas');
        }

        function cerrarModalMarcas() {
            if (window.ModalBuscador) window.ModalBuscador.cerrar('modal-marcas');
        }

        function seleccionarMarca(nombreMarca) {
            const input = document.getElementById('input-marca-valor');
            if (input) {
                input.value = nombreMarca;
            }
            actualizarUIMarca(nombreMarca);
            cerrarModalMarcas();
        }

        function deseleccionarMarca() {
            const input = document.getElementById('input-marca-valor');
            if (input) {
                input.value = '';
            }
            actualizarUIMarca('');
        }

        function usarMarcaPersonalizada() {
            const txt = document.getElementById('texto-marca-personalizada')?.textContent || '';
            if (txt) {
                seleccionarMarca(txt.trim());
            }
        }

        function actualizarUIMarca(valor) {
            const cardActiva = document.getElementById('card-marca-activa');
            const btnAbrir = document.getElementById('btn-abrir-modal-marcas');
            const displayNombre = document.getElementById('display-marca-nombre');
            const displayLogo = document.getElementById('display-marca-logo');

            const valorNorm = (valor || '').trim().toLowerCase();

            if (!valorNorm) {
                if (cardActiva) cardActiva.classList.add('hidden'), cardActiva.classList.remove('flex');
                if (btnAbrir) btnAbrir.classList.remove('hidden');
                return;
            }

            const brandEncontrada = marcasData.find(m =>
                (m.nombre && m.nombre.toLowerCase() === valorNorm) ||
                (m.slug && m.slug.toLowerCase() === valorNorm)
            );

            if (displayNombre) {
                displayNombre.textContent = brandEncontrada ? brandEncontrada.nombre : valor;
            }

            if (displayLogo) {
                displayLogo.innerHTML = getLogoHtmlForBrand(brandEncontrada || { nombre: valor });
            }

            if (btnAbrir) btnAbrir.classList.add('hidden');
            if (cardActiva) {
                cardActiva.classList.remove('hidden');
                cardActiva.classList.add('flex');
            }
        }

        // Cerrar modales con tecla Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                cerrarModalMarcas();
                cerrarModalCategorias();
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            calcularMargen();

            const descCortaInput = document.getElementById('descripcion_corta');
            if (descCortaInput) {
                actualizarContador(descCortaInput, 'contador-desc-corta', 180);
            }

            const marcaInicial = document.getElementById('input-marca-valor')?.value;
            if (marcaInicial) {
                actualizarUIMarca(marcaInicial);
            }

            const categoriaInicial = document.getElementById('input-categoria-valor')?.value;
            if (categoriaInicial) {
                actualizarUICategoria(categoriaInicial, '');
            }

            @if($errors->any())
                // En caso de errores de validación, mostrar todas las pestañas para ubicarlos rápido
                cambiarTabProducto('ver-todo');
            @endif
            });
    </script>
@endsection