@props([
    'icon' => 'verified_user',
    'text' => 'Pyme Panama',
])


      <div {{ $attributes->merge(['class' => 'mt-4 flex items-center justify-center gap-1 text-gray-400 text-xs select-none']) }}>
    <span class="material-symbols-outlined text-sm">{{ $icon }}</span>
    <span class="text-[11px]">{{ $text }}</span>
</div>
