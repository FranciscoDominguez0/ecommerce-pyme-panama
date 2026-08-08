@extends('layouts.admin')

@section('title', 'Producto del Mes — PayMe Panamá')

@section('breadcrumbs')
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <span class="text-slate-600">Promociones</span>
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <span class="font-bold text-slate-900 truncate">Producto del Mes</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-600 text-[24px]">star</span>
                Promociones Especiales
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Destaca un producto estrella con precio promocional en la portada de la tienda.</p>
        </div>
    </div>

    <!-- Navigation Tabs (Screen 3 Stitch) -->
    <div class="flex border-b border-slate-200 gap-6">
        <a href="{{ route('admin.promociones.envio-gratis') }}" 
           class="pb-3 text-xs font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-900 transition-colors flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">local_shipping</span>
            <span>Reglas de Envío Gratis</span>
        </a>
        <a href="{{ route('admin.promociones.producto-del-mes') }}" 
           class="pb-3 text-xs font-bold border-b-2 border-emerald-600 text-emerald-700 flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">star</span>
            <span>Producto del Mes</span>
        </a>
    </div>

    <!-- Main Section Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-sm font-extrabold text-slate-900">Producto Destacado del Mes</h2>
            <p class="text-xs text-slate-500">Configura el producto con descuento especial de alto impacto.</p>
        </div>
    </div>

    <!-- 12-Columns Split Layout (Screen 3 Stitch: 5 cols Left Card Spotlight, 7 cols Right Form) -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        
        <!-- Left Spotlight Preview Card (5 cols) -->
        <div class="md:col-span-5 bg-white border border-slate-200/80 rounded-2xl p-5 shadow-2xs flex flex-col justify-between">
            <div>
                <!-- Image Container with Discount Badge -->
                <div class="w-full h-56 bg-slate-50 rounded-xl mb-4 overflow-hidden relative border border-slate-100 flex items-center justify-center">
                    @if($promocionActual && $promocionActual->producto)
                        @php
                            $imgObj = $promocionActual->producto->imagenes->where('es_principal', true)->first() ?? $promocionActual->producto->imagenes->first();
                        @endphp
                        @if($imgObj)
                            <img id="spotlight-img" src="{{ asset($imgObj->ruta) }}" alt="Producto" class="w-full h-full object-cover">
                        @else
                            <div id="spotlight-placeholder" class="text-slate-300 flex flex-col items-center">
                                <span class="material-symbols-outlined text-[48px]">inventory_2</span>
                            </div>
                        @endif
                    @else
                        <div id="spotlight-placeholder" class="text-slate-300 flex flex-col items-center">
                            <span class="material-symbols-outlined text-[48px]">inventory_2</span>
                            <span class="text-xs font-semibold text-slate-400 mt-1">Sin producto seleccionado</span>
                        </div>
                    @endif

                    <div id="spotlight-badge" class="absolute top-3 right-3 bg-rose-600 text-white px-2.5 py-1 rounded-lg text-xs font-extrabold shadow-2xs">
                        -{{ number_format($promocionActual ? $promocionActual->descuento_especial : 20, 0) }}%
                    </div>
                </div>

                <!-- Info -->
                <div class="text-[11px] font-bold text-slate-400 tracking-wider uppercase mb-1" id="spotlight-sku">
                    SKU: {{ $promocionActual->producto->sku ?? 'PROD-001' }}
                </div>
                <h3 class="text-base font-extrabold text-slate-900 leading-tight mb-2" id="spotlight-titulo">
                    {{ $promocionActual->producto->nombre ?? 'Selecciona un Producto' }}
                </h3>

                <!-- Price display -->
                <div class="flex items-baseline gap-3 mt-4">
                    <span class="text-3xl font-extrabold text-slate-900" id="spotlight-precio-promo">
                        ${{ number_format($promocionActual ? $promocionActual->precioPromocional() : 0, 2) }}
                    </span>
                    <span class="text-sm font-semibold text-slate-400 line-through" id="spotlight-precio-base">
                        ${{ number_format($promocionActual->producto->precio ?? 0, 2) }}
                    </span>
                </div>
            </div>

            <!-- Status Indicator -->
            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500 font-medium">Estado de publicación:</span>
                @if($promocionActual && $promocionActual->esVigente())
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Vigente en Portada
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                        Inactivo
                    </span>
                @endif
            </div>
        </div>

        <!-- Right Settings Form Panel (7 cols) -->
        <div class="md:col-span-7 bg-white border border-slate-200/80 rounded-2xl p-6 shadow-2xs flex flex-col justify-between">
            <form action="{{ route('admin.promociones.producto-del-mes.guardar') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <input type="hidden" id="producto_id" name="producto_id" value="{{ old('producto_id', $promocionActual->producto_id ?? '') }}" required>

                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-900 flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-600 text-[18px]">settings</span>
                        Configuración de Promoción
                    </h3>
                    <button type="button" 
                            onclick="abrirModalSeleccionarProducto()" 
                            class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition-all flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">search</span>
                        <span>Seleccionar Producto</span>
                    </button>
                </div>

                <!-- Selected Product Display Box -->
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-lg bg-white border border-slate-200 flex items-center justify-center shrink-0 shadow-2xs">
                            <span class="material-symbols-outlined text-slate-600">inventory_2</span>
                        </div>
                        <div class="min-w-0">
                            <span id="display-prod-nombre" class="text-xs font-bold text-slate-900 truncate block">
                                {{ $promocionActual->producto->nombre ?? 'Ningún producto seleccionado' }}
                            </span>
                            <span id="display-prod-precio" class="text-[11px] text-slate-500 font-medium block">
                                Precio Base: ${{ number_format($promocionActual->producto->precio ?? 0, 2) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Descuento y Precio Final Calculado -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="descuento_especial" class="block text-[11px] font-bold text-slate-700 uppercase mb-1">
                            Descuento Especial (%) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   id="descuento_especial" 
                                   name="descuento_especial" 
                                   min="1" 
                                   max="99" 
                                   value="{{ old('descuento_especial', $promocionActual->descuento_especial ?? 20) }}" 
                                   oninput="calcularPrecioPromocional()" 
                                   required 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-3.5 pr-8 py-2.5 text-xs text-slate-900 font-bold focus:bg-white focus:border-emerald-500 outline-none">
                            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">percent</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">
                            Precio Final Calculado
                        </label>
                        <div id="calc-precio-final" class="w-full bg-emerald-50 border border-emerald-200 rounded-xl px-3.5 py-2.5 text-xs text-emerald-900 font-extrabold flex items-center">
                            ${{ number_format($promocionActual ? $promocionActual->precioPromocional() : 0, 2) }}
                        </div>
                    </div>
                </div>

                <!-- Vigencia -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="inicio_en" class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Fecha de Inicio <span class="text-rose-500">*</span></label>
                        <input type="date" 
                               id="inicio_en" 
                               name="inicio_en" 
                               value="{{ old('inicio_en', $promocionActual && $promocionActual->inicio_en ? $promocionActual->inicio_en->format('Y-m-d') : now()->format('Y-m-d')) }}" 
                               required 
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 font-semibold focus:bg-white focus:border-emerald-500 outline-none">
                    </div>

                    <div>
                        <label for="fin_en" class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Fecha de Fin <span class="text-rose-500">*</span></label>
                        <input type="date" 
                               id="fin_en" 
                               name="fin_en" 
                               value="{{ old('fin_en', $promocionActual && $promocionActual->fin_en ? $promocionActual->fin_en->format('Y-m-d') : now()->addDays(30)->format('Y-m-d')) }}" 
                               required 
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 font-semibold focus:bg-white focus:border-emerald-500 outline-none">
                    </div>
                </div>

                <!-- Descripción Banner (Opcional) -->
                <div>
                    <label for="descripcion_mes" class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Frase Promocional / Leyenda del Mes</label>
                    <input type="text" 
                           id="descripcion_mes" 
                           name="descripcion_mes" 
                           value="{{ old('descripcion_mes', $promocionActual->descripcion_mes ?? '') }}" 
                           placeholder="Ej. ¡Aprovecha la super oferta de la semana con envío relámpago!" 
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 font-medium focus:bg-white focus:border-emerald-500 outline-none">
                </div>

                <!-- Estado Switch -->
                <label class="flex items-center gap-3 cursor-pointer text-xs font-semibold text-slate-800 pt-2">
                    <input type="checkbox" name="activo" value="1" {{ old('activo', $promocionActual->activo ?? true) ? 'checked' : '' }} class="w-4 h-4 text-emerald-600 rounded focus:ring-emerald-500">
                    <span>Activar promoción inmediatamente en portada</span>
                </label>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[18px]">save</span>
                        <span>Guardar Cambios</span>
                    </button>
                </div>
            </form>
        </div>

    </div>

</div>

<!-- Modal Buscador de Productos -->
<x-modal-busqueda 
    id="modal-selector-producto" 
    titulo="Seleccionar Producto del Mes" 
    subtitulo="Busca el producto que deseas destacar en portada" 
    icono="inventory_2" 
    placeholder="Buscar por nombre o SKU..." 
    :porPagina="15" 
/>

@push('scripts')
<script>
    const productosData = @json($productosFormatted ?? []);
    let precioBaseSeleccionado = {{ $promocionActual->producto->precio ?? 0 }};

    document.addEventListener('DOMContentLoaded', () => {
        if (window.ModalBuscador) {
            window.ModalBuscador.init('modal-selector-producto', {
                items: productosData,
                porPagina: 15,
                emptyText: 'No se encontró ningún producto para',
                render: (prod) => {
                    const card = document.createElement('div');
                    card.className = `p-3 rounded-xl border transition-all cursor-pointer flex items-center justify-between group bg-white border-slate-200/90 hover:border-emerald-500 hover:bg-emerald-50/40 hover:shadow-2xs`;
                    card.onclick = () => seleccionarProductoModal(prod);

                    card.innerHTML = `
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center shrink-0 overflow-hidden border border-slate-200">
                                ${prod.imagen_url ? `<img src="${prod.imagen_url}" class="w-full h-full object-cover">` : '<span class="material-symbols-outlined text-slate-400">inventory_2</span>'}
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-slate-900 group-hover:text-emerald-950 truncate">${prod.nombre}</div>
                                <div class="text-[10px] text-slate-400 font-mono">${prod.sku} — $${prod.precio_base.toFixed(2)}</div>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-emerald-600 opacity-0 group-hover:opacity-100 transition-opacity">add_circle</span>
                    `;
                    return card;
                }
            });
        }
    });

    function abrirModalSeleccionarProducto() {
        if (window.ModalBuscador) window.ModalBuscador.abrir('modal-selector-producto');
    }

    function seleccionarProductoModal(prod) {
        document.getElementById('producto_id').value = prod.id;
        document.getElementById('display-prod-nombre').textContent = prod.nombre;
        document.getElementById('display-prod-precio').textContent = `Precio Base: $${prod.precio_base.toFixed(2)}`;
        
        document.getElementById('spotlight-titulo').textContent = prod.nombre;
        document.getElementById('spotlight-sku').textContent = `SKU: ${prod.sku}`;
        document.getElementById('spotlight-precio-base').textContent = `$${prod.precio_base.toFixed(2)}`;

        if (prod.imagen_url) {
            const img = document.getElementById('spotlight-img');
            if (img) img.src = prod.imagen_url;
        }

        precioBaseSeleccionado = prod.precio_base;
        calcularPrecioPromocional();

        if (window.ModalBuscador) window.ModalBuscador.cerrar('modal-selector-producto');
    }

    function calcularPrecioPromocional() {
        const pctInput = document.getElementById('descuento_especial');
        const pct = parseFloat(pctInput.value || 0);

        if (precioBaseSeleccionado <= 0) return;

        const desc = (precioBaseSeleccionado * pct) / 100;
        const final = Math.max(0, precioBaseSeleccionado - desc);

        document.getElementById('calc-precio-final').textContent = `$${final.toFixed(2)}`;
        document.getElementById('spotlight-precio-promo').textContent = `$${final.toFixed(2)}`;
        document.getElementById('spotlight-badge').textContent = `-${pct.toFixed(0)}%`;
    }
</script>
@endpush
@endsection
