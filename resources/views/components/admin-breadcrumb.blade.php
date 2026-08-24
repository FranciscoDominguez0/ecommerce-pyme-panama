<nav class="flex items-center gap-1 sm:gap-1.5 text-xs text-slate-500 font-medium min-w-0 flex-nowrap overflow-hidden" aria-label="Breadcrumb">
    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-1 text-slate-500 hover:text-slate-900 transition-colors shrink-0" title="Panel Principal">
        <span class="material-symbols-outlined text-[17px] text-slate-400">home</span>
        <span class="hidden sm:inline">Panel</span>
    </a>

    @hasSection('breadcrumbs')
        @yield('breadcrumbs')
    @else
        @php
            $segment2 = request()->segment(2);
            $segment3 = request()->segment(3);
            
            // Tratamos de adivinar la ruta del módulo principal (segment 2)
            $moduleRouteName = 'admin.' . $segment2 . '.index';
            if (!Route::has($moduleRouteName)) {
                // Si no existe con .index, tal vez sea solo el nombre del segmento (ej. admin.reportes)
                $moduleRouteName = 'admin.' . $segment2;
            }
            
            $hasValidModuleRoute = Route::has($moduleRouteName);
        @endphp

        @if($segment2)
            <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
            @if($segment3 && $hasValidModuleRoute)
                {{-- Si hay un segmento 3 (estamos dentro de una sub-página), el segmento 2 es un enlace navegable --}}
                <a href="{{ route($moduleRouteName) }}" class="capitalize font-medium text-slate-500 hover:text-slate-900 transition-colors truncate max-w-[90px] sm:max-w-none">
                    {{ str_replace('-', ' ', $segment2) }}
                </a>
            @else
                {{-- Si NO hay segmento 3, el segmento 2 es la página actual (texto plano en negrita) --}}
                <span class="capitalize {{ !$segment3 ? 'font-bold text-slate-900' : 'text-slate-600' }} truncate max-w-[90px] sm:max-w-none">
                    {{ str_replace('-', ' ', $segment2) }}
                </span>
            @endif
        @endif

        @if($segment3)
            <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
            <span class="capitalize font-bold text-slate-900 truncate max-w-[120px] sm:max-w-none">
                {{ is_numeric($segment3) ? 'Detalle #' . $segment3 : str_replace('-', ' ', $segment3) }}
            </span>
        @endif
    @endif
</nav>
