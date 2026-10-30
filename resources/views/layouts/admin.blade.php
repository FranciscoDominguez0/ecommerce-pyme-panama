@php
    $serverTheme = $_COOKIE['admin_theme'] ?? request()->cookie('admin_theme');
@endphp
<!DOCTYPE html>
<html class="{{ $serverTheme === 'dark' ? 'dark' : ($serverTheme === 'light' ? 'light' : '') }}" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    {{-- Script inline prioritario anti-parpadeo --}}
    <script>
        (function() {
            var lastSavedTheme = null;
            function applyTheme() {
                var theme = localStorage.getItem('color-theme');
                if (!theme) {
                    theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                }
                var cl = document.documentElement.classList;
                var needsDark = (theme === 'dark');
                var hasDark = cl.contains('dark');
                var hasLight = cl.contains('light');

                if (needsDark && (!hasDark || hasLight)) {
                    cl.add('dark');
                    cl.remove('light');
                } else if (!needsDark && (hasDark || !hasLight)) {
                    cl.remove('dark');
                    cl.add('light');
                }

                if (lastSavedTheme !== theme) {
                    lastSavedTheme = theme;
                    document.cookie = "admin_theme=" + theme + ";path=/;max-age=31536000;SameSite=Lax";
                }
            }
            applyTheme();

            // Evitar que morphs de Livewire o navegación quiten la clase antes del render
            if (window.MutationObserver && !window.__themeObserver) {
                window.__themeObserver = new MutationObserver(function() {
                    var theme = localStorage.getItem('color-theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                    var cl = document.documentElement.classList;
                    var isCorrect = (theme === 'dark') ? (cl.contains('dark') && !cl.contains('light')) : (!cl.contains('dark') && cl.contains('light'));
                    if (!isCorrect) {
                        applyTheme();
                    }
                });
                window.__themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
            }
        })();
    </script>

    {{-- Estilo crítico inline para pintar el fondo de inmediato y evitar saltos de layout (CLS / FOUT) --}}
    <style id="theme-preload-style">
        html { scrollbar-gutter: stable; }
        html.dark, html.dark body { background-color: #181a1b !important; color-scheme: dark; }
        html.light, html.light body { background-color: #f8fafc !important; color-scheme: light; }
        @media (prefers-color-scheme: dark) {
            html:not(.light), html:not(.light) body { background-color: #181a1b !important; color-scheme: dark; }
        }
        /* Bloqueo estricto anti-FOUT para iconos: evita que el texto de la ligadura se desborde o mueva el layout */
        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined';
            font-weight: normal;
            font-style: normal;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1em;
            height: 1em;
            max-width: 1em;
            max-height: 1em;
            overflow: hidden;
            vertical-align: middle;
            white-space: nowrap;
            letter-spacing: normal;
            direction: ltr;
            -webkit-font-feature-settings: 'liga';
            font-feature-settings: 'liga';
            -webkit-font-smoothing: antialiased;
            flex-shrink: 0;
            user-select: none;
        }
    </style>

    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $adminApp = (config('app.name') && config('app.name') !== 'Laravel') ? config('app.name') : 'PayMe Panamá';
        $viewAdminTitle = trim($__env->yieldContent('title', 'Panel de Administración'));
        if (str_contains($viewAdminTitle, 'PayMe')) {
            $pageAdminTitle = $viewAdminTitle;
        } else {
            $pageAdminTitle = $adminApp . ' - ' . $viewAdminTitle;
        }
    @endphp
    <title>{{ $pageAdminTitle }}</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-192.png') }}">

    <!-- Precarga de fuentes locales críticas (Cero latencia / Anti-FOUT) -->
    <link rel="preload" href="{{ asset('fonts/material-symbols-outlined.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('fonts/plus-jakarta-sans.woff2') }}" as="font" type="font/woff2" crossorigin>

    <link rel="stylesheet" href="{{ asset('fonts/fonts.css') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('layouts.partials.admin.styles')

    @stack('styles')
</head>
<body class="text-slate-900 dark:text-slate-100 min-h-screen flex flex-col md:flex-row text-sm antialiased selection:bg-emerald-100 selection:text-emerald-900 w-full max-w-full overflow-x-clip relative" style="background-color: var(--admin-bg);">
    
    @php
        $isFromLogin = session()->pull('is_from_login', false) || str_contains(request()->headers->get('referer', ''), '/login') || str_contains(request()->headers->get('referer', ''), '2fa-challenge');
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

    <!-- Área de contenido principal -->
    <div id="main-content" class="md:ml-64 flex-1 flex flex-col min-h-screen min-w-0 w-full max-w-full transition-all duration-300 ease-in-out">
        
        @include('layouts.partials.admin.topbar')

        <!-- Cuerpo principal -->
        <main class="flex-1 px-3.5 sm:px-8 py-4 sm:py-5 max-w-[1500px] w-full min-w-0 mx-auto relative">
            <div id="actual-page-content" class="w-full h-full {{ $isFromLogin ? 'opacity-0' : '' }}">
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