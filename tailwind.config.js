import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                // Material Design tokens — client storefront & guest layouts
                'primary': '#002349',
                'primary-container': '#002349',
                'primary-fixed': '#d5e3ff',
                'primary-fixed-dim': '#adc8f6',
                'on-primary': '#ffffff',
                'on-primary-container': '#718bb7',
                'secondary': '#006148',
                'secondary-container': '#8af5be',
                'secondary-fixed': '#8df7c1',
                'secondary-fixed-dim': '#71dba6',
                'on-secondary': '#ffffff',
                'on-secondary-container': '#00714b',
                'tertiary': '#735c00',
                'tertiary-container': '#cca830',
                'tertiary-fixed': '#ffe088',
                'tertiary-fixed-dim': '#e9c349',
                'background': '#f8f9ff',
                'on-background': '#0b1c30',
                'surface': '#f8f9ff',
                'surface-bright': '#f8f9ff',
                'surface-dim': '#cbdbf5',
                'surface-container-lowest': '#ffffff',
                'surface-container-low': '#eff4ff',
                'surface-container': '#e5eeff',
                'surface-container-high': '#dce9ff',
                'surface-container-highest': '#d3e4fe',
                'on-surface': '#0b1c30',
                'on-surface-variant': '#43474e',
                'outline': '#74777f',
                'outline-variant': '#c4c6cf',
                'error': '#ba1a1a',
                'error-container': '#ffdad6',
                'on-error': '#ffffff',
                'on-error-container': '#93000a',

                // Admin brand palette
                'brand': {
                    sidebar: '#1F2937',
                    surface: '#F8FAFC',
                    card: '#FFFFFF',
                    border: '#E5E7EB',
                    title: '#111827',
                    muted: '#6B7280',
                    emerald: '#059669',
                    emeraldLight: '#ECFDF5',
                    gold: '#D97706',
                },
            },

            fontFamily: {
                sans: ['Figtree', 'Plus Jakarta Sans', 'ui-sans-serif', 'system-ui', 'sans-serif', ...defaultTheme.fontFamily.sans],
                'headline-md': ['Figtree', 'sans-serif'],
                'display-lg': ['Figtree', 'sans-serif'],
                'body-lg': ['Figtree', 'sans-serif'],
                'body-md': ['Figtree', 'sans-serif'],
                'label-caps': ['Figtree', 'sans-serif'],
                'numeric-data': ['Figtree', 'sans-serif'],
            },

            spacing: {
                'gutter': '24px',
                'container-max': '1200px',
                'margin-desktop': '64px',
                'margin-mobile': '20px',
                'stack-sm': '4px',
                'stack-md': '16px',
                'stack-lg': '32px',
            },
        },
    },

    plugins: [forms],
};