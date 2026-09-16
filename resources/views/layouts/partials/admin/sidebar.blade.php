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

        <!-- Navigation Links -->
        <nav class="flex-1 pl-3 py-3 pr-0 overflow-y-auto space-y-4 hide-scrollbar">
            
            <!-- Grupo 1: General -->
            @canany(['admin.dashboard', 'admin.pedidos.ver', 'admin.devoluciones.ver'])
            <div>
                <div class="sidebar-group-title text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1.5 px-3 transition-all duration-300">
                    General
                </div>
                <div class="space-y-0.5">
                    @can('admin.dashboard')
                    <a href="{{ route('admin.dashboard') }}" 
                       class="group relative flex items-center gap-3 px-3.5 py-2.5 text-xs font-bold transition-all {{ request()->routeIs('admin.dashboard') ? 'sidebar-active-item' : 'rounded-full mr-3 text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                        <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->routeIs('admin.dashboard') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}" style="{{ request()->routeIs('admin.dashboard') ? 'font-variation-settings: \'FILL\' 1;' : '' }}">dashboard</span>
                        <span class="sidebar-text truncate transition-all duration-300">Dashboard</span>
                    </a>
                    @endcan

                    @can('admin.pedidos.ver')
                    @php
                        $nuevosPedidosCount = \App\Models\Pedido::whereNotExists(function ($query) {
                            $query->select(\Illuminate\Support\Facades\DB::raw(1))
                                  ->from('estados_pedido')
                                  ->whereColumn('estados_pedido.pedido_id', 'pedidos.id')
                                  ->whereNotIn('estados_pedido.estado', ['pendiente', 'pago_confirmado']);
                        })->count();
                    @endphp
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
                    <a href="{{ route('admin.productos.index') }}" 
                       class="group relative flex items-center gap-3 px-3.5 py-2.5 text-xs font-bold transition-all {{ request()->routeIs('admin.productos*') ? 'sidebar-active-item' : 'rounded-full mr-3 text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                        <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->routeIs('admin.productos*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">sell</span>
                        <span class="sidebar-text truncate transition-all duration-300">Productos</span>
                    </a>
                    @endcan

                    @can('admin.categorias.ver')
                    <a href="{{ route('admin.categorias.index') }}" 
                       class="group relative flex items-center gap-3 px-3.5 py-2.5 text-xs font-bold transition-all {{ request()->routeIs('admin.categorias*') ? 'sidebar-active-item' : 'rounded-full mr-3 text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                        <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->routeIs('admin.categorias*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">category</span>
                        <span class="sidebar-text truncate transition-all duration-300">Categorías</span>
                    </a>
                    @endcan

                    @can('admin.marcas.ver')
                    <a href="{{ route('admin.brands.index') }}" 
                       class="group relative flex items-center gap-3 px-3.5 py-2.5 text-xs font-bold transition-all {{ request()->routeIs('admin.brands*') ? 'sidebar-active-item' : 'rounded-full mr-3 text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                        <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->routeIs('admin.brands*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">verified</span>
                        <span class="sidebar-text truncate transition-all duration-300">Marcas & Logos</span>
                    </a>
                    @endcan

                    @can('admin.inventario.ver')
                    <a href="{{ url('/admin/inventario') }}" 
                       class="group relative flex items-center gap-3 px-3.5 py-2.5 text-xs font-bold transition-all {{ request()->is('admin/inventario*') ? 'sidebar-active-item' : 'rounded-full mr-3 text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                        <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->is('admin/inventario*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">warehouse</span>
                        <span class="sidebar-text truncate transition-all duration-300">Inventario</span>
                    </a>
                    @endcan

                    @can('admin.zonas.ver')
                    <a href="{{ route('admin.zonas-envio.index') }}" 
                       class="group relative flex items-center gap-3 px-3.5 py-2.5 text-xs font-bold transition-all {{ request()->routeIs('admin.zonas-envio*') ? 'sidebar-active-item' : 'rounded-full mr-3 text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                        <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->routeIs('admin.zonas-envio*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">local_shipping</span>
                        <span class="sidebar-text truncate transition-all duration-300">Zonas de Envío</span>
                    </a>
                    @endcan

                    @can('admin.cupones.ver')
                    <a href="{{ route('admin.promociones.cupones') }}" 
                       class="group relative flex items-center gap-3 px-3.5 py-2.5 text-xs font-bold transition-all {{ request()->routeIs('admin.promociones.cupones*') ? 'sidebar-active-item' : 'rounded-full mr-3 text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                        <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->routeIs('admin.promociones.cupones*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">local_offer</span>
                        <span class="sidebar-text truncate transition-all duration-300">Cupones</span>
                    </a>
                    @endcan

                    @can('admin.promociones.ver')
                    <a href="{{ route('admin.promociones.envio-gratis') }}" 
                       class="group relative flex items-center gap-3 px-3.5 py-2.5 text-xs font-bold transition-all {{ request()->routeIs('admin.promociones.envio-gratis*') || request()->routeIs('admin.promociones.producto-del-mes*') ? 'sidebar-active-item' : 'rounded-full mr-3 text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                        <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->routeIs('admin.promociones.envio-gratis*') || request()->routeIs('admin.promociones.producto-del-mes*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">campaign</span>
                        <span class="sidebar-text truncate transition-all duration-300">Promociones</span>
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
                    <a href="{{ route('admin.usuarios.index') }}" 
                       class="group relative flex items-center gap-3 px-3.5 py-2.5 text-xs font-bold transition-all {{ request()->is('admin/usuarios*') ? 'sidebar-active-item' : 'rounded-full mr-3 text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                        <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->is('admin/usuarios*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">admin_panel_settings</span>
                        <span class="sidebar-text truncate transition-all duration-300">Usuarios y Roles</span>
                    </a>
                    @endcan

                    @can('admin.facturas.ver')
                    <a href="{{ url('/admin/facturas') }}" 
                       class="group relative flex items-center gap-3 px-3.5 py-2.5 text-xs font-bold transition-all {{ request()->is('admin/facturas*') ? 'sidebar-active-item' : 'rounded-full mr-3 text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                        <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->is('admin/facturas*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">receipt_long</span>
                        <span class="sidebar-text truncate transition-all duration-300">Facturación</span>
                    </a>
                    @endcan

                    @can('admin.reportes.ver')
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
                    <a href="{{ url('/admin/auditoria') }}" 
                       class="group relative flex items-center gap-3 px-3.5 py-2.5 text-xs font-bold transition-all {{ request()->is('admin/auditoria*') ? 'sidebar-active-item' : 'rounded-full mr-3 text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                        <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->is('admin/auditoria*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}">security</span>
                        <span class="sidebar-text truncate transition-all duration-300">Auditoría</span>
                    </a>
                    @endcan

                    @can('admin.configuracion.ver')
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
        <div class="sidebar-version text-[10px] font-medium text-slate-500 tracking-wider cursor-default select-none transition-all duration-300">
            PayMe v1.0.0
        </div>
        <button id="theme-toggle" type="button" 
                class="relative inline-flex h-8 w-16 items-center rounded-full bg-slate-900 border border-slate-700 shadow-inner focus:outline-none focus:ring-2 focus:ring-emerald-500/50 focus:ring-offset-1 focus:ring-offset-slate-800">
            <span class="sr-only">Toggle Dark Mode</span>
            <span id="theme-toggle-thumb" 
                  class="inline-flex h-6 w-6 transform items-center justify-center rounded-full bg-white translate-x-1 shadow-sm">
                <span id="theme-toggle-light-icon" class="material-symbols-outlined text-[14px] text-amber-500" style="font-variation-settings: 'FILL' 1;">light_mode</span>
                <span id="theme-toggle-dark-icon" class="material-symbols-outlined text-[14px] text-slate-800 absolute opacity-0" style="font-variation-settings: 'FILL' 1;">dark_mode</span>
            </span>
        </button>
    </div>
</aside>
