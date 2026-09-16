<!-- Drawer de Menú Móvil -->
<div x-show="mobileMenuOpen" style="display: none;" class="relative z-50 md:hidden" aria-labelledby="mobile-menu" role="dialog" aria-modal="true">
    <!-- Background backdrop -->
    <div x-show="mobileMenuOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
    
    <div class="fixed inset-0 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
            <div class="pointer-events-none fixed inset-y-0 left-0 flex max-w-full pr-10">
                <!-- Panel del menú -->
                <div x-show="mobileMenuOpen"
                     x-transition:enter="transform transition ease-in-out duration-300"
                     x-transition:enter-start="-translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transform transition ease-in-out duration-300"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="-translate-x-full"
                     @click.away="mobileMenuOpen = false"
                     class="pointer-events-auto w-screen max-w-xs bg-white shadow-2xl h-full flex flex-col">
                     
                    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800 bg-[#1a1a1a] text-white">
                        <span class="font-bold text-[15px] tracking-wide">Menú</span>
                        <button @click="mobileMenuOpen = false" class="text-white hover:text-emerald-400 transition-colors">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    
                    <div class="overflow-y-auto flex-1 p-5 space-y-5">
                        @php
                            $globalCats = \App\Models\Categoria::whereNull('padre_id')->orderBy('nombre')->get();
                        @endphp
                        <a href="{{ route('dashboard') }}" wire:navigate class="block text-slate-800 font-medium text-sm pb-4 border-b border-slate-100">Inicio</a>
                        
                        <div x-data="{ catOpen: false }" class="pb-4 border-b border-slate-100">
                            <button @click="catOpen = !catOpen" class="w-full flex items-center justify-between text-slate-800 font-medium text-sm">
                                Categorías
                                <span class="material-symbols-outlined text-[18px] transition-transform" :class="catOpen ? 'rotate-180' : ''">chevron_right</span>
                            </button>
                            <div x-show="catOpen" class="pl-4 mt-3 space-y-3 border-l border-slate-200" style="display:none;">
                                @foreach($globalCats as $cat)
                                    <a href="{{ route('cliente.catalogo', ['categoria' => $cat->slug]) }}" wire:navigate class="block text-slate-500 hover:text-emerald-700 text-sm py-0.5 transition-colors">{{ $cat->nombre }}</a>
                                @endforeach
                            </div>
                        </div>
                        
                        <a href="{{ route('cliente.catalogo') }}" wire:navigate class="block text-slate-800 font-medium text-sm pb-4 border-b border-slate-100">Todos los productos</a>
                        
                        <div class="pb-4 border-b border-slate-100">
                            <a href="{{ route('cliente.catalogo', ['orden' => 'relevancia']) }}" wire:navigate class="w-full flex items-center justify-between text-slate-800 font-medium text-sm">
                                Lo más vendido
                                <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
