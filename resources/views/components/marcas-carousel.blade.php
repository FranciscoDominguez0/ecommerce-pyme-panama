@props(['marcas'])

<section class="py-10 bg-transparent">
    <div class="max-w-container-max mx-auto">
        <p class="text-lg font-bold text-slate-400 uppercase tracking-widest text-center mb-6">
            Marcas de Confianza
        </p>

        <div class="brands-slider-outer">
            <button class="brands-arrow brands-arrow-left" id="brandsPrev" aria-label="Anterior">
                <span class="material-symbols-outlined">chevron_left</span>
            </button>

            <div class="brands-viewport" id="brandsViewport">
                <div class="brands-track-manual" id="brandsTrack">
                    @foreach ($marcas ?? [] as $marca)
                        @php
                            /** @var \App\Models\Brand $marca */
                            $nombre = is_object($marca) ? ($marca->name ?? '') : data_get($marca, 'name', data_get($marca, 'nombre', ''));
                            $logo = is_object($marca) ? ($marca->logo_url ?? null) : data_get($marca, 'logo_url', data_get($marca, 'url'));
                        @endphp
                        <div class="brand-logo-card-m" title="{{ $nombre }}">
                            @if(!empty($logo))
                                <img src="{{ $logo }}" alt="{{ $nombre }}" loading="lazy" class="brand-logo-img-m" />
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
        transition: transform 0.5s cubic-bezier(.25, .46, .45, .94);
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
    @media (max-width: 640px) {
        .brands-slider-outer { gap: 6px; }
        .brands-track-manual { gap: 10px; }
        .brand-logo-card-m {
            width: 112px;
            height: 64px;
            padding: 8px 12px;
            border-radius: 10px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .05);
        }
        .brands-arrow {
            width: 32px;
            height: 32px;
        }
        .brands-arrow .material-symbols-outlined {
            font-size: 18px;
        }
    }
</style>

<script>
    (function () {
        function initBrandsCarousel() {
            const track = document.getElementById('brandsTrack');
            const vp = document.getElementById('brandsViewport');
            const prev = document.getElementById('brandsPrev');
            const next = document.getElementById('brandsNext');
            const outer = document.querySelector('.brands-slider-outer');
            if (!track || !vp) return;

            if (window.__brandsCarouselInstance) {
                window.__brandsCarouselInstance.destroy();
            }

            const cards = track.querySelectorAll('.brand-logo-card-m');
            const total = cards.length;
            if (total === 0) return;

            let cur = 0;
            let autoTimer = null;
            let isPaused = false;

            const getStep = () => {
                const firstCard = cards[0];
                const cardWidth = firstCard ? firstCard.offsetWidth : 160;
                const style = window.getComputedStyle(track);
                const gap = parseFloat(style.gap) || 16;
                return { width: cardWidth, gap, step: cardWidth + gap };
            };

            const vis = () => {
                const { gap, step } = getStep();
                return Math.max(1, Math.floor((vp.offsetWidth + gap) / step));
            };

            const max = () => Math.max(0, total - vis());

            const upd = (animate = true) => {
                const { step } = getStep();
                track.style.transition = animate ? 'transform 0.5s cubic-bezier(.25, .46, .45, .94)' : 'none';
                track.style.transform = `translateX(-${cur * step}px)`;
                if (!animate) {
                    requestAnimationFrame(() => {
                        track.style.transition = 'transform 0.5s cubic-bezier(.25, .46, .45, .94)';
                    });
                }
            };

            const nextSlide = () => {
                const limit = max();
                if (limit <= 0) return;
                if (cur >= limit) cur = 0;
                else cur++;
                upd(true);
            };

            const prevSlide = () => {
                const limit = max();
                if (limit <= 0) return;
                if (cur <= 0) cur = limit;
                else cur--;
                upd(true);
            };

            const startTimer = () => {
                stopTimer();
                autoTimer = setInterval(() => {
                    if (!isPaused) nextSlide();
                }, 3000);
            };

            const stopTimer = () => {
                if (autoTimer) {
                    clearInterval(autoTimer);
                    autoTimer = null;
                }
            };

            if (next) {
                next.onclick = function(e) {
                    e.preventDefault();
                    nextSlide();
                    startTimer();
                };
            }
            if (prev) {
                prev.onclick = function(e) {
                    e.preventDefault();
                    prevSlide();
                    startTimer();
                };
            }

            const container = outer || vp;
            const onMouseEnter = () => { isPaused = true; };
            const onMouseLeave = () => { isPaused = false; };
            container.addEventListener('mouseenter', onMouseEnter);
            container.addEventListener('mouseleave', onMouseLeave);

            let touchStartX = 0;
            let touchEndX = 0;
            const onTouchStart = (e) => {
                isPaused = true;
                touchStartX = e.changedTouches[0].screenX;
            };
            const onTouchEnd = (e) => {
                isPaused = false;
                touchEndX = e.changedTouches[0].screenX;
                const diff = touchStartX - touchEndX;
                if (Math.abs(diff) > 35) {
                    if (diff > 0) nextSlide();
                    else prevSlide();
                    startTimer();
                }
            };

            vp.addEventListener('touchstart', onTouchStart, { passive: true });
            vp.addEventListener('touchend', onTouchEnd, { passive: true });

            const onResize = () => {
                cur = Math.min(cur, max());
                upd(false);
            };
            window.addEventListener('resize', onResize);

            upd(false);
            startTimer();

            window.__brandsCarouselInstance = {
                destroy: () => {
                    stopTimer();
                    container.removeEventListener('mouseenter', onMouseEnter);
                    container.removeEventListener('mouseleave', onMouseLeave);
                    vp.removeEventListener('touchstart', onTouchStart);
                    vp.removeEventListener('touchend', onTouchEnd);
                    window.removeEventListener('resize', onResize);
                }
            };
        }

        initBrandsCarousel();
        document.addEventListener('livewire:navigated', initBrandsCarousel);
    })();
</script>
