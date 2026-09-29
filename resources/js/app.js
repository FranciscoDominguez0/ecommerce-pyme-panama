import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import ApexCharts from 'apexcharts';

window.ApexCharts = ApexCharts;

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
