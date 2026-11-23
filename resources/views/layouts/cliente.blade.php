<!DOCTYPE html>
<html class="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <!-- Polyfill preventivo para mitigar advertencias de APIs obsoletas evaluadas por scripts externos (Cloudflare) -->
    <script data-navigate-once>
        (function() {
            try {
                if (typeof window.StorageType !== 'undefined') {
                    var _st = { temporary: 0, persistent: 1 };
                    try { Object.defineProperty(window, 'StorageType', { value: _st, configurable: true, writable: true }); } catch (_) {}
                }
                if (typeof window.webkitStorageInfo !== 'undefined') {
                    var _wsi = { TEMPORARY: 0, PERSISTENT: 1, queryUsageAndQuota: function(){}, requestQuota: function(){} };
                    try { Object.defineProperty(window, 'webkitStorageInfo', { value: _wsi, configurable: true, writable: true }); } catch (_) {}
                }
                if ('fence' in window) {
                    try { Object.defineProperty(window, 'fence', { value: null, configurable: true, writable: true }); } catch (_) {}
                }
            } catch (_) {}
        })();
    </script>
    @include('layouts.partials.google-tag')
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="@yield('meta_description', 'PayMe Panamá — Tu tienda de tecnología, equipos IT y accesorios con envíos a todo Panamá y pagos seguros.')" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $siteTitle = (config('app.name') && config('app.name') !== 'Laravel') ? config('app.name') : 'PayMe Panamá — Equipos IT';
        $viewTitle = trim($__env->yieldContent('title'));
        if (!empty($viewTitle)) {
            $pageTitle = str_contains($viewTitle, 'PayMe') ? $viewTitle : ($viewTitle . ' — ' . $siteTitle);
        } else {
            $pageTitle = $siteTitle;
        }
    @endphp
    <title>{{ $pageTitle }}</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-192.png') }}">

    <!-- Precarga de fuentes locales críticas (Cero latencia / Anti-FOUT) -->
    <link rel="preload" href="{{ asset('fonts/material-symbols-outlined.woff2') }}" as="font" type="font/woff2"
        crossorigin>
    <link rel="preload" href="{{ asset('fonts/plus-jakarta-sans.woff2') }}" as="font" type="font/woff2" crossorigin>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="{{ asset('fonts/fonts.css') }}">

    @include('layouts.partials.cliente.styles')

    @livewireStyles
    @stack('styles')
</head>

<body
    class="bg-[#F8F9FF] text-[#0b1c30] flex flex-col min-h-screen text-sm antialiased selection:bg-[#8af5be] selection:text-[#00714b]"
    x-data="{ mobileMenuOpen: false }">

    @include('layouts.partials.cliente.header')

    @include('layouts.partials.cliente.mobile-menu')

    <!-- Contenido principal -->
    <main class="flex-1 relative">
        @php
            $isFromLogin = session()->pull('is_from_login', false) || str_contains(request()->headers->get('referer', ''), '/login') || str_contains(request()->headers->get('referer', ''), '2fa-challenge');
        @endphp

        <!-- Esqueleto de Carga (Solo post-login) -->
        @if($isFromLogin)
            <div id="global-cliente-skeleton" class="absolute inset-0 z-[60] bg-[#F8F9FF] transition-opacity duration-300">
                <x-cliente-skeleton :fullScreen="false" />
            </div>
        @endif

        <div id="actual-page-content"
            class="w-full transition-opacity duration-300 {{ $isFromLogin ? 'opacity-0' : 'opacity-100' }}">
            @yield('content')
        </div>
    </main>

    @include('layouts.partials.cliente.whatsapp-button')

    @include('layouts.partials.cliente.footer')

    <!-- Componentes Globales -->
    @persist('carrito-drawer')
    <livewire:carrito-drawer />
    @endpersist
    <x-toast-alert />
    <x-modal-articulo-agregado />

    @livewireScripts

    @include('layouts.partials.cliente.scripts')

    @stack('scripts')
</body>

</html>