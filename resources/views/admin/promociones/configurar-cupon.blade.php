@extends('layouts.admin')

@section('title', ($esEdicion ? 'Editar Cupón' : 'Configurar Cupón') . ' — PayMe Panamá')

@section('breadcrumbs')
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <a href="{{ route('admin.promociones.cupones') }}" class="text-slate-600 hover:text-slate-900 transition-colors">Cupones</a>
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <span class="font-bold text-slate-900 truncate">{{ $esEdicion ? 'Editar Cupón' : 'Configurar Cupón' }}</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Header & Action Bar -->
    <div class="flex items-center justify-between bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-600 text-[24px]">local_offer</span>
                {{ $esEdicion ? "Editar Cupón: {$cupon->codigo}" : 'Configurar Nuevo Cupón' }}
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Define las reglas, vigencia y restricciones del código de descuento.</p>
        </div>

        <a href="{{ route('admin.promociones.cupones') }}" 
           class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors flex items-center gap-1.5 shrink-0">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span>
            Volver al listado
        </a>
    </div>

    <!-- Main Grid Form (2 Columns) -->
    <form action="{{ $esEdicion ? route('admin.promociones.cupones.actualizar', $cupon->id) : route('admin.promociones.cupones.guardar') }}" 
          method="POST" 
          class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @csrf
        @if($esEdicion)
            @method('PUT')
        @endif

        <!-- Left Column: 3 Form Sections (Col-span 2) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Section 1: Configuración Básica -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs space-y-4">
                <div class="flex items-center gap-2.5 border-b border-slate-100 pb-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shadow-2xs">
                        <span class="material-symbols-outlined text-[18px]">local_offer</span>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">1. Configuración Básica</h2>
                        <p class="text-[11px] text-slate-500">Código, tipo y valor del descuento.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <!-- Código del cupón -->
                    <div>
                        <label for="codigo" class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Código del Cupón <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               id="codigo" 
                               name="codigo" 
                               value="{{ old('codigo', $cupon->codigo) }}" 
                               placeholder="EJ. VERANO2026" 
                               required 
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-900 font-mono font-bold tracking-widest uppercase focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none">
                        <p class="text-[11px] text-slate-400 mt-1">Los clientes ingresarán este código en la pantalla de pago.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Tipo de Descuento -->
                        <div>
                            <label for="tipo" class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Tipo de Descuento <span class="text-rose-500">*</span>
                            </label>
                            <select id="tipo" 
                                    name="tipo" 
                                    required 
                                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-900 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none cursor-pointer">
                                <option value="porcentaje" {{ old('tipo', $cupon->tipo) === 'porcentaje' ? 'selected' : '' }}>Porcentaje (%)</option>
                                <option value="monto_fijo" {{ old('tipo', $cupon->tipo) === 'monto_fijo' ? 'selected' : '' }}>Monto Fijo ($ USD/PAB)</option>
                                <option value="envio_gratis" {{ old('tipo', $cupon->tipo) === 'envio_gratis' ? 'selected' : '' }}>Envío Gratis</option>
                            </select>
                        </div>

                        <!-- Valor del Descuento -->
                        <div>
                            <label for="valor" class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Valor del Descuento <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" 
                                       id="valor" 
                                       name="valor" 
                                       step="0.01" 
                                       min="0" 
                                       value="{{ old('valor', $cupon->valor) }}" 
                                       required 
                                       placeholder="0.00" 
                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3.5 py-2.5 text-xs text-slate-900 font-bold focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none">
                                <span id="valor-symbol" class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">percent</span>
                            </div>
                        </div>
                    </div>

                    <!-- Estado Inicial -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Estado Inicial
                        </label>
                        <div class="flex items-center gap-6">
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-800">
                                <input type="radio" name="activo" value="1" {{ old('activo', $cupon->activo) ? 'checked' : '' }} class="text-emerald-600 focus:ring-emerald-500">
                                <span>Activo (Disponible de inmediato)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-600">
                                <input type="radio" name="activo" value="0" {{ !old('activo', $cupon->activo) ? 'checked' : '' }} class="text-emerald-600 focus:ring-emerald-500">
                                <span>Inactivo (Borrador)</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Restricciones y Límites -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs space-y-4">
                <div class="flex items-center gap-2.5 border-b border-slate-100 pb-3">
                    <div class="w-8 h-8 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-2xs">
                        <span class="material-symbols-outlined text-[18px]">rule</span>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">2. Restricciones y Límites</h2>
                        <p class="text-[11px] text-slate-500">Monto mínimo y límites de usos permitidos.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <!-- Monto mínimo -->
                    <div>
                        <label for="monto_minimo" class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Monto Mínimo de Compra ($ PAB)
                        </label>
                        <div class="relative">
                            <input type="number" 
                                   id="monto_minimo" 
                                   name="monto_minimo" 
                                   step="0.01" 
                                   min="0" 
                                   value="{{ old('monto_minimo', $cupon->monto_minimo) }}" 
                                   placeholder="Opcional (ej. 50.00)" 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3.5 py-2.5 text-xs text-slate-900 font-bold focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none">
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">attach_money</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">El cliente debe tener este subtotal en su carrito para activar el cupón.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Límite total de usos -->
                        <div>
                            <label for="maximo_usos_total" class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Límite Total de Usos
                            </label>
                            <input type="number" 
                                   id="maximo_usos_total" 
                                   name="maximo_usos_total" 
                                   min="1" 
                                   value="{{ old('maximo_usos_total', $cupon->maximo_usos_total) }}" 
                                   placeholder="Ilimitado" 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 font-semibold focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none">
                            <span class="text-[10px] text-slate-400 block mt-1">Dejar vacío para usos ilimitados en la tienda.</span>
                        </div>

                        <!-- Usos por cliente -->
                        <div>
                            <label for="usos_por_cliente" class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Usos Permetidos por Cliente
                            </label>
                            <input type="number" 
                                   id="usos_por_cliente" 
                                   name="usos_por_cliente" 
                                   min="1" 
                                   value="{{ old('usos_por_cliente', $cupon->usos_por_cliente ?? 1) }}" 
                                   placeholder="1" 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 font-semibold focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none">
                            <span class="text-[10px] text-slate-400 block mt-1">Límite por usuario (validado vía usos_cupon).</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Aplicación y Vigencia -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs space-y-4">
                <div class="flex items-center gap-2.5 border-b border-slate-100 pb-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shadow-2xs">
                        <span class="material-symbols-outlined text-[18px]">event_available</span>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">3. Aplicación y Vigencia</h2>
                        <p class="text-[11px] text-slate-500">A qué productos o categorías aplica y sus fechas activas.</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <!-- Aplicar a -->
                    <div>
                        <label for="aplica_a" class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Aplicar Descuento A <span class="text-rose-500">*</span>
                        </label>
                        <select id="aplica_a" 
                                name="aplica_a" 
                                onchange="cambiarAlcance(this.value)" 
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-900 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none cursor-pointer">
                            <option value="catalogo" {{ old('aplica_a', $cupon->aplica_a) === 'catalogo' || old('aplica_a', $cupon->aplica_a) === 'todo' ? 'selected' : '' }}>Todo el Catálogo</option>
                            <option value="categoria" {{ old('aplica_a', $cupon->aplica_a) === 'categoria' ? 'selected' : '' }}>Categoría Específica</option>
                            <option value="producto" {{ old('aplica_a', $cupon->aplica_a) === 'producto' ? 'selected' : '' }}>Producto Específico</option>
                        </select>
                    </div>

                    <!-- Selector Categoría con Modal -->
                    <div id="wrapper-categoria" class="{{ old('aplica_a', $cupon->aplica_a) === 'categoria' ? '' : 'hidden' }}">
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Seleccionar Categoría <span class="text-rose-500">*</span>
                        </label>
                        <input type="hidden" id="categoria_id" name="categoria_id" value="{{ old('categoria_id', $cupon->categoria_id) }}">
                        
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-lg bg-white border border-slate-200 flex items-center justify-center shrink-0 shadow-2xs">
                                    <span class="material-symbols-outlined text-emerald-600 text-[18px]">category</span>
                                </div>
                                <div class="min-w-0">
                                    <span id="display-categoria-nombre" class="text-xs font-bold text-slate-900 truncate block">
                                        {{ $cupon->categoria ? $cupon->categoria->nombre : 'Ninguna categoría seleccionada' }}
                                    </span>
                                </div>
                            </div>
                            <button type="button" 
                                    onclick="abrirModalCategoria()" 
                                    class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 shrink-0">
                                <span class="material-symbols-outlined text-[16px]">search</span>
                                <span>Buscar Categoría</span>
                            </button>
                        </div>
                    </div>

                    <!-- Selector Producto con Modal -->
                    <div id="wrapper-producto" class="{{ old('aplica_a', $cupon->aplica_a) === 'producto' ? '' : 'hidden' }}">
                        <label class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                            Seleccionar Producto <span class="text-rose-500">*</span>
                        </label>
                        <input type="hidden" id="producto_id" name="producto_id" value="{{ old('producto_id', $cupon->producto_id) }}">
                        
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-lg bg-white border border-slate-200 flex items-center justify-center shrink-0 shadow-2xs">
                                    <span class="material-symbols-outlined text-emerald-600 text-[18px]">inventory_2</span>
                                </div>
                                <div class="min-w-0">
                                    <span id="display-producto-nombre" class="text-xs font-bold text-slate-900 truncate block">
                                        {{ $cupon->producto ? $cupon->producto->nombre : 'Ningún producto seleccionado' }}
                                    </span>
                                    <span id="display-producto-sku" class="text-[10px] text-slate-400 font-mono block">
                                        {{ $cupon->producto ? 'SKU: ' . ($cupon->producto->sku ?? "PROD-{$cupon->producto->id}") : '' }}
                                    </span>
                                </div>
                            </div>
                            <button type="button" 
                                    onclick="abrirModalProductoCupon()" 
                                    class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition-all flex items-center gap-1.5 shrink-0">
                                <span class="material-symbols-outlined text-[16px]">search</span>
                                <span>Buscar Producto</span>
                            </button>
                        </div>
                    </div>

                    <!-- Fechas -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="inicio_en" class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Fecha y Hora de Inicio <span class="text-rose-500">*</span>
                            </label>
                            <input type="datetime-local" 
                                   id="inicio_en" 
                                   name="inicio_en" 
                                   value="{{ old('inicio_en', $cupon->inicio_en ? $cupon->inicio_en->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}" 
                                   required 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-900 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none">
                        </div>

                        <div>
                            <label for="fin_en" class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-1">
                                Fecha y Hora de Fin (Opcional)
                            </label>
                            <input type="datetime-local" 
                                   id="fin_en" 
                                   name="fin_en" 
                                   value="{{ old('fin_en', $cupon->fin_en ? $cupon->fin_en->format('Y-m-d\TH:i') : '') }}" 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-900 focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none">
                            <span class="text-[10px] text-slate-400 block mt-1">Dejar vacío para vigencia indefinida.</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column: Ticket Live Preview (Col-span 1) -->
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden sticky top-20">
                <div class="p-4 border-b border-slate-100 bg-slate-50/70 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-slate-900 flex items-center gap-2">
                        <span class="material-symbols-outlined text-emerald-600 text-[18px]">visibility</span>
                        Vista Previa del Cupón
                    </h3>
                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100/70 px-2 py-0.5 rounded-full uppercase">Ticket Vivo</span>
                </div>

                <div class="p-5 space-y-5">
                    <!-- Ticket Card Simulation -->
                    <div class="border border-slate-200 rounded-2xl p-5 bg-gradient-to-br from-slate-50 via-white to-emerald-50/30 shadow-xs relative overflow-hidden">
                        <!-- Top Accent Badge -->
                        <div class="flex items-center justify-between mb-4">
                            <span id="preview-tipo-badge" class="px-2.5 py-1 bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-lg text-[10px] font-bold uppercase tracking-wider">
                                Porcentaje
                            </span>
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        </div>

                        <!-- Big Value Display -->
                        <div class="mb-4">
                            <div id="preview-valor-display" class="text-3xl font-extrabold text-slate-900 tracking-tight">
                                10% OFF
                            </div>
                            <span class="text-[11px] text-slate-500 font-medium block mt-0.5">Descuento aplicable en checkout</span>
                        </div>

                        <!-- Code Box -->
                        <div class="mb-5">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Código Promocional</span>
                            <div id="preview-codigo-box" class="px-3.5 py-2 border-2 border-dashed border-slate-300 rounded-xl bg-white text-sm font-mono font-bold text-emerald-700 tracking-widest text-center shadow-2xs">
                                {{ strtoupper($cupon->codigo ?: 'CUPON2026') }}
                            </div>
                        </div>

                        <!-- Conditions List -->
                        <ul class="space-y-2 text-xs font-medium text-slate-600 border-t border-slate-200/60 pt-3">
                            <li class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-emerald-600 text-[16px]">check_circle</span>
                                <span id="preview-alcance-text">Aplica a todo el catálogo</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-slate-400 text-[16px]">info</span>
                                <span id="preview-minimo-text">Sin mínimo de compra</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-slate-400 text-[16px]">calendar_today</span>
                                <span id="preview-vigencia-text">Vigente</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-2 pt-2">
                        <button type="submit" 
                                class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">save</span>
                            <span>{{ $esEdicion ? 'Actualizar Cupón' : 'Guardar Cupón' }}</span>
                        </button>

                        <a href="{{ route('admin.promociones.cupones') }}" 
                           class="w-full py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl transition-all flex items-center justify-center">
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>

    </form>

