@extends('layouts.admin')

@section('title', 'Carrito de Compras — PayMe Panamá')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto">

    <!-- Header & Breadcrumb -->
    <div class="flex items-center justify-between bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('inicio') }}" class="hover:text-emerald-600 transition-colors">Tienda</a>
                <span>&rsaquo;</span>
                <span class="text-slate-800 font-bold">Carrito de Compras</span>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-600 text-[24px]">shopping_cart</span>
                Tu Carrito de Compras
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Revisa tus productos seleccionados y aplica tus cupones de descuento.</p>
        </div>
    </div>

    <!-- Main Grid: Items Left (col-span 7), Summary Right (col-span 5) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Column: Items List (7 cols) -->
        <div class="lg:col-span-7 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-xs font-bold text-slate-900 uppercase tracking-wider">Productos en Carrito</h2>
                    <span class="text-xs font-semibold text-slate-500">2 artículos</span>
                </div>

                <!-- Sample Item 1 -->
                <div class="flex items-center gap-4 py-3 border-b border-slate-100 last:border-0">
                    <div class="w-16 h-16 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center shrink-0 overflow-hidden">
                        <span class="material-symbols-outlined text-slate-400 text-[28px]">laptop_mac</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <span class="text-xs font-bold text-slate-900 block truncate">Laptop Lenovo ThinkPad L14 Gen 4</span>
                        <span class="text-[10px] text-slate-400 block font-mono">SKU: LNV-TP-L14</span>
                        <span class="text-xs font-extrabold text-slate-900 block mt-1">$799.00</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 font-bold flex items-center justify-center text-xs">-</button>
                        <span class="text-xs font-bold px-1">1</span>
                        <button type="button" class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 font-bold flex items-center justify-center text-xs">+</button>
                    </div>
                </div>

                <!-- Sample Item 2 -->
                <div class="flex items-center gap-4 py-3">
                    <div class="w-16 h-16 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center shrink-0 overflow-hidden">
                        <span class="material-symbols-outlined text-slate-400 text-[28px]">headphones</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <span class="text-xs font-bold text-slate-900 block truncate">Audífonos Inalámbricos JBL Tune 510BT</span>
                        <span class="text-[10px] text-slate-400 block font-mono">SKU: JBL-T510-BLK</span>
                        <span class="text-xs font-extrabold text-slate-900 block mt-1">$49.99</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 font-bold flex items-center justify-center text-xs">-</button>
                        <span class="text-xs font-bold px-1">1</span>
                        <button type="button" class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 font-bold flex items-center justify-center text-xs">+</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Summary & Coupon Box (5 cols) -->
        <div class="lg:col-span-5 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs space-y-4 sticky top-20">
                <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                    <span class="material-symbols-outlined text-emerald-600 text-[18px]">receipt_long</span>
                    Resumen del Pedido
                </h2>

                <!-- Cupon Box: "¿Tienes un cupón?" -->
                <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-2">
                    <label for="codigo_cupon_input" class="block text-[11px] font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-emerald-600 text-[16px]">local_offer</span>
                        ¿Tienes un cupón?
                    </label>

                    @php
                        $cuponAplicado = session('cupon_aplicado');
                        $subtotalCarrito = 848.99;
                        $descuentoMonto = $cuponAplicado ? (float) $cuponAplicado['descuento'] : 0.0;
                        $totalFinal = max(0.0, $subtotalCarrito - $descuentoMonto);
                    @endphp

                    @if($cuponAplicado)
                        <!-- Badge Cupón Aplicado -->
                        <div class="flex items-center justify-between p-2.5 bg-emerald-100/70 border border-emerald-300 rounded-xl text-xs font-bold text-emerald-900">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="material-symbols-outlined text-emerald-700 text-[18px]">check_circle</span>
                                <div class="truncate">
                                    <span class="font-mono tracking-wider font-extrabold uppercase">{{ $cuponAplicado['codigo'] }}</span>
                                    <span class="text-[10px] text-emerald-700 block font-semibold">Descuento aplicado: -${{ number_format($descuentoMonto, 2) }}</span>
                                </div>
                            </div>
                            <form action="{{ route('cliente.carrito.remover-cupon') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-emerald-800 hover:text-rose-600 p-1 rounded-lg hover:bg-emerald-200 transition-colors" title="Remover cupón">
                                    <span class="material-symbols-outlined text-[16px]">close</span>
                                </button>
                            </form>
                        </div>
                    @else
                        <!-- Formulario de Aplicación -->
                        <form action="{{ route('cliente.carrito.aplicar-cupon') }}" method="POST" class="flex gap-2">
                            @csrf
                            <input type="hidden" name="subtotal" value="{{ $subtotalCarrito }}">
                            <input type="text" 
                                   id="codigo_cupon_input" 
                                   name="codigo" 
                                   placeholder="Ingresa tu código..." 
                                   required 
                                   class="flex-1 bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-mono font-bold uppercase text-slate-900 focus:border-emerald-500 outline-none">
                            <button type="submit" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition-colors shrink-0">
                                Aplicar
                            </button>
                        </form>
                    @endif
                </div>

                <!-- Calculation Breakdown -->
                <div class="space-y-2.5 text-xs font-medium text-slate-600 border-t border-slate-100 pt-3">
                    <div class="flex items-center justify-between">
                        <span>Subtotal:</span>
                        <span class="font-bold text-slate-900">${{ number_format($subtotalCarrito, 2) }}</span>
                    </div>

                    @if($cuponAplicado)
                        <div class="flex items-center justify-between text-emerald-600 font-bold">
                            <span>Descuento (Cupón {{ $cuponAplicado['codigo'] }}):</span>
                            <span>-${{ number_format($descuentoMonto, 2) }}</span>
                        </div>
                    @endif

                    <div class="flex items-center justify-between text-slate-500">
                        <span>Envío Estimado:</span>
                        <span class="text-emerald-700 font-bold">¡Gratis en compras min.!</span>
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-200 pt-3 text-sm font-extrabold text-slate-900">
                        <span>Total del Pedido:</span>
                        <span class="text-xl text-emerald-700">${{ number_format($totalFinal, 2) }}</span>
                    </div>
                </div>

                <!-- Checkout CTA -->
                <button type="button" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-xs transition-all flex items-center justify-center gap-2">
                    <span>Proceder al Pago</span>
                    <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                </button>
            </div>
        </div>

    </div>

</div>
@endsection
