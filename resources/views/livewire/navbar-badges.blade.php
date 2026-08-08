<div class="flex items-center gap-1 sm:gap-2">
    <!-- Wishlist Link -->
    <a href="{{ route('cliente.lista-deseos') }}" class="relative p-1.5 text-gray-600 hover:text-red-500 transition-colors" title="Lista de Deseos">
        <span class="material-symbols-outlined text-[20px] sm:text-[22px]">favorite</span>
        @if($cantidadDeseos > 0)
            <span class="absolute top-0.5 right-0 bg-red-500 text-white text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center border-2 border-white">
                {{ $cantidadDeseos > 99 ? '99+' : $cantidadDeseos }}
            </span>
        @endif
    </a>

    <!-- Cart Button (Abre Drawer Lateral) -->
    <button type="button" 
            onclick="if(window.Livewire) { Livewire.dispatch('abrir-carrito-drawer'); } else { window.dispatchEvent(new CustomEvent('abrir-carrito')); }" 
            class="relative p-1.5 text-gray-600 hover:text-[#006148] transition-colors cursor-pointer" 
            title="Ver Carrito de Compras">
        <span class="material-symbols-outlined text-[20px] sm:text-[22px]">shopping_bag</span>
        @if($cantidadCarrito > 0)
            <span class="absolute top-0.5 right-0 bg-[#006148] text-white text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center border-2 border-white">
                {{ $cantidadCarrito > 99 ? '99+' : $cantidadCarrito }}
            </span>
        @endif
    </button>
</div>
