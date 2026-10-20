@php
    $gaId = config('services.google.analytics_id', 'G-MLCZR3R9SP');
@endphp

@if(!empty($gaId))
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', '{{ $gaId }}');

        // Registro de páginas vistas al navegar con Livewire (wire:navigate)
        (function() {
            let cargaInicial = true;
            document.addEventListener('livewire:navigated', function () {
                if (cargaInicial) {
                    cargaInicial = false;
                    return;
                }
                if (typeof gtag === 'function') {
                    gtag('event', 'page_view', {
                        page_title: document.title,
                        page_location: window.location.href,
                        page_path: window.location.pathname
                    });
                }
            });
        })();
    </script>
@endif
