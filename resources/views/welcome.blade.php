@extends('layouts.cliente')

@section('title', 'PayMe Panamá - Equipos Informáticos & Soluciones Tecnológicas')

@section('content')
<div class="space-y-12 sm:space-y-16 pb-16">

    <!-- ==========================================
         HERO SECTION: TIENDA DE TECNOLOGÍA & EQUIPOS IT
    =========================================== -->
    <section class="relative max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop pt-4 md:pt-8 pb-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
            
            <!-- Left Column: Copy & Actions -->
            <div class="flex flex-col gap-stack-md sm:gap-stack-lg z-10 text-left">

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
            <div class="flex justify-center lg:justify-end">
                <div class="relative w-full max-w-md lg:max-w-[460px] aspect-[4/3] rounded-2xl overflow-hidden border border-outline-variant/80 bg-surface-container shadow-lg group">
                    <img src="{{ asset('images/hero-tech-collection.png') }}" 
                         alt="Equipos Tecnológicos e Informáticos en Panamá" 
                         class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700 block"/>
                    
                    <!-- Floating overlay badge -->
                    <div class="absolute bottom-3 left-3 right-3 bg-white/95 backdrop-blur-md p-3 rounded-xl border border-white/60 shadow-md flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-[#002349] text-[#8af5be] flex items-center justify-center font-bold shadow-xs shrink-0">
                                <span class="material-symbols-outlined text-[18px]">verified</span>
                            </div>
                            <div>
                                <p class="font-headline-md text-xs font-bold text-primary">Equipos Garantizados</p>
                                <p class="font-label-caps text-[9px] text-gray-500">Garantía local & Factura Fiscal</p>
                            </div>
                        </div>
                        <span class="font-label-caps text-[9px] bg-secondary text-on-secondary px-2.5 py-1 rounded font-bold shadow-xs shrink-0">
                            Stock Local
                        </span>
                    </div>
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
         CARRUSEL DE MARCAS (MANUAL)
    =========================================== -->
    <section class="py-10 bg-white border-b border-outline-variant/40">
        <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">

            <p class="font-label-caps text-xs font-bold text-on-surface-variant tracking-widest uppercase text-center mb-7">Marcas que distribuimos</p>

            <div class="brands-slider-outer">
                <button class="brands-arrow brands-arrow-left" id="brandsPrev" aria-label="Anterior">
                    <span class="material-symbols-outlined">chevron_left</span>
                </button>

                <div class="brands-viewport" id="brandsViewport">
                    <div class="brands-track-manual" id="brandsTrack">
                        @foreach ([
                            'image.webp', 'image (1).webp', 'image (2).webp', 'image (3).webp',
                            'image (4).webp', 'image (5).webp', 'image (6).webp', 'image (7).webp',
                            'image (8).webp', 'image (9).webp', 'image (10).webp', 'image (11).webp',
                            'image (12).webp', 'image (13).webp', 'image (14).webp', 'image (15).webp',
                            'image (16).webp'
                        ] as $brand)
                        <div class="brand-logo-card-m">
                            <img src="{{ asset('images/Marcas/' . $brand) }}" alt="Marca tecnológica" loading="lazy" class="brand-logo-img-m"/>
                        </div>
                        @endforeach
                    </div>
                </div>

                <button class="brands-arrow brands-arrow-right" id="brandsNext" aria-label="Siguiente">
                    <span class="material-symbols-outlined">chevron_right</span>
                </button>
            </div>

        </div>
    </section>

    <style>
        .brands-slider-outer { position:relative; display:flex; align-items:center; gap:12px; }
        .brands-viewport     { overflow:hidden; flex:1; min-width:0; }
        .brands-track-manual { display:flex; align-items:center; gap:20px; transition:transform 0.45s cubic-bezier(.25,.46,.45,.94); will-change:transform; }
        .brand-logo-card-m   { flex-shrink:0; width:160px; height:90px; background:#fff; border:1.5px solid #e5e7eb; border-radius:14px; display:flex; align-items:center; justify-content:center; padding:14px 18px; box-shadow:0 2px 8px rgba(0,0,0,.06); transition:box-shadow .25s ease,border-color .25s ease,transform .25s ease; cursor:default; }
        .brand-logo-card-m:hover { box-shadow:0 6px 20px rgba(0,0,0,.11); border-color:#94a3b8; transform:translateY(-3px); }
        .brand-logo-img-m    { max-width:100%; max-height:100%; width:auto; height:auto; object-fit:contain; display:block; }
        .brands-arrow        { flex-shrink:0; width:44px; height:44px; border-radius:50%; border:1.5px solid #e2e8f0; background:#fff; color:#1e293b; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 2px 8px rgba(0,0,0,.08); transition:background .2s,box-shadow .2s,border-color .2s,transform .15s; z-index:10; }
        .brands-arrow:hover  { background:#f8fafc; border-color:#94a3b8; box-shadow:0 4px 14px rgba(0,0,0,.12); transform:scale(1.07); }
        .brands-arrow:active { transform:scale(0.96); }
        .brands-arrow:disabled { opacity:.35; cursor:not-allowed; transform:none; }
        .brands-arrow .material-symbols-outlined { font-size:22px; line-height:1; }
    </style>

    <script>
        (function () {
            const track = document.getElementById('brandsTrack');
            const vp    = document.getElementById('brandsViewport');
            const prev  = document.getElementById('brandsPrev');
            const next  = document.getElementById('brandsNext');
            if (!track || !prev || !next) return;
            const GAP = 20, CW = 160, STEP = CW + GAP;
            const total = track.querySelectorAll('.brand-logo-card-m').length;
            let cur = 0;
            const vis = () => Math.floor((vp.offsetWidth + GAP) / STEP) || 1;
            const max = () => Math.max(0, total - vis());
            const upd = (a = true) => {
                if (!a) track.style.transition = 'none';
                track.style.transform = `translateX(-${cur * STEP}px)`;
                if (!a) requestAnimationFrame(() => { track.style.transition = ''; });
                prev.disabled = cur === 0;
                next.disabled = cur >= max();
            };
            prev.addEventListener('click', () => { if (cur > 0)      { cur--; upd(); } });
            next.addEventListener('click', () => { if (cur < max())  { cur++; upd(); } });
            window.addEventListener('resize', () => { cur = Math.min(cur, max()); upd(false); });
            upd(false);
        })();
    </script>

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
         CUPÓN DE BIENVENIDA (PREMIUM)
    =========================================== -->
    <section class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-4 sm:py-6">
        <div class="relative overflow-hidden rounded-2xl shadow-lg border border-white/[0.06]" 
             style="background: linear-gradient(135deg, #0a1628 0%, #0d2140 45%, #052318 100%);">
            
            <!-- Subtle noise/grain overlay -->
            <div class="absolute inset-0 opacity-[0.03]" style="background-image: url(&quot;data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E&quot;); background-size: 128px;"></div>
            
            <!-- Accent glow blobs -->
            <div class="absolute top-0 right-0 w-72 h-72 rounded-full opacity-10 pointer-events-none" style="background: radial-gradient(circle, #34d399 0%, transparent 70%); transform: translate(30%, -40%);"></div>
            <div class="absolute bottom-0 left-0 w-48 h-48 rounded-full opacity-10 pointer-events-none" style="background: radial-gradient(circle, #60a5fa 0%, transparent 70%); transform: translate(-30%, 40%);"></div>

            <!-- Left accent border -->
            <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l-2xl" style="background: linear-gradient(to bottom, #34d399, #10b981, #059669);"></div>

            <!-- Content -->
            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-5 px-6 sm:px-8 py-5 md:py-4">
                
                <!-- Left: Icon + Text -->
                <div class="flex items-center gap-4 text-center md:text-left">
                    <div class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0"
                         style="background: linear-gradient(135deg, rgba(52,211,153,0.2), rgba(16,185,129,0.08)); border: 1px solid rgba(52,211,153,0.25);">
                        <span class="material-symbols-outlined text-emerald-400" style="font-size:22px; font-variation-settings: 'FILL' 1;">local_activity</span>
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <span class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-300/90">
                                Cupón de Bienvenida
                            </span>
                            <span class="hidden sm:flex items-center gap-1 text-[10px] font-medium text-slate-400">
                                <span class="w-0.5 h-0.5 rounded-full bg-slate-500 inline-block"></span>
                                Por tiempo limitado · 10 Provincias
                            </span>
                        </div>
                        <h3 class="text-sm sm:text-base font-bold text-white tracking-tight leading-snug">
                            Envío Gratis en tu Primera Compra
                        </h3>
                        <p class="text-[11px] text-slate-400 mt-0.5 leading-relaxed">
                            Laptops, periféricos y accesorios informáticos en todo Panamá.
                        </p>
                    </div>
                </div>

                <!-- Right: Code + Copy -->
                <div class="flex items-stretch gap-0 shrink-0 rounded-xl overflow-hidden border border-white/[0.1] shadow-inner"
                     style="background: rgba(0,0,0,0.35);">
                    <div class="px-4 py-2.5 flex flex-col justify-center border-r border-white/[0.08]">
                        <span class="text-[9px] uppercase tracking-widest font-bold text-slate-500 leading-none mb-1">Código</span>
                        <span class="font-mono text-xs sm:text-sm font-extrabold text-emerald-300 tracking-widest select-all leading-tight">
                            ENVIOGRATIS-PTY
                        </span>
                    </div>
                    <button type="button"
                            onclick="copyCouponCode('ENVIOGRATIS-PTY')"
                            class="px-4 py-2.5 flex flex-col items-center justify-center gap-0.5 text-slate-300 hover:text-white hover:bg-white/[0.06] active:bg-emerald-500/20 transition-all cursor-pointer group">
                        <span class="material-symbols-outlined text-[18px] group-hover:scale-110 transition-transform">content_copy</span>
                        <span class="text-[9px] font-semibold uppercase tracking-wider">Copiar</span>
                    </button>
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

    function copyCouponCode(code) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(code).then(() => {
                showToast(`¡Cupón "${code}" copiado al portapapeles!`);
            }).catch(() => {
                showToast(`¡Cupón "${code}" listo para usar!`);
            });
        } else {
            showToast(`¡Cupón "${code}" listo para usar!`);
        }
    }
</script>
@endpush
