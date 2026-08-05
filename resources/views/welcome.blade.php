@extends('layouts.cliente')

@section('title', 'PayMe Panamá - Equipos Informáticos & Soluciones Tecnológicas')

@section('content')
<div class="space-y-12 sm:space-y-16 pb-16">

    <!-- ==========================================
         HERO SECTION: TIENDA DE TECNOLOGÍA & EQUIPOS IT
    =========================================== -->
    <section class="relative max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop pt-8 md:pt-14 pb-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
            
            <!-- Left Column: Copy & Actions -->
            <div class="flex flex-col gap-stack-md sm:gap-stack-lg z-10 text-left">
                
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded bg-primary-fixed text-on-primary-fixed text-xs font-semibold w-fit">
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-secondary opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-secondary"></span>
                    </span>
                    <span class="font-label-caps text-[11px] font-bold">🇵🇦 TECNOLOGÍA & EQUIPOS IT EN PANAMÁ</span>
                </div>

                <h1 class="font-headline-md text-3xl sm:text-4xl lg:text-5xl font-bold text-primary tracking-tight leading-tight">
                    Equipos tecnológicos e informática para tu empresa y hogar.
                </h1>

                <p class="font-body-lg text-base sm:text-lg text-on-surface-variant max-w-xl leading-relaxed">
                    Laptops empresariales, accesorios y componentes con garantía local, facturación fiscal <strong class="text-primary font-semibold">ITBMS (7%)</strong> y pagos seguros con <strong class="text-primary font-semibold">Yappy, Tarjetas & ACH</strong>.
                </p>

                <div class="flex flex-wrap gap-3.5 pt-2">
                    <a href="#catalogo" class="bg-primary hover:bg-primary-container text-on-primary font-label-caps text-xs sm:text-sm px-6 py-3.5 rounded shadow-sm hover:opacity-95 transition-all inline-flex items-center gap-2 font-semibold">
                        <span class="material-symbols-outlined text-[18px]">shopping_bag</span>
                        <span>Explorar Catálogo</span>
                    </a>

                    @guest
                        <a href="{{ route('register') }}" class="bg-transparent border border-primary text-primary hover:bg-surface-container font-label-caps text-xs sm:text-sm px-6 py-3.5 rounded transition-colors inline-flex items-center gap-2 font-semibold">
                            <span class="material-symbols-outlined text-[18px]">person_add</span>
                            <span>Crear Cuenta</span>
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="bg-transparent border border-primary text-primary hover:bg-surface-container font-label-caps text-xs sm:text-sm px-6 py-3.5 rounded transition-colors inline-flex items-center gap-2 font-semibold">
                            <span class="material-symbols-outlined text-[18px]">account_circle</span>
                            <span>Ir a Mi Cuenta</span>
                        </a>
                    @endguest
                </div>

                <!-- Trust Badges Small -->
                <div class="pt-3 border-t border-outline-variant/60 flex flex-wrap items-center gap-4 text-xs font-medium text-on-surface-variant">
                    <div class="flex items-center gap-1 text-secondary">
                        <span class="material-symbols-outlined text-base">verified</span>
                        <span class="font-label-caps text-[11px] text-primary">Garantía Local en Panamá</span>
                    </div>
                    <div class="flex items-center gap-1 text-secondary">
                        <span class="material-symbols-outlined text-base">local_shipping</span>
                        <span class="font-label-caps text-[11px] text-primary">Envíos a las 10 Provincias</span>
                    </div>
                </div>

            </div>

            <!-- Right Column: Hero Visual Asset -->
            <div class="relative w-full aspect-video lg:aspect-square rounded-2xl overflow-hidden border border-outline-variant/80 bg-surface-container shadow-xl group">
                <img src="{{ asset('images/hero-tech-collection.png') }}" 
                     alt="Equipos Tecnológicos e Informáticos en Panamá" 
                     class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700 block"/>
                
                <!-- Gradient overlay badge -->
                <div class="absolute bottom-4 left-4 right-4 bg-white/95 backdrop-blur-md p-3.5 sm:p-4 rounded-xl border border-white/60 shadow-lg flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-[#002349] text-[#8af5be] flex items-center justify-center font-bold shadow-xs shrink-0">
                            <span class="material-symbols-outlined text-[20px]">verified</span>
                        </div>
                        <div>
                            <p class="font-headline-md text-xs sm:text-sm font-bold text-primary">Equipos 100% Garantizados</p>
                            <p class="font-label-caps text-[10px] text-gray-500">Garantía local & Factura Fiscal ITBMS (7%)</p>
                        </div>
                    </div>
                    <span class="font-label-caps text-[10px] bg-secondary text-on-secondary px-3 py-1.5 rounded-md font-bold shadow-xs shrink-0">
                        Stock Local 🇵🇦
                    </span>
                </div>
            </div>

        </div>
    </section>

    <!-- ==========================================
         BARRA DE CONFIANZA INSTITUCIONAL (TRUST BAR)
    =========================================== -->
    <section class="bg-surface-container border-y border-outline-variant py-6">
        <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop flex flex-col md:flex-row justify-between items-center gap-4 sm:gap-stack-md">
            
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-secondary text-2xl" style="font-variation-settings: 'FILL' 1;">payments</span>
                <span class="font-label-caps text-xs sm:text-label-caps font-bold text-primary">Pagos con Yappy & ACH</span>
            </div>

            <div class="hidden md:block w-px h-6 bg-outline-variant/60"></div>

            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-secondary text-2xl" style="font-variation-settings: 'FILL' 1;">receipt_long</span>
                <span class="font-label-caps text-xs sm:text-label-caps font-bold text-primary">Facturación Fiscal ITBMS (7%)</span>
            </div>

            <div class="hidden md:block w-px h-6 bg-outline-variant/60"></div>

            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-secondary text-2xl" style="font-variation-settings: 'FILL' 1;">local_shipping</span>
                <span class="font-label-caps text-xs sm:text-label-caps font-bold text-primary">Envíos a las 10 Provincias</span>
            </div>

        </div>
    </section>

    <!-- ==========================================
         BENEFICIO DE BIENVENIDA (ENVÍO GRATIS)
    =========================================== -->
    <section class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="bg-primary text-on-primary rounded-2xl p-5 sm:p-6 flex flex-col md:flex-row items-center justify-between gap-4 text-center md:text-left shadow-lg border border-primary-container">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-xl bg-secondary text-on-secondary flex items-center justify-center shrink-0 shadow-sm">
                    <span class="material-symbols-outlined text-2xl">local_shipping</span>
                </div>
                <div>
                    <h3 class="font-headline-md text-base sm:text-lg font-bold text-white">
                        Envío GRATIS en tu primera compra superior a $35
                    </h3>
                    <p class="font-body-md text-xs sm:text-sm text-primary-fixed mt-0.5">
                        Válido para laptops, accesorios, suministros y periféricos en todo Panamá.
                    </p>
                </div>
            </div>

            <a href="#catalogo" class="bg-secondary hover:bg-[#004f3b] text-on-secondary font-label-caps text-xs sm:text-sm px-6 py-2.5 rounded-xl transition-all shadow-sm font-bold shrink-0 inline-flex items-center gap-2">
                <span>Ver Productos</span>
                <span class="material-symbols-outlined text-base">arrow_forward</span>
            </a>
        </div>
    </section>

    <!-- ==========================================
         PRODUCTOS DESTACADOS (SHOWCASE DE TECNOLOGÍA)
    =========================================== -->
    <section id="catalogo" class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-6">
        
        <div class="mb-8 text-center max-w-xl mx-auto">
            <h2 class="font-headline-md text-2xl sm:text-3xl text-primary font-bold mb-2">
                Equipos & Soluciones Tecnológicas Destacadas
            </h2>
            <p class="font-body-md text-sm text-on-surface-variant">
                Laptops de alta gama, periféricos profesionales y componentes con garantía local directa.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <!-- Product 1: Laptop Empresarial -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-5 flex flex-col hover:shadow-xl hover:border-secondary transition-all duration-300 group">
                <div class="aspect-4/3 bg-white border border-gray-100 rounded-xl mb-4 overflow-hidden relative flex items-center justify-center p-3">
                    <img alt="Laptop Empresarial 15.6 Core i5" class="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform duration-500" src="{{ asset('images/products/laptop-hp.png') }}"/>
                    <span class="absolute top-2.5 left-2.5 bg-primary text-on-primary font-label-caps text-[10px] font-bold px-2 py-0.5 rounded shadow-xs">
                        Más Vendido
                    </span>
                </div>
                <h3 class="font-headline-md text-primary text-base font-bold mb-1 group-hover:text-secondary transition-colors">
                    Laptop Empresarial 15.6" (Core i5 / 16GB / 512GB SSD)
                </h3>
                <p class="font-body-md text-xs text-on-surface-variant mb-4 flex-grow line-clamp-2">
                    Potencia y portabilidad para teletrabajo, contabilidad y oficinas. Garantía local de 1 año con soporte directo en Panamá.
                </p>
                <div class="flex justify-between items-center pt-3 border-t border-outline-variant/40">
                    <div>
                        <span class="font-numeric-data text-lg text-primary font-bold block">$525.00</span>
                        <span class="font-label-caps text-[9px] text-gray-400">+ ITBMS (7%)</span>
                    </div>
                    <button type="button" onclick="addToCart('Laptop Empresarial 15.6', 525.00)" class="bg-secondary text-on-secondary px-3.5 py-2 rounded-lg font-label-caps text-xs hover:opacity-90 transition-opacity flex items-center gap-1 shadow-xs font-semibold">
                        <span class="material-symbols-outlined text-[16px]">add_shopping_cart</span>
                        <span>Comprar</span>
                    </button>
                </div>
            </div>

            <!-- Product 2: Impresora EcoTank -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-5 flex flex-col hover:shadow-xl hover:border-secondary transition-all duration-300 group">
                <div class="aspect-4/3 bg-white border border-gray-100 rounded-xl mb-4 overflow-hidden relative flex items-center justify-center p-3">
                    <img alt="Impresora Multifuncional EcoTank WiFi" class="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform duration-500" src="{{ asset('images/products/impresora-ecotank.png') }}"/>
                    <span class="absolute top-2.5 left-2.5 bg-emerald-600 text-white font-label-caps text-[10px] font-bold px-2 py-0.5 rounded shadow-xs">
                        Ahorro Oficina
                    </span>
                </div>
                <h3 class="font-headline-md text-primary text-base font-bold mb-1 group-hover:text-secondary transition-colors">
                    Impresora Multifuncional EcoTank WiFi
                </h3>
                <p class="font-body-md text-xs text-on-surface-variant mb-4 flex-grow line-clamp-2">
                    Sistema continuo de tinta con impresión inalámbrica y escáner. Miles de páginas a costo ultra bajo para tu negocio.
                </p>
                <div class="flex justify-between items-center pt-3 border-t border-outline-variant/40">
                    <div>
                        <span class="font-numeric-data text-lg text-primary font-bold block">$195.00</span>
                        <span class="font-label-caps text-[9px] text-gray-400">+ ITBMS (7%)</span>
                    </div>
                    <button type="button" onclick="addToCart('Impresora Multifuncional EcoTank', 195.00)" class="bg-secondary text-on-secondary px-3.5 py-2 rounded-lg font-label-caps text-xs hover:opacity-90 transition-opacity flex items-center gap-1 shadow-xs font-semibold">
                        <span class="material-symbols-outlined text-[16px]">add_shopping_cart</span>
                        <span>Comprar</span>
                    </button>
                </div>
            </div>

            <!-- Product 3: Monitor Profesional 27" -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-5 flex flex-col hover:shadow-xl hover:border-secondary transition-all duration-300 group">
                <div class="aspect-4/3 bg-white border border-gray-100 rounded-xl mb-4 overflow-hidden relative flex items-center justify-center p-3">
                    <img alt="Monitor Profesional 27 Full HD IPS" class="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform duration-500" src="{{ asset('images/products/monitor-ips.png') }}"/>
                    <span class="absolute top-2.5 left-2.5 bg-blue-600 text-white font-label-caps text-[10px] font-bold px-2 py-0.5 rounded shadow-xs">
                        Garantía Local
                    </span>
                </div>
                <h3 class="font-headline-md text-primary text-base font-bold mb-1 group-hover:text-secondary transition-colors">
                    Monitor Profesional 27" IPS Full HD HDMI
                </h3>
                <p class="font-body-md text-xs text-on-surface-variant mb-4 flex-grow line-clamp-2">
                    Pantalla ultradelgada antirreflejo con base ergonómica y protección ocular para máxima productividad diaria.
                </p>
                <div class="flex justify-between items-center pt-3 border-t border-outline-variant/40">
                    <div>
                        <span class="font-numeric-data text-lg text-primary font-bold block">$139.00</span>
                        <span class="font-label-caps text-[9px] text-gray-400">+ ITBMS (7%)</span>
                    </div>
                    <button type="button" onclick="addToCart('Monitor Profesional 27 IPS', 139.00)" class="bg-secondary text-on-secondary px-3.5 py-2 rounded-lg font-label-caps text-xs hover:opacity-90 transition-opacity flex items-center gap-1 shadow-xs font-semibold">
                        <span class="material-symbols-outlined text-[16px]">add_shopping_cart</span>
                        <span>Comprar</span>
                    </button>
                </div>
            </div>

            <!-- Product 4: Combo Teclado & Mouse Inalámbrico -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-5 flex flex-col hover:shadow-xl hover:border-secondary transition-all duration-300 group">
                <div class="aspect-4/3 bg-white border border-gray-100 rounded-xl mb-4 overflow-hidden relative flex items-center justify-center p-3">
                    <img alt="Combo Teclado y Mouse Inalámbrico" class="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform duration-500" src="{{ asset('images/products/combo-teclado-mouse.png') }}"/>
                    <span class="absolute top-2.5 left-2.5 bg-secondary text-on-secondary font-label-caps text-[10px] font-bold px-2 py-0.5 rounded shadow-xs">
                        Envío Rápido
                    </span>
                </div>
                <h3 class="font-headline-md text-primary text-base font-bold mb-1 group-hover:text-secondary transition-colors">
                    Combo Teclado & Mouse Inalámbrico
                </h3>
                <p class="font-body-md text-xs text-on-surface-variant mb-4 flex-grow line-clamp-2">
                    Conexión confiable de 2.4GHz con receptor nano USB, diseño silencioso y durabilidad garantizada para oficinas.
                </p>
                <div class="flex justify-between items-center pt-3 border-t border-outline-variant/40">
                    <div>
                        <span class="font-numeric-data text-lg text-primary font-bold block">$32.00</span>
                        <span class="font-label-caps text-[9px] text-gray-400">+ ITBMS (7%)</span>
                    </div>
                    <button type="button" onclick="addToCart('Combo Teclado y Mouse', 32.00)" class="bg-secondary text-on-secondary px-3.5 py-2 rounded-lg font-label-caps text-xs hover:opacity-90 transition-opacity flex items-center gap-1 shadow-xs font-semibold">
                        <span class="material-symbols-outlined text-[16px]">add_shopping_cart</span>
                        <span>Comprar</span>
                    </button>
                </div>
            </div>

            <!-- Product 5: Disco Sólido SSD 1TB -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-5 flex flex-col hover:shadow-xl hover:border-secondary transition-all duration-300 group">
                <div class="aspect-4/3 bg-white border border-gray-100 rounded-xl mb-4 overflow-hidden relative flex items-center justify-center p-3">
                    <img alt="Disco Sólido Externo SSD 1TB USB-C" class="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform duration-500" src="{{ asset('images/products/disco-ssd.png') }}"/>
                    <span class="absolute top-2.5 left-2.5 bg-purple-700 text-white font-label-caps text-[10px] font-bold px-2 py-0.5 rounded shadow-xs">
                        Top Almacenamiento
                    </span>
                </div>
                <h3 class="font-headline-md text-primary text-base font-bold mb-1 group-hover:text-secondary transition-colors">
                    Disco Sólido Externo SSD 1TB USB-C
                </h3>
                <p class="font-body-md text-xs text-on-surface-variant mb-4 flex-grow line-clamp-2">
                    Velocidades de transferencia de hasta 1050 MB/s con carcasa resistente. Respaldos inmediatos y seguros para tu negocio.
                </p>
                <div class="flex justify-between items-center pt-3 border-t border-outline-variant/40">
                    <div>
                        <span class="font-numeric-data text-lg text-primary font-bold block">$79.00</span>
                        <span class="font-label-caps text-[9px] text-gray-400">+ ITBMS (7%)</span>
                    </div>
                    <button type="button" onclick="addToCart('Disco Solido Externo SSD 1TB', 79.00)" class="bg-secondary text-on-secondary px-3.5 py-2 rounded-lg font-label-caps text-xs hover:opacity-90 transition-opacity flex items-center gap-1 shadow-xs font-semibold">
                        <span class="material-symbols-outlined text-[16px]">add_shopping_cart</span>
                        <span>Comprar</span>
                    </button>
                </div>
            </div>

            <!-- Product 6: Router Gigabit WiFi 6 -->
            <div class="bg-surface-container-lowest border border-outline-variant rounded-2xl p-5 flex flex-col hover:shadow-xl hover:border-secondary transition-all duration-300 group">
                <div class="aspect-4/3 bg-white border border-gray-100 rounded-xl mb-4 overflow-hidden relative flex items-center justify-center p-3">
                    <img alt="Router Gigabit Doble Banda WiFi 6" class="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform duration-500" src="{{ asset('images/products/router-wifi.png') }}"/>
                    <span class="absolute top-2.5 left-2.5 bg-amber-600 text-white font-label-caps text-[10px] font-bold px-2 py-0.5 rounded shadow-xs">
                        Redes & Conectividad
                    </span>
                </div>
                <h3 class="font-headline-md text-primary text-base font-bold mb-1 group-hover:text-secondary transition-colors">
                    Router Gigabit Doble Banda WiFi 6
                </h3>
                <p class="font-body-md text-xs text-on-surface-variant mb-4 flex-grow line-clamp-2">
                    Cobertura extendida para conectar múltiples equipos, impresoras en red y cajas registradoras con cero interrupciones.
                </p>
                <div class="flex justify-between items-center pt-3 border-t border-outline-variant/40">
                    <div>
                        <span class="font-numeric-data text-lg text-primary font-bold block">$58.00</span>
                        <span class="font-label-caps text-[9px] text-gray-400">+ ITBMS (7%)</span>
                    </div>
                    <button type="button" onclick="addToCart('Router Gigabit WiFi 6', 58.00)" class="bg-secondary text-on-secondary px-3.5 py-2 rounded-lg font-label-caps text-xs hover:opacity-90 transition-opacity flex items-center gap-1 shadow-xs font-semibold">
                        <span class="material-symbols-outlined text-[16px]">add_shopping_cart</span>
                        <span>Comprar</span>
                    </button>
                </div>
            </div>

        </div>
    </section>

    <!-- ==========================================
         PROCESO FÁCIL & TRANSPARENTE
    =========================================== -->
    <section class="bg-surface-container-low py-14 border-y border-outline-variant/60">
        <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
            <h2 class="font-headline-md text-primary text-center mb-10 text-2xl sm:text-3xl font-bold">
                Proceso Fácil & Transparente
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <div class="text-center flex flex-col items-center p-6 bg-surface-container-lowest rounded-2xl border border-outline-variant/60">
                    <div class="w-16 h-16 bg-primary-fixed rounded-2xl flex items-center justify-center mb-4 text-primary shadow-xs">
                        <span class="material-symbols-outlined text-3xl">laptop_chromebook</span>
                    </div>
                    <h3 class="font-headline-md text-primary text-lg font-bold mb-2">1. Elige tus Equipos</h3>
                    <p class="font-body-md text-xs sm:text-sm text-on-surface-variant leading-relaxed">
                        Explora nuestro catálogo de tecnología garantizada y añade a tu orden con un solo clic.
                    </p>
                </div>

                <div class="text-center flex flex-col items-center p-6 bg-surface-container-lowest rounded-2xl border border-outline-variant/60">
                    <div class="w-16 h-16 bg-primary-fixed rounded-2xl flex items-center justify-center mb-4 text-primary shadow-xs">
                        <span class="material-symbols-outlined text-3xl">credit_card</span>
                    </div>
                    <h3 class="font-headline-md text-primary text-lg font-bold mb-2">2. Paga con Yappy, ACH o Tarjeta</h3>
                    <p class="font-body-md text-xs sm:text-sm text-on-surface-variant leading-relaxed">
                        Transacciones seguras y al instante con bancos locales y confirmación inmediata.
                    </p>
                </div>

                <div class="text-center flex flex-col items-center p-6 bg-surface-container-lowest rounded-2xl border border-outline-variant/60">
                    <div class="w-16 h-16 bg-primary-fixed rounded-2xl flex items-center justify-center mb-4 text-primary shadow-xs">
                        <span class="material-symbols-outlined text-3xl">local_shipping</span>
                    </div>
                    <h3 class="font-headline-md text-primary text-lg font-bold mb-2">3. Recibe con Garantía Local</h3>
                    <p class="font-body-md text-xs sm:text-sm text-on-surface-variant leading-relaxed">
                        Envíos rápidos y asegurados a cualquier provincia de Panamá con factura fiscal.
                    </p>
                </div>

            </div>
        </div>
    </section>

    <!-- ==========================================
         TESTIMONIOS DE CLIENTES
    =========================================== -->
    <section class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-12">
        <h2 class="font-headline-md text-primary text-center mb-10 text-2xl sm:text-3xl font-bold">
            Lo que dicen nuestros clientes empresariales y particulares
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <div class="bg-surface-container-lowest p-6 rounded-2xl shadow-xs border border-outline-variant flex flex-col justify-between">
                <p class="font-body-md text-xs sm:text-sm text-on-surface-variant italic leading-relaxed">
                    "Excelente servicio, compré una laptop de alto rendimiento para mi oficina y el envío llegó al día siguiente. Muy cómodo pagar directo con Yappy."
                </p>
                <div class="flex items-center gap-3.5 mt-5 pt-4 border-t border-outline-variant/40">
                    <div class="w-10 h-10 rounded-full bg-secondary-container flex items-center justify-center text-secondary font-bold text-sm">
                        MR
                    </div>
                    <div>
                        <p class="font-headline-md text-xs font-bold text-primary">Mariana Rodríguez</p>
                        <p class="font-label-caps text-[10px] text-on-surface-variant">Gerente de Operaciones • Panamá</p>
                    </div>
                </div>
            </div>

            <div class="bg-surface-container-lowest p-6 rounded-2xl shadow-xs border border-outline-variant flex flex-col justify-between">
                <p class="font-body-md text-xs sm:text-sm text-on-surface-variant italic leading-relaxed">
                    "Adquirimos los periféricos y suministros informáticos para nuestro equipo de desarrollo. La garantía local y el soporte nos dieron total tranquilidad."
                </p>
                <div class="flex items-center gap-3.5 mt-5 pt-4 border-t border-outline-variant/40">
                    <div class="w-10 h-10 rounded-full bg-secondary-container flex items-center justify-center text-secondary font-bold text-sm">
                        CG
                    </div>
                    <div>
                        <p class="font-headline-md text-xs font-bold text-primary">Carlos González</p>
                        <p class="font-label-caps text-[10px] text-on-surface-variant">Coordinador de IT • Herrera</p>
                    </div>
                </div>
            </div>

            <div class="bg-surface-container-lowest p-6 rounded-2xl shadow-xs border border-outline-variant flex flex-col justify-between">
                <p class="font-body-md text-xs sm:text-sm text-on-surface-variant italic leading-relaxed">
                    "Equipamos toda nuestra estación de trabajo a través de la plataforma. La facturación con desglose de ITBMS fue inmediata y exacta para la contabilidad."
                </p>
                <div class="flex items-center gap-3.5 mt-5 pt-4 border-t border-outline-variant/40">
                    <div class="w-10 h-10 rounded-full bg-secondary-container flex items-center justify-center text-secondary font-bold text-sm">
                        LD
                    </div>
                    <div>
                        <p class="font-headline-md text-xs font-bold text-primary">Luis Alberto De Gracia</p>
                        <p class="font-label-caps text-[10px] text-on-surface-variant">Director de Finanzas • Costa del Este</p>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- ==========================================
         PREGUNTAS FRECUENTES (FAQ)
    =========================================== -->
    <section class="max-w-3xl mx-auto px-margin-mobile md:px-margin-desktop py-8">
        <h2 class="font-headline-md text-primary text-center mb-8 text-2xl sm:text-3xl font-bold">
            Preguntas Frecuentes (FAQ)
        </h2>
        
        <div class="space-y-3.5">
            
            <details class="bg-surface-container-lowest border border-outline-variant rounded-xl group transition-all">
                <summary class="font-headline-md text-xs sm:text-sm font-bold text-primary p-4 cursor-pointer flex justify-between items-center list-none">
                    <span>¿Puedo adquirir equipos y pagar con Yappy o ACH como empresa?</span>
                    <span class="material-symbols-outlined transition-transform group-open:rotate-180 text-gray-400">expand_more</span>
                </summary>
                <div class="p-4 pt-0 font-body-md text-xs sm:text-sm text-on-surface-variant border-t border-outline-variant mt-2 leading-relaxed">
                    Sí, aceptamos Yappy Comercial, Tarjetas Visa/Mastercard y transferencias bancarias directas ACH con emisión de factura fiscal detallando el ITBMS (7%).
                </div>
            </details>

            <details class="bg-surface-container-lowest border border-outline-variant rounded-xl group transition-all">
                <summary class="font-headline-md text-xs sm:text-sm font-bold text-primary p-4 cursor-pointer flex justify-between items-center list-none">
                    <span>¿Cuánto tardan los envíos al interior del país?</span>
                    <span class="material-symbols-outlined transition-transform group-open:rotate-180 text-gray-400">expand_more</span>
                </summary>
                <div class="p-4 pt-0 font-body-md text-xs sm:text-sm text-on-surface-variant border-t border-outline-variant mt-2 leading-relaxed">
                    Los envíos a provincias centrales tardan de 24 a 48 horas, y a Chiriquí o Bocas del Toro hasta 72 horas hábiles con número de seguimiento y empaque de protección.
                </div>
            </details>

            <details class="bg-surface-container-lowest border border-outline-variant rounded-xl group transition-all">
                <summary class="font-headline-md text-xs sm:text-sm font-bold text-primary p-4 cursor-pointer flex justify-between items-center list-none">
                    <span>¿Los equipos cuentan con garantía local en Panamá?</span>
                    <span class="material-symbols-outlined transition-transform group-open:rotate-180 text-gray-400">expand_more</span>
                </summary>
                <div class="p-4 pt-0 font-body-md text-xs sm:text-sm text-on-surface-variant border-t border-outline-variant mt-2 leading-relaxed">
                    Todos los equipos informáticos, laptops, componentes y periféricos cuentan con garantía local respaldada directamente en Panamá.
                </div>
            </details>

        </div>
    </section>

    <!-- ==========================================
         CALL TO ACTION FINAL
    =========================================== -->
    <section class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="bg-primary text-on-primary py-12 sm:py-16 px-6 sm:px-12 rounded-3xl text-center shadow-xl relative overflow-hidden">
            <div class="max-w-2xl mx-auto space-y-4">
                <h2 class="font-headline-md text-2xl sm:text-4xl font-bold text-white tracking-tight">
                    Equipa tu negocio con tecnología confiable
                </h2>
                <p class="font-body-lg text-xs sm:text-sm text-primary-fixed leading-relaxed">
                    Encuentra las mejores marcas de computación, accesorios y servicios informáticos con el respaldo, rapidez y seguridad de PayMe Panamá.
                </p>
                <div class="pt-3 flex flex-wrap justify-center gap-3.5">
                    @guest
                        <a href="{{ route('register') }}" class="bg-secondary hover:bg-[#004f3b] text-on-secondary font-label-caps text-xs sm:text-sm px-8 py-3.5 rounded-xl shadow-md hover:scale-105 transition-all font-bold">
                            Crear Cuenta de Cliente
                        </a>
                        <a href="{{ route('login') }}" class="bg-transparent border border-on-primary text-on-primary hover:bg-white/10 font-label-caps text-xs sm:text-sm px-8 py-3.5 rounded-xl transition-colors font-semibold">
                            Ingresar
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="bg-secondary hover:bg-[#004f3b] text-on-secondary font-label-caps text-xs sm:text-sm px-8 py-3.5 rounded-xl shadow-md hover:scale-105 transition-all font-bold">
                            Ir a Mi Panel de Control
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </section>

</div>

<!-- Toast notification popup -->
<div id="toast-box" class="fixed bottom-24 left-1/2 -translate-x-1/2 z-50 bg-[#002349] text-white px-5 py-3 rounded-xl shadow-2xl border border-white/20 hidden items-center gap-3 text-xs font-semibold">
    <span class="material-symbols-outlined text-secondary-container text-lg" style="font-variation-settings: 'FILL' 1;">check_circle</span>
    <span id="toast-text">Acción completada</span>
</div>
@endsection

@push('scripts')
<script>
    function showToast(msg) {
        const box = document.getElementById('toast-box');
        const text = document.getElementById('toast-text');
        text.textContent = msg;
        box.classList.remove('hidden');
        box.classList.add('flex');
        setTimeout(() => {
            box.classList.add('hidden');
            box.classList.remove('flex');
        }, 3000);
    }

    function addToCart(name, price) {
        showToast(`¡"${name}" ($${price.toFixed(2)}) añadido a tu orden!`);
    }
</script>
@endpush
