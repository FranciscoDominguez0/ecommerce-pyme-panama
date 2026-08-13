@extends('layouts.admin')

@section('title', 'Ajuste Manual de Inventario')

@section('breadcrumbs')
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <a href="{{ route('admin.inventario.index') }}" class="font-medium text-slate-500 hover:text-slate-700 truncate">Inventario</a>
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <span class="font-bold text-slate-900 truncate">Ajuste Manual</span>
@endsection

@section('content')
<div class="space-y-6 w-full min-w-0 max-w-full">

    {{-- Page Header --}}
    <div class="flex items-center gap-3 pb-4 border-b border-slate-200/80">
        <a href="{{ route('admin.inventario.index') }}"
           class="p-1.5 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors shrink-0">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
        </a>
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Ajustar Inventario Manualmente</h2>
            <p class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">
                Corrige la cantidad disponible para reflejar discrepancias físicas, daños o conteos de inventario.
            </p>
        </div>
    </div>

    {{-- Errors --}}
    @if(session('error'))
        <div class="flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 font-medium">
            <span class="material-symbols-outlined text-[18px] text-red-500">error</span>
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Two-column bento layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        {{-- LEFT: Main form --}}
        <div class="lg:col-span-7 card-elevated rounded-xl p-6 sm:p-8">
            <form method="POST" action="{{ route('admin.inventario.ajuste') }}" id="form-ajuste">
                @csrf

                <div class="mb-6">
                    <h3 class="text-base font-extrabold text-slate-900 mb-1">Seleccionar ítem</h3>
                    <p class="text-xs text-slate-500">Elige el producto y la variante (si aplica) a ajustar.</p>
                </div>

                {{-- Product + Variant --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-5">
                    <div>
                        <label for="producto_id" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                            Producto <span class="text-red-500">*</span>
                        </label>
                        <x-product-selector id="producto_id" name="producto_id" :value="old('producto_id', request('producto_id'))" :error="$errors->has('producto_id')" />
                    </div>
                    <div>
                        <label for="variante_id" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                            Variante <span class="text-slate-400 font-normal">(si aplica)</span>
                        </label>
                        <select id="variante_id" name="variante_id" disabled
                                class="w-full px-4 py-2.5 border border-slate-200 rounded-xl bg-white text-sm text-slate-800 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                            <option value="">Selecciona un producto primero</option>
                        </select>
                    </div>
                </div>

                <hr class="border-slate-100 my-5">

                <div class="mb-6">
                    <h3 class="text-base font-extrabold text-slate-900 mb-1">Cantidad correcta</h3>
                    <p class="text-xs text-slate-500">Introduce el stock real según el conteo físico.</p>
                </div>

                {{-- New stock --}}
                <div class="mb-5">
                    <label for="nuevo_stock" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                        Nuevo stock (cantidad real) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="nuevo_stock" name="nuevo_stock" min="0"
                           value="{{ old('nuevo_stock', '') }}" required
                           placeholder="0"
                           class="w-full px-4 py-3 border {{ $errors->has('nuevo_stock') ? 'border-red-400' : 'border-slate-200' }} rounded-xl bg-white text-2xl text-slate-800 font-extrabold focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-all tabular-nums">
                </div>

                {{-- Reason --}}
                <div class="mb-5">
                    <label for="motivo" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                        Motivo del ajuste <span class="text-red-500">*</span>
                    </label>
                    <select id="motivo" name="motivo" required
                            class="w-full px-4 py-2.5 border {{ $errors->has('motivo') ? 'border-red-400' : 'border-slate-200' }} rounded-xl bg-white text-sm text-slate-800 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-all">
                        <option value="Conteo físico de inventario" {{ old('motivo') === 'Conteo físico de inventario' ? 'selected' : '' }}>Conteo físico de inventario</option>
                        <option value="Corrección de error de sistema" {{ old('motivo') === 'Corrección de error de sistema' ? 'selected' : '' }}>Corrección de error de sistema</option>
                        <option value="Merma / Caducidad / Daño"    {{ old('motivo') === 'Merma / Caducidad / Daño'    ? 'selected' : '' }}>Merma / Caducidad / Daño</option>
                        <option value="Robo / Pérdida"              {{ old('motivo') === 'Robo / Pérdida'              ? 'selected' : '' }}>Robo / Pérdida</option>
                        <option value="Discrepancia de proveedores" {{ old('motivo') === 'Discrepancia de proveedores' ? 'selected' : '' }}>Discrepancia de proveedores</option>
                    </select>
                </div>

                {{-- Notes --}}
                <div class="mb-6">
                    <label for="notas" class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                        Notas internas <span class="text-slate-400 font-normal">(opcional)</span>
                    </label>
                    <textarea id="notas" name="notas" rows="3"
                              placeholder="Describe la razón del ajuste, quién lo autorizó, etc…"
                              class="w-full px-4 py-2.5 border border-slate-200 rounded-xl bg-white text-sm text-slate-700 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-all resize-none">{{ old('notas') }}</textarea>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-100">
                    <a href="{{ route('admin.inventario.index') }}"
                       class="px-5 py-2.5 text-sm font-semibold text-slate-600 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition-colors">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="flex items-center gap-2 px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-bold transition-all shadow-sm">
                        <span class="material-symbols-outlined text-[18px]">sync_alt</span>
                        Aplicar Ajuste
                    </button>
                </div>
            </form>
        </div>

        {{-- RIGHT: Live preview panel --}}
        <div class="lg:col-span-5 space-y-4">

            {{-- Preview card --}}
            <div class="card-elevated rounded-xl p-6 space-y-5">
                <div class="flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-[18px] text-amber-500">sync_alt</span>
                    <h3 class="text-sm font-extrabold text-slate-800">Vista previa del ajuste</h3>
                </div>

                {{-- Before --}}
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Stock actual</p>
                    <p id="preview-antes" class="text-3xl font-extrabold text-slate-700 tabular-nums">—</p>
                </div>

                {{-- Arrow + difference --}}
                <div id="preview-diff-wrap" class="hidden">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-slate-300 text-[20px]">arrow_downward</span>
                        <span id="preview-diff"
                              class="text-sm font-bold px-2.5 py-1 rounded-lg tabular-nums">
                        </span>
                    </div>
                </div>

                {{-- After --}}
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nuevo stock</p>
                    <p id="preview-nuevo" class="text-4xl font-extrabold tabular-nums text-slate-900">—</p>
                </div>

                {{-- Divider + stock minimo info --}}
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-400">Stock mínimo configurado</span>
                    <span id="preview-minimo" class="font-bold text-slate-600">—</span>
                </div>
            </div>

            {{-- Warning box --}}
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                <div class="flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-amber-500 text-[18px] shrink-0 mt-0.5">info</span>
                    <div class="text-xs text-amber-700 space-y-1">
                        <p class="font-bold">Acción de alto impacto</p>
                        <p>Un ajuste modifica el stock directamente y queda registrado en el historial con tu usuario como responsable. No se puede deshacer.</p>
                    </div>
                </div>
            </div>

            {{-- Low stock warning (conditional) --}}
            <div id="alerta-stock-bajo" class="hidden bg-red-50 border border-red-200 rounded-xl p-4">
                <div class="flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-red-500 text-[18px] shrink-0 mt-0.5">warning</span>
                    <div class="text-xs text-red-700 space-y-1">
                        <p class="font-bold">Nuevo stock por debajo del mínimo</p>
                        <p>El valor ingresado está por debajo del stock mínimo configurado para este producto.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
    
    {{-- Modal Selector de Productos --}}
    <x-modal-busqueda id="modal-productos" titulo="Seleccionar Producto" subtitulo="Busca y selecciona un producto para el ajuste" icono="inventory_2" />
</div>
@endsection

@push('scripts')
@php
    $productosDataJson = $productos->map(function($p) {
        return [
            'id' => $p->id,
            'nombre' => $p->nombre,
            'sku' => $p->sku,
            'stock' => $p->stock,
            'minimo' => $p->stock_minimo,
            'imagen_ruta' => $p->imagen_url,
            'subtitulo' => $p->sku ? 'SKU: ' . $p->sku . ' | Stock: ' . $p->stock : 'Stock: ' . $p->stock,
            'icono' => 'inventory_2'
        ];
    })->values();
@endphp
<script>
(function() {
    const productoSelect = document.getElementById('producto_id');
    const varianteSelect = document.getElementById('variante_id');
    const nuevoStockInput = document.getElementById('nuevo_stock');

    const previewAntes   = document.getElementById('preview-antes');
    const previewNuevo   = document.getElementById('preview-nuevo');
    const previewDiff    = document.getElementById('preview-diff');
    const previewDiffWrap = document.getElementById('preview-diff-wrap');
    const previewMinimo  = document.getElementById('preview-minimo');
    const alertaBajo     = document.getElementById('alerta-stock-bajo');

    let stockActual = null;
    let stockMinimo = null;

    function actualizarPreview() {
        const nuevo = parseInt(nuevoStockInput.value, 10);
        if (stockActual !== null && !isNaN(nuevo)) {
            const diff = nuevo - stockActual;

            previewAntes.textContent = stockActual;
            previewNuevo.textContent = nuevo;
            previewNuevo.className = 'text-4xl font-extrabold tabular-nums ' +
                (nuevo === 0 ? 'text-red-600' : (stockMinimo !== null && nuevo <= stockMinimo ? 'text-amber-600' : 'text-slate-900'));

            previewDiffWrap.classList.remove('hidden');
            if (diff > 0) {
                previewDiff.textContent = '+' + diff + ' (entrada)';
                previewDiff.className = 'text-sm font-bold px-2.5 py-1 rounded-lg tabular-nums bg-emerald-50 text-emerald-700';
            } else if (diff < 0) {
                previewDiff.textContent = diff + ' (salida)';
                previewDiff.className = 'text-sm font-bold px-2.5 py-1 rounded-lg tabular-nums bg-red-50 text-red-600';
            } else {
                previewDiff.textContent = 'Sin cambio';
                previewDiff.className = 'text-sm font-bold px-2.5 py-1 rounded-lg tabular-nums bg-slate-100 text-slate-500';
            }

            if (stockMinimo !== null && nuevo <= stockMinimo) {
                alertaBajo.classList.remove('hidden');
            } else {
                alertaBajo.classList.add('hidden');
            }
        } else {
            previewAntes.textContent = stockActual !== null ? stockActual : '—';
            previewNuevo.textContent = '—';
            previewNuevo.className = 'text-4xl font-extrabold tabular-nums text-slate-900';
            previewDiffWrap.classList.add('hidden');
            alertaBajo.classList.add('hidden');
        }
        previewMinimo.textContent = stockMinimo !== null ? stockMinimo : '—';
    }

    function cargarVariantes(productoId) {
        varianteSelect.innerHTML = '<option value="">Cargando…</option>';
        varianteSelect.disabled  = true;

        fetch('{{ url("/admin/inventario/variantes") }}/' + productoId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            varianteSelect.innerHTML = '<option value="">Sin variante (producto base)</option>';
            if (data.length > 0) {
                data.forEach(v => {
                    const opt = document.createElement('option');
                    opt.value         = v.id;
                    opt.dataset.stock = v.stock;
                    opt.textContent   = v.label + (v.sku ? ' — ' + v.sku : '') + ' (stock: ' + v.stock + ')';
                    varianteSelect.appendChild(opt);
                });
                varianteSelect.disabled = false;
                stockActual = data[0].stock;

                const urlParams  = new URLSearchParams(window.location.search);
                const preVariante = urlParams.get('variante_id');
                if (preVariante) {
                    varianteSelect.value = preVariante;
                    const selOpt = varianteSelect.options[varianteSelect.selectedIndex];
                    if (selOpt && selOpt.value) stockActual = parseInt(selOpt.dataset.stock, 10);
                }
            } else {
                varianteSelect.disabled = true;
                const selected = document.getElementById('producto_id');
                stockActual = selected.dataset.stock ? parseInt(selected.dataset.stock, 10) : null;
            }
            actualizarPreview();
        })
        .catch(() => {
            varianteSelect.innerHTML = '<option value="">Error al cargar</option>';
            varianteSelect.disabled  = true;
        });
    }

    productoSelect.addEventListener('change', function() {
        stockActual = null;
        stockMinimo = null;
        actualizarPreview();

        if (this.value) {
            const opt = this;
            stockMinimo = opt.dataset.minimo ? parseInt(opt.dataset.minimo, 10) : null;
            cargarVariantes(this.value);
        } else {
            varianteSelect.innerHTML = '<option value="">Selecciona un producto primero</option>';
            varianteSelect.disabled  = true;
        }
    });

    varianteSelect.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (opt && opt.value) {
            stockActual = parseInt(opt.dataset.stock, 10);
        } else {
            const prodOpt = document.getElementById('producto_id');
            stockActual = prodOpt.dataset.stock ? parseInt(prodOpt.dataset.stock, 10) : null;
        }
        actualizarPreview();
    });

    nuevoStockInput.addEventListener('input', actualizarPreview);

    // Initializer para el Modal Buscador
    const productosData = @json($productosDataJson);

    window.ModalBuscador.init('modal-productos', {
        items: productosData,
        porPagina: 10,
        onSelect: function(item) {
            const selectProd = document.getElementById('producto_id');
            selectProd.value = item.id;
            selectProd.dataset.stock = item.stock;
            if (item.minimo !== undefined && item.minimo !== null) selectProd.dataset.minimo = item.minimo;
            else delete selectProd.dataset.minimo;
            
            window.updateProductSelectorUI('producto_id', item);
            window.ModalBuscador.cerrar('modal-productos');
            
            // Disparar evento para cargar variantes
            selectProd.dispatchEvent(new Event('change'));
        }
    });

    // Pre-load logic if old input or query param exists
    (function init() {
        const val = productoSelect.value;
        if (val) {
            const prod = productosData.find(p => p.id == val);
            if (prod) {
                productoSelect.dataset.stock = prod.stock;
                if (prod.minimo !== null) productoSelect.dataset.minimo = prod.minimo;
                window.updateProductSelectorUI('producto_id', prod);
                stockMinimo = prod.minimo ? parseInt(prod.minimo, 10) : null;
            }
            cargarVariantes(val);
        }
    })();
})();
</script>
@endpush
