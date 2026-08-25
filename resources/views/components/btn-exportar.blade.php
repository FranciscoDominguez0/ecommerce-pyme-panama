@props([
    'excelUrl' => null,
    'pdfUrl' => null,
    'excelOnclick' => null,
    'pdfOnclick' => null,
])

<!-- Dropdown Exportar -->
<div x-data="{ open: false }" class="relative inline-block text-left z-40">
    <button type="button" @click="open = !open" @click.away="open = false" 
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 hover:border-slate-300 transition-all shadow-xs h-full w-full justify-center sm:w-auto">
        <span class="material-symbols-outlined text-[18px] text-slate-500">file_download</span>
        <span class="sm:inline">Exportar</span>
        <span class="material-symbols-outlined text-[16px] text-slate-400 transition-transform duration-200" :class="{'rotate-180': open}">expand_more</span>
    </button>
    
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-100" 
         x-transition:enter-start="transform opacity-0 scale-95" 
         x-transition:enter-end="transform opacity-100 scale-100" 
         x-transition:leave="transition ease-in duration-75" 
         x-transition:leave-start="transform opacity-100 scale-100" 
         x-transition:leave-end="transform opacity-0 scale-95" 
         class="absolute right-0 sm:left-auto mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-100 overflow-hidden py-1"
         style="display: none;">
        
        <!-- Opción Excel -->
        @if($excelUrl)
            <a href="{{ $excelUrl }}" target="_blank" @click="open = false"
               class="flex items-center gap-2 px-4 py-2.5 text-xs font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors w-full text-left">
                <span class="material-symbols-outlined text-[18px] text-emerald-500">table_view</span>
                <span>Exportar a Excel</span>
            </a>
        @elseif($excelOnclick)
            <button type="button" onclick="{{ $excelOnclick }}; open = false;" 
               class="flex items-center gap-2 px-4 py-2.5 text-xs font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors w-full text-left">
                <span class="material-symbols-outlined text-[18px] text-emerald-500">table_view</span>
                <span>Exportar a Excel</span>
            </button>
        @endif
        
        <!-- Opción PDF -->
        @if($pdfUrl)
            <a href="{{ $pdfUrl }}" target="_blank" @click="open = false"
               class="flex items-center gap-2 px-4 py-2.5 text-xs font-medium text-slate-700 hover:bg-rose-50 hover:text-rose-700 transition-colors w-full text-left">
                <span class="material-symbols-outlined text-[18px] text-rose-500">picture_as_pdf</span>
                <span>Exportar a PDF</span>
            </a>
        @elseif($pdfOnclick)
            <button type="button" onclick="{{ $pdfOnclick }}; open = false;" 
               class="flex items-center gap-2 px-4 py-2.5 text-xs font-medium text-slate-700 hover:bg-rose-50 hover:text-rose-700 transition-colors w-full text-left">
                <span class="material-symbols-outlined text-[18px] text-rose-500">picture_as_pdf</span>
                <span>Exportar a PDF</span>
            </button>
        @endif
    </div>
</div>
