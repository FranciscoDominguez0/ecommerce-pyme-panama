import Alpine from 'alpinejs';
import ApexCharts from 'apexcharts';

window.ApexCharts = ApexCharts;

// Livewire bundles and auto-initializes its own Alpine instance on any page
// that loads @livewireScripts (window.Livewire is set synchronously by the
// classic livewire.js script, which always executes before deferred module
// scripts like this one). Starting a second instance would trigger
// "Alpine has already been initialized" warnings and break reactivity.
// Only take ownership of Alpine on pages without Livewire loaded.
if (!window.Livewire && !document.querySelector('[wire\\:snapshot]')) {
    window.Alpine = Alpine;
    Alpine.start();
}