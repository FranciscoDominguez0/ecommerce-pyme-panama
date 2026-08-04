@props([
    'size' => 'default', // 'sm', 'default', 'lg', 'xl'
    'boxed' => false,
])

@php
    $hasCustomLogo = file_exists(public_path('images/logo.svg')) 
        || file_exists(public_path('images/logo.png')) 
        || file_exists(public_path('images/logo.webp'));

    $customLogoPath = file_exists(public_path('images/logo.svg')) 
        ? 'images/logo.svg' 
        : (file_exists(public_path('images/logo.png')) ? 'images/logo.png' : 'images/logo.webp');

    $sizeClasses = [
        'sm' => 'w-6 h-6',
        'default' => 'w-8 h-8',
        'lg' => 'w-10 h-10',
        'xl' => 'w-12 h-12',
    ][$size] ?? 'w-8 h-8';
@endphp

@if ($boxed)
    <div class="w-12 h-12 rounded-xl shadow-sm bg-white p-2 flex items-center justify-center border border-gray-200 shrink-0">
        @if ($hasCustomLogo)
            <img src="{{ asset($customLogoPath) }}" alt="{{ config('app.name', 'PayMe Panamá') }}" {{ $attributes->merge(['class' => $sizeClasses . ' object-contain']) }}>
        @else
            <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" {{ $attributes->merge(['class' => $sizeClasses . ' text-primary-container']) }}>
                <rect width="40" height="40" rx="10" fill="#002349"/>
                <path d="M12 28V12H20C22.2091 12 24 13.7909 24 16C24 18.2091 22.2091 20 20 20H16.5V28H12Z" fill="white"/>
                <path d="M22 20C24.2091 20 26 21.7909 26 24C26 26.2091 24.2091 28 22 28H18V24H22Z" fill="#00875A"/>
                <circle cx="28" cy="14" r="3" fill="#D4AF37"/>
            </svg>
        @endif
    </div>
@else
    @if ($hasCustomLogo)
        <img src="{{ asset($customLogoPath) }}" alt="{{ config('app.name', 'PayMe Panamá') }}" {{ $attributes->merge(['class' => $sizeClasses . ' object-contain']) }}>
    @else
        <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" {{ $attributes->merge(['class' => $sizeClasses . ' text-primary-container']) }}>
            <rect width="40" height="40" rx="10" fill="#002349"/>
            <path d="M12 28V12H20C22.2091 12 24 13.7909 24 16C24 18.2091 22.2091 20 20 20H16.5V28H12Z" fill="white"/>
            <path d="M22 20C24.2091 20 26 21.7909 26 24C26 26.2091 24.2091 28 22 28H18V24H22Z" fill="#00875A"/>
            <circle cx="28" cy="14" r="3" fill="#D4AF37"/>
        </svg>
    @endif
@endif
