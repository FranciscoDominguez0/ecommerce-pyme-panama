@props([
    'tipo' => 'entrada', // 'entrada', 'salida', 'ajuste'
    'productos' => [],
    'tituloModal' => 'Seleccionar Producto',
    'subtituloModal' => 'Busca y selecciona un producto',
])

{{-- Modal buscador reutilizable para inventario --}}
<x-modal-busqueda 
    id="modal-productos" 
    :titulo="$tituloModal" 
    :subtitulo="$subtituloModal" 
    icono="inventory_2" 
/>

@push('scripts')
<script>
(function() {
    const tipoOperacion = @json($tipo);
    const productosData = @json($productos);

    const productoSelect  = document.getElementById('producto_id');
    const varianteSelect  = document.getElementById('variante_id');
    const cantidadInput   = document.getElementById(tipoOperacion === 'ajuste' ? 'nuevo_stock' : 'cantidad');

    // Elementos de resumen para entrada y salida
    const resumenAntes    = document.getElementById('resumen-stock-antes');
    const resumenCantidad = document.getElementById('resumen-cantidad');
    const resumenNuevo    = document.getElementById('resumen-stock-nuevo');
    const avisoStock      = document.getElementById('aviso-stock');
    const btnSubmit       = document.getElementById('btn-submit');

    // Elementos de resumen exclusivos para ajuste
    const previewAntes    = document.getElementById('preview-antes');
    const previewNuevo    = document.getElementById('preview-nuevo');
    const previewDiff     = document.getElementById('preview-diff');
    const previewDiffWrap = document.getElementById('preview-diff-wrap');
    const previewMinimo   = document.getElementById('preview-minimo');
    const alertaBajo      = document.getElementById('alerta-stock-bajo');

    let stockActual = null;
    let stockMinimo = null;

    // Actualiza la caja de vista previa según el tipo de operación
    function actualizarResumen() {
        if (!cantidadInput) return;

        if (tipoOperacion === 'ajuste') {
            const nuevo = parseInt(cantidadInput.value, 10);
            if (stockActual !== null && !isNaN(nuevo)) {
                const diff = nuevo - stockActual;

                if (previewAntes) previewAntes.textContent = stockActual;
                if (previewNuevo) {
                    previewNuevo.textContent = nuevo;
                    previewNuevo.className = 'text-4xl font-extrabold tabular-nums ' +
                        (nuevo === 0 ? 'text-red-600' : (stockMinimo !== null && nuevo <= stockMinimo ? 'text-amber-600' : 'text-slate-900 dark:text-white'));
                }

                if (previewDiffWrap) previewDiffWrap.classList.remove('hidden');
                if (previewDiff) {
                    if (diff > 0) {
                        previewDiff.textContent = '+' + diff + ' (entrada)';
                        previewDiff.className = 'text-sm font-bold px-2.5 py-1 rounded-lg tabular-nums bg-emerald-50 text-emerald-700';
                    } else if (diff < 0) {
                        previewDiff.textContent = diff + ' (salida)';
                        previewDiff.className = 'text-sm font-bold px-2.5 py-1 rounded-lg tabular-nums bg-red-50 text-red-600';
                    } else {
                        previewDiff.textContent = 'Sin cambio';
                        previewDiff.className = 'text-sm font-bold px-2.5 py-1 rounded-lg tabular-nums bg-slate-100 dark:bg-transparent text-slate-500 dark:text-slate-400';
                    }
                }

                if (alertaBajo) {
                    if (stockMinimo !== null && nuevo <= stockMinimo) {
                        alertaBajo.classList.remove('hidden');
                    } else {
                        alertaBajo.classList.add('hidden');
                    }
                }
            } else {
                if (previewAntes) previewAntes.textContent = stockActual !== null ? stockActual : '—';
                if (previewNuevo) {
                    previewNuevo.textContent = '—';
                    previewNuevo.className = 'text-4xl font-extrabold tabular-nums text-slate-900 dark:text-white';
                }
                if (previewDiffWrap) previewDiffWrap.classList.add('hidden');
                if (alertaBajo) alertaBajo.classList.add('hidden');
            }

            if (previewMinimo) previewMinimo.textContent = stockMinimo !== null ? stockMinimo : '—';
            return;
        }

        // Operaciones de Entrada o Salida
        const cant = parseInt(cantidadInput.value, 10) || 0;

        if (tipoOperacion === 'entrada') {
            if (stockActual !== null) {
                const nuevo = stockActual + cant;
                if (resumenAntes) resumenAntes.textContent = stockActual;
                if (resumenCantidad) resumenCantidad.textContent = '+' + cant;
                if (resumenNuevo) resumenNuevo.textContent = nuevo;
            } else {
                if (resumenAntes) resumenAntes.textContent = '—';
                if (resumenCantidad) resumenCantidad.textContent = '+' + cant;
                if (resumenNuevo) resumenNuevo.textContent = '—';
            }
        } else if (tipoOperacion === 'salida') {
            if (stockActual !== null) {
                const nuevo = stockActual - cant;
                if (resumenAntes) resumenAntes.textContent = stockActual;
                if (resumenCantidad) resumenCantidad.textContent = '-' + cant;
                if (resumenNuevo) {
                    resumenNuevo.textContent = nuevo < 0 ? '⚠ ' + nuevo : nuevo;
                    resumenNuevo.className = 'text-3xl font-extrabold tabular-nums ' + (nuevo < 0 ? 'text-red-600' : 'text-slate-900 dark:text-white');
                }

                if (cant > stockActual) {
                    if (avisoStock) avisoStock.classList.remove('hidden');
                    if (btnSubmit) btnSubmit.disabled = true;
                } else {
                    if (avisoStock) avisoStock.classList.add('hidden');
                    if (btnSubmit) btnSubmit.disabled = false;
                }
            } else {
                if (resumenAntes) resumenAntes.textContent = '—';
                if (resumenCantidad) resumenCantidad.textContent = '-' + cant;
                if (resumenNuevo) {
                    resumenNuevo.textContent = '—';
                    resumenNuevo.className = 'text-3xl font-extrabold tabular-nums text-slate-900 dark:text-white';
                }
                if (avisoStock) avisoStock.classList.add('hidden');
                if (btnSubmit) btnSubmit.disabled = false;
            }
        }
    }

    // Consulta las variantes asociadas al producto seleccionado
    function cargarVariantes(productoId) {
        if (!varianteSelect) return;

        varianteSelect.innerHTML = '<option value="">Cargando…</option>';
        varianteSelect.disabled = true;

        fetch('{{ url("/admin/inventario/variantes") }}/' + productoId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            varianteSelect.innerHTML = '<option value="">Sin variante (producto base)</option>';
            if (data.length > 0) {
                data.forEach(v => {
                    const opt = document.createElement('option');
                    opt.value = v.id;
                    opt.dataset.stock = v.stock;
                    opt.textContent = v.label + (v.sku ? ' — ' + v.sku : '') + ' (stock: ' + v.stock + ')';
                    varianteSelect.appendChild(opt);
                });
                varianteSelect.disabled = false;

                const urlParams = new URLSearchParams(window.location.search);
                const preVariante = urlParams.get('variante_id');
                if (preVariante) {
                    varianteSelect.value = preVariante;
                    const optSel = varianteSelect.options[varianteSelect.selectedIndex];
                    stockActual = (optSel && optSel.value) ? parseInt(optSel.dataset.stock, 10) : data[0].stock;
                } else {
                    stockActual = data[0].stock;
                }
            } else {
                varianteSelect.disabled = true;
                stockActual = productoSelect.dataset.stock ? parseInt(productoSelect.dataset.stock, 10) : null;
            }
            actualizarResumen();
        })
        .catch(() => {
            varianteSelect.innerHTML = '<option value="">Error al cargar variantes</option>';
            varianteSelect.disabled = true;
        });
    }

    function varianteSeleccionada() {
        if (!varianteSelect) return;
        const opt = varianteSelect.options[varianteSelect.selectedIndex];
        if (opt && opt.value) {
            stockActual = parseInt(opt.dataset.stock, 10);
        } else {
            stockActual = productoSelect.dataset.stock ? parseInt(productoSelect.dataset.stock, 10) : null;
        }
        actualizarResumen();
    }

    if (productoSelect) {
        productoSelect.addEventListener('change', function() {
            stockActual = null;
            stockMinimo = null;
            actualizarResumen();

            const pid = this.value;
            if (pid) {
                if (this.dataset.minimo) {
                    stockMinimo = parseInt(this.dataset.minimo, 10);
                }
                cargarVariantes(pid);
            } else if (varianteSelect) {
                varianteSelect.innerHTML = '<option value="">Selecciona un producto primero</option>';
                varianteSelect.disabled = true;
            }
        });
    }

    if (varianteSelect) {
        varianteSelect.addEventListener('change', varianteSeleccionada);
    }

    if (cantidadInput) {
        cantidadInput.addEventListener('input', actualizarResumen);
    }

    // Inicialización del modal buscador inteligente
    if (window.ModalBuscador) {
        window.ModalBuscador.init('modal-productos', {
            items: productosData,
            porPagina: 10,
            onSelect: function(item) {
                if (!productoSelect) return;
                productoSelect.value = item.id;
                productoSelect.dataset.stock = item.stock;

                if (item.minimo !== undefined && item.minimo !== null) {
                    productoSelect.dataset.minimo = item.minimo;
                } else {
                    delete productoSelect.dataset.minimo;
                }

                if (window.updateProductSelectorUI) {
                    window.updateProductSelectorUI('producto_id', item);
                }
                window.ModalBuscador.cerrar('modal-productos');

                productoSelect.dispatchEvent(new Event('change'));
            }
        });
    }

    // Pre-carga si se recibe un id previo o por parámetro URL
    (function init() {
        if (!productoSelect) return;
        const val = productoSelect.value;
        if (val) {
            const prod = productosData.find(p => p.id == val);
            if (prod) {
                productoSelect.dataset.stock = prod.stock;
                if (prod.minimo !== null && prod.minimo !== undefined) {
                    productoSelect.dataset.minimo = prod.minimo;
                    stockMinimo = parseInt(prod.minimo, 10);
                }
                if (window.updateProductSelectorUI) {
                    window.updateProductSelectorUI('producto_id', prod);
                }
            }
            cargarVariantes(val);
        }
    })();
})();
</script>
@endpush
