<!-- TopNavBar Ejecutivo (Fijo en la parte superior al hacer scroll) -->
<header class="sticky top-0 z-40 w-full max-w-full px-3.5 sm:px-8 py-3 bg-white/95 dark:bg-[#181a1b]/95 backdrop-blur-md border-b border-slate-200/80 dark:border-gray-800 shadow-xs flex items-center justify-between gap-2 sm:gap-4 shrink-0">
    
    <!-- Left: Toggle & Responsive Breadcrumbs -->
    <div class="flex items-center gap-2 sm:gap-3 min-w-0 overflow-hidden">
        <!-- Hamburger Button (Mobile) -->
        <button onclick="toggleSidebar()" class="md:hidden p-1.5 text-slate-700 hover:bg-slate-100 rounded-lg transition-colors shrink-0" aria-label="Abrir menú">
            <span class="material-symbols-outlined text-[22px]">menu</span>
        </button>
        
        <!-- Desktop Sidebar Toggle removed (Moved to edge of sidebar) -->

        <!-- Breadcrumbs de Navegación -->
        <x-admin-breadcrumb />
    </div>

    <!-- Right: Search, Notifications & User Profile -->
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
