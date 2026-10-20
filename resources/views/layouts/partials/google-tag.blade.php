@php
    $gaId = config('services.google.analytics_id') ?: 'G-MLCZR3R9SP';
@endphp

@if(!empty($gaId))
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', '{{ $gaId }}');

        // Helper profesional de E-commerce para Google Analytics 4
        window.gtagEcommerce = {
            viewItem: function(item) {
                if (typeof gtag !== 'function' || !item) return;
                gtag('event', 'view_item', {
                    currency: 'USD',
                    value: Number(item.price || 0),
                    items: [{
                        item_id: String(item.id || ''),
                        item_name: item.name || '',
                        item_category: item.category || undefined,
                        item_brand: item.brand || undefined,
                        price: Number(item.price || 0),
                        quantity: 1
                    }]
                });
            },
            addToCart: function(item, quantity) {
                if (typeof gtag !== 'function' || !item) return;
                quantity = Number(quantity || 1);
                gtag('event', 'add_to_cart', {
                    currency: 'USD',
                    value: Number(item.price || 0) * quantity,
                    items: [{
                        item_id: String(item.id || ''),
                        item_name: item.name || '',
                        item_category: item.category || undefined,
                        item_variant: item.variant || undefined,
                        price: Number(item.price || 0),
                        quantity: quantity
                    }]
                });
            },
            beginCheckout: function(total, items) {
                if (typeof gtag !== 'function') return;
                gtag('event', 'begin_checkout', {
                    currency: 'USD',
                    value: Number(total || 0),
                    items: items || []
                });
            },
            purchase: function(order) {
                if (typeof gtag !== 'function' || !order) return;
                gtag('event', 'purchase', {
                    transaction_id: String(order.id || ''),
                    value: Number(order.total || 0),
                    tax: Number(order.tax || 0),
                    shipping: Number(order.shipping || 0),
                    currency: 'USD',
                    coupon: order.coupon || undefined,
                    items: order.items || []
                });
            }
        };

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
