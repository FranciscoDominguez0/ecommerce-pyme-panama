@props([
    'name' => 'provincia',
    'id' => null,
    'selected' => null,
    'placeholder' => '-- Selecciona una provincia --',
    'useId' => false,
    'zonas' => null,
    'required' => false,
])

@php
    $provinciasPanama = [
        'Bocas del Toro',
        'Coclé',
        'Colón',
        'Chiriquí',
        'Darién',
        'Herrera',
        'Los Santos',
        'Panamá',
        'Panamá Oeste',
        'Veraguas',
    ];
@endphp

<select 
    name="{{ $name }}" 
    id="{{ $id ?? $name }}" 
    {{ $required ? 'required' : '' }} 
    {{ $attributes->merge(['class' => 'w-full bg-slate-50 dark:bg-[#181a1b] border border-slate-200 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500/20 outline-none cursor-pointer transition-all']) }}
>
    @if($placeholder)
        <option value="" class="bg-white dark:bg-[#181a1b] text-slate-900 dark:text-white">{{ $placeholder }}</option>
    @endif

    @if($useId && $zonas)
        @foreach($zonas as $zona)
            <option value="{{ $zona->id }}" {{ (string)$selected === (string)$zona->id ? 'selected' : '' }} class="bg-white dark:bg-[#181a1b] text-slate-900 dark:text-white">
                {{ $zona->nombre }}
            </option>
        @endforeach
    @else
        @foreach($provinciasPanama as $provincia)
            <option value="{{ $provincia }}" {{ (string)$selected === (string)$provincia ? 'selected' : '' }} class="bg-white dark:bg-[#181a1b] text-slate-900 dark:text-white">
                {{ $provincia }}
            </option>
        @endforeach
    @endif
</select>
