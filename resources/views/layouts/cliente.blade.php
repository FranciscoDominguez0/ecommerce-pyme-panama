<!DOCTYPE html>
<html class="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'PayMe Panamá') }} - @yield('title', 'Tienda Online')</title>

    <!-- Favicon & Iconos Oficiales -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-192.png') }}">

    <!-- Vite Build Pipeline: Tailwind CSS compilado en producción -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Tipografía Oficial de Laravel: Figtree & Material Symbols (Local) -->
    <link rel="stylesheet" href="{{ asset('fonts/fonts.css') }}">
    <link rel="preload" href="{{ asset('fonts/material-symbols-outlined.woff2') }}" as="font" type="font/woff2"
        crossorigin>

    <style>
        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Figtree', sans-serif;
            letter-spacing: -0.015em;
        }

        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined';
            font-weight: normal;
            font-style: normal;
            font-size: 20px;
            line-height: 1;
            letter-spacing: normal;
            text-transform: none;
            display: inline-block;
            white-space: nowrap;
            word-wrap: normal;
            direction: ltr;
            -webkit-font-feature-settings: 'liga';
            -webkit-font-smoothing: antialiased;
            font-feature-settings: 'liga';
            text-rendering: optimizeLegibility;
        }

        .whatsapp-float {
            position: fixed;
            width: 52px;
            height: 52px;
            bottom: 20px;
            right: 20px;
            background-color: #25d366;
            color: #FFF;
            border-radius: 50px;
            text-align: center;
            font-size: 26px;
            box-shadow: 0 4px 16px rgba(37, 211, 102, 0.4);
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .whatsapp-float:hover {
            transform: scale(1.08);
            box-shadow: 0 8px 24px rgba(37, 211, 102, 0.6);
        }

        .sidebar-nav-active {
            background-color: rgba(0, 35, 73, 0.10) !important;
            color: #002349 !important;
        }
    </style>

    @livewireStyles
    @stack('styles')
</head>

<body
    class="bg-[#F8F9FF] text-[#0b1c30] flex flex-col min-h-screen text-sm antialiased selection:bg-[#8af5be] selection:text-[#00714b]">

    <!-- Top Notification Banner -->
    <div
        class="bg-[#002349] text-white text-[11px] font-semibold py-1.5 px-4 text-center flex items-center justify-center gap-2">
        <span>Envíos a todo Panamá</span>
        <span class="opacity-40">•</span>
        <span class="flex items-center gap-1 text-[#8af5be]">
            <span class="material-symbols-outlined text-[13px]">verified_user</span> Pagos con Yappy, Tarjeta & ACH
        </span>
    </div>

    <!-- Main Navigation Bar -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-gray-200/80 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-15 py-2.5 gap-4">

                <!-- Brand Logo -->
                <a href="{{ url('/') }}" wire:navigate class="flex items-center gap-2.5 shrink-0">
                    <x-application-logo size="default" />
                    <div class="hidden md:block">
                        <span class="text-base font-bold text-[#002349] tracking-tight block leading-none">PayMe <span
                                class="text-[#006148]">Panamá</span></span>
                        <span
                            class="text-[9px] text-gray-500 font-semibold uppercase tracking-wider block mt-0.5">Tecnología
                            & Equipos IT</span>
                    </div>
                </a>

                <!-- Search Bar -->
                <div class="hidden md:flex flex-1 max-w-md mx-4">
                    <form action="{{ route('cliente.catalogo') }}" method="GET" class="w-full relative">
                        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar productos, categorías..."
                            class="w-full bg-gray-50 border border-gray-200 rounded-lg pl-9 pr-3 py-1.5 text-xs text-gray-900 focus:bg-white focus:ring-1 focus:ring-[#006148] focus:border-[#006148] transition-all" />
                        <button type="submit" class="absolute left-2.5 top-2 text-gray-400 hover:text-[#006148] transition-colors">
                            <span class="material-symbols-outlined text-[16px]">search</span>
                        </button>
                    </form>
                </div>

                <!-- Navigation Links & User Menu -->
                <div class="flex items-center gap-3">
                    <!-- Badges y Enlaces Dinámicos de Carrito y Deseos -->
                    <livewire:navbar-badges />

                    <!-- User Authentication -->
                    @auth
                        <div class="flex items-center gap-2">
                            <a href="{{ route('dashboard') }}" wire:navigate
                                class="flex items-center gap-1.5 py-1 px-2.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-xs font-semibold text-[#002349] transition-colors">
                                <span class="material-symbols-outlined text-[15px]">account_circle</span>
                                <span>{{ Auth::user()->nombre ?? 'Mi Cuenta' }}</span>
                            </a>

                            @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('super_admin'))
                                <a href="{{ route('admin.dashboard') }}"
                                    class="hidden sm:inline-flex items-center gap-1 py-1 px-2.5 rounded-lg bg-[#002349] text-white text-xs font-semibold hover:bg-[#00132b] transition-colors">
                                    <span class="material-symbols-outlined text-[14px]">admin_panel_settings</span>
                                    <span>Panel Admin</span>
                                </a>
                            @endif

                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs text-gray-500 hover:text-red-600 p-1"
                                    title="Cerrar Sesión">
                                    <span class="material-symbols-outlined text-[17px]">logout</span>
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('login') }}" wire:navigate
                                class="text-xs font-semibold text-[#002349] hover:text-[#006148] px-2.5 py-1.5 transition-colors">
                                Iniciar Sesión
                            </a>
                            <a href="{{ route('register') }}" wire:navigate
                                class="text-xs font-semibold text-white bg-[#006148] hover:bg-[#004f3b] px-3 py-1.5 rounded-lg shadow-xs transition-colors">
                                Registrarme
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Floating WhatsApp Button -->
    <a href="https://wa.me/50768118272?text=Hola%2C%20tengo%20una%20consulta%20sobre%20los%20productos%20de%20PayMe%20Panam%C3%A1"
        target="_blank" rel="noopener noreferrer" class="whatsapp-float" title="Chatea con nosotros por WhatsApp">
        <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24">
            <path
                d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.861.174.086.275.073.376-.044.101-.116.433-.506.549-.68.116-.173.231-.145.39-.086s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.1.824zm-3.423-14.416c-6.627 0-12 5.373-12 12 0 2.155.57 4.178 1.564 5.927l-1.637 5.975 6.136-1.61c1.706.93 3.659 1.464 5.737 1.464 6.627 0 12-5.373 12-12 0-6.627-5.373-12-12-12z" />
        </svg>
    </a>

    <!-- Footer -->
    <footer class="bg-[#002349] text-white pt-10 pb-6 border-t border-white/10 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Brand Bio -->
                <div class="space-y-3 md:col-span-1">
                    <div class="flex items-center gap-2.5">
                        <x-application-logo size="default" />
                        <span class="text-base font-bold text-white">PayMe Panamá</span>
                    </div>
                    <p class="text-xs text-gray-300 leading-relaxed">
                        Tienda especializada en tecnología, equipos informáticos, periféricos y servicios IT en la
                        República de Panamá.
                    </p>
                </div>

                <!-- Navigation Links -->
                <div>
                    <h4 class="text-[11px] font-bold text-white uppercase tracking-wider mb-3 text-[#8af5be]">Tienda
                    </h4>
                    <ul class="space-y-1.5 text-xs text-gray-300">
                        <li><a href="{{ url('/') }}" wire:navigate class="hover:text-white transition-colors">Inicio</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Catálogo de Productos</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Ofertas Especiales</a></li>
                    </ul>
                </div>

                <!-- Customer Service -->
                <div>
                    <h4 class="text-[11px] font-bold text-white uppercase tracking-wider mb-3 text-[#8af5be]">Soporte
                    </h4>
                    <ul class="space-y-1.5 text-xs text-gray-300">
                        <li><a href="#" class="hover:text-white transition-colors">Preguntas Frecuentes</a></li>
                        <li><a href="#" class="hover:text-white transition-colors">Políticas de Envío</a></li>
