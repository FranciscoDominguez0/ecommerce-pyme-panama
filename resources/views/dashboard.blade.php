@extends('layouts.cliente')

@section('title', 'Mi Cuenta')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Welcome Header -->
    <div class="bg-[#002349] text-white rounded-xl p-5 sm:p-6 shadow-sm mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-1.5 text-[#8af5be] text-[11px] font-semibold uppercase tracking-wider mb-0.5">
                <span class="material-symbols-outlined text-[14px]">verified_user</span>
                <span>Cliente Verificado</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight">
                ¡Hola, {{ Auth::user()->nombre ?? 'Cliente' }}!
            </h1>
            <p class="text-xs text-gray-300 mt-0.5">
                Bienvenido a tu panel de compras en PayMe Panamá.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ url('/') }}" class="px-3.5 py-2 bg-[#006148] hover:bg-[#004f3b] text-white text-xs font-semibold rounded-lg transition-colors shadow-xs">
                Explorar Catálogo
            </a>
        </div>
    </div>

    <!-- Client Quick Stats & Sections -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        
        <div class="bg-white p-4.5 rounded-xl border border-gray-200/80 shadow-soft flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-[#006148] flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-xl">shopping_bag</span>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Mis Pedidos</p>
                <p class="text-lg font-bold text-[#002349]">0 Activos</p>
            </div>
        </div>

        <div class="bg-white p-4.5 rounded-xl border border-gray-200/80 shadow-soft flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-xl">location_on</span>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Dirección de Entrega</p>
                <p class="text-xs font-semibold text-[#002349]">Panamá, Rep. de Panamá</p>
            </div>
        </div>

        <div class="bg-white p-4.5 rounded-xl border border-gray-200/80 shadow-soft flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-xl">lock</span>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Seguridad</p>
                <p class="text-xs font-semibold text-[#002349]">Cifrado SSL 256 bits</p>
            </div>
        </div>

    </div>

    <!-- Orders Section -->
    <div class="bg-white rounded-xl border border-gray-200/80 shadow-soft p-5 sm:p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-bold text-[#002349]">Historial de Pedidos Recientes</h2>
        </div>
        
        <div class="text-center py-10">
            <span class="material-symbols-outlined text-4xl text-gray-300 block mb-2">inventory_2</span>
            <p class="text-sm font-bold text-[#002349]">No tienes pedidos realizados todavía</p>
            <p class="text-xs text-gray-400 max-w-md mx-auto mt-0.5 mb-5">
                Encuentra los mejores productos y aprovecha nuestros métodos de pago locales en Panamá como Yappy, Tarjeta de Crédito o ACH.
            </p>
            <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#006148] hover:bg-[#004f3b] text-white text-xs font-semibold rounded-lg transition-colors shadow-xs">
                <span class="material-symbols-outlined text-[16px]">storefront</span>
                <span>Ir a la Tienda</span>
            </a>
        </div>
    </div>

</div>
@endsection
