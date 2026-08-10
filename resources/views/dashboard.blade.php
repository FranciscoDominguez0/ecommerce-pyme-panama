@extends('layouts.cliente')

@section('title', 'Mi Cuenta')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Encabezado superior de bienvenida -->
    <div class="relative bg-gradient-to-br from-[#002349] to-[#003c71] text-white rounded-xl p-5 sm:p-6 shadow-sm mb-6 overflow-hidden">
        <!-- Luces de fondo decorativas (efecto visual) -->
        <div class="absolute top-0 right-0 -mr-10 -mt-10 w-48 h-48 rounded-full bg-white opacity-5 blur-2xl pointer-events-none"></div>
        <div class="absolute bottom-0 right-20 w-32 h-32 rounded-full bg-[#006148] opacity-30 blur-2xl pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                @auth
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-white/10 backdrop-blur-sm border border-white/10 rounded-full text-[#8af5be] text-[10px] font-bold uppercase tracking-wider mb-2">
                    <span class="material-symbols-outlined text-[12px]">verified_user</span>
                    <span>Cuenta Segura</span>
                </div>
                @endauth
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight mb-1">
                    ¡Hola, {{ Auth::check() ? Auth::user()->nombre : 'Bienvenido' }}!
                </h1>
                <p class="text-xs text-blue-100 max-w-lg">
                    {{ Auth::check() ? 'Bienvenido a tu panel de control. Administra tus pedidos, actualiza tus datos y no te pierdas nuestras ofertas exclusivas.' : 'Explora nuestro catálogo y encuentra los mejores productos. Inicia sesión para guardar tus favoritos.' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Tarjetas de acceso rápido (Pedidos, Favoritos, Perfil) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        
        <a href="{{ route('cliente.perfil.pedidos.index') }}" wire:navigate class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 group flex items-center gap-4 relative overflow-hidden">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 group-hover:bg-emerald-600 group-hover:text-white transition-colors duration-300 z-10">
                <span class="material-symbols-outlined text-xl">local_shipping</span>
            </div>
            <div class="z-10">
                <p class="text-sm font-bold text-slate-800 group-hover:text-emerald-700 transition-colors">Mis Pedidos</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Rastrea tus compras</p>
            </div>
            <!-- Animación visual (círculo que aparece al pasar el ratón) -->
            <div class="absolute -bottom-6 -right-6 w-16 h-16 bg-emerald-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
        </a>

        <a href="{{ route('cliente.lista-deseos') }}" wire:navigate class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 group flex items-center gap-4 relative overflow-hidden">
            <div class="w-10 h-10 rounded-lg bg-rose-50 text-rose-500 flex items-center justify-center shrink-0 group-hover:bg-rose-500 group-hover:text-white transition-colors duration-300 z-10">
                <span class="material-symbols-outlined text-xl">favorite</span>
            </div>
            <div class="z-10">
                <p class="text-sm font-bold text-slate-800 group-hover:text-rose-600 transition-colors">Lista de Deseos</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Tus favoritos</p>
            </div>
            <!-- Animación visual (círculo que aparece al pasar el ratón) -->
            <div class="absolute -bottom-6 -right-6 w-16 h-16 bg-rose-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
        </a>

        <a href="{{ route('cliente.perfil.datos') }}" wire:navigate class="bg-white p-4 rounded-xl border border-slate-100 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-300 group flex items-center gap-4 relative overflow-hidden">
            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300 z-10">
                <span class="material-symbols-outlined text-xl">manage_accounts</span>
            </div>
            <div class="z-10">
                <p class="text-sm font-bold text-slate-800 group-hover:text-blue-700 transition-colors">Mi Perfil</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Datos y seguridad</p>
            </div>
            <div class="absolute -bottom-6 -right-6 w-16 h-16 bg-blue-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
        </a>

    </div>

    <!-- Productos Recomendados Section -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-[#002349]">Productos Recomendados para ti</h2>
            <a href="{{ route('cliente.catalogo') }}" wire:navigate class="text-sm font-semibold text-emerald-700 hover:underline">Ver todo el catálogo</a>
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
