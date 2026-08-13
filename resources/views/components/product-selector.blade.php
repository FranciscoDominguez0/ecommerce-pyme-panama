@props(['id' => 'producto_id', 'name' => 'producto_id', 'value' => '', 'error' => false])

<input type="hidden" id="{{ $id }}" name="{{ $name }}" value="{{ $value }}" required>

<button type="button" onclick="window.ModalBuscador.abrir('modal-productos')" id="btn-seleccionar-{{ $id }}"
        class="w-full p-3 border {{ $error ? 'border-red-400' : 'border-slate-200 hover:border-emerald-500' }} rounded-xl bg-white text-left flex items-center justify-between shadow-sm group transition-all">
    
    <div class="flex items-center gap-3 min-w-0" id="preview-container-{{ $id }}">
        {{-- Empty state --}}
        <div id="empty-state-{{ $id }}" class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-400 shrink-0">
                <span class="material-symbols-outlined text-[20px]">inventory_2</span>
            </div>
            <span class="text-sm font-semibold text-slate-500">Seleccionar producto…</span>
        </div>

        {{-- Selected state (Hidden by default) --}}
        <div id="selected-state-{{ $id }}" class="hidden items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-lg border border-slate-200 overflow-hidden bg-slate-100 flex items-center justify-center shrink-0" id="img-container-{{ $id }}">
                <span class="material-symbols-outlined text-slate-400 text-[20px]">inventory_2</span>
            </div>
            <div class="min-w-0 flex-1 flex flex-col justify-center">
                <p class="text-sm font-bold text-slate-800 truncate leading-tight" id="title-{{ $id }}"></p>
                <p class="text-[11px] text-slate-500 font-medium truncate mt-0.5" id="subtitle-{{ $id }}"></p>
            </div>
        </div>
    </div>
    
    <span class="material-symbols-outlined text-slate-400 text-[20px] group-hover:text-emerald-500 transition-colors shrink-0 ml-2">search</span>
</button>

@once
@push('scripts')
<script>
    window.updateProductSelectorUI = function(id, item) {
        const emptyState = document.getElementById('empty-state-' + id);
        const selState = document.getElementById('selected-state-' + id);
        const title = document.getElementById('title-' + id);
        const subtitle = document.getElementById('subtitle-' + id);
        const imgContainer = document.getElementById('img-container-' + id);
        
        if (item) {
            emptyState.classList.add('hidden');
            emptyState.classList.remove('flex');
            
            selState.classList.remove('hidden');
            selState.classList.add('flex');
            
            title.textContent = item.nombre;
            subtitle.textContent = item.subtitulo || (item.sku ? 'SKU: ' + item.sku : '');
            
            if (item.imagen_ruta) {
                imgContainer.innerHTML = `<img src="${item.imagen_ruta}" alt="${item.nombre}" class="w-full h-full object-cover">`;
            } else {
                imgContainer.innerHTML = `<span class="material-symbols-outlined text-slate-400 text-[20px]">inventory_2</span>`;
            }
        }
    }
</script>
@endpush
@endonce
