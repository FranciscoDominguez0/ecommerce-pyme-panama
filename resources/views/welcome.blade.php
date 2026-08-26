@extends('layouts.cliente')

@section('title', 'PayMe Panamá - Equipos Informáticos & Soluciones Tecnológicas')

@section('content')
    <div class="space-y-12 sm:space-y-16 pb-16">

        <!-- ==========================================
             HERO SECTION: DARK TECH STYLE
        =========================================== -->
        <section class="relative w-full overflow-hidden"
            style="background: linear-gradient(135deg, #060d18 0%, #0b1628 40%, #091a10 100%); min-height: 420px;">

            <!-- Glow blobs -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden">
                <div class="absolute top-[-80px] right-[-60px] w-[500px] h-[500px] rounded-full opacity-20"
                    style="background: radial-gradient(circle, #22c55e 0%, transparent 65%);"></div>
                <div class="absolute bottom-[-60px] left-[-40px] w-[300px] h-[300px] rounded-full opacity-10"
                    style="background: radial-gradient(circle, #3b82f6 0%, transparent 65%);"></div>
            </div>

            <div
                class="relative z-10 max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop flex flex-col lg:flex-row items-center gap-0 min-h-[420px]">

                <!-- Left: Copy -->
                <div class="flex-1 flex flex-col justify-center gap-6 py-10 lg:py-14 pr-0 lg:pr-10">

                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white leading-tight tracking-tight">
                        Tecnología que impulsa<br>
                        tu <span style="color: #22c55e;">productividad.</span>
                    </h1>

                    <p class="text-sm sm:text-base text-slate-400 max-w-md leading-relaxed">
                        Equipos de alto rendimiento, componentes originales,<br class="hidden sm:block"> accesorios y
                        soporte local en Panamá.
                    </p>

                    <!-- Botones CTA originales -->
                    <div class="flex flex-wrap gap-3.5 pt-2">
                        <a href="#catalogo"
                            class="inline-flex items-center gap-2 px-6 py-3.5 rounded-lg font-bold text-sm text-white transition-all shadow-lg hover:brightness-110 active:scale-95"
                            style="background:#22c55e;">
                            <span class="material-symbols-outlined text-[18px]">shopping_bag</span>
                            <span>Explorar Catálogo</span>
                        </a>

                        @guest
                            <a href="{{ route('register') }}" wire:navigate
                                class="inline-flex items-center gap-2 px-6 py-3.5 rounded-lg font-bold text-sm text-white border transition-all hover:bg-white/10 active:scale-95"
                                style="border-color:rgba(255,255,255,0.3);">
                                <span class="material-symbols-outlined text-[18px]">person_add</span>
                                <span>Crear Cuenta</span>
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" wire:navigate
                                class="inline-flex items-center gap-2 px-6 py-3.5 rounded-lg font-bold text-sm text-white border transition-all hover:bg-white/10 active:scale-95"
                                style="border-color:rgba(255,255,255,0.3);">
                                <span class="material-symbols-outlined text-[18px]">account_circle</span>
                                <span>Ir a Mi Cuenta</span>
                            </a>
                        @endguest
                    </div>

                    <!-- Trust badges pequeños originales -->
                    <div class="pt-3 border-t flex flex-wrap items-center gap-4 text-xs"
                        style="border-color:rgba(255,255,255,0.1);">
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-base" style="color:#22c55e;">verified</span>
                            <span class="text-slate-300 text-[11px] font-semibold">Garantía Local en Panamá</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-base" style="color:#22c55e;">local_shipping</span>
                            <span class="text-slate-300 text-[11px] font-semibold">Envíos a las 10 Provincias</span>
                        </div>
                    </div>


                </div>

                <!-- Right: GIF hero slider -->
                <div class="w-full lg:w-[52%] shrink-0 self-stretch min-h-[320px] lg:min-h-full relative overflow-hidden"
                    id="heroSlider">

                    <!-- Slide 1 -->
                    <video src="https://res.cloudinary.com/y4pbdezt/video/upload/v1787711954/Imagen1.webm" aria-label="Arma tu PC a Medida"
                        class="hero-slide absolute inset-0 w-full h-full object-cover block"
                        style="max-height:480px; object-position:center; opacity:1; visibility:visible; transition: opacity 0.7s ease, visibility 0s 0s;" 
                        autoplay loop muted playsinline fetchpriority="high"></video>

                    <!-- Slide 2 -->
                    <video src="https://res.cloudinary.com/y4pbdezt/video/upload/v1787711954/Imagen2.webm" aria-label="Portátiles en Panamá"
                        class="hero-slide absolute inset-0 w-full h-full object-cover block"
                        style="max-height:480px; object-position:center; opacity:0; visibility:hidden; transition: opacity 0.7s ease, visibility 0s 0.7s;" 
                        autoplay loop muted playsinline></video>

                    <!-- Slide 3 -->
                    <video src="https://res.cloudinary.com/y4pbdezt/video/upload/v1787711955/Imagen3.webm" aria-label="Periféricos en Panamá"
                        class="hero-slide absolute inset-0 w-full h-full object-cover block"
                        style="max-height:480px; object-position:center; opacity:0; visibility:hidden; transition: opacity 0.7s ease, visibility 0s 0.7s;" 
                        autoplay loop muted playsinline></video>

                    <!-- Dot indicators -->
                    <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-2 z-10" id="heroDots">
                        <button class="hero-dot w-2.5 h-2.5 rounded-full border border-white/50 transition-all"
                            style="background:#22c55e;" aria-label="Slide 1"></button>
                        <button class="hero-dot w-2.5 h-2.5 rounded-full border border-white/50 transition-all"
                            style="background:rgba(255,255,255,0.35);" aria-label="Slide 2"></button>
                        <button class="hero-dot w-2.5 h-2.5 rounded-full border border-white/50 transition-all"
                            style="background:rgba(255,255,255,0.35);" aria-label="Slide 3"></button>
                    </div>

                </div>

                <script>
                    (function () {
                        const slides = document.querySelectorAll('.hero-slide');
                        const dots = document.querySelectorAll('.hero-dot');
                        let cur = 0;

                        function goTo(n) {
                            slides[cur].style.transition = 'opacity 0.7s ease, visibility 0s 0.7s';
                            slides[cur].style.opacity = '0';
                            slides[cur].style.visibility = 'hidden';
                            dots[cur].style.background = 'rgba(255,255,255,0.35)';
                            
                            cur = (n + slides.length) % slides.length;
                            
                            slides[cur].style.transition = 'opacity 0.7s ease, visibility 0s 0s';
                            slides[cur].style.visibility = 'visible';
                            slides[cur].style.opacity = '1';
                            dots[cur].style.background = '#22c55e';
                        }

                        dots.forEach((d, i) => d.addEventListener('click', () => { clearInterval(timer); goTo(i); timer = setInterval(() => goTo(cur + 1), 6000); }));

                        let timer = setInterval(() => goTo(cur + 1), 6000);
                    })();
                </script>


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

                @forelse($destacados as $prod)
                    <x-producto-card :prod="$prod" />
                @empty
                    <div
                        class="col-span-full py-16 flex flex-col items-center justify-center border-2 border-dashed border-gray-200 rounded-2xl">
                        <span class="material-symbols-outlined text-[48px] text-gray-300 mb-3">inventory_2</span>
                        <p class="text-sm font-bold text-gray-500 mb-1">No hay productos destacados</p>
                        <p class="text-xs text-gray-400">Activa la estrella de destacado en los productos del panel.</p>
                    </div>
                @endforelse

            </div>
        </section>



        <!-- ==========================================
             CARRUSEL DE MARCAS (MANUAL)
        =========================================== -->
        <section class="py-10 bg-white border-b border-outline-variant/40">
            <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">

                <p
                    class="font-label-caps text-xs font-bold text-on-surface-variant tracking-widest uppercase text-center mb-7">
                    Marcas que distribuimos</p>

                <div class="brands-slider-outer">
                    <button class="brands-arrow brands-arrow-left" id="brandsPrev" aria-label="Anterior">
                        <span class="material-symbols-outlined">chevron_left</span>
                    </button>

                    <div class="brands-viewport" id="brandsViewport">
                        <div class="brands-track-manual" id="brandsTrack">
                            @foreach ($marcasDistribuidores ?? [] as $marca)
                                @php
                                    /** @var \App\Models\Brand $marca */
                                    $nombre = is_object($marca) ? ($marca->name ?? '') : data_get($marca, 'name', data_get($marca, 'nombre', ''));
                                    $logo = is_object($marca) ? ($marca->logo_url ?? null) : data_get($marca, 'logo_url', data_get($marca, 'url'));
                                @endphp
                                <div class="brand-logo-card-m" title="{{ $nombre }}">
                                    @if(!empty($logo))
                                        <img src="{{ $logo }}" alt="{{ $nombre }}" loading="lazy"
                                            class="brand-logo-img-m" />
                                    @else
                                        <span class="text-xs font-black uppercase text-slate-700 tracking-wider">{{ $nombre }}</span>
                                    @endif
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
            .brands-slider-outer {
                position: relative;
                display: flex;
                align-items: center;
                gap: 12px;
            }

            .brands-viewport {
                overflow: hidden;
                flex: 1;
                min-width: 0;
            }

            .brands-track-manual {
                display: flex;
                align-items: center;
                gap: 20px;
                transition: transform 0.45s cubic-bezier(.25, .46, .45, .94);
                will-change: transform;
            }

            .brand-logo-card-m {
                flex-shrink: 0;
                width: 160px;
                height: 90px;
                background: #fff;
                border: 1.5px solid #e5e7eb;
                border-radius: 14px;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 14px 18px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
                transition: box-shadow .25s ease, border-color .25s ease, transform .25s ease;
                cursor: default;
            }

            .brand-logo-card-m:hover {
                box-shadow: 0 6px 20px rgba(0, 0, 0, .11);
                border-color: #94a3b8;
                transform: translateY(-3px);
            }

            .brand-logo-img-m {
                max-width: 100%;
                max-height: 100%;
                width: auto;
                height: auto;
                object-fit: contain;
                display: block;
            }

            .brands-arrow {
                flex-shrink: 0;
                width: 44px;
                height: 44px;
                border-radius: 50%;
                border: 1.5px solid #e2e8f0;
                background: #fff;
                color: #1e293b;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                box-shadow: 0 2px 8px rgba(0, 0, 0, .08);
                transition: background .2s, box-shadow .2s, border-color .2s, transform .15s;
                z-index: 10;
            }

            .brands-arrow:hover {
                background: #f8fafc;
                border-color: #94a3b8;
                box-shadow: 0 4px 14px rgba(0, 0, 0, .12);
                transform: scale(1.07);
            }

            .brands-arrow:active {
                transform: scale(0.96);
            }

            .brands-arrow:disabled {
                opacity: .35;
                cursor: not-allowed;
                transform: none;
            }

            .brands-arrow .material-symbols-outlined {
                font-size: 22px;
                line-height: 1;
            }
        </style>

        <script>
            (function () {
                const track = document.getElementById('brandsTrack');
                const vp = document.getElementById('brandsViewport');
                const prev = document.getElementById('brandsPrev');
                const next = document.getElementById('brandsNext');
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
                prev.addEventListener('click', () => { if (cur > 0) { cur--; upd(); } });
                next.addEventListener('click', () => { if (cur < max()) { cur++; upd(); } });
                window.addEventListener('resize', () => { cur = Math.min(cur, max()); upd(false); });
                upd(false);
            })();
        </script>

        <!-- ==========================================
             CÓMO FUNCIONA / PROCESO
        =========================================== -->
        <section class="relative py-12 overflow-hidden border-y border-outline-variant/30" id="proceso-section">

            {{-- Fondo sutil --}}
            <div class="absolute inset-0 bg-surface-container-low/60 pointer-events-none"></div>
            <div
                class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-px bg-gradient-to-r from-transparent via-primary/25 to-transparent">
            </div>
            <div
                class="absolute bottom-0 left-1/2 -translate-x-1/2 w-[600px] h-px bg-gradient-to-r from-transparent via-primary/25 to-transparent">
            </div>

            <div class="relative max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">

                {{-- Header compacto alineado a la izquierda en desktop --}}
                <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3 mb-10">
                    <div>
                        <p class="text-[10px] font-bold tracking-[0.2em] uppercase text-primary mb-1.5">Cómo funciona</p>
                        <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-on-surface leading-tight">
                            De la selección a tu puerta<br class="hidden sm:block"> en minutos
                        </h2>
                    </div>
                    <p class="text-xs text-on-surface-variant max-w-xs leading-relaxed md:text-right">
                        Proceso 100% digital, sin filas ni papeleo.<br>Confirmación inmediata en cada etapa.
                    </p>
                </div>

                {{-- Steps: layout horizontal tipo pipeline --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 relative">

                    {{-- Línea de progreso desktop --}}
                    <div
                        class="hidden sm:block absolute top-5 left-[calc(16.5%+1.5rem)] right-[calc(16.5%+1.5rem)] h-[2px] z-0">
                        <div class="h-full bg-gradient-to-r from-primary/20 via-primary/50 to-primary/20 rounded-full">
                        </div>
                    </div>

                    @php
                        $steps = [
                            ['num' => '01', 'icon' => 'manage_search', 'title' => 'Explora y elige', 'desc' => 'Filtra por categoría, marca o precio. Stock actualizado en tiempo real con ficha técnica completa.', 'tag' => 'Sin cuenta requerida'],
                            ['num' => '02', 'icon' => 'lock', 'title' => 'Pago seguro', 'desc' => 'Yappy, ACH, tarjeta o transferencia bancaria. Cifrado SSL con confirmación al instante.', 'tag' => 'Confirmación instantánea'],
                            ['num' => '03', 'icon' => 'local_shipping', 'title' => 'Recibe con garantía', 'desc' => 'Despacho el mismo día hábil. Tracking en tiempo real y factura fiscal con ITBMS incluida.', 'tag' => 'Garantía local'],
                        ];
                    @endphp

                    @foreach($steps as $step)
                        <div
                            class="group relative flex flex-col p-5 bg-surface-container-lowest rounded-2xl border border-outline-variant/40 hover:border-primary/30 transition-all duration-300 hover:shadow-lg overflow-hidden z-10">
                            {{-- Acento lateral izquierdo --}}
                            <div
                                class="absolute left-0 top-4 bottom-4 w-[3px] rounded-r-full bg-primary/20 group-hover:bg-primary/60 transition-colors duration-300">
                            </div>

                            {{-- Número grande de fondo --}}
                            <span
                                class="absolute top-2 right-3 text-5xl font-black text-outline-variant/15 select-none leading-none">{{ $step['num'] }}</span>

                            {{-- Icono + número pequeño --}}
                            <div class="flex items-center gap-3 mb-4">
                                <div
                                    class="w-10 h-10 rounded-xl bg-primary/10 border border-primary/15 flex items-center justify-center text-primary shrink-0 group-hover:bg-primary/15 transition-colors duration-300">
                                    <span class="material-symbols-outlined text-xl">{{ $step['icon'] }}</span>
                                </div>
                                <span class="text-[10px] font-black tracking-widest text-primary/50 uppercase">Paso
                                    {{ $step['num'] }}</span>
                            </div>

                            <h3 class="text-sm font-bold text-on-surface mb-1.5 leading-snug">{{ $step['title'] }}</h3>
                            <p class="text-xs text-on-surface-variant leading-relaxed flex-1">{{ $step['desc'] }}</p>

                            <div class="mt-4 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-primary/70 shrink-0"></span>
                                <span class="text-[10px] font-semibold text-primary/80 tracking-wide">{{ $step['tag'] }}</span>
                            </div>
                        </div>
                    @endforeach

                </div>

                {{-- Trust strip --}}
                <div
                    class="mt-8 pt-6 border-t border-outline-variant/30 flex flex-wrap justify-center items-center gap-x-7 gap-y-2">
                    @foreach([
                            ['receipt_long', 'Factura fiscal ITBMS'],
                            ['support_agent', 'Soporte vía WhatsApp'],
                            ['verified_user', 'Garantía oficial'],
                        ] as [$ico, $label])
                        <span class="flex items-center gap-1.5 text-[11px] text-on-surface-variant/80">
                            <span class="material-symbols-outlined text-[14px] text-primary">{{ $ico }}</span>
                            {{ $label }}
                        </span>
                    @endforeach
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

                <div
                    class="bg-surface-container-lowest p-6 rounded-2xl shadow-xs border border-outline-variant flex flex-col justify-between">
                    <p class="font-body-md text-xs sm:text-sm text-on-surface-variant italic leading-relaxed">
                        "Excelente servicio, compré una laptop de alto rendimiento para mi oficina y el envío llegó al día
                        siguiente. Muy cómodo pagar directo con Yappy."
                    </p>
                    <div class="flex items-center gap-3.5 mt-5 pt-4 border-t border-outline-variant/40">
                        <div
                            class="w-10 h-10 rounded-full bg-secondary-container flex items-center justify-center text-secondary font-bold text-sm">
                            MR
                        </div>
                        <div>
                            <p class="font-headline-md text-xs font-bold text-primary">Mariana Rodríguez</p>
                            <p class="font-label-caps text-[10px] text-on-surface-variant">Gerente de Operaciones • Panamá
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-surface-container-lowest p-6 rounded-2xl shadow-xs border border-outline-variant flex flex-col justify-between">
                    <p class="font-body-md text-xs sm:text-sm text-on-surface-variant italic leading-relaxed">
                        "Adquirimos los periféricos y suministros informáticos para nuestro equipo de desarrollo. La
                        garantía local y el soporte nos dieron total tranquilidad."
                    </p>
                    <div class="flex items-center gap-3.5 mt-5 pt-4 border-t border-outline-variant/40">
                        <div
                            class="w-10 h-10 rounded-full bg-secondary-container flex items-center justify-center text-secondary font-bold text-sm">
                            CG
                        </div>
                        <div>
                            <p class="font-headline-md text-xs font-bold text-primary">Carlos González</p>
                            <p class="font-label-caps text-[10px] text-on-surface-variant">Coordinador de IT • Herrera</p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-surface-container-lowest p-6 rounded-2xl shadow-xs border border-outline-variant flex flex-col justify-between">
                    <p class="font-body-md text-xs sm:text-sm text-on-surface-variant italic leading-relaxed">
                        "Equipamos toda nuestra estación de trabajo a través de la plataforma. La facturación con desglose
                        de ITBMS fue inmediata y exacta para la contabilidad."
                    </p>
                    <div class="flex items-center gap-3.5 mt-5 pt-4 border-t border-outline-variant/40">
                        <div
                            class="w-10 h-10 rounded-full bg-secondary-container flex items-center justify-center text-secondary font-bold text-sm">
                            LD
                        </div>
                        <div>
                            <p class="font-headline-md text-xs font-bold text-primary">Luis Alberto De Gracia</p>
                            <p class="font-label-caps text-[10px] text-on-surface-variant">Director de Finanzas • Costa del
                                Este</p>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- ==========================================
             CARRUSEL DE BANNERS PROMOCIONALES
        =========================================== -->
        <section class="w-full overflow-hidden relative" id="bannerCarousel" style="max-height:340px;">

            <!-- Slides wrapper -->
            <div id="bannerTrack" class="flex transition-transform duration-700 ease-in-out" style="will-change:transform;">
                @foreach ([
                        'BANNERS AGOSTO_Mesa de trabajo 1.webp',
                        'BANNERS ENERO 2_Mesa de trabajo.webp',
                        'BANNERS - COMPONENTES_Mesa de tr.webp',
                        'BANNERS - COMPONENTES_Mesa de tr (1).webp',
                    ] as $banner)
                    <div class="w-full shrink-0" style="min-width:100%;">
                        <img src="{{ asset('images/Banners/' . $banner) }}" alt="Banner promocional PayMe Panamá"
                            class="w-full object-cover block" style="max-height:340px; object-position:center;" 
                            loading="lazy" decoding="async" />
                    </div>
                @endforeach
            </div>

            <!-- Arrows -->
            <button id="bannerPrev" aria-label="Anterior"
                class="absolute left-3 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-black/40 hover:bg-black/65 text-white flex items-center justify-center transition-all backdrop-blur-sm">
                <span class="material-symbols-outlined text-[22px]">chevron_left</span>
            </button>
            <button id="bannerNext" aria-label="Siguiente"
                class="absolute right-3 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-black/40 hover:bg-black/65 text-white flex items-center justify-center transition-all backdrop-blur-sm">
                <span class="material-symbols-outlined text-[22px]">chevron_right</span>
            </button>

            <!-- Dots -->
            <div id="bannerDots" class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-2 z-20">
                @foreach ([
                        'BANNERS AGOSTO_Mesa de trabajo 1.webp',
                        'BANNERS ENERO 2_Mesa de trabajo.webp',
                        'BANNERS - COMPONENTES_Mesa de tr.webp',
                        'BANNERS - COMPONENTES_Mesa de tr (1).webp',
                    ] as $i => $b)
                    <button class="banner-dot w-2.5 h-2.5 rounded-full border border-white/60 transition-all"
                        style="background: {{ $i === 0 ? '#22c55e' : 'rgba(255,255,255,0.4)' }};"
                        aria-label="Slide {{ $i + 1 }}"></button>
                @endforeach
            </div>

        </section>

        <script>
            (function () {
                const track = document.getElementById('bannerTrack');
                const dots = document.querySelectorAll('.banner-dot');
                const total = dots.length;

                // Clone first and last slides for infinite effect
                const slides = Array.from(track.children);
                const firstClone = slides[0].cloneNode(true);
                const lastClone = slides[total - 1].cloneNode(true);
                track.appendChild(firstClone);
                track.insertBefore(lastClone, slides[0]);

                // Start at index 1 (real first slide, skipping the prepended clone)
                let cur = 1;
                track.style.transition = 'none';
                track.style.transform = `translateX(-${cur * 100}%)`;

                function updateDots(realIdx) {
                    dots.forEach((d, i) => d.style.background = i === realIdx ? '#22c55e' : 'rgba(255,255,255,0.4)');
                }

                function goTo(n) {
                    cur = n;
                    track.style.transition = 'transform 0.7s ease-in-out';
                    track.style.transform = `translateX(-${cur * 100}%)`;
                    // real index = cur - 1, clamped within [0, total-1]
                    const realIdx = Math.min(Math.max(cur - 1, 0), total - 1);
                    updateDots(realIdx);
                }

                // After transition ends: if we're on a clone, jump instantly to real counterpart
                track.addEventListener('transitionend', () => {
                    if (cur === 0) {
                        // Was on prepended clone of last → jump to real last
                        track.style.transition = 'none';
                        cur = total;
                        track.style.transform = `translateX(-${cur * 100}%)`;
                    } else if (cur === total + 1) {
                        // Was on appended clone of first → jump to real first
                        track.style.transition = 'none';
                        cur = 1;
                        track.style.transform = `translateX(-${cur * 100}%)`;
                    }
                });

                document.getElementById('bannerPrev').addEventListener('click', () => {
                    clearInterval(timer);
                    goTo(cur - 1);
                    timer = setInterval(() => goTo(cur + 1), 5000);
                });
                document.getElementById('bannerNext').addEventListener('click', () => {
                    clearInterval(timer);
                    goTo(cur + 1);
                    timer = setInterval(() => goTo(cur + 1), 5000);
                });
                dots.forEach((d, i) => d.addEventListener('click', () => {
                    clearInterval(timer);
                    goTo(i + 1); // +1 because index 0 is the prepended clone
                    timer = setInterval(() => goTo(cur + 1), 5000);
                }));

                updateDots(0);
                let timer = setInterval(() => goTo(cur + 1), 5000);
            })();
        </script>


    </div>

    <!-- Toast notification popup -->
    <div id="toast-box"
        class="fixed bottom-24 left-1/2 -translate-x-1/2 z-50 bg-[#002349] text-white px-5 py-3 rounded-xl shadow-2xl border border-white/20 hidden items-center gap-3 text-xs font-semibold">
        <span class="material-symbols-outlined text-secondary-container text-lg"
            style="font-variation-settings: 'FILL' 1;">check_circle</span>
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