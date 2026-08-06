@extends('layouts.admin')

@section('title', ($esEdicion ?? false) ? 'Editar Producto' : 'Nuevo Producto')

@section('breadcrumbs')
    <span class="hidden sm:inline-flex items-center gap-1.5 text-slate-500">
        <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
        <span>Catálogo</span>
    </span>
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <a href="{{ route('admin.productos.index') }}" class="text-slate-500 hover:text-slate-900 transition-colors">Productos</a>
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <span class="font-bold text-slate-900 truncate">{{ ($esEdicion ?? false) ? ($producto->nombre ?? 'Editar Producto') : 'Nuevo Producto' }}</span>
@endsection

@section('content')
<div class="space-y-5 w-full min-w-0 max-w-full">

    {{-- ── Errores de Validación ── --}}
    @if($errors->any())
        <div class="px-4 py-3 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-xs">
            <div class="flex items-center gap-2 font-bold mb-2">
                <span class="material-symbols-outlined text-[20px]">error</span>
                <span>Corrige los siguientes errores antes de guardar:</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-xs">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif



    <!-- Formulario Principal -->
    @if($esEdicion ?? false)
        <form id="form-producto" method="POST"
              action="{{ route('admin.productos.update', $id ?? $producto->id) }}"
              enctype="multipart/form-data"
              class="space-y-5">
            @csrf
            @method('PUT')
    @else
        <form id="form-producto" method="POST"
              action="{{ route('admin.productos.store') }}"
              enctype="multipart/form-data"
              class="space-y-5">
            @csrf
    @endif
        
        <!-- Header con Acciones Principales -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-slate-200/80 pb-3">
            <div>
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-br from-emerald-500/20 to-teal-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-600 shadow-xs shrink-0">
                        <span class="material-symbols-outlined text-[22px]">{{ ($esEdicion ?? false) ? 'edit_note' : 'add_box' }}</span>
                    </div>
                    <div>
                        <h1 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight leading-tight">
                            {{ ($esEdicion ?? false) ? 'Editar Producto & Variantes' : 'Crear Nuevo Producto' }}
                        </h1>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Completa los detalles del artículo, atributos, precios e inventario para la tienda online.
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
                    <a href="{{ route('cliente.producto.detalle', $producto->slug) }}" 
                       target="_blank"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 transition-all shadow-xs">
                        <span class="material-symbols-outlined text-[17px]">visibility</span>
                        <span>Ver en Tienda</span>
                    </a>
                @endif

                <button type="submit" 
                        class="inline-flex items-center gap-1.5 px-4 py-1.5 sm:px-5 sm:py-2 bg-slate-900 hover:bg-slate-800 rounded-xl text-xs font-bold text-white shadow-sm transition-all transform active:scale-95">
                    <span class="material-symbols-outlined text-[17px]">check_circle</span>
                    <span>{{ ($esEdicion ?? false) ? 'Actualizar Producto' : 'Publicar Producto' }}</span>
                </button>
            </div>
        </div>

        <!-- Grid Principal: 2 Columnas (8 cols contenido / 4 cols lateral) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
            
            <!-- Columna Central / Izquierda (8 columnas) -->
            <div class="lg:col-span-8 space-y-6">
                
                <!-- CARD 1: Información General -->
                <div class="card-elevated p-5 sm:p-6 rounded-2xl space-y-4">
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                        <span class="material-symbols-outlined text-emerald-600 text-[20px]">info</span>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Información General</h2>
                    </div>

                    <div class="space-y-4">
                        <!-- Nombre del Producto -->
                        <div>
                            <label for="nombre" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Nombre del Producto <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   id="nombre" 
                                   name="nombre" 
                                   required
                                   value="{{ old('nombre', $producto->nombre ?? '') }}" 
                                   placeholder="Ej. MacBook Air 13'' Apple M2..." 
                                   class="input-panama w-full text-sm rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 py-2.5 px-3">
                        </div>

                        <!-- Slug / Enlace Permanente -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="slug" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                                    Slug / Enlace Permanente <span class="text-rose-500">*</span>
                                </label>
                                <button type="button" 
                                        onclick="regenerarSlugDesdeNombre()" 
                                        class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 hover:text-emerald-800 transition-colors cursor-pointer group">
                                    <span id="icono-autorenew-slug" class="material-symbols-outlined text-[15px] group-hover:rotate-180 transition-transform duration-300">autorenew</span>
                                    <span>Regenerar desde nombre</span>
                                </button>
                            </div>
                            <div class="relative flex items-center">
                                <span class="absolute left-3 text-xs text-slate-400 pointer-events-none select-none font-mono">tutienda.com/producto/</span>
                                <input type="text" 
                                       id="slug" 
                                       name="slug" 
                                       required
                                       value="{{ old('slug', $producto->slug ?? '') }}" 
                                       placeholder="macbook-air-13-apple-m2" 
                                       class="input-panama w-full pl-44 pr-3 py-2 text-xs font-mono rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 text-slate-800">
                            </div>
                        </div>

                        <!-- SKU Base y Categoría -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="sku" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                    SKU Base <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       id="sku" 
                                       name="sku" 
                                       required
                                       value="{{ old('sku', $producto->sku ?? '') }}" 
                                       placeholder="PTL-LEV-493" 
                                       class="input-panama w-full text-xs font-mono uppercase rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 py-2.5 px-3">
                            </div>

                            <div>
                                <label for="categoria_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                    Categoría <span class="text-rose-500">*</span>
                                </label>
                                <select id="categoria_id" 
                                        name="categoria_id" 
                                        required 
                                        class="input-panama w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 py-2.5 px-3">
                                    <option value="">Selecciona una categoría...</option>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->id }}" @selected(old('categoria_id', $producto->categoria_id ?? '') == $cat->id)>
                                            {{ $cat->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Marca y Modelo del Producto -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                    Marca (Fabricante)
                                </label>

                                <!-- Input oculto para enviar la marca seleccionada en el POST -->
                                <input type="hidden" 
                                       id="input-marca-valor" 
                                       name="marca" 
                                       value="{{ old('marca', $producto->brand->name ?? ($producto->marca ?? '')) }}">

                                <!-- Tarjeta de Marca Seleccionada / Botón para Abrir Modal -->
                                <div id="contenedor-marca-card" class="relative">
                                    <!-- Estado 1: Marca Seleccionada (Compacto) -->
                                    <div id="card-marca-activa" class="hidden items-center justify-between p-1.5 bg-slate-50 border border-slate-200 rounded-xl hover:border-slate-300 transition-all h-[44px]">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <div id="display-marca-logo" class="w-9 h-7 bg-white border border-slate-200/80 rounded-md flex items-center justify-center p-0.5 shadow-2xs shrink-0 overflow-hidden">
                                                <!-- Logo dinámico -->
                                            </div>
                                            <div class="min-w-0">
                                                <div id="display-marca-nombre" class="text-xs font-bold text-slate-900 truncate">Lenovo</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-0.5 shrink-0">
                                            <button type="button" 
                                                    onclick="abrirModalMarcas()" 
                                                    class="px-2 py-1 text-[11px] font-semibold text-slate-700 hover:bg-slate-200 rounded-md transition-colors cursor-pointer flex items-center gap-0.5">
                                                <span class="material-symbols-outlined text-[13px]">sync_alt</span>
                                                <span>Cambiar</span>
                                            </button>
                                            <button type="button" 
                                                    onclick="deseleccionarMarca()" 
                                                    class="p-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-md transition-colors cursor-pointer"
                                                    title="Quitar marca">
                                                <span class="material-symbols-outlined text-[15px]">close</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Estado 2: Sin Marca Seleccionada (Compacto) -->
                                    <button type="button" 
                                            id="btn-abrir-modal-marcas" 
                                            onclick="abrirModalMarcas()" 
                                            class="w-full flex items-center justify-between px-3 bg-slate-50 hover:bg-slate-100/80 border border-dashed border-slate-300 hover:border-slate-400 rounded-xl transition-all group text-left cursor-pointer h-[44px]">
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-slate-400 group-hover:text-slate-700 text-[18px]">verified</span>
                                            <span class="text-xs font-medium text-slate-600">Seleccionar Marca...</span>
                                        </div>
                                        <span class="px-2 py-0.5 rounded-md bg-white border border-slate-200 text-slate-600 text-[10px] font-bold flex items-center gap-1 shadow-2xs group-hover:border-slate-300">
                                            <span class="material-symbols-outlined text-[12px]">search</span>
                                            <span>Explorar</span>
                                        </span>
                                    </button>
                                </div>

                                <!-- Chips de acceso rápido para sugeridas (Compactos) -->
                                @if(isset($marcas) && $marcas->where('is_suggested', true)->count() > 0)
                                    <div class="flex items-center gap-1 mt-1.5 flex-wrap">
                                        <span class="text-[9.5px] text-slate-400 font-medium">Populares:</span>
                                        @foreach($marcas->where('is_suggested', true)->take(6) as $mSugerida)
                                            <button type="button" 
                                                    onclick="seleccionarMarca('{{ $mSugerida->name }}')" 
                                                    class="px-1.5 py-0.5 rounded bg-slate-100 hover:bg-slate-900 hover:text-white text-[9.5px] font-semibold text-slate-600 transition-all cursor-pointer">
                                                {{ $mSugerida->name }}
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div>
                                <label for="modelo" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                    Modelo Técnico
                                </label>
                                <input type="text" 
                                       id="modelo" 
                                       name="modelo" 
                                       value="{{ old('modelo', $producto->modelo ?? '') }}" 
                                       placeholder="Ej: 82VG00WXUS, Archer AX23" 
                                       class="input-panama w-full text-xs font-mono rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 h-[44px] px-3">
                                <p class="text-[10px] text-slate-400 mt-1.5">
                                    Número de modelo de fábrica para búsqueda exacta.
                                </p>
                            </div>
                        </div>

                        <!-- Descripción Corta -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="descripcion_corta" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                                    Descripción Corta (Resumen)
                                </label>
                                <span id="contador-desc-corta" class="text-[10px] text-slate-400">0 / 180 caracteres</span>
                            </div>
                            <textarea id="descripcion_corta" 
                                      name="descripcion_corta" 
                                      rows="2" 
                                      maxlength="180" 
                                      oninput="actualizarContador(this, 'contador-desc-corta', 180)"
                                      placeholder="Breve descripción que aparecerá en los listados del catálogo y tarjetas sociales..." 
                                      class="input-panama w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 p-3">{{ old('descripcion_corta', $producto->descripcion_corta ?? '') }}</textarea>
                        </div>

                        <!-- Descripción Detallada -->
                        <div>
                            <label for="descripcion" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Descripción Detallada & Especificaciones
                            </label>
                            
                            <!-- Barra de herramientas simulada -->
                            <div class="flex items-center gap-1 bg-slate-50 border border-slate-200 border-b-0 rounded-t-xl px-3 py-1.5 text-slate-500 text-xs">
                                <button type="button" class="p-1 hover:bg-slate-200 rounded font-bold">B</button>
                                <button type="button" class="p-1 hover:bg-slate-200 rounded italic font-serif">I</button>
                                <span class="text-slate-300 mx-1">|</span>
                                <button type="button" class="p-1 hover:bg-slate-200 rounded material-symbols-outlined text-[16px]">format_list_bulleted</button>
                                <button type="button" class="p-1 hover:bg-slate-200 rounded material-symbols-outlined text-[16px]">link</button>
                                <button type="button" class="p-1 hover:bg-slate-200 rounded material-symbols-outlined text-[16px]">image</button>
                            </div>
                            <textarea id="descripcion" 
                                      name="descripcion" 
                                      rows="5" 
                                      placeholder="Escribe la descripción completa, características técnicas, contenido de la caja y garantía..." 
                                      class="input-panama w-full text-xs rounded-b-xl rounded-t-none border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 p-3">{{ old('descripcion', $producto->descripcion ?? '') }}</textarea>
                        </div>

                    </div>
                </div>

                <!-- CARD 2: Precios & Rentabilidad -->
                <div class="card-elevated p-5 sm:p-6 rounded-2xl space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-600 text-[20px]">payments</span>
                            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Precios & Rentabilidad</h2>
                        </div>
                        <span class="text-[11px] font-semibold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">Moneda: USD ($)</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Precio Base -->
                        <div>
                            <label for="precio" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Precio Base <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative flex items-center">
                                <span class="absolute left-3 text-xs font-bold text-slate-400">$</span>
                                <input type="number" 
                                       step="0.01" 
                                       min="0" 
                                       id="precio" 
                                       name="precio" 
                                       required
                                       oninput="calcularMargen()"
                                       value="{{ old('precio', $producto->precio ?? '0.00') }}" 
                                       placeholder="0.00" 
                                       class="input-panama w-full pl-7 pr-3 py-2.5 text-xs font-bold text-slate-900 rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20">
                            </div>
                        </div>

                        <!-- Precio Oferta -->
                        <div>
                            <label for="precio_oferta" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Precio Oferta (Descuento)
                            </label>
                            <div class="relative flex items-center">
                                <span class="absolute left-3 text-xs font-bold text-slate-400">$</span>
                                <input type="number" 
                                       step="0.01" 
                                       min="0" 
                                       id="precio_oferta" 
                                       name="precio_oferta" 
                                       oninput="calcularMargen()"
                                       value="{{ old('precio_oferta', $producto->precio_oferta ?? '') }}" 
                                       placeholder="Opcional" 
                                       class="input-panama w-full pl-7 pr-3 py-2.5 text-xs font-bold text-emerald-700 rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20">
                            </div>
                        </div>

                        <!-- Costo Unitario / Privado -->
                        <div>
                            <label for="costo_unitario" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Costo Unitario (Privado)
                            </label>
                            <div class="relative flex items-center">
                                <span class="absolute left-3 text-xs font-bold text-slate-400">$</span>
                                <input type="number" 
                                       step="0.01" 
                                       min="0" 
                                       id="costo_unitario" 
                                       name="costo_unitario" 
                                       oninput="calcularMargen()"
                                       value="0.00" 
                                       placeholder="0.00" 
                                       class="input-panama w-full pl-7 pr-3 py-2.5 text-xs font-mono rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20">
                            </div>
                        </div>
                    </div>

                    <!-- Indicador en vivo de Margen de Ganancia -->
                    <div id="badge-margen" class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between text-xs gap-2">
                        <div class="flex items-center gap-2 text-slate-600">
                            <span class="material-symbols-outlined text-[18px] text-emerald-600">trending_up</span>
                            <span class="font-medium">Rentabilidad calculada estimada:</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span id="margen-monto" class="font-bold text-slate-900">+$0.00 por unidad</span>
                            <span id="margen-porcentaje" class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">0% margen</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Columna Lateral / Derecha (4 columnas) -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- CARD LATERAL 1: Estado & Visibilidad -->
                <div class="card-elevated p-5 rounded-2xl space-y-4">
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                        <span class="material-symbols-outlined text-emerald-600 text-[20px]">toggle_on</span>
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Estado & Visibilidad</h3>
                    </div>

                    <div class="space-y-4">
                        <!-- Select de Estado -->
                        <div>
                            <label for="activo" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Estado del Producto
                            </label>
                            <select id="activo" 
                                    name="activo" 
                                    class="input-panama w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 py-2.5 px-3">
                                <option value="1" @selected(old('activo', $producto->activo ?? true) == true)>🟢 Activo (Visible en tienda)</option>
                                <option value="0" @selected(old('activo', $producto->activo ?? true) == false)>⚪ Inactivo (Borrador oculto)</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Switch de Producto Destacado -->
                            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <div class="space-y-0.5">
                                    <p class="text-xs font-bold text-slate-800 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-amber-500 text-[16px]">star</span>
                                        <span>Destacado</span>
                                    </p>
                                    <p class="text-[9px] text-slate-500">Sección destacados</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="destacado" value="1" class="sr-only peer" @checked(old('destacado', $producto->destacado ?? false))>
                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-amber-500"></div>
                                </label>
                            </div>

                            <!-- Switch de Aplicación de ITBMS 7% -->
                            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <div class="space-y-0.5">
                                    <p class="text-xs font-bold text-slate-800 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-emerald-600 text-[16px]">receipt_long</span>
                                        <span>ITBMS (7%)</span>
                                    </p>
                                    <p class="text-[9px] text-slate-500">Impuesto Panamá</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="aplica_itbms" value="1" class="sr-only peer" @checked(old('aplica_itbms', $producto->aplica_itbms ?? true))>
                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD LATERAL 2: Inventario & Stock -->
                <div class="card-elevated p-5 rounded-2xl space-y-4">
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                        <span class="material-symbols-outlined text-emerald-600 text-[20px]">warehouse</span>
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Inventario & Stock</h3>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="stock" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Stock Total
                            </label>
                            <input type="number" 
                                   id="stock" 
                                   name="stock" 
                                   min="0" 
                                   value="{{ old('stock', $producto->stock ?? 0) }}" 
                                   class="input-panama w-full text-xs font-bold rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 py-2.5 px-3">
                        </div>

                        <div>
                            <label for="stock_minimo" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Mínimo Alerta
                            </label>
                            <input type="number" 
                                   id="stock_minimo" 
                                   name="stock_minimo" 
                                   min="0" 
                                   value="{{ old('stock_minimo', $producto->stock_minimo ?? 3) }}" 
                                   class="input-panama w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 py-2.5 px-3">
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- SECCIÓN INFERIOR A ANCHO COMPLETO (100% Full Width) -->
        <div class="space-y-6">

            <!-- CARD FULL-WIDTH 1: Submódulo de Galería de Imágenes -->
            @include('admin.productos.imagenes', ['imagenes' => $producto->imagenes ?? collect()])

            <!-- CARD FULL-WIDTH 2: Submódulo Constructor de Variantes -->
            @include('admin.productos.variantes', [
                'variantes' => $producto->variantes ?? collect(),
                'tiposVariante' => $tiposVariante ?? collect()
            ])

        </div>

    </form>

</div>

<!-- MODAL DE SELECCIÓN DE MARCAS CON BUSCADOR (COMPACTO) -->
<div id="modal-marcas" 
     class="fixed inset-0 z-50 hidden items-center justify-center p-3 sm:p-4" 
     style="background-color: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);"
     onclick="if(event.target === this) cerrarModalMarcas();">
    
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-lg max-h-[85vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        
        <!-- Modal Header Compacto -->
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/70 shrink-0">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-lg bg-slate-900 text-white flex items-center justify-center shadow-xs">
                    <span class="material-symbols-outlined text-[15px]">verified</span>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-900">Seleccionar Marca</h3>
                </div>
            </div>
            <button type="button" 
                    onclick="cerrarModalMarcas()" 
                    class="w-6 h-6 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-200/60 flex items-center justify-center transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-[16px]">close</span>
            </button>
        </div>

        <!-- Buscador en Vivo y Tabs Compactos -->
        <div class="p-3 border-b border-slate-100 bg-white shrink-0">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-[16px]">
                    search
                </span>
                <input type="text" 
                       id="buscador-marcas-modal" 
                       placeholder="Filtrar marca (ej: Lenovo, HP, ASUS, Apple, TP-Link)..." 
                       oninput="filtrarMarcasModal(this.value)" 
                       class="w-full pl-8 pr-7 py-1.5 bg-slate-50 hover:bg-slate-100/60 focus:bg-white border border-slate-200 rounded-lg text-xs text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-400 transition-all">
                <button type="button" 
                        id="btn-limpiar-busqueda-marcas" 
                        onclick="document.getElementById('buscador-marcas-modal').value = ''; filtrarMarcasModal('');" 
                        class="hidden absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 p-0.5 rounded cursor-pointer">
                    <span class="material-symbols-outlined text-[14px]">cancel</span>
                </button>
            </div>

            <!-- Filtros rápidos por tab -->
            <div class="flex items-center gap-1 mt-2 text-xs">
                <button type="button" 
                        id="tab-marcas-todas" 
                        onclick="cambiarTabMarcas('todas')" 
                        class="px-2.5 py-0.5 rounded-md font-bold text-[10.5px] bg-slate-900 text-white shadow-2xs transition-all cursor-pointer">
                    Todas (<span id="contador-marcas-total">0</span>)
                </button>
                <button type="button" 
                        id="tab-marcas-sugeridas" 
                        onclick="cambiarTabMarcas('sugeridas')" 
                        class="px-2.5 py-0.5 rounded-md font-semibold text-[10.5px] bg-slate-100 text-slate-600 hover:bg-slate-200 transition-all cursor-pointer flex items-center gap-1">
                    <span class="material-symbols-outlined text-[12px] text-amber-500">star</span>
                    <span>Sugeridas (<span id="contador-marcas-sugeridas">0</span>)</span>
                </button>
            </div>
        </div>

        <!-- Grid de Tarjetas de Marcas Compacto -->
        <div class="p-3 overflow-y-auto max-h-[300px] flex-1 bg-slate-50/40">
            <div id="grid-marcas-modal" class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                <!-- Tarjetas generadas dinámicamente con JavaScript -->
            </div>

            <!-- Empty state / Opción para crear marca personalizada -->
            <div id="empty-state-marcas" class="hidden text-center py-6 px-3">
                <div class="w-10 h-10 mx-auto rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-1.5">
                    <span class="material-symbols-outlined text-[20px]">search_off</span>
                </div>
                <div class="text-xs font-bold text-slate-800">No se encontró en el catálogo</div>
                <p class="text-[10.5px] text-slate-500 mt-0.5 mb-2.5">Puedes asignarla directamente como marca personalizada.</p>
                <button type="button" 
                        id="btn-usar-marca-personalizada" 
                        onclick="usarMarcaPersonalizada()" 
                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg shadow-xs transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-[14px]">add_circle</span>
                    <span>Usar «<span id="texto-marca-personalizada"></span>»</span>
                </button>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="px-4 py-2.5 border-t border-slate-100 bg-white flex items-center justify-between shrink-0">
            <div class="text-[10px] text-slate-400 flex items-center gap-1">
                <span class="material-symbols-outlined text-[13px]">touch_app</span>
                <span>Clic en la marca para asignarla</span>
            </div>
            <button type="button" 
                    onclick="cerrarModalMarcas()" 
                    class="px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer">
                Cerrar
            </button>
        </div>
    </div>
</div>

<script>
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
                pctEl.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800';
            } else {
                pctEl.className = 'px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800';
            }
        }
    }

    @php
        $marcasArray = isset($marcas) 
            ? $marcas->map(function($m) {
                return [
                    'id' => $m->id,
                    'nombre' => $m->name,
                    'slug' => $m->slug,
                    'url' => $m->logo_url,
                    'is_suggested' => (bool)$m->is_suggested,
                    'verified' => (bool)$m->verified,
                ];
            })->values() 
            : \App\Helpers\BrandHelper::getAvailableBrands();
    @endphp
    const marcasData = {!! json_encode($marcasArray) !!};
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

    function abrirModalMarcas() {
        const modal = document.getElementById('modal-marcas');
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // Actualizar contadores
        const totalEl = document.getElementById('contador-marcas-total');
        const sugeridasEl = document.getElementById('contador-marcas-sugeridas');
        if (totalEl) totalEl.textContent = marcasData.length;
        if (sugeridasEl) sugeridasEl.textContent = marcasData.filter(m => m.is_suggested).length;

        // Reset buscador
        const input = document.getElementById('buscador-marcas-modal');
        if (input) {
            input.value = '';
            setTimeout(() => input.focus(), 80);
        }
        
        cambiarTabMarcas('todas');
    }

    function cerrarModalMarcas() {
        const modal = document.getElementById('modal-marcas');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function cambiarTabMarcas(tab) {
        activeTabMarcas = tab;
        const tabTodas = document.getElementById('tab-marcas-todas');
        const tabSugeridas = document.getElementById('tab-marcas-sugeridas');

        if (tab === 'todas') {
            tabTodas.className = 'px-2.5 py-0.5 rounded-md font-bold text-[10.5px] bg-slate-900 text-white shadow-2xs transition-all cursor-pointer';
            tabSugeridas.className = 'px-2.5 py-0.5 rounded-md font-semibold text-[10.5px] bg-slate-100 text-slate-600 hover:bg-slate-200 transition-all cursor-pointer flex items-center gap-1';
        } else {
            tabTodas.className = 'px-2.5 py-0.5 rounded-md font-semibold text-[10.5px] bg-slate-100 text-slate-600 hover:bg-slate-200 transition-all cursor-pointer';
            tabSugeridas.className = 'px-2.5 py-0.5 rounded-md font-bold text-[10.5px] bg-amber-500 text-white shadow-2xs transition-all cursor-pointer flex items-center gap-1';
        }

        const input = document.getElementById('buscador-marcas-modal');
        filtrarMarcasModal(input ? input.value : '');
    }

    function filtrarMarcasModal(query) {
        const grid = document.getElementById('grid-marcas-modal');
        const empty = document.getElementById('empty-state-marcas');
        const btnClear = document.getElementById('btn-limpiar-busqueda-marcas');
        const txtPersonalizada = document.getElementById('texto-marca-personalizada');
        const valorActual = (document.getElementById('input-marca-valor')?.value || '').trim().toLowerCase();

        if (btnClear) {
            btnClear.classList.toggle('hidden', !query);
        }

        const q = (query || '').trim().toLowerCase();
        let lista = marcasData;

        if (activeTabMarcas === 'sugeridas') {
            lista = lista.filter(m => m.is_suggested);
        }

        if (q) {
            lista = lista.filter(m => 
                (m.nombre && m.nombre.toLowerCase().includes(q)) || 
                (m.slug && m.slug.toLowerCase().includes(q))
            );
        }

        grid.innerHTML = '';

        if (lista.length === 0) {
            grid.classList.add('hidden');
            empty.classList.remove('hidden');
            if (txtPersonalizada) {
                txtPersonalizada.textContent = query.trim() || 'Nueva Marca';
            }
            return;
        }

        grid.classList.remove('hidden');
        empty.classList.add('hidden');

        lista.forEach(brand => {
            const isSelected = valorActual && (brand.nombre.toLowerCase() === valorActual || brand.slug.toLowerCase() === valorActual);
            
            const card = document.createElement('div');
            card.className = `p-2 rounded-xl border transition-all cursor-pointer text-center flex flex-col items-center justify-between group ${
                isSelected 
                    ? 'bg-emerald-50/80 border-emerald-400 ring-2 ring-emerald-500/20 shadow-xs' 
                    : 'bg-white border-slate-200/80 hover:border-slate-400 hover:shadow-xs'
            }`;
            card.onclick = () => seleccionarMarca(brand.nombre);

            card.innerHTML = `
                <div class="w-full h-9 rounded-lg bg-white border border-slate-100 flex items-center justify-center p-1 mb-1 overflow-hidden shadow-2xs group-hover:scale-105 transition-transform">
                    ${getLogoHtmlForBrand(brand)}
                </div>
                <div class="w-full">
                    <div class="text-[11px] font-bold text-slate-800 truncate group-hover:text-slate-900">${brand.nombre}</div>
                    ${brand.is_suggested ? '<span class="inline-flex items-center gap-0.5 text-[8.5px] font-bold text-amber-600"><span class="material-symbols-outlined text-[9px]">star</span>Sugerida</span>' : ''}
                </div>
            `;
            grid.appendChild(card);
        });
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

    // Cerrar modal con tecla Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            cerrarModalMarcas();
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        calcularMargen();
        const valorInicial = document.getElementById('input-marca-valor')?.value;
        if (valorInicial) {
            actualizarUIMarca(valorInicial);
        }
    });
</script>
@endsection
