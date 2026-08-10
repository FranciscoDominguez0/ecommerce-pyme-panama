@extends('layouts.cliente')

@section('title', 'Carrito de Compras')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
    
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs font-semibold text-gray-500 mb-6" aria-label="Breadcrumb">
        <a href="{{ url('/') }}" wire:navigate class="hover:text-[#002349] transition-colors">Inicio</a>
        <span class="text-gray-300">/</span>
        <a href="{{ route('cliente.catalogo') }}" wire:navigate class="hover:text-[#002349] transition-colors">Catálogo</a>
        <span class="text-gray-300">/</span>
        <span class="text-[#002349]">Carrito</span>
    </nav>

    <!-- Componente Reactivo Livewire Carrito -->
    <livewire:carrito-widget />

</div>
@endsection
