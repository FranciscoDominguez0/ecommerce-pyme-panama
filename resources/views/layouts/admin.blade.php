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

    <!-- Tailwind CSS CDN con plugins -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,container-queries"></script>
    
    <!-- Fuente Oficial de Laravel: Figtree & Material Symbols -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=block" rel="stylesheet"/>

    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        brand: {
                            sidebar: "#0c1b2f",
                            surface: "#f8fafc",
                            card: "#ffffff",
                            border: "#e2e8f0",
                            title: "#0f172a",
                            muted: "#64748b",
                            emerald: "#006c47",
                            emeraldLight: "#ecfdf5",
                            gold: "#d97706",
                        }
                    },
                    fontFamily: {
                        sans: ["Figtree", "sans-serif"],
                    }
                }
            }
        }
    </script>

    <style>
        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }
        body { 
            font-family: 'Figtree', sans-serif; 
            letter-spacing: -0.011em;
            background-color: #f8fafc;
            color: #0f172a;
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
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .card-elevated {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 1px 2px -1px rgba(0, 0, 0, 0.03);
        }
    </style>

    @stack('styles')
</head>
<body class="bg-[#f8fafc] text-[#0f172a] min-h-screen flex flex-col md:flex-row text-sm antialiased selection:bg-emerald-100 selection:text-emerald-900 w-full max-w-full overflow-x-hidden relative">

    <!-- Mobile Sidebar Drawer (Overlay) -->
    <div id="mobile-sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-[#070d18]/80 z-40 hidden md:hidden transition-opacity backdrop-blur-sm"></div>

    <!-- Sidebar Admin Ejecutivo (Deep Slate Navy & Emerald Accents) -->
    <aside id="admin-sidebar" class="fixed left-0 top-0 h-full w-64 bg-[#09111e] text-slate-200 z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out border-r border-slate-800/80 shadow-2xl flex flex-col justify-between select-none">
        
        <!-- Header & Navigation -->
        <div class="flex flex-col flex-1 min-h-0">
            
            <!-- Brand Logo Header -->
            <div class="px-5 py-4 border-b border-slate-800/80 flex items-center justify-between bg-gradient-to-b from-white/[0.03] to-transparent">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                    <div class="p-1.5 rounded-xl bg-gradient-to-br from-emerald-500/20 to-blue-500/10 border border-emerald-500/30 group-hover:border-emerald-400/60 transition-all shadow-inner">
                        <x-application-logo size="default" class="group-hover:scale-105 transition-transform" />
                    </div>
                    <div>
                        <h1 class="text-sm font-extrabold text-white tracking-tight leading-tight">
                            PayMe <span class="text-emerald-400 font-bold">Panamá</span>
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
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 px-3">
                        General
                    </div>
                    <div class="space-y-0.5">
                        <!-- Dashboard -->
                        <a href="{{ route('admin.dashboard') }}" 
                           class="group relative flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-r from-emerald-500/15 via-emerald-500/10 to-transparent text-white border-l-[3px] border-emerald-400 shadow-xs' : 'text-slate-300 hover:bg-white/[0.05] hover:text-white' }}">
                            <span class="material-symbols-outlined text-[18px] transition-colors {{ request()->routeIs('admin.dashboard') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-400' }}" style="{{ request()->routeIs('admin.dashboard') ? 'font-variation-settings: \'FILL\' 1;' : '' }}">dashboard</span>
                            <span class="truncate">Dashboard</span>
                        </a>

                        <!-- Pedidos & Ventas -->
                        <a href="{{ url('/admin/pedidos') }}" 
                           class="group relative flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->is('admin/pedidos*') ? 'bg-gradient-to-r from-emerald-500/15 via-emerald-500/10 to-transparent text-white border-l-[3px] border-emerald-400 shadow-xs' : 'text-slate-300 hover:bg-white/[0.05] hover:text-white' }}">
                            <span class="material-symbols-outlined text-[18px] transition-colors {{ request()->is('admin/pedidos*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-400' }}">shopping_bag</span>
                            <span class="truncate">Pedidos & Ventas</span>
                        </a>
                    </div>
                </div>

                <!-- Grupo 2: Catálogo & Stock -->
                <div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 px-3">
                        Catálogo & Stock
                    </div>
                    <div class="space-y-0.5">
                        <!-- Productos -->
                        <a href="{{ route('admin.productos.index') }}" 
                           class="group relative flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.productos*') ? 'bg-gradient-to-r from-emerald-500/15 via-emerald-500/10 to-transparent text-white border-l-[3px] border-emerald-400 shadow-xs' : 'text-slate-300 hover:bg-white/[0.05] hover:text-white' }}">
                            <span class="material-symbols-outlined text-[18px] transition-colors {{ request()->routeIs('admin.productos*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-400' }}">inventory_2</span>
                            <span class="truncate">Productos</span>
                        </a>

                        <!-- Categorías -->
                        <a href="{{ route('admin.categorias.index') }}" 
                           class="group relative flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.categorias*') ? 'bg-gradient-to-r from-emerald-500/15 via-emerald-500/10 to-transparent text-white border-l-[3px] border-emerald-400 shadow-xs' : 'text-slate-300 hover:bg-white/[0.05] hover:text-white' }}">
                            <span class="material-symbols-outlined text-[18px] transition-colors {{ request()->routeIs('admin.categorias*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-400' }}">category</span>
                            <span class="truncate">Categorías</span>
                        </a>

                        <!-- Inventario -->
                        <a href="{{ url('/admin/inventario') }}" 
                           class="group relative flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->is('admin/inventario*') ? 'bg-gradient-to-r from-emerald-500/15 via-emerald-500/10 to-transparent text-white border-l-[3px] border-emerald-400 shadow-xs' : 'text-slate-300 hover:bg-white/[0.05] hover:text-white' }}">
                            <span class="material-symbols-outlined text-[18px] transition-colors {{ request()->is('admin/inventario*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-400' }}">warehouse</span>
                            <span class="truncate">Inventario</span>
                        </a>
                    </div>
                </div>

                <!-- Grupo 3: Clientes & Facturación -->
                <div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 px-3">
                        Clientes & Finanzas
                    </div>
                    <div class="space-y-0.5">
                        <!-- Clientes / Usuarios -->
                        <a href="{{ url('/admin/usuarios') }}" 
                           class="group relative flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->is('admin/usuarios*') ? 'bg-gradient-to-r from-emerald-500/15 via-emerald-500/10 to-transparent text-white border-l-[3px] border-emerald-400 shadow-xs' : 'text-slate-300 hover:bg-white/[0.05] hover:text-white' }}">
                            <span class="material-symbols-outlined text-[18px] transition-colors {{ request()->is('admin/usuarios*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-400' }}">group</span>
                            <span class="truncate">Clientes / Usuarios</span>
                        </a>

                        <!-- Facturación Fiscal -->
                        <a href="{{ url('/admin/facturas') }}" 
                           class="group relative flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->is('admin/facturas*') ? 'bg-gradient-to-r from-emerald-500/15 via-emerald-500/10 to-transparent text-white border-l-[3px] border-emerald-400 shadow-xs' : 'text-slate-300 hover:bg-white/[0.05] hover:text-white' }}">
                            <span class="material-symbols-outlined text-[18px] transition-colors {{ request()->is('admin/facturas*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-400' }}">receipt_long</span>
                            <span class="truncate">Facturación Fiscal</span>
                        </a>

                        <!-- Cupones de Descuento -->
                        <a href="{{ url('/admin/cupones') }}" 
                           class="group relative flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->is('admin/cupones*') ? 'bg-gradient-to-r from-emerald-500/15 via-emerald-500/10 to-transparent text-white border-l-[3px] border-emerald-400 shadow-xs' : 'text-slate-300 hover:bg-white/[0.05] hover:text-white' }}">
                            <span class="material-symbols-outlined text-[18px] transition-colors {{ request()->is('admin/cupones*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-400' }}">local_activity</span>
                            <span class="truncate">Cupones</span>
                        </a>

                        <!-- Reportes -->
                        <a href="{{ url('/admin/reportes') }}" 
                           class="group relative flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->is('admin/reportes*') ? 'bg-gradient-to-r from-emerald-500/15 via-emerald-500/10 to-transparent text-white border-l-[3px] border-emerald-400 shadow-xs' : 'text-slate-300 hover:bg-white/[0.05] hover:text-white' }}">
                            <span class="material-symbols-outlined text-[18px] transition-colors {{ request()->is('admin/reportes*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-400' }}">bar_chart</span>
                            <span class="truncate">Reportes</span>
                        </a>
                    </div>
                </div>

                <!-- Grupo 4: Sistema & Auditoría -->
                <div>
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 px-3">
                        Sistema & Seguridad
                    </div>
                    <div class="space-y-0.5">
                        <!-- Auditoría -->
                        <a href="{{ url('/admin/auditoria') }}" 
                           class="group relative flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->is('admin/auditoria*') ? 'bg-gradient-to-r from-emerald-500/15 via-emerald-500/10 to-transparent text-white border-l-[3px] border-emerald-400 shadow-xs' : 'text-slate-300 hover:bg-white/[0.05] hover:text-white' }}">
                            <span class="material-symbols-outlined text-[18px] transition-colors {{ request()->is('admin/auditoria*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-400' }}">security</span>
                            <span class="truncate">Auditoría</span>
                        </a>

                        <!-- Configuración -->
                        <a href="{{ url('/admin/configuracion') }}" 
                           class="group relative flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->is('admin/configuracion*') ? 'bg-gradient-to-r from-emerald-500/15 via-emerald-500/10 to-transparent text-white border-l-[3px] border-emerald-400 shadow-xs' : 'text-slate-300 hover:bg-white/[0.05] hover:text-white' }}">
                            <span class="material-symbols-outlined text-[18px] transition-colors {{ request()->is('admin/configuracion*') ? 'text-emerald-400' : 'text-slate-400 group-hover:text-emerald-400' }}">settings</span>
                            <span class="truncate">Configuración</span>
                        </a>
                    </div>
                </div>

            </nav>
        </div>

        <!-- Sidebar Footer / Actions -->
        <div class="p-3 border-t border-slate-800/80 bg-gradient-to-t from-black/20 to-transparent space-y-1.5">
            
            <!-- Quick Link: Ver Tienda Pública -->
            <a href="{{ url('/') }}" target="_blank" 
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-300 bg-white/[0.03] hover:bg-white/[0.08] hover:text-white border border-slate-800 hover:border-slate-700 transition-all group">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[17px] text-emerald-400 group-hover:scale-110 transition-transform">storefront</span>
                    <span>Ver Tienda Online</span>
                </span>
                <span class="material-symbols-outlined text-[15px] text-slate-400 group-hover:text-white">open_in_new</span>
            </a>

            <!-- Logout Button -->
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit" 
                        class="w-full flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl text-xs font-semibold text-rose-300/90 hover:text-rose-200 hover:bg-rose-500/10 border border-transparent hover:border-rose-500/20 transition-all group">
                    <span class="material-symbols-outlined text-[17px] text-rose-400 group-hover:-translate-x-0.5 transition-transform">logout</span>
                    <span>Cerrar Sesión</span>
                </button>
            </form>

        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 md:ml-64 flex flex-col min-h-screen min-w-0 w-full max-w-full overflow-x-hidden">
        
        <!-- TopNavBar Ejecutivo (Fijo al ancho de pantalla del móvil) -->
        <header class="sticky top-0 z-30 w-full max-w-full px-3.5 sm:px-8 py-3 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-xs flex items-center justify-between gap-2 sm:gap-4 shrink-0">
            
            <!-- Left: Toggle & Responsive Breadcrumbs -->
            <div class="flex items-center gap-2 sm:gap-3 min-w-0 overflow-hidden">
                <!-- Hamburger Button (Mobile) -->
                <button onclick="toggleSidebar()" class="md:hidden p-1.5 text-slate-700 hover:bg-slate-100 rounded-lg transition-colors shrink-0" aria-label="Abrir menú">
                    <span class="material-symbols-outlined text-[22px]">menu</span>
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
                
                <!-- Reloj / Fecha en Tiempo Real (Panamá GMT-5 / 12 Horas) -->
                <div class="hidden lg:flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-slate-700 text-xs font-semibold select-none shadow-2xs" title="Hora Oficial de Panamá (GMT-5)">
                    <span class="material-symbols-outlined text-[16px] text-emerald-600">schedule</span>
                    <span id="topbar-live-clock">{{ \Carbon\Carbon::now('America/Panama')->locale('es')->isoFormat('ddd, D MMM · hh:mm A') }}</span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">PA</span>
                </div>

                <!-- Notifications -->
                <button class="relative p-1.5 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors shrink-0" title="Notificaciones">
                    <span class="material-symbols-outlined text-[18px]">notifications</span>
                    <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                </button>

                <div class="h-4 w-px bg-slate-200 shrink-0"></div>

                <!-- User Profile Badge -->
                <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">
                    <div class="w-8 h-8 rounded-full bg-[#09111e] text-white font-bold flex items-center justify-center text-xs shadow-xs ring-2 ring-slate-100 shrink-0">
                        {{ strtoupper(substr(Auth::user()->nombre ?? 'A', 0, 1)) }}
                    </div>
                    <div class="hidden sm:flex flex-col text-left">
                        <span class="text-xs font-bold text-slate-900 leading-tight truncate max-w-[120px] md:max-w-none">{{ Auth::user()->nombre_completo ?? Auth::user()->nombre ?? 'Administrador' }}</span>
                        <span class="text-[10px] font-semibold text-emerald-700 leading-tight">
                            {{ (Auth::user() && Auth::user()->hasRole('super_admin')) ? 'Super Administrador' : 'Administrador' }}
                        </span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Body / Canvas -->
        <main class="flex-1 px-3.5 sm:px-8 py-6 max-w-[1500px] w-full min-w-0 mx-auto">
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

    @stack('scripts')
</body>
</html>
