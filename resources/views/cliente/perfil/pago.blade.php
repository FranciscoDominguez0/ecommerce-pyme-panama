@extends('layouts.cliente')

@section('title', 'Métodos de Pago')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col md:flex-row gap-8">
        <x-cliente.perfil.sidebar active="pago" />

        <div class="flex-1 min-w-0">
            <a href="{{ route('dashboard') }}"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-on-surface-variant hover:text-primary transition-colors mb-4">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                Volver al Dashboard
            </a>
            <div class="mb-6">
                <h1 class="text-xl sm:text-2xl font-bold text-primary">Métodos de Pago</h1>
                <p class="text-sm text-on-surface-variant mt-1">Administra los métodos de pago disponibles para tus compras.</p>
            </div>

            <div class="bg-white border border-outline-variant rounded-xl p-6 shadow-sm mb-6">
                <div class="flex items-center gap-2 mb-4">
                    <span class="material-symbols-outlined text-secondary text-xl">verified_user</span>
                    <h2 class="text-base font-bold text-primary">Pagos Seguros en Panamá</h2>
                </div>
                <p class="text-xs text-on-surface-variant mb-6">PayMe Panamá acepta los siguientes métodos de pago locales. Todos los pagos son procesados de forma segura.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5 flex items-center gap-4 hover:border-secondary/30 transition-colors">
                        <div class="w-12 h-12 rounded-full bg-surface-container flex items-center justify-center overflow-hidden border border-outline-variant p-2 shrink-0">
                            <img src="{{ asset('images/pa-yappy.webp') }}" alt="Yappy" class="max-w-full max-h-full object-contain">
                        </div>
                        <div>
                            <span class="block text-sm font-bold text-primary">Yappy</span>
                            <span class="block text-xs text-on-surface-variant">Pago instantáneo con Banco General</span>
                        </div>
                    </div>

                    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5 flex items-center gap-4 hover:border-secondary/30 transition-colors">
                        <span class="material-symbols-outlined text-3xl text-on-surface-variant">credit_card</span>
                        <div>
                            <span class="block text-sm font-bold text-primary">Tarjeta de Crédito / Débito</span>
                            <span class="block text-xs text-on-surface-variant">Visa, Mastercard</span>
                        </div>
                    </div>

                    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5 flex items-center gap-4 hover:border-secondary/30 transition-colors">
                        <span class="material-symbols-outlined text-3xl text-on-surface-variant">account_balance</span>
                        <div>
                            <span class="block text-sm font-bold text-primary">Transferencia Bancaria (ACH)</span>
                            <span class="block text-xs text-on-surface-variant">Depósito o transferencia local</span>
                        </div>
                    </div>

                    <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-5 flex items-center gap-4 hover:border-secondary/30 transition-colors">
                        <span class="material-symbols-outlined text-3xl text-on-surface-variant">local_shipping</span>
                        <div>
                            <span class="block text-sm font-bold text-primary">Pago Contra Entrega</span>
                            <span class="block text-xs text-on-surface-variant">Efectivo o tarjeta al recibir tu pedido</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-surface-container-low border border-outline-variant rounded-xl p-5 flex items-start gap-3">
                <span class="material-symbols-outlined text-amber-600 text-xl shrink-0 mt-0.5">info</span>
                <div>
                    <p class="text-sm font-semibold text-primary mb-1">Información Importante</p>
                    <p class="text-xs text-on-surface-variant leading-relaxed">El método de pago se selecciona durante el proceso de compra (checkout). Puedes elegir entre las opciones disponibles según tu preferencia. Para pagos con transferencia, deberás subir el comprobante en el checkout.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