<li><a href="{{ route('terminos') }}" wire:navigate class="hover:text-white transition-colors">Términos y
                                    Condiciones</a></li>
                    </ul>
                </div>

                <!-- Contact & Security -->
                <div class="space-y-3">
                    <h4 class="text-[11px] font-bold text-white uppercase tracking-wider mb-1.5 text-[#8af5be]">
                        Seguridad & Pagos</h4>
                    <p class="text-xs text-gray-300">Aceptamos pagos locales en Panamá:</p>
                    <div class="flex flex-wrap items-center gap-2 pt-1">

                        <!-- Yappy Official Logo -->
                        <div class="w-14 h-8 bg-white rounded-lg flex items-center justify-center p-1 shadow-xs border border-white/20 hover:scale-105 transition-transform overflow-hidden shrink-0"
                            title="Yappy Comercial Panamá">
                            <img src="{{ asset('images/pa-yappy.webp') }}" alt="Yappy Panamá"
                                class="max-h-full max-w-full w-auto h-auto object-contain block" />
                        </div>

                        <!-- Visa Official Logo -->
                        <div class="w-14 h-8 bg-white rounded-lg flex items-center justify-center p-1 shadow-xs border border-white/20 hover:scale-105 transition-transform overflow-hidden shrink-0"
                            title="Visa">
                            <img src="{{ asset('images/visa-logo.png') }}" alt="Visa"
                                class="max-h-full max-w-full w-auto h-auto object-contain block" />
                        </div>

                        <!-- Mastercard Official Logo -->
                        <div class="w-14 h-8 bg-white rounded-lg flex items-center justify-center p-1 shadow-xs border border-white/20 hover:scale-105 transition-transform overflow-hidden shrink-0"
                            title="Mastercard">
                            <img src="{{ asset('images/mastercard-logo.png') }}" alt="Mastercard"
                                class="max-h-full max-w-full w-auto h-auto object-contain block" />
                        </div>

                        <!-- Sistema Clave Official Logo -->
                        <div class="w-14 h-8 bg-white rounded-lg flex items-center justify-center p-0.5 shadow-xs border border-white/20 hover:scale-105 transition-transform overflow-hidden shrink-0"
                            title="Sistema Clave Panamá">
                            <img src="{{ asset('images/clave-logo.png') }}" alt="Sistema Clave Panamá"
                                class="max-h-full max-w-full w-auto h-auto object-contain block" />
                        </div>

                    </div>
                    <div class="pt-1 flex items-center gap-1.5 text-emerald-400 text-xs font-medium">
                        <span class="material-symbols-outlined text-[15px]">verified_user</span>
                        <span>Garantía & Respaldo Local</span>
                    </div>
                </div>
            </div>

            <div
                class="border-t border-white/10 pt-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] text-gray-400">
                <p class="flex items-center gap-1.5">
                    <span>Comercio Electrónico Seguro</span>
                    <span class="opacity-40">•</span>
                    <span>República de Panamá</span>
                </p>
            </div>
        </div>
    </footer>

    <!-- Carrito Lateral Drawer Offcanvas -->
    <livewire:carrito-drawer />

    <!-- Sistema Global de Alertas y Notificaciones Toast -->
    <x-toast-alert />

    @livewireScripts
    <script>
        window.abrirCarritoDrawer = function () {
            if (window.Livewire) {
                Livewire.dispatch('abrir-carrito-drawer');
            } else {
                window.dispatchEvent(new CustomEvent('abrir-carrito'));
            }
        };

        document.addEventListener('livewire:init', () => {
            Livewire.on('mostrar-toast', (event) => {
                const data = Array.isArray(event) ? event[0] : (event.detail ? (Array.isArray(event.detail) ? event.detail[0] : event.detail) : event);
                if (window.mostrarToast && data) {
                    window.mostrarToast(data.tipo || 'info', data.mensaje || '');
                }
            });
        });
    </script>
    @stack('scripts')
</body>

</html>