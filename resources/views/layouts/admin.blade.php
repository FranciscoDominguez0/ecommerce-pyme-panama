<!DOCTYPE html>
<html class="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'PayMe Panamá') }} - @yield('title', 'Panel de Administración')</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-192.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link rel="stylesheet" href="{{ asset('fonts/fonts.css') }}">
    <link rel="preload" href="{{ asset('fonts/material-symbols-outlined.woff2') }}" as="font" type="font/woff2" crossorigin>

    @include('layouts.partials.admin.styles')

    @stack('styles')

    {{-- Script inline: aplica dark mode desde localStorage ANTES del primer render para evitar flash blanco --}}
    <script>
        (function() {
            var theme = localStorage.getItem('color-theme');
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (theme === 'dark' || (!theme && prefersDark)) {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('light');
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('light');
            }
        })();
    </script>
</head>
<body class="text-slate-900 dark:text-slate-100 min-h-screen flex flex-col md:flex-row text-sm antialiased selection:bg-emerald-100 selection:text-emerald-900 w-full max-w-full overflow-x-clip relative" style="background-color: var(--admin-bg);">
    
    @php
        $isFromLogin = session()->pull('is_from_login', false) || str_contains(request()->headers->get('referer', ''), '/login') || str_contains(request()->headers->get('referer', ''), '/2fa');
    @endphp

    @if($isFromLogin)
        <div id="global-admin-skeleton-wrapper" class="fixed inset-0 z-[9999] transition-opacity duration-300"
             style="background-color: #F8FAFC;"
             data-dark-bg="#121415"
             data-light-bg="#F8FAFC">
            <script>
                (function(){
                    var el = document.getElementById('global-admin-skeleton-wrapper');
                    if (!el) return;
                    var isDark = document.documentElement.classList.contains('dark');
                    el.style.backgroundColor = isDark ? el.dataset.darkBg : el.dataset.lightBg;
                })();
            </script>
            <x-admin-skeleton :fullScreen="true" />
        </div>
    @endif

    <div id="top-progress-bar"></div>

    <div id="mobile-sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/80 z-40 opacity-0 pointer-events-none md:hidden transition-opacity duration-300 backdrop-blur-sm"></div>

    @include('layouts.partials.admin.sidebar')

    <!-- Main Content Area -->
    <div id="main-content" class="md:ml-64 flex-1 flex flex-col min-h-screen min-w-0 w-full max-w-full transition-all duration-300 ease-in-out">
        
        @include('layouts.partials.admin.topbar')

        <!-- Main Body -->
        <main class="flex-1 px-3.5 sm:px-8 py-4 sm:py-5 max-w-[1500px] w-full min-w-0 mx-auto relative">
            <div id="actual-page-content" class="w-full h-full transition-all duration-300 {{ $isFromLogin ? 'opacity-0' : 'animate-fade-in-up' }}">
                @yield('content')
            </div>
        </main>

        <footer class="px-4 sm:px-8 py-3.5 border-t border-slate-200/70 bg-white dark:bg-[#181a1b] dark:border-gray-800 text-xs text-slate-500 flex items-center justify-center text-center w-full">
            <div>
                © {{ date('Y') }} <span class="font-semibold text-slate-700 dark:text-slate-300">PayMe Panamá</span> — Sistema de Comercio Electrónico PyME.
            </div>
        </footer>
    </div>

    @include('layouts.partials.admin.scripts')

    <x-modal-escaner inputId="buscar" formId="top-search-form" />
    <x-toast-alert />
    <x-modal-eliminar />

    @stack('scripts')
</body>
</html>