import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

// Carga perezosa (lazy load) de ApexCharts: se divide automáticamente en un chunk
// separado para no penalizar el peso inicial ni el rendimiento en páginas sin gráficos.
window.loadApexCharts = async function() {
    if (!window.ApexCharts) {
        const module = await import('apexcharts');
        window.ApexCharts = module.default;
    }
    return window.ApexCharts;
};

// Si la vista actual contiene contenedores de gráficos, inicializar de inmediato
if (document.querySelector('#spark1, #mainChart, #revenueChart, [data-chart]')) {
    window.loadApexCharts();
}
document.addEventListener('livewire:navigated', () => {
    if (document.querySelector('#spark1, #mainChart, #revenueChart, [data-chart]')) {
        window.loadApexCharts();
    }
});

if (!window.Livewire && !document.querySelector('[wire\\:snapshot]')) {
    Alpine.plugin(collapse);
    window.Alpine = Alpine;
    Alpine.start();
}
/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 */


/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';
