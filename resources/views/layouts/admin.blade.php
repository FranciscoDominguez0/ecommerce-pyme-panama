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
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94A3B8; }
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
        html.sidebar-collapsed #admin-sidebar a { justify-content: center; padding-left: 0; padding-right: 0; }
        html.sidebar-collapsed #admin-sidebar .sidebar-header { justify-content: center; padding-left: 0; padding-right: 0; }
        html.sidebar-collapsed #admin-sidebar .brand-logo-container { margin: 0 auto; }
    </style>

    @stack('styles')
</head>
<body class="bg-[#F8FAFC] text-slate-900 min-h-screen flex flex-col md:flex-row text-sm antialiased selection:bg-emerald-100 selection:text-emerald-900 w-full max-w-full overflow-x-clip relative">

    <!-- Mobile Sidebar Drawer (Overlay) -->
    <div id="mobile-sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/80 z-40 hidden md:hidden transition-opacity backdrop-blur-sm"></div>

    <!-- Sidebar Admin (Fondo #1F2937, Bordes #E5E7EB/20) -->
    <aside id="admin-sidebar" class="w-64 fixed left-0 top-0 h-full bg-[#1F2937] text-slate-200 z-50 transform -translate-x-full md:translate-x-0 transition-all duration-300 ease-in-out border-r border-gray-700/60 shadow-2xl flex flex-col justify-between select-none">
        
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
            <nav class="flex-1 px-3 py-3 overflow-y-auto space-y-4">
                
                <!-- Grupo 1: General -->
                <div>
                    <div class="sidebar-group-title text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 px-3 transition-all duration-300">
                        General
                    </div>
                    <div class="space-y-0.5">
                        <!-- Dashboard -->
                        <a href="{{ route('admin.dashboard') }}" 
                           class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-[#2B3648] text-[#34D399] shadow-2xs' : 'text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                            <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->routeIs('admin.dashboard') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}" style="{{ request()->routeIs('admin.dashboard') ? 'font-variation-settings: \'FILL\' 1;' : '' }}">dashboard</span>
                            <span class="sidebar-text truncate transition-all duration-300">Dashboard</span>
                        </a>

                        <!-- Pedidos & Ventas -->
                        <a href="{{ url('/admin/pedidos') }}" 
                           class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->is('admin/pedidos*') ? 'bg-[#2B3648] text-[#34D399] shadow-2xs' : 'text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                            <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->is('admin/pedidos*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">shopping_bag</span>
                            <span class="sidebar-text truncate transition-all duration-300">Pedidos & Ventas</span>
                        </a>

                        <!-- Devoluciones -->
                        <a href="{{ route('admin.devoluciones.index') }}" 
                           class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('admin.devoluciones*') ? 'bg-[#2B3648] text-[#34D399] shadow-2xs' : 'text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                            <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->routeIs('admin.devoluciones*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">assignment_return</span>
                            <span class="sidebar-text truncate transition-all duration-300">Devoluciones</span>
                        </a>
                    </div>
                </div>

                <!-- Grupo 2: Catálogo & Stock -->
                <div>
                    <div class="sidebar-group-title text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 px-3 transition-all duration-300">
                        Catálogo & Stock
                    </div>
                    <div class="space-y-0.5">
                        <!-- Productos -->
                        <a href="{{ route('admin.productos.index') }}" 
                           class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('admin.productos*') ? 'bg-[#2B3648] text-[#34D399] shadow-2xs' : 'text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                            <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->routeIs('admin.productos*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">sell</span>
                            <span class="sidebar-text truncate transition-all duration-300">Productos</span>
                        </a>

                        <!-- Categorías -->
                        <a href="{{ route('admin.categorias.index') }}" 
                           class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('admin.categorias*') ? 'bg-[#2B3648] text-[#34D399] shadow-2xs' : 'text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                            <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->routeIs('admin.categorias*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">category</span>
                            <span class="sidebar-text truncate transition-all duration-300">Categorías</span>
                        </a>

                        <!-- Marcas -->
                        <a href="{{ route('admin.brands.index') }}" 
                           class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('admin.brands*') ? 'bg-[#2B3648] text-[#34D399] shadow-2xs' : 'text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                            <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->routeIs('admin.brands*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">verified</span>
                            <span class="sidebar-text truncate transition-all duration-300">Marcas & Logos</span>
                        </a>

                        <!-- Inventario -->
                        <a href="{{ url('/admin/inventario') }}" 
                           class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->is('admin/inventario*') ? 'bg-[#2B3648] text-[#34D399] shadow-2xs' : 'text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                            <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->is('admin/inventario*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">warehouse</span>
                            <span class="sidebar-text truncate transition-all duration-300">Inventario</span>
                        </a>

                        <!-- Zonas de Envío -->
                        <a href="{{ route('admin.zonas-envio.index') }}" 
                           class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('admin.zonas-envio*') ? 'bg-[#2B3648] text-[#34D399] shadow-2xs' : 'text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                            <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->routeIs('admin.zonas-envio*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">local_shipping</span>
                            <span class="sidebar-text truncate transition-all duration-300">Zonas de Envío</span>
                        </a>

                        <!-- Cupones de Descuento -->
                        <a href="{{ route('admin.promociones.cupones') }}" 
                           class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('admin.promociones.cupones*') ? 'bg-[#2B3648] text-[#34D399] shadow-2xs' : 'text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                            <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->routeIs('admin.promociones.cupones*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">local_offer</span>
                            <span class="sidebar-text truncate transition-all duration-300">Cupones de Descuento</span>
                        </a>

                        <!-- Promociones Especiales -->
                        <a href="{{ route('admin.promociones.envio-gratis') }}" 
                           class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('admin.promociones.envio-gratis*') || request()->routeIs('admin.promociones.producto-del-mes*') ? 'bg-[#2B3648] text-[#34D399] shadow-2xs' : 'text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                            <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->routeIs('admin.promociones.envio-gratis*') || request()->routeIs('admin.promociones.producto-del-mes*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">campaign</span>
                            <span class="sidebar-text truncate transition-all duration-300">Promociones Especiales</span>
                        </a>
                    </div>
                </div>

                <!-- Grupo 3: Clientes & Facturación -->
                <div>
                    <div class="sidebar-group-title text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 px-3 transition-all duration-300">
                        Clientes & Finanzas
                    </div>
                    <div class="space-y-0.5">
                        @can('admin.usuarios.gestionar')
                        <!-- Usuarios y Roles -->
                        <a href="{{ route('admin.usuarios.index') }}" 
                           class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->is('admin/usuarios*') ? 'bg-[#2B3648] text-[#34D399] shadow-2xs' : 'text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                            <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->is('admin/usuarios*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">admin_panel_settings</span>
                            <span class="sidebar-text truncate transition-all duration-300">Usuarios y Roles</span>
                        </a>
                        @endcan

                        <!-- Facturación Fiscal -->
                        <a href="{{ url('/admin/facturas') }}" 
                           class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->is('admin/facturas*') ? 'bg-[#2B3648] text-[#34D399] shadow-2xs' : 'text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                            <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->is('admin/facturas*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">receipt_long</span>
                            <span class="sidebar-text truncate transition-all duration-300">Facturación Fiscal</span>
                        </a>

                        <!-- Reportes -->
                        <a href="{{ url('/admin/reportes') }}" 
                           class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->is('admin/reportes*') ? 'bg-[#2B3648] text-[#34D399] shadow-2xs' : 'text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                            <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->is('admin/reportes*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">bar_chart</span>
                            <span class="sidebar-text truncate transition-all duration-300">Reportes</span>
                        </a>
                    </div>
                </div>

                <!-- Grupo 4: Sistema & Auditoría -->
                <div>
                    <div class="sidebar-group-title text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 px-3 transition-all duration-300">
                        Sistema & Seguridad
                    </div>
                    <div class="space-y-0.5">
                        <!-- Auditoría -->
                        <a href="{{ url('/admin/auditoria') }}" 
                           class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->is('admin/auditoria*') ? 'bg-[#2B3648] text-[#34D399] shadow-2xs' : 'text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                            <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->is('admin/auditoria*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">security</span>
                            <span class="sidebar-text truncate transition-all duration-300">Auditoría</span>
                        </a>

                        <!-- Configuración -->
                        <a href="{{ url('/admin/configuracion') }}" 
                           class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->is('admin/configuracion*') ? 'bg-[#2B3648] text-[#34D399] shadow-2xs' : 'text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                            <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->is('admin/configuracion*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">settings</span>
                            <span class="sidebar-text truncate transition-all duration-300">Configuración</span>
                        </a>
                    </div>
                </div>

            </nav>
        </div>

        <!-- Sidebar Footer / Actions -->
        <div class="p-3 border-t border-gray-700/60 bg-black/20 space-y-1.5">

            <!-- Logout Button -->
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit" 
                        class="w-full flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl text-xs font-semibold text-rose-300/90 hover:text-rose-200 hover:bg-rose-500/10 border border-transparent hover:border-rose-500/20 transition-all group">
                    <span class="material-symbols-outlined text-[17px] text-rose-400 group-hover:-translate-x-0.5 transition-transform">logout</span>
                    <span class="sidebar-text transition-all duration-300">Cerrar Sesión</span>
                </button>
            </form>

        </div>
    </aside>

    <!-- Main Content Area -->
    <div id="main-content" class="md:ml-64 flex-1 flex flex-col min-h-screen min-w-0 w-full max-w-full transition-all duration-300 ease-in-out">
        
        <!-- TopNavBar Ejecutivo (Fijo en la parte superior al hacer scroll) -->
        <header class="sticky top-0 z-40 w-full max-w-full px-3.5 sm:px-8 py-3 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-xs flex items-center justify-between gap-2 sm:gap-4 shrink-0">
            
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
                <nav class="flex items-center gap-1 sm:gap-1.5 text-xs text-slate-500 font-medium min-w-0 flex-nowrap overflow-hidden" aria-label="Breadcrumb">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-1 text-slate-500 hover:text-slate-900 transition-colors shrink-0" title="Panel Principal">
                        <span class="material-symbols-outlined text-[17px] text-slate-400">home</span>
                        <span class="hidden sm:inline">Panel</span>
                    </a>
                    
                    @hasSection('breadcrumbs')
                        @yield('breadcrumbs')
                    @else
                        @if(request()->segment(2))
                            <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
                            <span class="capitalize text-slate-600 truncate max-w-[90px] sm:max-w-none">{{ str_replace('-', ' ', request()->segment(2)) }}</span>
                        @endif
                        @if(request()->segment(3) && !is_numeric(request()->segment(3)))
                            <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
                            <span class="capitalize font-bold text-slate-900 truncate max-w-[90px] sm:max-w-none">{{ str_replace('-', ' ', request()->segment(3)) }}</span>
                        @endif
                    @endif
                </nav>
            </div>

            <!-- Right: Live Clock, Notifications & User Profile (Siempre visible en el extremo derecho) -->
            <div class="flex items-center gap-2 sm:gap-3 text-slate-800 shrink-0 ml-auto">
                
                <!-- Buscador Global en TopBar -->
                <form action="{{ route('admin.productos.index') }}" method="GET" class="relative hidden sm:flex items-center w-56 md:w-72 lg:w-80">
                    <span class="material-symbols-outlined absolute left-3 text-slate-400 text-[18px] pointer-events-none">search</span>
                    <input type="text" 
                           name="buscar" 
                           placeholder="Buscar productos, SKU, marca..." 
                           class="w-full pl-9 pr-9 py-1.5 text-xs bg-slate-100/90 border border-slate-200/80 rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-slate-800 placeholder-slate-400 transition-all outline-none">
                    <span class="absolute right-2.5 text-[10px] font-mono font-bold text-slate-400 bg-white px-1.5 py-0.5 rounded border border-slate-200 shadow-2xs pointer-events-none hidden lg:inline">⌘K</span>
                </form>

                <!-- Notifications -->
                <button class="relative p-1.5 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors shrink-0" title="Notificaciones">
                    <span class="material-symbols-outlined text-[18px]">notifications</span>
                    <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                </button>

                <div class="h-4 w-px bg-slate-200 shrink-0"></div>

                <!-- User Profile Badge + Dropdown -->
                <div class="relative shrink-0" x-data="{ open: false }" @click.outside="open = false">

                    {{-- Trigger: Avatar + Nombre --}}
                    <button @click="open = !open"
                            class="flex items-center gap-2 sm:gap-2.5 rounded-xl px-2 py-1.5 hover:bg-slate-100 transition-colors cursor-pointer select-none"
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
                            <span class="text-xs font-bold text-slate-900 leading-tight truncate max-w-[120px] md:max-w-none">
                                {{ Auth::user()->nombre_completo ?? Auth::user()->nombre ?? 'Administrador' }}
                            </span>
                            <span class="text-[10px] font-semibold text-emerald-700 leading-tight">
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
                         class="absolute right-0 top-full mt-2 w-48 bg-white rounded-2xl shadow-xl border border-slate-100 py-1.5 z-50 origin-top-right">

                        {{-- Perfil --}}
                        <a href="{{ route('admin.perfil') }}"
                           class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-emerald-700 transition-colors">
                            <span class="material-symbols-outlined text-[18px] text-slate-400">manage_accounts</span>
                            Perfil
                        </a>

                        <div class="my-1 h-px bg-slate-100 mx-3"></div>

                        {{-- Cerrar sesión --}}
                        <form method="POST" action="{{ route('logout') }}" id="admin-logout-form">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm font-semibold text-rose-600 hover:bg-rose-50 transition-colors">
                                <span class="material-symbols-outlined text-[18px] text-rose-400">logout</span>
                                Cerrar sesión
                            </button>
                        </form>

                    </div>
                </div>
            </div>
        </header>

        <!-- Main Body / Canvas -->
        <main class="flex-1 px-3.5 sm:px-8 py-4 sm:py-5 max-w-[1500px] w-full min-w-0 mx-auto">
            @yield('content')
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
    </script>

    <!-- Sistema Global de Alertas y Notificaciones Toast -->
    <x-toast-alert />

    <!-- Sistema Global de Confirmación Defensiva para Eliminación -->
    <x-modal-eliminar />

    @stack('scripts')
</body>
</html>
  