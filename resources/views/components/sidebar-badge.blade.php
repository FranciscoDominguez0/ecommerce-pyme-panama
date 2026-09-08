@props(['count' => 0])

@if($count > 0)
    <span {{ $attributes->merge(['class' => 'sidebar-text bg-emerald-500 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full shadow-sm shadow-emerald-500/40 animate-pulse relative']) }}>
        {{ $count }}
    </span>
@endif
