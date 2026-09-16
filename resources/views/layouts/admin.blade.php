<!DOCTYPE html>
<html class="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'PayMe Panamá') }} - @yield('title', 'Panel de Administración')</title>

    <!-- Favicon & Iconos Oficiales -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-192.png') }}">

    <!-- Vite Build Pipeline: Tailwind CSS compilado en producción -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Fuente Oficial de Laravel & Business SaaS: Plus Jakarta Sans, Figtree & Material Symbols (Local) -->
    <link rel="stylesheet" href="{{ asset('fonts/fonts.css') }}">
    <link rel="preload" href="{{ asset('fonts/material-symbols-outlined.woff2') }}" as="font" type="font/woff2" crossorigin>

    <style>
        html, body {
            max-width: 100%;
            overflow-x: clip;
        }
        body { 
            font-family: 'Plus Jakarta Sans', 'Figtree', sans-serif; 
            letter-spacing: -0.011em;
            background-color: #F8FAFC;
            color: #111827;
        }
        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined';
            font-weight: normal;
            font-style: normal;
            font-size: 20px;
            line-height: 1;
            display: inline-block;
        -webkit-font-smoothing: antialiased;
        }

        :root {
            --admin-bg-light: #f8fafc;
            --admin-bg-dark: #181a1b;
            --admin-bg: var(--admin-bg-light);
        }
        html.dark {
            --admin-bg: var(--admin-bg-dark);
        }

        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94A3B8; }
        /* Firefox Scrollbar */
        * {
            scrollbar-width: thin;
            scrollbar-color: #475569 transparent;
        }
        
        /* Ocultar barra de scroll en el Sidebar */
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        .card-elevated {
            background-color: #FFFFFF;
            border: 1px solid #E5E7EB;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 1px 2px -1px rgba(0, 0, 0, 0.03);
        }
        :root {
            --sidebar-offset: 0px;
        }
        @media (min-width: 768px) {
            :root {
                --sidebar-offset: 256px;
            }
        }
        
        /* Barra de progreso de navegación superior (Estilo Premium) */
        #top-progress-bar {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            background: #10B981; /* emerald-500 */
            z-index: 99999;
            width: 0%;
            opacity: 0;
            pointer-events: none;
            box-shadow: 0 0 10px #10B981, 0 0 4px #10B981;
        }
        
        .navigating #top-progress-bar {
            opacity: 1;
            width: 75%;
            transition: width 15s cubic-bezier(0.1, 0.05, 0, 1);
        }

        /* Animaciones Suaves de Página */
        @keyframes subtleFadeIn {
            0% { opacity: 0; transform: translateY(6px); }
            100% { opacity: 1; transform: none; }
        }
        .animate-fade-in-up {
            animation: subtleFadeIn 0.35s ease-out;
        }
        
        /* Animación de salida ultra-sutil (Sin blurs ni blancos) */
        .page-transitioning {
            opacity: 0.65 !important;
            pointer-events: none;
            transition: opacity 0.2s ease-out !important;
        }

        /* Seamless Active Sidebar Item */
        .sidebar-active-item .material-symbols-outlined {
            color: #059669 !important;
            font-variation-settings: 'FILL' 1;
        }

        @media (min-width: 768px) {
            .sidebar-active-item {
                background-color: var(--admin-bg) !important;
                color: #059669 !important;
                border-top-left-radius: 9999px;
                border-bottom-left-radius: 9999px;
                border-top-right-radius: 0;
                border-bottom-right-radius: 0;
                position: relative;
                margin-right: 0 !important;
                padding-right: 1.625rem !important; /* Compensa el mr-3 inactivo para alinear badges */
            }
            html.dark .sidebar-active-item {
                color: #10b981 !important; /* emerald-500 */
            }
            .sidebar-active-item::before,
            .sidebar-active-item::after {
                content: '';
                position: absolute;
                right: 0;
                width: 20px;
                height: 20px;
                z-index: -1;
            }
            .sidebar-active-item::before {
                top: -20px;
                background-image: radial-gradient(circle at top left, transparent 20px, var(--admin-bg) 20.5px) !important;
            }
            .sidebar-active-item::after {
                bottom: -20px;
                background-image: radial-gradient(circle at bottom left, transparent 20px, var(--admin-bg) 20.5px) !important;
            }
        }
        @media (max-width: 767px) {
            .sidebar-active-item {
                background-color: var(--admin-bg) !important;
                color: #059669 !important;
                border-radius: 9999px;
                margin-right: 0.75rem !important; /* mr-3 */
            }
        }
    </style>


    <!-- Prevención de Parpadeo (FOUC) para el Sidebar -->
    <script>
        if (localStorage.getItem('sidebarExpanded') === 'false') {
            document.documentElement.classList.add('sidebar-collapsed');
        }
    </script>
    <style>
        :root {
            --sidebar-width: 256px;
        }
        html.sidebar-collapsed {
            --sidebar-width: 64px;
        }
        @media (min-width: 768px) {
            #admin-sidebar { width: var(--sidebar-width) !important; }
            #main-content { margin-left: var(--sidebar-width) !important; }
        }
        /* Collapsible Sidebar Styles */
        html.sidebar-collapsed #admin-sidebar .sidebar-text { display: none; }
        html.sidebar-collapsed #admin-sidebar .sidebar-group-title { display: none; }
        html.sidebar-collapsed #admin-sidebar .brand-text { display: none; }
        html.sidebar-collapsed #admin-sidebar a, html.sidebar-collapsed #admin-sidebar button:not(#theme-toggle) { 
            justify-content: center; 
            padding-left: 0; 
            padding-right: 0; 
            width: 40px; 
            height: 40px; 
            margin: 0 auto; 
        }
        html.sidebar-collapsed #admin-sidebar .sidebar-header { justify-content: center; padding-left: 0; padding-right: 0; }
        html.sidebar-collapsed #admin-sidebar .brand-logo-container { margin: 0 auto; }
        html.sidebar-collapsed #admin-sidebar .sidebar-version { display: none; }
        html.sidebar-collapsed #admin-sidebar .sidebar-footer { padding-left: 0; padding-right: 0; justify-content: center; }
        html.sidebar-collapsed #admin-sidebar #theme-toggle { transform: scale(0.85); margin: 0 auto; }
        html.sidebar-collapsed #admin-sidebar .sidebar-active-item { 
            padding-right: 0 !important; 
            width: 52px !important; 
        }
    </style>

    
    <!-- Dark Mode Initializer -->
    <script>
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    @stack('styles')
</head>
<body class="text-slate-900 dark:text-slate-100 min-h-screen flex flex-col md:flex-row text-sm antialiased selection:bg-emerald-100 selection:text-emerald-900 w-full max-w-full overflow-x-clip relative" style="background-color: var(--admin-bg);">
    
    @php
        $isFromLogin = session('is_from_login', false) || str_contains(request()->headers->get('referer', ''), '/login') || str_contains(request()->headers->get('referer', ''), '/2fa');
    @endphp

    <!-- Esqueleto Global de Carga (Cubre toda la pantalla) -->
    @if($isFromLogin)
        <div id="global-admin-skeleton-wrapper" class="fixed inset-0 z-[9999] bg-[#F8FAFC] transition-opacity duration-300">
            <x-admin-skeleton :fullScreen="true" />
        </div>
    @endif

    <!-- Barra de progreso superior -->
    <div id="top-progress-bar"></div>

    <!-- Mobile Sidebar Drawer (Overlay) -->
    <div id="mobile-sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/80 z-40 hidden md:hidden transition-opacity backdrop-blur-sm"></div>

    <!-- Sidebar Admin (Fondo #1F2937) -->
    <aside id="admin-sidebar" class="w-64 fixed left-0 top-0 h-full bg-[#1F2937] text-slate-200 z-50 transform -translate-x-full md:translate-x-0 transition-all duration-300 ease-in-out shadow-2xl md:shadow-none flex flex-col justify-between select-none">
        
        <!-- Header & Navigation -->
        <div class="flex flex-col flex-1 min-h-0">
            
            <!-- Brand Logo Header -->
            <div class="sidebar-header px-5 py-4 border-b border-gray-700/60 flex items-center justify-between bg-black/20 shrink-0 transition-all duration-300">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                    <div class="brand-logo-container w-9 h-9 rounded-xl bg-white p-1.5 flex items-center justify-center shadow-md border border-slate-700/40 group-hover:scale-105 transition-all shrink-0 overflow-hidden">
                        <x-application-logo size="sm" />
                    </div>
                    <div class="brand-text transition-all duration-300">
                        <h1 class="text-sm font-extrabold text-white tracking-tight leading-tight">
                            PayMe <span class="text-[#059669] font-bold">Panamá</span>
                        </h1>
                        <span class="text-[10px] font-medium text-slate-400 tracking-wide block">Panel Administrativo</span>
                    </div>
                </a>
                <button onclick="toggleSidebar()" class="md:hidden text-slate-400 hover:text-white p-1 rounded-lg hover:bg-white/10 transition-colors">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <!-- Navigation Links (Scrollable with custom scrollbar) -->
            <nav class="flex-1 pl-3 py-3 pr-0 overflow-y-auto space-y-4 hide-scrollbar">
                
                <!-- Grupo 1: General -->
                @canany(['admin.dashboard', 'admin.pedidos.ver', 'admin.devoluciones.ver'])
                <div>
                    <div class="sidebar-group-title text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 px-3 transition-all duration-300">
                        General
                    </div>
                    <div class="space-y-0.5">
                        @can('admin.dashboard')
                        <!-- Dashboard -->
                        <a href="{{ route('admin.dashboard') }}" 
                           class="group relative flex items-center gap-3 px-3.5 py-2.5 text-xs font-bold transition-all {{ request()->routeIs('admin.dashboard') ? 'sidebar-active-item' : 'rounded-full mr-3 text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                            <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->routeIs('admin.dashboard') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}" style="{{ request()->routeIs('admin.dashboard') ? 'font-variation-settings: \'FILL\' 1;' : '' }}">dashboard</span>
                            <span class="sidebar-text truncate transition-all duration-300">Dashboard</span>
                        </a>
                        @endcan

                        @can('admin.pedidos.ver')
                        @php
                            // Un pedido es "nuevo" (por procesar) si no tiene estados avanzados
                            $nuevosPedidosCount = \App\Models\Pedido::whereNotExists(function ($query) {
                                $query->select(\Illuminate\Support\Facades\DB::raw(1))
                                      ->from('estados_pedido')
                                      ->whereColumn('estados_pedido.pedido_id', 'pedidos.id')
                                      ->whereNotIn('estados_pedido.estado', ['pendiente', 'pago_confirmado']);
                            })->count();
                        @endphp
                        <!-- Pedidos & Ventas -->
                        <a href="{{ url('/admin/pedidos') }}" 
                           class="group relative flex items-center justify-between gap-3 px-3.5 py-2.5 text-xs font-bold transition-all {{ request()->is('admin/pedidos*') ? 'sidebar-active-item' : 'rounded-full mr-3 text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->is('admin/pedidos*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">shopping_bag</span>
                                <span class="sidebar-text truncate transition-all duration-300">Pedidos & Ventas</span>
                            </div>
                            <x-sidebar-badge :count="$nuevosPedidosCount" />
                        </a>
                        @endcan

                        @can('admin.devoluciones.ver')
                        @php
                            $nuevasDevolucionesCount = \App\Models\Devolucion::where('estado', 'pendiente')->count();
                        @endphp
                        <!-- Devoluciones -->
                        <a href="{{ route('admin.devoluciones.index') }}" 
                           class="group relative flex items-center justify-between gap-3 px-3.5 py-2.5 text-xs font-bold transition-all {{ request()->routeIs('admin.devoluciones*') ? 'sidebar-active-item' : 'rounded-full mr-3 text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->routeIs('admin.devoluciones*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">assignment_return</span>
                                <span class="sidebar-text truncate transition-all duration-300">Devoluciones</span>
                            </div>
                            <x-sidebar-badge :count="$nuevasDevolucionesCount" />
                        </a>
                        @endcan
                    </div>
                </div>
                @endcanany

                <!-- Grupo 2: Catálogo & Stock -->
                @canany(['admin.productos.ver', 'admin.categorias.ver', 'admin.marcas.ver', 'admin.inventario.ver', 'admin.zonas.ver', 'admin.cupones.ver', 'admin.promociones.ver'])
                <div>
                    <div class="sidebar-group-title text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 px-3 transition-all duration-300">
                        Catálogo & Stock
                    </div>
                    <div class="space-y-0.5">
                        @can('admin.productos.ver')
                        <!-- Productos -->
                        <a href="{{ route('admin.productos.index') }}" 
                           class="group relative flex items-center gap-3 px-3.5 py-2.5 text-xs font-bold transition-all {{ request()->routeIs('admin.productos*') ? 'sidebar-active-item' : 'rounded-full mr-3 text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                            <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->routeIs('admin.productos*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">sell</span>
                            <span class="sidebar-text truncate transition-all duration-300">Productos</span>
                        </a>
                        @endcan

                        @can('admin.categorias.ver')
                        <!-- Categorías -->
                        <a href="{{ route('admin.categorias.index') }}" 
                           class="group relative flex items-center gap-3 px-3.5 py-2.5 text-xs font-bold transition-all {{ request()->routeIs('admin.categorias*') ? 'sidebar-active-item' : 'rounded-full mr-3 text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                            <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->routeIs('admin.categorias*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">category</span>
                            <span class="sidebar-text truncate transition-all duration-300">Categorías</span>
                        </a>
                        @endcan

                        @can('admin.marcas.ver')
                        <!-- Marcas -->
                        <a href="{{ route('admin.brands.index') }}" 
                           class="group relative flex items-center gap-3 px-3.5 py-2.5 text-xs font-bold transition-all {{ request()->routeIs('admin.brands*') ? 'sidebar-active-item' : 'rounded-full mr-3 text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                            <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->routeIs('admin.brands*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">verified</span>
                            <span class="sidebar-text truncate transition-all duration-300">Marcas & Logos</span>
                        </a>
                        @endcan

                        @can('admin.inventario.ver')
                        <!-- Inventario -->
                        <a href="{{ url('/admin/inventario') }}" 
                           class="group relative flex items-center gap-3 px-3.5 py-2.5 text-xs font-bold transition-all {{ request()->is('admin/inventario*') ? 'sidebar-active-item' : 'rounded-full mr-3 text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                            <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->is('admin/inventario*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">warehouse</span>
                            <span class="sidebar-text truncate transition-all duration-300">Inventario</span>
                        </a>
                        @endcan

                        @can('admin.zonas.ver')
                        <!-- Zonas de Envío -->
                        <a href="{{ route('admin.zonas-envio.index') }}" 
                           class="group relative flex items-center gap-3 px-3.5 py-2.5 text-xs font-bold transition-all {{ request()->routeIs('admin.zonas-envio*') ? 'sidebar-active-item' : 'rounded-full mr-3 text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                            <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->routeIs('admin.zonas-envio*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">local_shipping</span>
                            <span class="sidebar-text truncate transition-all duration-300">Zonas de Envío</span>
                        </a>
                        @endcan

                        @can('admin.cupones.ver')
                        <!-- Cupones de Descuento -->
                        <a href="{{ route('admin.promociones.cupones') }}" 
                           class="group relative flex items-center gap-3 px-3.5 py-2.5 text-xs font-bold transition-all {{ request()->routeIs('admin.promociones.cupones*') ? 'sidebar-active-item' : 'rounded-full mr-3 text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                            <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->routeIs('admin.promociones.cupones*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">local_offer</span>
                            <span class="sidebar-text truncate transition-all duration-300">Cupones de Descuento</span>
                        </a>
                        @endcan

                        @can('admin.promociones.ver')
                        <!-- Promociones Especiales -->
                        <a href="{{ route('admin.promociones.envio-gratis') }}" 
                           class="group relative flex items-center gap-3 px-3.5 py-2.5 text-xs font-bold transition-all {{ request()->routeIs('admin.promociones.envio-gratis*') || request()->routeIs('admin.promociones.producto-del-mes*') ? 'sidebar-active-item' : 'rounded-full mr-3 text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                            <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->routeIs('admin.promociones.envio-gratis*') || request()->routeIs('admin.promociones.producto-del-mes*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">campaign</span>
                            <span class="sidebar-text truncate transition-all duration-300">Promociones Especiales</span>
                        </a>
                        @endcan
                    </div>
                </div>
                @endcanany

                <!-- Grupo 3: Clientes & Facturación -->
                @canany(['admin.usuarios.ver', 'admin.facturas.ver', 'admin.reportes.ver'])
                <div>
                    <div class="sidebar-group-title text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 px-3 transition-all duration-300">
                        Clientes & Finanzas
                    </div>
                    <div class="space-y-0.5">
                        @can('admin.usuarios.ver')
                        <!-- Usuarios y Roles -->
                        <a href="{{ route('admin.usuarios.index') }}" 
                           class="group relative flex items-center gap-3 px-3.5 py-2.5 text-xs font-bold transition-all {{ request()->is('admin/usuarios*') ? 'sidebar-active-item' : 'rounded-full mr-3 text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                            <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->is('admin/usuarios*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">admin_panel_settings</span>
                            <span class="sidebar-text truncate transition-all duration-300">Usuarios y Roles</span>
                        </a>
                        @endcan

                        @can('admin.facturas.ver')
                        <!-- Facturación Fiscal -->
                        <a href="{{ url('/admin/facturas') }}" 
                           class="group relative flex items-center gap-3 px-3.5 py-2.5 text-xs font-bold transition-all {{ request()->is('admin/facturas*') ? 'sidebar-active-item' : 'rounded-full mr-3 text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                            <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->is('admin/facturas*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">receipt_long</span>
                            <span class="sidebar-text truncate transition-all duration-300">Facturación Fiscal</span>
                        </a>
                        @endcan

                        @can('admin.reportes.ver')
                        <!-- Reportes -->
                        <a href="{{ url('/admin/reportes') }}" 
                           class="group relative flex items-center gap-3 px-3.5 py-2.5 text-xs font-bold transition-all {{ request()->is('admin/reportes*') ? 'sidebar-active-item' : 'rounded-full mr-3 text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                            <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->is('admin/reportes*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">bar_chart</span>
                            <span class="sidebar-text truncate transition-all duration-300">Reportes</span>
                        </a>
                        @endcan
                    </div>
                </div>
                @endcanany

                <!-- Grupo 4: Sistema & Auditoría -->
                @canany(['admin.auditoria.ver', 'admin.configuracion.ver'])
                <div>
                    <div class="sidebar-group-title text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 px-3 transition-all duration-300">
                        Sistema & Seguridad
                    </div>
                    <div class="space-y-0.5">
                        @can('admin.auditoria.ver')
                        <!-- Auditoría -->
                        <a href="{{ url('/admin/auditoria') }}" 
                           class="group relative flex items-center gap-3 px-3.5 py-2.5 text-xs font-bold transition-all {{ request()->is('admin/auditoria*') ? 'sidebar-active-item' : 'rounded-full mr-3 text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                            <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->is('admin/auditoria*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">security</span>
                            <span class="sidebar-text truncate transition-all duration-300">Auditoría</span>
                        </a>
                        @endcan

                        @can('admin.configuracion.ver')
                        <!-- Configuración -->
                        <a href="{{ url('/admin/configuracion') }}" 
                           class="group relative flex items-center gap-3 px-3.5 py-2.5 text-xs font-bold transition-all {{ request()->is('admin/configuracion*') ? 'sidebar-active-item' : 'rounded-full mr-3 text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                            <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->is('admin/configuracion*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">settings</span>
                            <span class="sidebar-text truncate transition-all duration-300">Configuración</span>
                        </a>
                        @endcan
                    </div>
                </div>
                @endcanany

            </nav>
        </div>

        <!-- Sidebar Footer / Actions -->
        <div class="sidebar-footer px-4 py-4 bg-black/20 flex justify-between items-center transition-all duration-300">

            <!-- System Version (Subtle) -->
            <div class="sidebar-version text-[10px] font-medium text-slate-500 tracking-wider cursor-default select-none transition-all duration-300">
                PayMe v1.0.0
            </div>

            <!-- Dark Mode Toggle Switch Premium -->
            <button id="theme-toggle" type="button" 
                    class="relative inline-flex h-8 w-16 items-center rounded-full bg-slate-900 border border-slate-700 shadow-inner focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:ring-offset-1 focus:ring-offset-slate-800">
                <span class="sr-only">Toggle Dark Mode</span>
                <span id="theme-toggle-thumb" 
                      class="inline-flex h-6 w-6 transform items-center justify-center rounded-full bg-white translate-x-1 shadow-sm">
                    <!-- Sun Icon (Claro) -->
                    <span id="theme-toggle-light-icon" class="material-symbols-outlined text-[14px] text-amber-500" style="font-variation-settings: 'FILL' 1;">light_mode</span>
                    <!-- Moon Icon (Oscuro) -->
                    <span id="theme-toggle-dark-icon" class="material-symbols-outlined text-[14px] text-slate-800 absolute opacity-0" style="font-variation-settings: 'FILL' 1;">dark_mode</span>
                </span>
            </button>
            
        </div>
    </aside>

    <!-- Main Content Area -->
    <div id="main-content" class="md:ml-64 flex-1 flex flex-col min-h-screen min-w-0 w-full max-w-full transition-all duration-300 ease-in-out">
        
        <!-- TopNavBar Ejecutivo (Fijo en la parte superior al hacer scroll) -->
        <header class="sticky top-0 z-40 w-full max-w-full px-3.5 sm:px-8 py-3 bg-white/95 dark:bg-[#181a1b]/95 backdrop-blur-md border-b border-slate-200/80 dark:border-gray-800 shadow-xs flex items-center justify-between gap-2 sm:gap-4 shrink-0">
            
            <!-- Left: Toggle & Responsive Breadcrumbs -->
            <div class="flex items-center gap-2 sm:gap-3 min-w-0 overflow-hidden">
                <!-- Hamburger Button (Mobile) -->
                <button onclick="toggleSidebar()" class="md:hidden p-1.5 text-slate-700 hover:bg-slate-100 rounded-lg transition-colors shrink-0" aria-label="Abrir menú">
                    <span class="material-symbols-outlined text-[22px]">menu</span>
                </button>
                
                <!-- Desktop Sidebar Toggle -->
                <button onclick="toggleDesktopSidebar()" class="hidden md:block p-1.5 text-slate-700 hover:bg-slate-100 rounded-lg transition-colors shrink-0" aria-label="Alternar menú lateral">
                    <span class="material-symbols-outlined text-[22px]" id="desktop-sidebar-icon">menu</span>
                </button>

                <!-- Breadcrumbs de Navegación (Ultra-Responsive para celular) -->
                <x-admin-breadcrumb />
            </div>

            <!-- Right: Live Clock, Notifications & User Profile (Siempre visible en el extremo derecho) -->
            <div class="flex items-center gap-2 sm:gap-3 text-slate-800 shrink-0 ml-auto">
                
                <!-- Buscador Global en TopBar -->
                <form id="top-search-form" action="{{ route('admin.productos.index') }}" method="GET" class="relative hidden sm:flex items-center w-56 md:w-72 lg:w-80 group">
                    <span class="material-symbols-outlined absolute left-3 text-slate-400 text-[18px] pointer-events-none">search</span>
                    <input type="text" 
                           name="buscar" 
                           placeholder="Buscar productos, SKU, marca..." 
                           class="w-full pl-9 pr-10 py-1.5 text-xs bg-slate-100/90 dark:bg-gray-800 border border-slate-200/80 dark:border-gray-700/80 rounded-xl focus:bg-white dark:focus:bg-gray-800 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-slate-800 dark:text-slate-100 placeholder-slate-400 transition-all outline-none">
                    
                    <!-- Botón Escáner Funcional -->
                    <button type="button" 
                            onclick="window.ModalEscaner.abrir()"
                            title="Escanear Código de Barras"
                            class="absolute right-2 text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 p-1 rounded hover:bg-slate-200 dark:hover:bg-gray-700 transition-colors flex items-center justify-center z-10">
                        <span class="material-symbols-outlined text-[16px]">barcode_scanner</span>
                    </button>
                </form>

                <!-- Notifications Livewire Component -->
                <livewire:admin.notificaciones-bell />

                <div class="h-4 w-px bg-slate-200 shrink-0"></div>

                <!-- User Profile Badge + Dropdown -->
                <div class="relative shrink-0" x-data="{ open: false }" @click.outside="open = false">

                    {{-- Trigger: Avatar + Nombre --}}
                    <button @click="open = !open"
                            class="flex items-center gap-2 sm:gap-2.5 rounded-xl px-2 py-1.5 hover:bg-slate-100 dark:hover:bg-gray-800 transition-colors cursor-pointer select-none"
                            :aria-expanded="open">

                        @if(Auth::user() && Auth::user()->foto_perfil_ruta)
                            <img src="{{ asset(Auth::user()->foto_perfil_ruta) }}"
                                 alt="Foto de perfil"
                                 class="w-8 h-8 rounded-full object-cover shadow-xs ring-2 ring-slate-100 shrink-0">
                        @else
                            <div class="w-8 h-8 rounded-full bg-[#09111e] text-white font-bold flex items-center justify-center text-xs shadow-xs ring-2 ring-slate-100 shrink-0">
                                {{ strtoupper(substr(Auth::user()->nombre ?? 'A', 0, 1)) }}
                            </div>
                        @endif

                        <div class="hidden sm:flex flex-col text-left">
                            <span class="text-xs font-bold text-slate-900 dark:text-slate-100 leading-tight truncate max-w-[120px] md:max-w-none">
                                {{ Auth::user()->nombre_completo ?? Auth::user()->nombre ?? 'Administrador' }}
                            </span>
                            <span class="text-[10px] font-semibold text-emerald-700 dark:text-emerald-400 leading-tight">
                                {{ (Auth::user() && Auth::user()->hasRole('super_admin')) ? 'Super Administrador' : 'Administrador' }}
                            </span>
                        </div>

                        <span class="material-symbols-outlined text-[16px] text-slate-400 hidden sm:inline transition-transform duration-200"
                              :class="open ? 'rotate-180' : ''">expand_more</span>
                    </button>

                    {{-- Dropdown Panel --}}
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                         style="display:none;"
                         class="absolute right-0 top-full mt-2 w-48 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-slate-100 dark:border-gray-700 py-1.5 z-50 origin-top-right">

                        {{-- Perfil --}}
                        <a href="{{ route('admin.perfil') }}"
                           class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-gray-700/50 hover:text-emerald-700 dark:hover:text-emerald-400 transition-colors">
                            <span class="material-symbols-outlined text-[18px] text-slate-400">manage_accounts</span>
                            Perfil
                        </a>

                        <div class="my-1 h-px bg-slate-100 dark:bg-gray-700 mx-3"></div>

                        {{-- Cerrar sesión --}}
                        <form method="POST" action="{{ route('logout') }}" id="admin-logout-form">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-gray-700/50 transition-colors">
                                <span class="material-symbols-outlined text-[18px] text-rose-400">logout</span>
                                Cerrar sesión
                            </button>
                        </form>

                    </div>
                </div>
            </div>
        </header>

        <!-- Main Body / Canvas -->
        <main class="flex-1 px-3.5 sm:px-8 py-4 sm:py-5 max-w-[1500px] w-full min-w-0 mx-auto relative">
            @php
                $isFromLogin = session('is_from_login', false) || str_contains(request()->headers->get('referer', ''), '/login') || str_contains(request()->headers->get('referer', ''), '/2fa');
            @endphp
            
            <!-- Contenido Real -->
            <div id="actual-page-content" class="w-full h-full transition-all duration-300 {{ $isFromLogin ? 'opacity-0' : 'animate-fade-in-up' }}">
                @yield('content')
            </div>
        </main>

        <!-- Admin Footer -->
        <footer class="px-4 sm:px-8 py-3.5 border-t border-slate-200/70 bg-white text-xs text-slate-500 flex items-center justify-center text-center w-full">
            <div>
                © {{ date('Y') }} <span class="font-semibold text-slate-700">PayMe Panamá</span> — Sistema de Comercio Electrónico PyME.
            </div>
        </footer>
    </div>

    <!-- Scripts Globales del Layout Admin -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const backdrop = document.getElementById('mobile-sidebar-backdrop');
            sidebar.classList.toggle('-translate-x-full');
            backdrop.classList.toggle('hidden');
        }

        function toggleDesktopSidebar() {
            const html = document.documentElement;
            html.classList.toggle('sidebar-collapsed');
            const isCollapsed = html.classList.contains('sidebar-collapsed');
            localStorage.setItem('sidebarExpanded', !isCollapsed);
            document.getElementById('desktop-sidebar-icon').textContent = isCollapsed ? 'menu' : 'menu_open';
        }

        // Initialize desktop icon
        document.addEventListener('DOMContentLoaded', () => {
            if (document.documentElement.classList.contains('sidebar-collapsed')) {
                const icon = document.getElementById('desktop-sidebar-icon');
                if (icon) icon.textContent = 'menu';
            } else {
                const icon = document.getElementById('desktop-sidebar-icon');
                if (icon) icon.textContent = 'menu_open';
            }
        });


        // Mantener la posición del scroll del sidebar entre recargas de página
        document.addEventListener("DOMContentLoaded", function() {
            const sidebarNav = document.querySelector('#admin-sidebar nav');
            if (sidebarNav) {
                const savedScroll = sessionStorage.getItem('adminSidebarScroll');
                if (savedScroll !== null) {
                    sidebarNav.scrollTop = parseInt(savedScroll, 10);
                }
                window.addEventListener('beforeunload', () => {
                    sessionStorage.setItem('adminSidebarScroll', sidebarNav.scrollTop);
                });
            }
        });

        // Reloj en vivo de Panamá (GMT-5, formato 12 horas en español)
        function updatePanamaClock() {
            const clockEl = document.getElementById('topbar-live-clock');
            if (!clockEl) return;

            try {
                const now = new Date();
                const formatter = new Intl.DateTimeFormat('es-PA', {
                    timeZone: 'America/Panama',
                    weekday: 'short',
                    day: 'numeric',
                    month: 'short',
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });

                const formatted = formatter.format(now);
                // Asegurar formato legible y capitalización correcta
                const capitalized = formatted.charAt(0).toUpperCase() + formatted.slice(1).replace(/\./g, '');
                clockEl.textContent = capitalized;
            } catch (e) {
                // Fallback silencioso
            }
        }

        setInterval(updatePanamaClock, 1000);
        updatePanamaClock();

        // --------------------------------------------------------
        // Interceptor Global de Navegación (Transición Suave)
        // --------------------------------------------------------
        
        function cleanupTransition() {
            document.body.classList.remove('navigating');
            const actualContent = document.getElementById('actual-page-content');
            if (actualContent) {
                actualContent.classList.remove('page-transitioning');
                // Remover la clase de animación 100% para destruir cualquier Containing Block de CSS
                setTimeout(() => {
                    actualContent.classList.remove('animate-fade-in-up');
                }, 400);
            }
        }

        function handleLoginSkeleton() {
            const skeleton = document.getElementById('global-admin-skeleton-wrapper');
            const actualContent = document.getElementById('actual-page-content');
            
            if (skeleton && !skeleton.classList.contains('hidden')) {
                // Simular un tiempo de carga post-login para mostrar el efecto premium
                setTimeout(() => {
                    if (actualContent) {
                        actualContent.classList.remove('opacity-0');
                        actualContent.classList.add('opacity-100');
                        actualContent.classList.add('animate-fade-in-up');
                    }
                    skeleton.style.opacity = '0';
                    
                    setTimeout(() => {
                        skeleton.classList.add('hidden');
                        if (actualContent) {
                            setTimeout(() => actualContent.classList.remove('animate-fade-in-up'), 400);
                        }
                    }, 300); // 300ms debe coincidir con transition-opacity
                }, 800);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            cleanupTransition();

            const sidebarLinks = document.querySelectorAll('#admin-sidebar nav a, a.nav-transition');
            
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    
                    // Ignorar anclas, js, links en blanco o la misma página
                    if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
                    if (this.getAttribute('target') === '_blank') return;
                    if (href === window.location.href || href === window.location.pathname) return;

                    document.body.classList.add('navigating');
                    const actualContent = document.getElementById('actual-page-content');
                    if (actualContent) {
                        actualContent.classList.add('page-transitioning');
                    }
                });
            });

            handleLoginSkeleton();
        });

        // BFCache (Back/Forward Cache) Fix
        window.addEventListener('pageshow', cleanupTransition);
        
        // Livewire Navigation Fix (Borra estados de navegación tras SPA swap y restaura UI)
        document.addEventListener('livewire:navigated', () => {
            cleanupTransition();
            handleLoginSkeleton();
            
            // 1. Restaurar el estado del modo oscuro
            if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            if (typeof updateThemeToggleUI === 'function') {
                updateThemeToggleUI();
            }

            // 2. Restaurar el estado del sidebar
            if (localStorage.getItem('sidebarExpanded') === 'false') {
                document.documentElement.classList.add('sidebar-collapsed');
                const icon = document.getElementById('desktop-sidebar-icon');
                if (icon) icon.textContent = 'menu';
            } else {
                document.documentElement.classList.remove('sidebar-collapsed');
                const icon = document.getElementById('desktop-sidebar-icon');
                if (icon) icon.textContent = 'menu_open';
            }
            
            // 3. Re-bind theme toggle si Livewire reemplaza el DOM
            const themeToggleBtn = document.getElementById('theme-toggle');
            if (themeToggleBtn) {
                themeToggleBtn.removeEventListener('click', window.toggleThemeHandler);
                themeToggleBtn.addEventListener('click', window.toggleThemeHandler);
            }
        });

        // Theme Toggle Logic
        function updateThemeToggleUI() {
            const thumb = document.getElementById('theme-toggle-thumb');
            const darkIcon = document.getElementById('theme-toggle-dark-icon');
            const lightIcon = document.getElementById('theme-toggle-light-icon');
            
            if(!thumb || !darkIcon || !lightIcon) return;

            if (document.documentElement.classList.contains('dark')) {
                thumb.classList.remove('translate-x-1');
                thumb.classList.add('translate-x-9');
                thumb.classList.remove('bg-white');
                thumb.classList.add('bg-slate-800');
                darkIcon.classList.remove('opacity-0');
                darkIcon.classList.add('text-white');
                lightIcon.classList.add('opacity-0');
            } else {
                thumb.classList.remove('translate-x-9');
                thumb.classList.add('translate-x-1');
                thumb.classList.remove('bg-slate-800');
                thumb.classList.add('bg-white');
                darkIcon.classList.add('opacity-0');
                darkIcon.classList.remove('text-white');
                lightIcon.classList.remove('opacity-0');
            }
        }

        window.toggleThemeHandler = function() {
            // Habilitamos las transiciones solo al hacer clic manual para que haya animación
            document.getElementById('theme-toggle').classList.add('transition-colors', 'duration-300');
            document.getElementById('theme-toggle-thumb').classList.add('transition-transform', 'duration-300');
            document.getElementById('theme-toggle-dark-icon').classList.add('transition-opacity', 'duration-300');
            document.getElementById('theme-toggle-light-icon').classList.add('transition-opacity', 'duration-300');

            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('color-theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('color-theme', 'dark');
            }
            updateThemeToggleUI();
            window.dispatchEvent(new Event('theme-changed'));
        };

        document.addEventListener('DOMContentLoaded', () => {
            updateThemeToggleUI(true);
            const themeToggleBtn = document.getElementById('theme-toggle');
            if (themeToggleBtn) {
                themeToggleBtn.addEventListener('click', window.toggleThemeHandler);
            }
        });

    </script>

    <!-- Modal Escáner -->
    <x-modal-escaner inputId="buscar" formId="top-search-form" />

    <!-- Sistema Global de Alertas y Notificaciones Toast -->
    <x-toast-alert />

    <!-- Sistema Global de Confirmación Defensiva para Eliminación -->
    <x-modal-eliminar />

    <!-- Sistema de Tooltips Profesional para el Sidebar -->
    <div id="sidebar-tooltip-container" class="fixed z-[100] pointer-events-none opacity-0 transition-all duration-200 ease-out bg-slate-800 text-white text-xs font-semibold pl-3 pr-2.5 py-1.5 rounded-lg shadow-xl whitespace-nowrap border border-slate-700/80" style="transform: scale(0.95);">
        <!-- Flechita (renderizada detrás del texto) -->
        <div class="absolute w-2.5 h-2.5 bg-slate-800 border-l border-b border-slate-700/80 -left-[5px] top-1/2 rounded-sm z-0" style="transform: translateY(-50%) rotate(45deg);"></div>
        <span id="sidebar-tooltip-text" class="relative z-10 block"></span>
    </div>

    <script>
        document.addEventListener('mouseover', (e) => {
            if (!document.documentElement.classList.contains('sidebar-collapsed') || window.innerWidth < 768) return;
            
            const target = e.target.closest('#admin-sidebar a, #admin-sidebar button');
            if (target) {
                const tooltip = document.getElementById('sidebar-tooltip-container');
                const tooltipText = document.getElementById('sidebar-tooltip-text');
                
                let text = '';
                const textSpan = target.querySelector('.sidebar-text');
                if (textSpan) {
                    text = textSpan.textContent.trim();
                } else if (target.id === 'theme-toggle') {
                    text = document.documentElement.classList.contains('dark') ? 'Modo Claro' : 'Modo Oscuro';
                }
                
                if (!text) return;
                
                tooltipText.textContent = text;
                
                const rect = target.getBoundingClientRect();
                tooltip.style.left = (rect.right + 14) + 'px';
                tooltip.style.top = (rect.top + (rect.height / 2) - (tooltip.offsetHeight / 2)) + 'px';
                
                tooltip.classList.remove('opacity-0');
                tooltip.classList.add('opacity-100');
                tooltip.style.transform = 'scale(1)';
            }
        });

        document.addEventListener('mouseout', (e) => {
            const target = e.target.closest('#admin-sidebar a, #admin-sidebar button');
            if (target) {
                const tooltip = document.getElementById('sidebar-tooltip-container');
                tooltip.classList.add('opacity-0');
                tooltip.classList.remove('opacity-100');
                tooltip.style.transform = 'scale(0.95)';
            }
        });
        
        const sidebarNav = document.querySelector('#admin-sidebar nav');
        if (sidebarNav) {
            sidebarNav.addEventListener('scroll', () => {
                const tooltip = document.getElementById('sidebar-tooltip-container');
                if (!tooltip.classList.contains('opacity-0')) {
                    tooltip.classList.add('opacity-0');
                    tooltip.classList.remove('opacity-100');
                    tooltip.style.transform = 'scale(0.95)';
                }
            }, { passive: true });
        }
    </script>

    @stack('scripts')
</body>
</html>