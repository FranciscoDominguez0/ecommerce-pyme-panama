<!DOCTYPE html>
<html class="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'PayMe Panamá') }} - @yield('title', 'Panel de Administración')</title>

    <!-- Tailwind CSS CDN con plugins -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,container-queries"></script>
    
    <!-- Fuente Oficial de Laravel: Figtree & Material Symbols -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

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
        ::-webkit-scrollbar { width: 5px; height: 5px; }
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
<body class="bg-[#f8fafc] text-[#0f172a] min-h-screen flex text-sm antialiased selection:bg-emerald-100 selection:text-emerald-900">

    <!-- Mobile Sidebar Drawer (Overlay) -->
    <div id="mobile-sidebar-backdrop" onclick="toggleSidebar()" class="fixed inset-0 bg-[#0c1b2f]/70 z-40 hidden md:hidden transition-opacity backdrop-blur-sm"></div>

    <!-- Sidebar Admin Ejecutivo (Midnight Oxford Navy - Tono Azul de Alta Gama) -->
    <aside id="admin-sidebar" class="fixed left-0 top-0 h-full w-64 bg-[#0c1b2f] text-[#eaf1ff] py-5 z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out border-r border-[#172c49] shadow-2xl flex flex-col justify-between">
        
        <!-- Logo & Header -->
        <div>
            <div class="px-5 mb-6 flex items-center justify-between">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                    <x-application-logo size="default" class="group-hover:scale-105 transition-transform" />
                    <div>
                        <h1 class="text-sm font-extrabold text-white tracking-tight leading-tight flex items-center gap-1">
                            PayMe <span class="text-emerald-400 font-bold">Panamá</span>
                        </h1>
                        <span class="text-[10px] font-semibold text-[#8ca4c4] tracking-wider uppercase block">Panel PyME</span>
                    </div>
                </a>
                <button onclick="toggleSidebar()" class="md:hidden text-[#8ca4c4] hover:text-white p-1">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <!-- Navigation Links -->
            <div class="px-3 flex flex-col gap-1 overflow-y-auto max-h-[calc(100vh-210px)]">
                <div class="text-[10px] font-bold text-[#627d9f] uppercase tracking-widest mb-1.5 px-3">Módulos</div>

                <!-- Dashboard -->
                <a href="{{ route('admin.dashboard') }}" 
                   class="group flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-[#183358] text-white shadow-xs border-l-4 border-emerald-400' : 'text-[#93a7c3] hover:bg-[#13263f] hover:text-white' }}">
                    <span class="material-symbols-outlined text-[18px] {{ request()->routeIs('admin.dashboard') ? 'text-emerald-400' : 'text-[#728ba8] group-hover:text-[#93a7c3]' }}" style="{{ request()->routeIs('admin.dashboard') ? 'font-variation-settings: \'FILL\' 1;' : '' }}">dashboard</span>
                    <span>Dashboard</span>
                </a>

                <!-- Pedidos / Ventas -->
                <a href="{{ url('/admin/pedidos') }}" 
                   class="group flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all {{ request()->is('admin/pedidos*') ? 'bg-[#183358] text-white font-semibold shadow-xs border-l-4 border-emerald-400' : 'text-[#93a7c3] hover:bg-[#13263f] hover:text-white' }}">
                    <span class="material-symbols-outlined text-[18px] {{ request()->is('admin/pedidos*') ? 'text-emerald-400' : 'text-[#728ba8] group-hover:text-[#93a7c3]' }}">sync_alt</span>
                    <span>Pedidos & Ventas</span>
                </a>

                <!-- Catálogo & Productos -->
                <a href="{{ url('/admin/productos') }}" 
                   class="group flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all {{ request()->is('admin/productos*') ? 'bg-[#183358] text-white font-semibold shadow-xs border-l-4 border-emerald-400' : 'text-[#93a7c3] hover:bg-[#13263f] hover:text-white' }}">
                    <span class="material-symbols-outlined text-[18px] {{ request()->is('admin/productos*') ? 'text-emerald-400' : 'text-[#728ba8] group-hover:text-[#93a7c3]' }}">inventory_2</span>
                    <span>Productos</span>
                </a>

                <!-- Categorías -->
                <a href="{{ url('/admin/categorias') }}" 
                   class="group flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all {{ request()->is('admin/categorias*') ? 'bg-[#183358] text-white font-semibold shadow-xs border-l-4 border-emerald-400' : 'text-[#93a7c3] hover:bg-[#13263f] hover:text-white' }}">
                    <span class="material-symbols-outlined text-[18px] {{ request()->is('admin/categorias*') ? 'text-emerald-400' : 'text-[#728ba8] group-hover:text-[#93a7c3]' }}">category</span>
                    <span>Categorías</span>
                </a>

                <!-- Inventario -->
                <a href="{{ url('/admin/inventario') }}" 
                   class="group flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all {{ request()->is('admin/inventario*') ? 'bg-[#183358] text-white font-semibold shadow-xs border-l-4 border-emerald-400' : 'text-[#93a7c3] hover:bg-[#13263f] hover:text-white' }}">
                    <span class="material-symbols-outlined text-[18px] {{ request()->is('admin/inventario*') ? 'text-emerald-400' : 'text-[#728ba8] group-hover:text-[#93a7c3]' }}">warehouse</span>
                    <span>Inventario</span>
                </a>

                <!-- Clientes & Usuarios -->
                <a href="{{ url('/admin/usuarios') }}" 
                   class="group flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all {{ request()->is('admin/usuarios*') ? 'bg-[#183358] text-white font-semibold shadow-xs border-l-4 border-emerald-400' : 'text-[#93a7c3] hover:bg-[#13263f] hover:text-white' }}">
                    <span class="material-symbols-outlined text-[18px] {{ request()->is('admin/usuarios*') ? 'text-emerald-400' : 'text-[#728ba8] group-hover:text-[#93a7c3]' }}">group</span>
                    <span>Clientes / Usuarios</span>
                </a>

                <!-- Facturación -->
                <a href="{{ url('/admin/facturas') }}" 
                   class="group flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all {{ request()->is('admin/facturas*') ? 'bg-[#183358] text-white font-semibold shadow-xs border-l-4 border-emerald-400' : 'text-[#93a7c3] hover:bg-[#13263f] hover:text-white' }}">
                    <span class="material-symbols-outlined text-[18px] {{ request()->is('admin/facturas*') ? 'text-emerald-400' : 'text-[#728ba8] group-hover:text-[#93a7c3]' }}">receipt_long</span>
                    <span>Facturación</span>
                </a>

                <!-- Cupones -->
                <a href="{{ url('/admin/cupones') }}" 
                   class="group flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all {{ request()->is('admin/cupones*') ? 'bg-[#183358] text-white font-semibold shadow-xs border-l-4 border-emerald-400' : 'text-[#93a7c3] hover:bg-[#13263f] hover:text-white' }}">
                    <span class="material-symbols-outlined text-[18px] {{ request()->is('admin/cupones*') ? 'text-emerald-400' : 'text-[#728ba8] group-hover:text-[#93a7c3]' }}">local_activity</span>
                    <span>Cupones</span>
                </a>

                <!-- Reportes -->
                <a href="{{ url('/admin/reportes') }}" 
                   class="group flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all {{ request()->is('admin/reportes*') ? 'bg-[#183358] text-white font-semibold shadow-xs border-l-4 border-emerald-400' : 'text-[#93a7c3] hover:bg-[#13263f] hover:text-white' }}">
                    <span class="material-symbols-outlined text-[18px] {{ request()->is('admin/reportes*') ? 'text-emerald-400' : 'text-[#728ba8] group-hover:text-[#93a7c3]' }}">bar_chart</span>
                    <span>Reportes</span>
                </a>

                <div class="text-[10px] font-bold text-[#627d9f] uppercase tracking-widest mt-3 mb-1.5 px-3">Sistema</div>

                <!-- Auditoría -->
                <a href="{{ url('/admin/auditoria') }}" 
                   class="group flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all {{ request()->is('admin/auditoria*') ? 'bg-[#183358] text-white font-semibold shadow-xs border-l-4 border-emerald-400' : 'text-[#93a7c3] hover:bg-[#13263f] hover:text-white' }}">
                    <span class="material-symbols-outlined text-[18px] {{ request()->is('admin/auditoria*') ? 'text-emerald-400' : 'text-[#728ba8] group-hover:text-[#93a7c3]' }}">security</span>
                    <span>Auditoría</span>
                </a>

                <!-- Configuración -->
                <a href="{{ url('/admin/configuracion') }}" 
                   class="group flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all {{ request()->is('admin/configuracion*') ? 'bg-[#183358] text-white font-semibold shadow-xs border-l-4 border-emerald-400' : 'text-[#93a7c3] hover:bg-[#13263f] hover:text-white' }}">
                    <span class="material-symbols-outlined text-[18px] {{ request()->is('admin/configuracion*') ? 'text-emerald-400' : 'text-[#728ba8] group-hover:text-[#93a7c3]' }}">settings</span>
                    <span>Configuración</span>
                </a>
            </div>
        </div>

        <!-- Sidebar Footer -->
        <div class="px-4 pt-3 border-t border-[#172c49] flex flex-col gap-1.5">
            <!-- Link to Public Store -->
            <a href="{{ url('/') }}" target="_blank" 
               class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs font-semibold text-[#eaf1ff] bg-[#13263f] hover:bg-[#183358] hover:text-white border border-[#1e3b63] transition-colors">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[16px] text-emerald-400">storefront</span>
                    <span>Ver Tienda</span>
                </span>
                <span class="material-symbols-outlined text-[14px] text-[#8ca4c4]">open_in_new</span>
            </a>

            <!-- Logout -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" 
                        class="w-full flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-medium text-rose-300 hover:text-rose-200 hover:bg-rose-500/10 transition-colors">
                    <span class="material-symbols-outlined text-[16px]">logout</span>
                    <span>Cerrar Sesión</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 md:ml-64 flex flex-col min-h-screen">
        
        <!-- TopNavBar -->
        <header class="sticky top-0 z-30 w-full px-4 sm:px-8 py-3 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-xs flex items-center justify-between gap-4">
            
            <!-- Left: Toggle & Search -->
            <div class="flex items-center gap-4 flex-1">
                <!-- Hamburger Button (Mobile) -->
                <button onclick="toggleSidebar()" class="md:hidden p-2 text-slate-700 hover:bg-slate-100 rounded-lg">
                    <span class="material-symbols-outlined">menu</span>
                </button>

                <!-- Search Input -->
                <div class="hidden sm:flex items-center bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 w-full max-w-md focus-within:border-slate-400 focus-within:bg-white focus-within:ring-2 focus-within:ring-slate-900/5 transition-all">
                    <span class="material-symbols-outlined text-slate-400 text-[18px]">search</span>
                    <input type="text" 
                           placeholder="Buscar pedidos, productos, clientes, facturas..." 
                           class="bg-transparent border-none focus:ring-0 w-full text-xs text-slate-800 placeholder:text-slate-400 p-0 ml-2"/>
                    <kbd class="hidden md:inline-block px-1.5 py-0.5 text-[10px] font-mono font-medium text-slate-400 bg-white border border-slate-200 rounded">Ctrl+K</kbd>
                </div>
            </div>

            <!-- Right: Actions & User Info -->
            <div class="flex items-center gap-3 text-slate-800">
                <!-- Notifications -->
                <button class="relative p-1.5 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors" title="Notificaciones">
                    <span class="material-symbols-outlined text-[18px]">notifications</span>
                    <span class="absolute top-1.5 right-1.5 w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                </button>

                <div class="h-4 w-px bg-slate-200 hidden sm:block"></div>

                <!-- User Profile Badge -->
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-full bg-[#0c1b2f] text-white font-bold flex items-center justify-center text-xs shadow-xs">
                        {{ strtoupper(substr(Auth::user()->nombre ?? 'A', 0, 1)) }}
                    </div>
                    <div class="hidden sm:flex flex-col text-left">
                        <span class="text-xs font-semibold text-slate-900 leading-tight">{{ Auth::user()->nombre_completo ?? Auth::user()->nombre ?? 'Administrador' }}</span>
                        <span class="text-[10px] font-semibold text-emerald-700 leading-tight">
                            {{ Auth::user()->hasRole('super_admin') ? 'Super Administrador' : 'Administrador' }}
                        </span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Body / Canvas -->
        <main class="flex-1 px-4 sm:px-8 py-6 max-w-[1500px] w-full mx-auto">
            @yield('content')
        </main>

        <!-- Admin Footer -->
        <footer class="px-4 sm:px-8 py-3 border-t border-slate-200/70 bg-white text-xs text-slate-500 flex items-center justify-between">
            <div>
                © {{ date('Y') }} <span class="font-semibold text-slate-700">PayMe Panamá</span> — Sistema de Comercio Electrónico PyME.
            </div>
        </footer>
    </div>

    <!-- Toggle Sidebar Script -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('admin-sidebar');
            const backdrop = document.getElementById('mobile-sidebar-backdrop');
            sidebar.classList.toggle('-translate-x-full');
            backdrop.classList.toggle('hidden');
        }
    </script>

    @stack('scripts')
</body>
</html>
