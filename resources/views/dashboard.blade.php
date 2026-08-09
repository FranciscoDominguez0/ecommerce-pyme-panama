@extends('layouts.cliente')

@section('title', 'Mi Cuenta')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Welcome Header -->
    <div class="bg-[#002349] text-white rounded-xl p-5 sm:p-6 shadow-sm mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            @auth
            <div class="flex items-center gap-1.5 text-[#8af5be] text-[11px] font-semibold uppercase tracking-wider mb-0.5">
                <span class="material-symbols-outlined text-[14px]">verified_user</span>
                <span>Cuenta Segura</span>
            </div>
            @endauth
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight">
                ¡Hola, {{ Auth::check() ? Auth::user()->nombre : 'Bienvenido' }}!
            </h1>
            <p class="text-xs text-gray-300 mt-0.5">
                {{ Auth::check() ? 'Bienvenido a tu panel de control. Aquí puedes gestionar tus compras y preferencias.' : 'Encuentra los mejores productos y ofertas en nuestra tienda.' }}
            </p>
        </div>
    </div>

    <!-- Enlaces Rápidos (Quick Links) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        
        <a href="#" class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:border-emerald-200 transition-all group flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                <span class="material-symbols-outlined text-2xl">local_shipping</span>
            </div>
            <div>
                <p class="text-sm font-bold text-[#002349] group-hover:text-emerald-700 transition-colors">Mis Pedidos</p>
                <p class="text-xs text-gray-500 mt-0.5">Rastrea y revisa tus compras</p>
            </div>
        </a>

        <a href="{{ route('cliente.lista-deseos') }}" class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:border-rose-200 transition-all group flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center shrink-0 group-hover:bg-rose-500 group-hover:text-white transition-colors">
                <span class="material-symbols-outlined text-2xl">favorite</span>
            </div>
            <div>
                <p class="text-sm font-bold text-[#002349] group-hover:text-rose-600 transition-colors">Lista de Deseos</p>
                <p class="text-xs text-gray-500 mt-0.5">Tus productos favoritos</p>
            </div>
        </a>

        <a href="#" class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:border-blue-200 transition-all group flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                <span class="material-symbols-outlined text-2xl">manage_accounts</span>
            </div>
            <div>
                <p class="text-sm font-bold text-[#002349] group-hover:text-blue-700 transition-colors">Mi Perfil</p>
                <p class="text-xs text-gray-500 mt-0.5">Datos, direcciones y seguridad</p>
            </div>
        </a>

    </div>

    <!-- Productos Recomendados Section -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-[#002349]">Productos Recomendados para ti</h2>
            <a href="{{ route('cliente.catalogo') }}" class="text-sm font-semibold text-emerald-700 hover:underline">Ver todo el catálogo</a>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($productos as $prod)
                <x-producto-card :prod="$prod" />
            @empty
                <div class="col-span-full text-center py-10 bg-white rounded-xl border border-gray-200/80 shadow-soft">
                    <span class="material-symbols-outlined text-4xl text-gray-300 block mb-2">inventory_2</span>
                    <p class="text-sm font-bold text-[#002349]">No hay productos disponibles en este momento</p>
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
