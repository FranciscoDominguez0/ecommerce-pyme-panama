<!DOCTYPE html>
<html class="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'PayMe Panamá') }} - @yield('title', 'Tienda Online')</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-192.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="{{ asset('fonts/fonts.css') }}">
    <link rel="preload" href="{{ asset('fonts/material-symbols-outlined.woff2') }}" as="font" type="font/woff2" crossorigin>

    @include('layouts.partials.cliente.styles')

    @livewireStyles
    @stack('styles')
</head>
<body class="bg-[#F8F9FF] text-[#0b1c30] flex flex-col min-h-screen text-sm antialiased selection:bg-[#8af5be] selection:text-[#00714b]" x-data="{ mobileMenuOpen: false }">

    @include('layouts.partials.cliente.header')
    
    @include('layouts.partials.cliente.mobile-menu')

    <!-- Main Content -->
    <main class="flex-1 relative">
        @php
            $isFromLogin = session('is_from_login', false) || str_contains(request()->headers->get('referer', ''), '/login') || str_contains(request()->headers->get('referer', ''), '/2fa');
        @endphp
        
        <!-- Esqueleto de Carga (Solo post-login) -->
        @if($isFromLogin)
            <div id="global-cliente-skeleton" class="absolute inset-0 z-[60] bg-[#F8F9FF] transition-opacity duration-300">
                <x-cliente-skeleton :fullScreen="false" />
            </div>
        @endif

        <div id="actual-page-content" class="w-full transition-opacity duration-300 {{ $isFromLogin ? 'opacity-0' : 'opacity-100' }}">
            @yield('content')
        </div>
    </main>

    @include('layouts.partials.cliente.whatsapp-button')
    
    @include('layouts.partials.cliente.footer')

    <!-- Componentes Globales -->
    <livewire:carrito-drawer />
    <x-toast-alert />
    <x-modal-articulo-agregado />

    @livewireScripts
    
    @include('layouts.partials.cliente.scripts')
    
    @stack('scripts')
</body>
</html>