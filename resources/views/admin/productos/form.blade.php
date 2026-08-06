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
<div class="space-y-6 w-full min-w-0 max-w-full">

    {{-- Alerta de éxito animada (Toast flotante) --}}
    @if(session('success'))
        <div id="alerta-exito" class="fixed top-6 right-6 z-[100] flex items-center gap-3.5 px-4 py-4 rounded-2xl bg-white border border-emerald-100 shadow-2xl text-slate-700 text-sm font-bold animate-in fade-in slide-in-from-top-8 duration-500 max-w-sm overflow-hidden">
            <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-emerald-600 text-[24px]">check_circle</span>
            </div>
            <span class="flex-1">{{ session('success') }}</span>
            <button type="button" onclick="cerrarAlerta()" class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-lg hover:bg-slate-100">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
            <div class="absolute bottom-0 left-0 h-1 bg-emerald-500/20 w-full">
                <div id="alerta-barra" class="h-full bg-emerald-500 w-full transition-all duration-[4000ms] ease-linear"></div>
            </div>
        </div>
        <script>
            function cerrarAlerta() {
                const alerta = document.getElementById('alerta-exito');
                if(!alerta) return;
                alerta.classList.replace('animate-in', 'animate-out');
                alerta.classList.replace('fade-in', 'fade-out');
                alerta.classList.replace('slide-in-from-top-8', 'slide-out-to-top-8');
                setTimeout(() => alerta.remove(), 300);
            }
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    const barra = document.getElementById('alerta-barra');
                    if(barra) barra.style.width = '0%';
                }, 50);
                setTimeout(() => cerrarAlerta(), 4000);
            });
        </script>
    @endif


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
              class="space-y-6">
            @csrf
            @method('PUT')
    @else
        <form id="form-producto" method="POST"
              action="{{ route('admin.productos.store') }}"
              enctype="multipart/form-data"
              class="space-y-6">
            @csrf
    @endif
        
        <!-- Header con Acciones Principales -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500/20 to-teal-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-600 shadow-xs">
                        <span class="material-symbols-outlined text-[24px]">{{ ($esEdicion ?? false) ? 'edit_note' : 'add_box' }}</span>
                    </div>
                    <div>
                        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                            {{ ($esEdicion ?? false) ? 'Editar Producto & Variantes' : 'Crear Nuevo Producto' }}
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                            Completa los detalles del artículo, atributos, precios e inventario para la tienda online.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <a href="{{ route('admin.productos.index') }}" 
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-600 bg-white border border-slate-200 hover:bg-slate-50 transition-all shadow-xs">
                    <span class="material-symbols-outlined text-[18px] text-slate-400">arrow_back</span>
                    <span>Volver</span>
                </a>
                
                @if(($esEdicion ?? false) && !empty($producto->slug))
                    <a href="{{ route('cliente.producto.detalle', $producto->slug) }}" 
                       target="_blank"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 transition-all shadow-xs">
                        <span class="material-symbols-outlined text-[18px]">visibility</span>
                        <span>Ver en Tienda</span>
                    </a>
                @endif

                <button type="submit" 
                        class="inline-flex items-center gap-1.5 px-5 py-2 bg-slate-900 hover:bg-slate-800 rounded-xl text-xs font-bold text-white shadow-sm transition-all transform active:scale-95">
                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                    <span>{{ ($esEdicion ?? false) ? 'Actualizar Producto' : 'Publicar Producto' }}</span>
                </button>
            </div>
        </div>

        <!-- Grid Principal: 2 Columnas (8 cols contenido / 4 cols lateral) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
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
                            <label for="nombre" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
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
                                <label for="slug" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
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
                                <label for="sku" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                    SKU Base <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       id="sku" 
                                       name="sku" 
                                       required
                                       value="{{ old('sku', $producto->sku ?? '') }}" 
                                       placeholder="PROD-001" 
                                       class="input-panama w-full text-xs font-mono uppercase rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 py-2.5 px-3">
                            </div>

                            <div>
                                <label for="categoria_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
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

                        <!-- Descripción Corta -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label for="descripcion_corta" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
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
                            <label for="descripcion" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
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
                        <span class="text-[11px] text-slate-400">Moneda oficial: USD ($ PAB)</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Precio Base -->
                        <div>
                            <label for="precio" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
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
                            <label for="precio_oferta" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Precio Oferta (Opcional)
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
                                       class="input-panama w-full pl-7 pr-3 py-2.5 text-xs font-bold text-slate-900 rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20">
                            </div>
                        </div>

                        <!-- Costo Unitario / Privado -->
                        <div>
                            <label for="costo_unitario" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">
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
                    <div id="badge-margen" class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-emerald-600 text-[18px]">query_stats</span>
                            <span class="font-medium text-slate-600">Margen Bruto Estimado:</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span id="margen-monto" class="font-bold text-slate-900">+$0.00 por unidad</span>
                            <span id="margen-porcentaje" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">0% margen</span>
                        </div>
                    </div>
                </div>

                <!-- CARD 3: Submódulo de Galería de Imágenes -->
                @include('admin.productos.imagenes', ['imagenes' => $producto->imagenes ?? collect()])

                <!-- CARD 4: Submódulo Constructor de Variantes -->
                @include('admin.productos.variantes', [
                    'variantes' => $producto->variantes ?? collect(),
                    'tiposVariante' => $tiposVariante ?? collect()
                ])



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
                                    class="input-panama w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 py-2 px-3">
                                <option value="1" @selected(old('activo', $producto->activo ?? true) == true)>🟢 Activo (Visible en tienda)</option>
                                <option value="0" @selected(old('activo', $producto->activo ?? true) == false)>⚪ Inactivo (Borrador oculto)</option>
                            </select>
                        </div>

                        <!-- Switch de Producto Destacado -->
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <div class="space-y-0.5">
                                <p class="text-xs font-bold text-slate-800 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-amber-500 text-[16px]">star</span>
                                    <span>Producto Destacado</span>
                                </p>
                                <p class="text-[10px] text-slate-500">Mostrar en la sección de destacados de la portada</p>
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
                                    <span>Aplica ITBMS (7%)</span>
                                </p>
                                <p class="text-[10px] text-slate-500">Impuesto fiscal en Panamá</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="aplica_itbms" value="1" class="sr-only peer" @checked(old('aplica_itbms', $producto->aplica_itbms ?? true))>
                                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- CARD LATERAL 2: Inventario & Stock -->
                <div class="card-elevated p-5 rounded-2xl space-y-4">
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                        <span class="material-symbols-outlined text-emerald-600 text-[20px]">warehouse</span>
                        <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Inventario & Stock</h3>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label for="stock" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Stock Total Disponible
                            </label>
                            <input type="number" 
                                   id="stock" 
                                   name="stock" 
                                   min="0" 
                                   value="{{ old('stock', $producto->stock ?? 0) }}" 
                                   class="input-panama w-full text-xs font-bold rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 py-2 px-3">
                        </div>

                        <div>
                            <label for="stock_minimo" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                                Stock Mínimo para Alerta
                            </label>
                            <input type="number" 
                                   id="stock_minimo" 
                                   name="stock_minimo" 
                                   min="0" 
                                   value="{{ old('stock_minimo', $producto->stock_minimo ?? 3) }}" 
                                   class="input-panama w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 py-2 px-3">
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </form>

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
            actualizarSerp();
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

    function actualizarSerp() {
        const nombre = document.getElementById('nombre')?.value || 'Nombre del Producto';
        const metaTitulo = document.getElementById('meta_titulo')?.value || nombre;
        const slug = document.getElementById('slug')?.value || 'nuevo-producto';
        const desc = document.getElementById('meta_descripcion')?.value || document.getElementById('descripcion_corta')?.value || 'Descripción del producto...';

        const titleEl = document.getElementById('serp-title');
        const urlEl = document.getElementById('serp-url');
        const snipEl = document.getElementById('serp-snippet');

        if (titleEl) titleEl.textContent = `${metaTitulo} | Tu Tienda Online`;
        if (urlEl) urlEl.textContent = `https://tutienda.com/producto/${slug}`;
        if (snipEl) snipEl.textContent = desc;
    }

    document.addEventListener('DOMContentLoaded', () => {
        calcularMargen();
    });
</script>
@endsection
