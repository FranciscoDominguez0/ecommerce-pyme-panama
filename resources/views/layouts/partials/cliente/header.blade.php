<!-- Main Navigation Bar -->
<header class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-gray-200/80 shadow-xs">
    <div class="w-full max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-15 py-2.5 gap-4">

            <!-- Menu & Brand Logo -->
            <div class="flex items-center gap-3 shrink-0">
                <!-- Hamburger (Solo móvil) -->
                <button @click="mobileMenuOpen = true" class="md:hidden p-1 text-slate-700 hover:text-[#006148] transition-colors">
                    <span class="material-symbols-outlined text-[26px]">menu</span>
                </button>
                
                <div class="hidden md:flex items-center gap-2.5">
                    <x-application-logo size="default" />
                    <div class="hidden md:block">
                        <span class="text-base font-bold text-[#002349] tracking-tight block leading-none">PayMe <span
                                class="text-[#006148]">Panamá</span></span>
                        <span
                            class="text-[9px] text-gray-500 font-semibold uppercase tracking-wider block mt-0.5">Tecnología
                            & Equipos IT</span>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="hidden md:flex flex-1 max-w-md mx-4 relative" x-data="predictiveSearch()">
                <form action="{{ route('cliente.catalogo') }}" method="GET" class="w-full relative">
                    <input type="text" name="buscar" id="global-search-input" value="{{ request('buscar') }}" 
                        placeholder="Buscar productos, categorías..."
                        autocomplete="off"
                        x-model="query"
                        @input.debounce.300ms="fetchResults"
                        @focus="open = true"
                        @click.outside="open = false"
                        @keydown.escape.window="open = false"
                        class="w-full bg-gray-50 border border-gray-200 rounded-lg pl-9 pr-3 py-1.5 text-xs text-gray-900 focus:bg-white focus:ring-1 focus:ring-[#006148] focus:border-[#006148] transition-all" />
                    <button type="submit" class="absolute left-2.5 top-2 text-gray-400 hover:text-[#006148] transition-colors">
                        <span class="material-symbols-outlined text-[16px]">search</span>
                    </button>
                </form>

                <!-- Dropdown de resultados predictivos -->
                <div x-show="open && (loading || results.length > 0 || (query.length >= 2 && results.length === 0))" 
                     style="display: none;"
                     x-transition.opacity.duration.200ms
                     class="absolute top-full left-0 w-full mt-1 bg-white border border-gray-200 shadow-xl rounded-xl overflow-hidden z-50 flex flex-col">
                     
                     <template x-if="loading">
                         <div class="p-4 text-center text-xs text-gray-500 flex items-center justify-center gap-2">
                             <span class="material-symbols-outlined animate-spin text-[16px]">progress_activity</span>
                             <span>Buscando...</span>
                         </div>
                     </template>

                     <template x-if="!loading && results.length > 0">
                         <div>
                             <div class="px-4 py-2 bg-gray-50/50 border-b border-gray-100 text-[10px] font-bold text-gray-500 uppercase tracking-wider flex justify-between items-center">
                                 <span>Productos Sugeridos</span>
                                 <span x-text="results.length"></span>
                             </div>
                             <ul class="max-h-80 overflow-y-auto">
                                 <template x-for="producto in results" :key="producto.url">
                                     <li>
                                         <a :href="producto.url" class="flex items-center gap-3 p-3 hover:bg-gray-50 transition-colors border-b border-gray-50 last:border-0 group">
                                             <div class="w-10 h-10 rounded-md border border-gray-100 bg-white shrink-0 overflow-hidden flex items-center justify-center">
                                                 <template x-if="producto.imagen">
                                                     <img :src="producto.imagen" :alt="producto.nombre" class="w-full h-full object-cover">
                                                 </template>
                                                 <template x-if="!producto.imagen">
                                                     <span class="material-symbols-outlined text-gray-300 text-[18px]">image</span>
                                                 </template>
                                             </div>
                                             <div class="flex-1 min-w-0">
                                                 <div class="text-xs font-semibold text-[#002349] group-hover:text-[#006148] truncate transition-colors" x-text="producto.nombre"></div>
                                                 <div class="text-[10px] text-gray-400 mt-0.5 truncate" x-text="producto.sku ? 'SKU: ' + producto.sku : ''"></div>
                                             </div>
                                             <div class="text-xs font-bold text-[#006148] shrink-0" x-text="'$' + producto.precio"></div>
                                         </a>
                                     </li>
                                 </template>
                             </ul>
                             <div class="p-2 border-t border-gray-100 text-center bg-gray-50/50 hover:bg-gray-100 transition-colors cursor-pointer" @click="$el.closest('form').submit()">
                                 <a :href="'{{ route('cliente.catalogo') }}?buscar=' + encodeURIComponent(query)" class="text-[11px] font-bold text-[#002349] inline-flex items-center gap-1">
                                     Ver todos los resultados
                                     <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                                 </a>
                             </div>
                         </div>
                     </template>

                     <template x-if="!loading && !results.length && query.length >= 2">
                         <div class="p-6 text-center">
                             <span class="material-symbols-outlined text-gray-300 text-[32px] mb-2 block">search_off</span>
                             <p class="text-xs text-gray-500 font-medium">No se encontraron productos para "<span x-text="query" class="text-gray-800 font-bold"></span>"</p>
                         </div>
                     </template>
                </div>
            </div>

            <!-- Script de Alpine.js para la búsqueda predictiva -->
            <script>
                function predictiveSearch() {
                    return {
                        open: false,
                        query: '{{ request('buscar') }}',
                        results: [],
                        loading: false,
                        fetchResults() {
                            if (this.query.length < 2) {
                                this.results = [];
                                this.open = false;
                                return;
                            }
                            this.loading = true;
                            this.open = true;
                            fetch('{{ route('cliente.buscar-predictivo') }}?buscar=' + encodeURIComponent(this.query))
                                .then(res => res.json())
                                .then(data => {
                                    this.results = data;
                                    this.loading = false;
                                })
                                .catch(() => {
                                    this.results = [];
                                    this.loading = false;
                                });
                        }
                    }
                }
            </script>

            <!-- Navigation Links & User Menu -->
            <div class="flex items-center gap-3">
                <!-- Badges y Enlaces Dinámicos de Carrito y Deseos -->
                <livewire:navbar-badges />

                <!-- User Authentication -->
                @auth
                    <div class="flex items-center gap-2">
                        @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('super_admin'))
                            <a href="{{ route('admin.dashboard') }}"
                                class="hidden sm:inline-flex items-center gap-1 py-1 px-2.5 rounded-lg bg-[#002349] text-white text-xs font-semibold hover:bg-[#00132b] transition-colors">
                                <span class="material-symbols-outlined text-[14px]">admin_panel_settings</span>
                                <span>Panel Admin</span>
                            </a>
                        @endif

                        <div class="relative shrink-0" x-data="{ open: false }" @click.outside="open = false">
                            <button @click="open = !open"
                                class="flex items-center gap-2 py-0.5 pl-0.5 pr-3.5 rounded-full bg-gray-100 hover:bg-gray-200 text-sm font-bold text-[#002349] transition-colors cursor-pointer select-none"
                                :aria-expanded="open">
                                @if(Auth::user()->foto_perfil_ruta)
                                    <img src="{{ asset(Auth::user()->foto_perfil_ruta) }}" alt="Perfil" class="w-9 h-9 rounded-full object-cover shadow-sm border border-white shrink-0">
                                @else
                                    <div class="w-9 h-9 rounded-full bg-[#006148] text-white flex items-center justify-center text-sm shadow-sm border border-white shrink-0">
                                        {{ Auth::user() ? strtoupper(substr(Auth::user()->nombre, 0, 1) . (Auth::user()->apellido ? substr(Auth::user()->apellido, 0, 1) : '')) : 'MC' }}
                                    </div>
                                @endif
                                <span>{{ Auth::user() ? strtok(Auth::user()->nombre, ' ') : 'Usuario' }}</span>
                                <span class="material-symbols-outlined text-[16px] text-gray-500 hidden sm:inline transition-transform duration-200"
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
                                 class="absolute right-0 top-full mt-2 w-48 bg-white rounded-2xl shadow-xl border border-gray-100 py-1.5 z-50 origin-top-right">
        
                                {{-- Mi Perfil --}}
                                <a href="{{ route('cliente.perfil.datos') }}" wire:navigate
                                   class="flex items-center gap-2.5 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:text-[#006148] transition-colors">
                                    <span class="material-symbols-outlined text-[18px] text-gray-400">person</span>
                                    Mi Perfil
                                </a>
        
                                <div class="my-1 h-px bg-gray-100 mx-3"></div>
        
                                {{-- Cerrar sesión --}}
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50 transition-colors">
                                        <span class="material-symbols-outlined text-[18px] text-red-400">logout</span>
                                        Cerrar sesión
                                    </button>
                                </form>
                            </div>
                        </div>
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
</header>
