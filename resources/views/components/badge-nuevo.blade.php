@props(['condicion' => true])

@if($condicion)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center px-1.5 py-0.5 bg-emerald-500 text-white text-[10px] font-bold rounded uppercase tracking-wide relative overflow-visible font-sans']) }}>
        <span class="absolute -top-1 -right-1 flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
        </span>
        Nuevo
    </span>
@endif
