<!-- Sidebar Admin (Fondo #1F2937) -->
<aside id="admin-sidebar" class="w-64 fixed left-0 top-0 h-full bg-[#1F2937] text-slate-200 z-50 transform -translate-x-full md:translate-x-0 transition-all duration-300 ease-in-out shadow-2xl md:shadow-none flex flex-col justify-between select-none">
    

    
    <!-- Encabezado y Navegación -->
    <div class="flex flex-col flex-1 min-h-0">
        
        <!-- Encabezado del Logo de Marca -->
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

        <!-- Área Desplazable de Navegación -->
        <nav class="flex-1 pl-3 pt-8 pb-3 pr-0 overflow-y-auto space-y-2 hide-scrollbar">
            
            <!-- 1. Dashboard (Individual) -->
            @can('admin.dashboard')
            <div>
                <a href="{{ route('admin.dashboard') }}" 
                   class="group relative flex items-center gap-3 px-3.5 py-2.5 text-xs font-bold transition-all {{ request()->routeIs('admin.dashboard') ? 'sidebar-active-item' : 'rounded-full mr-3 text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                    <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->routeIs('admin.dashboard') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}" style="{{ request()->routeIs('admin.dashboard') ? 'font-variation-settings: \'FILL\' 1;' : '' }}">dashboard</span>
                    <span class="sidebar-text truncate transition-all duration-300">Dashboard</span>
                </a>
            </div>
            @endcan

            <!-- 2. Ventas & Pedidos (Desplegable) -->
            @canany(['admin.pedidos.ver', 'admin.devoluciones.ver', 'admin.facturas.ver'])
            @php
                $isVentasActive = request()->is('admin/pedidos*') || request()->routeIs('admin.devoluciones*') || request()->is('admin/facturas*');
                $nuevosPedidosCount = \App\Models\Pedido::whereNotExists(function ($query) {
                    $query->select(\Illuminate\Support\Facades\DB::raw(1))
                          ->from('estados_pedido')
                          ->whereColumn('estados_pedido.pedido_id', 'pedidos.id')
                          ->whereNotIn('estados_pedido.estado', ['pendiente', 'pago_confirmado']);
                })->count();
                $nuevasDevolucionesCount = \App\Models\Devolucion::where('estado', 'pendiente')->count();
            @endphp
            <div x-data="{ open: {{ $isVentasActive ? 'true' : 'false' }}, pinned: false }" @click.outside="pinned = false" @sidebar-hover.window="if ($event.detail !== $el) pinned = false" class="nav-group relative" :class="pinned ? 'is-pinned' : ''">
                <button @click="open = !open; pinned = !pinned;"
                        class="w-full group relative flex items-center justify-between gap-3 px-3.5 py-2.5 text-xs font-bold transition-all rounded-full mr-3 {{ $isVentasActive ? 'text-white is-active-parent' : 'text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        {{-- Ícono con indicador de notificaciones (solo visible cuando el sidebar está colapsado) --}}
                        <span class="relative">
                            <span class="material-symbols-outlined text-[19px] transition-colors {{ $isVentasActive ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}" style="{{ $isVentasActive ? 'font-variation-settings: \'FILL\' 1;' : '' }}">shopping_cart</span>
                            @if(($nuevosPedidosCount + $nuevasDevolucionesCount) > 0)
                                <span class="sidebar-collapsed-badge" style="position:absolute; top:-6px; right:-6px; min-width:16px; height:16px; padding:0 3px; border-radius:999px; background:#f43f5e; color:#fff; font-size:9px; font-weight:800; line-height:16px; text-align:center; box-shadow:0 0 0 2px #1F2937;">
                                    {{ min($nuevosPedidosCount + $nuevasDevolucionesCount, 99) }}
                                </span>
                            @endif
                        </span>
                        <span class="sidebar-text truncate transition-all duration-300">Ventas</span>
                    </div>
                    {{-- Cuando el sidebar está expandido mostramos también el total junto al chevron --}}
                    <div class="sidebar-text flex items-center gap-1.5">
                        @if(($nuevosPedidosCount + $nuevasDevolucionesCount) > 0)
                            <span style="background:#f43f5e; color:#fff; font-size:10px; font-weight:800; padding:1px 6px; border-radius:999px; line-height:16px;">
                                {{ min($nuevosPedidosCount + $nuevasDevolucionesCount, 99) }}
                            </span>
                        @endif
                        <span class="material-symbols-outlined text-[16px] transition-transform duration-300" :class="open ? 'rotate-180' : ''">expand_more</span>
                    </div>
                </button>
                
                <div x-show="open" x-collapse.duration.150ms
                     style="display: {{ $isVentasActive ? 'block' : 'none' }};"
                     class="submenu-container mt-1 mb-2 flex flex-col space-y-0.5">
                    <div class="popover-header">Ventas</div>
                    @can('admin.pedidos.ver')
                    <div class="submenu-item relative {{ request()->is('admin/pedidos*') ? 'is-active' : '' }}">
                        <a href="{{ url('/admin/pedidos') }}" 
                           class="flex items-center justify-between py-2 px-3 ml-[36px] mr-3 rounded-xl text-[12.5px] font-medium transition-colors {{ request()->is('admin/pedidos*') ? 'sidebar-active-item' : 'text-slate-400 hover:text-white hover:bg-[#2B3648]/40' }}">
                            <span>Pedidos</span>
                            <x-sidebar-badge :count="$nuevosPedidosCount" />
                        </a>
                    </div>
                    @endcan
                    @can('admin.devoluciones.ver')
                    <div class="submenu-item relative {{ request()->routeIs('admin.devoluciones*') ? 'is-active' : '' }}">
                        <a href="{{ route('admin.devoluciones.index') }}" 
                           class="flex items-center justify-between py-2 px-3 ml-[36px] mr-3 rounded-xl text-[12.5px] font-medium transition-colors {{ request()->routeIs('admin.devoluciones*') ? 'sidebar-active-item' : 'text-slate-400 hover:text-white hover:bg-[#2B3648]/40' }}">
                            <span>Devoluciones</span>
                            <x-sidebar-badge :count="$nuevasDevolucionesCount" />
                        </a>
                    </div>
                    @endcan
                    @can('admin.facturas.ver')
                    <div class="submenu-item relative {{ request()->is('admin/facturas*') ? 'is-active' : '' }}">
                        <a href="{{ url('/admin/facturas') }}" 
                           class="flex items-center justify-between py-2 px-3 ml-[36px] mr-3 rounded-xl text-[12.5px] font-medium transition-colors {{ request()->is('admin/facturas*') ? 'sidebar-active-item' : 'text-slate-400 hover:text-white hover:bg-[#2B3648]/40' }}">
                            <span>Facturación</span>
                        </a>
                    </div>
                    @endcan
                </div>
            </div>
            @endcanany

            <!-- 3. Catálogo (Desplegable) -->
            @canany(['admin.productos.ver', 'admin.categorias.ver', 'admin.marcas.ver'])
            @php
                $isCatalogoActive = request()->routeIs('admin.productos*') || request()->routeIs('admin.categorias*') || request()->routeIs('admin.brands*');
            @endphp
            <div x-data="{ open: {{ $isCatalogoActive ? 'true' : 'false' }}, pinned: false }" @click.outside="pinned = false" @sidebar-hover.window="if ($event.detail !== $el) pinned = false" class="nav-group relative" :class="pinned ? 'is-pinned' : ''">
                <button @click="open = !open; pinned = !pinned;"
                        class="w-full group relative flex items-center justify-between gap-3 px-3.5 py-2.5 text-xs font-bold transition-all rounded-full mr-3 {{ $isCatalogoActive ? 'text-white is-active-parent' : 'text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[19px] transition-colors {{ $isCatalogoActive ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}" style="{{ $isCatalogoActive ? 'font-variation-settings: \'FILL\' 1;' : '' }}">sell</span>
                        <span class="sidebar-text truncate transition-all duration-300">Catálogo</span>
                    </div>
                    <span class="sidebar-text material-symbols-outlined text-[16px] transition-transform duration-300" :class="open ? 'rotate-180' : ''">expand_more</span>
                </button>
                
                <div x-show="open" x-collapse.duration.150ms
                     style="display: {{ $isCatalogoActive ? 'block' : 'none' }};"
                     class="submenu-container mt-1 mb-2 flex flex-col space-y-0.5">
                    <div class="popover-header">Catálogo</div>
                    @can('admin.productos.ver')
                    <div class="submenu-item relative {{ request()->routeIs('admin.productos*') ? 'is-active' : '' }}">
                        <a href="{{ route('admin.productos.index') }}" 
                           class="flex items-center justify-between py-2 px-3 ml-[36px] mr-3 rounded-xl text-[12.5px] font-medium transition-colors {{ request()->routeIs('admin.productos*') ? 'sidebar-active-item' : 'text-slate-400 hover:text-white hover:bg-[#2B3648]/40' }}">
                            <span>Productos</span>
                        </a>
                    </div>
                    @endcan
                    @can('admin.categorias.ver')
                    <div class="submenu-item relative {{ request()->routeIs('admin.categorias*') ? 'is-active' : '' }}">
                        <a href="{{ route('admin.categorias.index') }}" 
                           class="flex items-center justify-between py-2 px-3 ml-[36px] mr-3 rounded-xl text-[12.5px] font-medium transition-colors {{ request()->routeIs('admin.categorias*') ? 'sidebar-active-item' : 'text-slate-400 hover:text-white hover:bg-[#2B3648]/40' }}">
                            <span>Categorías</span>
                        </a>
                    </div>
                    @endcan
                    @can('admin.marcas.ver')
                    <div class="submenu-item relative {{ request()->routeIs('admin.brands*') ? 'is-active' : '' }}">
                        <a href="{{ route('admin.brands.index') }}" 
                           class="flex items-center justify-between py-2 px-3 ml-[36px] mr-3 rounded-xl text-[12.5px] font-medium transition-colors {{ request()->routeIs('admin.brands*') ? 'sidebar-active-item' : 'text-slate-400 hover:text-white hover:bg-[#2B3648]/40' }}">
                            <span>Marcas & Logos</span>
                        </a>
                    </div>
                    @endcan
                </div>
            </div>
            @endcanany

            <!-- 4. Logística & Stock (Desplegable) -->
            @canany(['admin.inventario.ver', 'admin.zonas.ver'])
            @php
                $isLogisticaActive = request()->is('admin/inventario*') || request()->routeIs('admin.zonas-envio*');
            @endphp
            <div x-data="{ open: {{ $isLogisticaActive ? 'true' : 'false' }}, pinned: false }" @click.outside="pinned = false" @sidebar-hover.window="if ($event.detail !== $el) pinned = false" class="nav-group relative" :class="pinned ? 'is-pinned' : ''">
                <button @click="open = !open; pinned = !pinned;"
                        class="w-full group relative flex items-center justify-between gap-3 px-3.5 py-2.5 text-xs font-bold transition-all rounded-full mr-3 {{ $isLogisticaActive ? 'text-white is-active-parent' : 'text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[19px] transition-colors {{ $isLogisticaActive ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}" style="{{ $isLogisticaActive ? 'font-variation-settings: \'FILL\' 1;' : '' }}">local_shipping</span>
                        <span class="sidebar-text truncate transition-all duration-300">Logística & Stock</span>
                    </div>
                    <span class="sidebar-text material-symbols-outlined text-[16px] transition-transform duration-300" :class="open ? 'rotate-180' : ''">expand_more</span>
                </button>
                
                <div x-show="open" x-collapse.duration.150ms
                     style="display: {{ $isLogisticaActive ? 'block' : 'none' }};"
                     class="submenu-container mt-1 mb-2 flex flex-col space-y-0.5">
                    <div class="popover-header">Logística & Stock</div>
                    @can('admin.inventario.ver')
                    <div class="submenu-item relative {{ request()->is('admin/inventario*') ? 'is-active' : '' }}">
                        <a href="{{ url('/admin/inventario') }}" 
                           class="flex items-center justify-between py-2 px-3 ml-[36px] mr-3 rounded-xl text-[12.5px] font-medium transition-colors {{ request()->is('admin/inventario*') ? 'sidebar-active-item' : 'text-slate-400 hover:text-white hover:bg-[#2B3648]/40' }}">
                            <span>Inventario</span>
                        </a>
                    </div>
                    @endcan
                    @can('admin.zonas.ver')
                    <div class="submenu-item relative {{ request()->routeIs('admin.zonas-envio*') ? 'is-active' : '' }}">
                        <a href="{{ route('admin.zonas-envio.index') }}" 
                           class="flex items-center justify-between py-2 px-3 ml-[36px] mr-3 rounded-xl text-[12.5px] font-medium transition-colors {{ request()->routeIs('admin.zonas-envio*') ? 'sidebar-active-item' : 'text-slate-400 hover:text-white hover:bg-[#2B3648]/40' }}">
                            <span>Zonas de Envío</span>
                        </a>
                    </div>
                    @endcan
                </div>
            </div>
            @endcanany

            <!-- 5. Marketing (Desplegable) -->
            @canany(['admin.cupones.ver', 'admin.promociones.ver'])
            @php
                $isMarketingActive = request()->routeIs('admin.promociones.cupones*') || request()->routeIs('admin.promociones.envio-gratis*') || request()->routeIs('admin.promociones.producto-del-mes*');
            @endphp
            <div x-data="{ open: {{ $isMarketingActive ? 'true' : 'false' }}, pinned: false }" @click.outside="pinned = false" @sidebar-hover.window="if ($event.detail !== $el) pinned = false" class="nav-group relative" :class="pinned ? 'is-pinned' : ''">
                <button @click="open = !open; pinned = !pinned;"
                        class="w-full group relative flex items-center justify-between gap-3 px-3.5 py-2.5 text-xs font-bold transition-all rounded-full mr-3 {{ $isMarketingActive ? 'text-white is-active-parent' : 'text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[19px] transition-colors {{ $isMarketingActive ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}" style="{{ $isMarketingActive ? 'font-variation-settings: \'FILL\' 1;' : '' }}">campaign</span>
                        <span class="sidebar-text truncate transition-all duration-300">Marketing</span>
                    </div>
                    <span class="sidebar-text material-symbols-outlined text-[16px] transition-transform duration-300" :class="open ? 'rotate-180' : ''">expand_more</span>
                </button>
                
                <div x-show="open" x-collapse.duration.150ms
                     style="display: {{ $isMarketingActive ? 'block' : 'none' }};"
                     class="submenu-container mt-1 mb-2 flex flex-col space-y-0.5">
                    <div class="popover-header">Marketing</div>
                    @can('admin.cupones.ver')
                    <div class="submenu-item relative {{ request()->routeIs('admin.promociones.cupones*') ? 'is-active' : '' }}">
                        <a href="{{ route('admin.promociones.cupones') }}" 
                           class="flex items-center justify-between py-2 px-3 ml-[36px] mr-3 rounded-xl text-[12.5px] font-medium transition-colors {{ request()->routeIs('admin.promociones.cupones*') ? 'sidebar-active-item' : 'text-slate-400 hover:text-white hover:bg-[#2B3648]/40' }}">
                            <span>Cupones</span>
                        </a>
                    </div>
                    @endcan
                    @can('admin.promociones.ver')
                    <div class="submenu-item relative {{ request()->routeIs('admin.promociones.envio-gratis*') || request()->routeIs('admin.promociones.producto-del-mes*') ? 'is-active' : '' }}">
                        <a href="{{ route('admin.promociones.envio-gratis') }}" 
                           class="flex items-center justify-between py-2 px-3 ml-[36px] mr-3 rounded-xl text-[12.5px] font-medium transition-colors {{ request()->routeIs('admin.promociones.envio-gratis*') || request()->routeIs('admin.promociones.producto-del-mes*') ? 'sidebar-active-item' : 'text-slate-400 hover:text-white hover:bg-[#2B3648]/40' }}">
                            <span>Promociones</span>
                        </a>
                    </div>
                    @endcan
                </div>
            </div>
            @endcanany

            <!-- 6. Usuarios (Individual) -->
            @can('admin.usuarios.ver')
            <div>
                <a href="{{ route('admin.usuarios.index') }}" 
                   class="group relative flex items-center gap-3 px-3.5 py-2.5 text-xs font-bold transition-all {{ request()->is('admin/usuarios*') ? 'sidebar-active-item' : 'rounded-full mr-3 text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                    <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->is('admin/usuarios*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}" style="{{ request()->is('admin/usuarios*') ? 'font-variation-settings: \'FILL\' 1;' : '' }}">admin_panel_settings</span>
                    <span class="sidebar-text truncate transition-all duration-300">Usuarios y Roles</span>
                </a>
            </div>
            @endcan

            <!-- 7. Reportes (Individual) -->
            @can('admin.reportes.ver')
            <div>
                <a href="{{ url('/admin/reportes') }}" 
                   class="group relative flex items-center gap-3 px-3.5 py-2.5 text-xs font-bold transition-all {{ request()->is('admin/reportes*') ? 'sidebar-active-item' : 'rounded-full mr-3 text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                    <span class="material-symbols-outlined text-[19px] transition-colors {{ request()->is('admin/reportes*') ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}" style="{{ request()->is('admin/reportes*') ? 'font-variation-settings: \'FILL\' 1;' : '' }}">bar_chart</span>
                    <span class="sidebar-text truncate transition-all duration-300">Reportes</span>
                </a>
            </div>
            @endcan

            <!-- 8. Sistema & Seguridad (Desplegable) -->
            @canany(['admin.auditoria.ver', 'admin.configuracion.ver'])
            @php
                $isSistemaActive = request()->is('admin/auditoria*') || request()->routeIs('admin.configuracion.*');
            @endphp
            <div x-data="{ open: {{ $isSistemaActive ? 'true' : 'false' }}, pinned: false }" @click.outside="pinned = false" @sidebar-hover.window="if ($event.detail !== $el) pinned = false" class="nav-group relative" :class="pinned ? 'is-pinned' : ''">
                <button @click="open = !open; pinned = !pinned;"
                        class="w-full group relative flex items-center justify-between gap-3 px-3.5 py-2.5 text-xs font-bold transition-all rounded-full mr-3 {{ $isSistemaActive ? 'text-white is-active-parent' : 'text-slate-300 hover:bg-[#2B3648]/60 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[19px] transition-colors {{ $isSistemaActive ? 'text-[#34D399]' : 'text-slate-400 group-hover:text-[#34D399]' }}" style="{{ $isSistemaActive ? 'font-variation-settings: \'FILL\' 1;' : '' }}">settings</span>
                        <span class="sidebar-text truncate transition-all duration-300">Sistema</span>
                    </div>
                    <span class="sidebar-text material-symbols-outlined text-[16px] transition-transform duration-300" :class="open ? 'rotate-180' : ''">expand_more</span>
                </button>
                
                <div x-show="open" x-collapse.duration.150ms
                     style="display: {{ $isSistemaActive ? 'block' : 'none' }};"
                     class="submenu-container mt-1 mb-2 flex flex-col space-y-0.5">
                    <div class="popover-header">Sistema</div>
                    @can('admin.auditoria.ver')
                    <div class="submenu-item relative {{ request()->is('admin/auditoria*') ? 'is-active' : '' }}">
                        <a href="{{ url('/admin/auditoria') }}" 
                           class="flex items-center justify-between py-2 px-3 ml-[36px] mr-3 rounded-xl text-[12.5px] font-medium transition-colors {{ request()->is('admin/auditoria*') ? 'sidebar-active-item' : 'text-slate-400 hover:text-white hover:bg-[#2B3648]/40' }}">
                            <span>Auditoría</span>
                        </a>
                    </div>
                    @endcan
                    @can('admin.configuracion.ver')
                    <div class="submenu-item relative {{ request()->routeIs('admin.configuracion.*') ? 'is-active' : '' }}">
                        <a href="{{ url('/admin/configuracion') }}" 
                           class="flex items-center justify-between py-2 px-3 ml-[36px] mr-3 rounded-xl text-[12.5px] font-medium transition-colors {{ request()->routeIs('admin.configuracion.*') ? 'sidebar-active-item' : 'text-slate-400 hover:text-white hover:bg-[#2B3648]/40' }}">
                            <span>Configuración</span>
                        </a>
                    </div>
                    @endcan
                </div>
            </div>
            @endcanany

        </nav>
    </div>

    <!-- Pie del Panel Lateral (Perfil y Tema) -->
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
