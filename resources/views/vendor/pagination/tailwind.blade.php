@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navegación de Paginación" class="flex flex-col items-center justify-center w-full py-2">

        <div class="flex flex-col items-center justify-center gap-2.5 text-center w-full">


            <div class="flex justify-center">
                <span class="inline-flex rtl:flex-row-reverse shadow-2xs rounded-xl overflow-hidden border border-slate-200">

                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span aria-disabled="true" aria-label="Anterior">
                            <span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-300 bg-slate-50 border-r border-slate-200 cursor-not-allowed leading-5" aria-hidden="true">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" wire:navigate class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border-r border-slate-200 leading-5 hover:bg-slate-100 transition-colors" aria-label="Anterior">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span aria-disabled="true">
                                <span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-500 bg-white border-r border-slate-200 cursor-default leading-5">{{ $element }}</span>
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page">
                                        <span class="inline-flex items-center px-3.5 py-1.5 text-xs font-extrabold text-white bg-slate-900 border-r border-slate-900 cursor-default leading-5">{{ $page }}</span>
                                    </span>
                                @else
                                    <a href="{{ $url }}" wire:navigate class="inline-flex items-center px-3.5 py-1.5 text-xs font-semibold text-slate-700 bg-white border-r border-slate-200 leading-5 hover:bg-slate-100 transition-colors" aria-label="Ir a página {{ $page }}">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" wire:navigate class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white leading-5 hover:bg-slate-100 transition-colors" aria-label="Siguiente">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @else
                        <span aria-disabled="true" aria-label="Siguiente">
                            <span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-slate-300 bg-slate-50 cursor-not-allowed leading-5" aria-hidden="true">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </span>
                    @endif
                </span>
            </div>
        </div>
    </nav>
@endif
