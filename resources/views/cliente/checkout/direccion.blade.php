@extends('layouts.cliente')

@section('title', 'Dirección de Envío')

@section('content')
<div class="flex-grow pt-8 pb-12 px-4 md:px-16 max-w-7xl mx-auto w-full">
    <!-- Progress Indicator -->
    <div class="mb-12 flex justify-center w-full max-w-3xl mx-auto">
        <div class="flex items-center w-full relative">
            <!-- Step 1: Address (Active) -->
            <div class="flex flex-col items-center relative z-10 w-1/3">
                <div class="w-8 h-8 rounded-full bg-primary text-on-primary flex items-center justify-center font-numeric-data text-xl font-semibold mb-2 shadow-[0_4px_20px_rgba(0,35,73,0.12)]">1</div>
                <span class="font-label-caps text-xs font-semibold uppercase tracking-wide text-primary text-center">Dirección</span>
            </div>
            <!-- Connector 1-2 -->
            <div class="absolute top-4 left-[16.6%] right-[50%] h-[2px] bg-outline-variant -z-10"></div>
            <!-- Step 2: Payment (Pending) -->
            <div class="flex flex-col items-center relative z-10 w-1/3">
                <div class="w-8 h-8 rounded-full bg-surface-container border-2 border-outline-variant text-outline flex items-center justify-center font-numeric-data text-xl font-semibold mb-2">2</div>
                <span class="font-label-caps text-xs font-semibold uppercase tracking-wide text-on-surface-variant text-center">Pago</span>
            </div>
            <!-- Connector 2-3 -->
            <div class="absolute top-4 left-[50%] right-[16.6%] h-[2px] bg-outline-variant -z-10"></div>
            <!-- Step 3: Confirmation (Pending) -->
            <div class="flex flex-col items-center relative z-10 w-1/3">
                <div class="w-8 h-8 rounded-full bg-surface-container border-2 border-outline-variant text-outline flex items-center justify-center font-numeric-data text-xl font-semibold mb-2">3</div>
                <span class="font-label-caps text-xs font-semibold uppercase tracking-wide text-on-surface-variant text-center">Confirmación</span>
            </div>
        </div>
    </div>

    <div class="mb-8 max-w-4xl mx-auto">
        <h1 class="text-lg sm:text-xl font-bold text-primary mb-2">Seleccione su dirección de envío</h1>
        <p class="text-on-surface-variant text-sm">Elija una dirección guardada o agregue una nueva para su entrega.</p>
    </div>

    <livewire:gestion-direcciones :compact="true" :mostrarPredeterminada="false" :zonasEnvio="$zonasEnvio" />
</div>
@endsection