</div>

<!-- Modal Selector de Categoría -->
<x-modal-busqueda 
    id="modal-selector-categoria" 
    titulo="Seleccionar Categoría" 
    subtitulo="Busca y elige la categoría aplicable al cupón" 
    icono="category" 
    placeholder="Filtrar por nombre de categoría..." 
    :porPagina="15" 
/>

<!-- Modal Selector de Producto -->
<x-modal-busqueda 
    id="modal-selector-producto-cupon" 
    titulo="Seleccionar Producto" 
    subtitulo="Busca y elige el producto aplicable al cupón" 
    icono="inventory_2" 
    placeholder="Filtrar por nombre o SKU..." 
    :porPagina="15" 
/>

@push('scripts')
<script>
    const categoriasData = @json($categoriasFormatted ?? []);
    const productosData = @json($productosFormatted ?? []);

    function cambiarAlcance(val) {
        document.getElementById('wrapper-categoria').classList.toggle('hidden', val !== 'categoria');
        document.getElementById('wrapper-producto').classList.toggle('hidden', val !== 'producto');
        actualizarPreview();
    }

    function abrirModalCategoria() {
        if (window.ModalBuscador) window.ModalBuscador.abrir('modal-selector-categoria');
    }

    function seleccionarCategoriaModal(cat) {
        document.getElementById('categoria_id').value = cat.id;
        document.getElementById('display-categoria-nombre').textContent = cat.nombre;
        actualizarPreview();
        if (window.ModalBuscador) window.ModalBuscador.cerrar('modal-selector-categoria');
    }

    function abrirModalProductoCupon() {
        if (window.ModalBuscador) window.ModalBuscador.abrir('modal-selector-producto-cupon');
    }

    function seleccionarProductoCuponModal(prod) {
        document.getElementById('producto_id').value = prod.id;
        document.getElementById('display-producto-nombre').textContent = prod.nombre;
        document.getElementById('display-producto-sku').textContent = `SKU: ${prod.sku}`;
        actualizarPreview();
        if (window.ModalBuscador) window.ModalBuscador.cerrar('modal-selector-producto-cupon');
    }

    function actualizarPreview() {
        const codigoInput = document.getElementById('codigo');
        const tipoInput = document.getElementById('tipo');
        const valorInput = document.getElementById('valor');
        const minimoInput = document.getElementById('monto_minimo');
        const aplicaInput = document.getElementById('aplica_a');

        const prevCodigo = document.getElementById('preview-codigo-box');
        const prevTipo = document.getElementById('preview-tipo-badge');
        const prevValor = document.getElementById('preview-valor-display');
        const prevMinimo = document.getElementById('preview-minimo-text');
        const prevAlcance = document.getElementById('preview-alcance-text');
        const valorSymbol = document.getElementById('valor-symbol');

        if (!codigoInput || !tipoInput) return;

        // Código
        const cod = (codigoInput.value || '').trim().toUpperCase();
        prevCodigo.textContent = cod || 'EJ: CUPON2026';

        // Tipo & Valor
        const tipo = tipoInput.value;
        const val = parseFloat(valorInput.value || 0);

        if (tipo === 'porcentaje') {
            prevTipo.textContent = 'PORCENTAJE';
            prevValor.textContent = `${val}% OFF`;
            if (valorSymbol) valorSymbol.textContent = 'percent';
        } else if (tipo === 'monto_fijo') {
            prevTipo.textContent = 'MONTO FIJO';
            prevValor.textContent = `$${val.toFixed(2)} OFF`;
            if (valorSymbol) valorSymbol.textContent = 'attach_money';
        } else {
            prevTipo.textContent = 'ENVÍO GRATIS';
            prevValor.textContent = 'ENVÍO GRATIS';
            if (valorSymbol) valorSymbol.textContent = 'local_shipping';
        }

        // Mínimo
        const min = parseFloat(minimoInput.value || 0);
        if (min > 0) {
            prevMinimo.textContent = `Compra mín. $${min.toFixed(2)}`;
        } else {
            prevMinimo.textContent = 'Sin mínimo de compra';
        }

        // Alcance
        const alc = aplicaInput.value;
        if (alc === 'catalogo' || alc === 'todo') {
            prevAlcance.textContent = 'Aplica a todo el catálogo';
        } else if (alc === 'categoria') {
            const txt = document.getElementById('display-categoria-nombre')?.textContent.trim();
            prevAlcance.textContent = `Categoría: ${txt && txt !== 'Ninguna categoría seleccionada' ? txt : 'Seleccionada'}`;
        } else {
            const txt = document.getElementById('display-producto-nombre')?.textContent.trim();
            prevAlcance.textContent = `Producto: ${txt && txt !== 'Ningún producto seleccionado' ? txt : 'Seleccionado'}`;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Inicializar modal de búsqueda de Categorías (Regla #6: 15 elementos con paginación e imágenes)
        if (window.ModalBuscador) {
            window.ModalBuscador.init('modal-selector-categoria', {
                items: categoriasData,
                porPagina: 15,
                emptyText: 'No se encontró ninguna categoría para',
                render: (cat) => {
                    const card = document.createElement('div');
                    card.className = `p-3 rounded-xl border transition-all cursor-pointer flex items-center justify-between group bg-white border-slate-200/90 hover:border-emerald-500 hover:bg-emerald-50/40 hover:shadow-2xs`;
                    card.onclick = () => seleccionarCategoriaModal(cat);

                    card.innerHTML = `
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center shrink-0 overflow-hidden border border-slate-200">
                                ${cat.imagen_url ? `<img src="${cat.imagen_url}" class="w-full h-full object-cover">` : '<span class="material-symbols-outlined text-slate-400">category</span>'}
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-slate-900 group-hover:text-emerald-950 truncate">${cat.nombre}</div>
                                <div class="text-[10px] text-slate-400 font-medium">${cat.padre_nombre ? `↳ ${cat.padre_nombre}` : 'Categoría Principal'}</div>
                            </div>
                        </div>
                        <span class="material-symbols-outlined text-emerald-600 opacity-0 group-hover:opacity-100 transition-opacity">add_circle</span>
                    `;
                    return card;
                }
            });

            // Inicializar modal de búsqueda de Productos (Regla #6: 15 elementos con paginación)
            window.ModalBuscador.init('modal-selector-producto-cupon', {
                items: productosData,
                porPagina: 15,
                emptyText: 'No se encontró ningún producto para',
                render: (prod) => {
                    const card = document.createElement('div');
                    card.className = `p-3 rounded-xl border transition-all cursor-pointer flex items-center justify-between group bg-white border-slate-200/90 hover:border-emerald-500 hover:bg-emerald-50/40 hover:shadow-2xs`;
                    card.onclick = () => seleccionarProductoCuponModal(prod);

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

        const inputs = ['codigo', 'tipo', 'valor', 'monto_minimo', 'aplica_a'];
        inputs.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', actualizarPreview);
                el.addEventListener('change', actualizarPreview);
            }
        });
        actualizarPreview();
    });
</script>
@endpush
@endsection
