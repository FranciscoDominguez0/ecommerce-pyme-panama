<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'PayMe Panamá') }}</title>

    <!-- Favicon & Iconos Oficiales -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-192.png') }}">

    <!-- Default Laravel Font: Figtree & Material Symbols (Local) -->
    <link rel="stylesheet" href="{{ asset('fonts/fonts.css') }}">
    <link rel="preload" href="{{ asset('fonts/material-symbols-outlined.woff2') }}" as="font" type="font/woff2"
        crossorigin>

    <!-- Tailwind CSS compilado vía Vite -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        body {
            font-family: 'Figtree', ui-sans-serif, system-ui, sans-serif;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid #e5e7eb;
            box-shadow: 0 10px 25px -5px rgba(0, 35, 73, 0.06), 0 8px 10px -6px rgba(0, 35, 73, 0.04);
        }

        .input-focus-ring:focus-within {
            border-color: #006c47;
            box-shadow: 0 0 0 1px #006c47;
        }

        .input-error-ring {
            border-color: #dc2626 !important;
            box-shadow: 0 0 0 1px #dc2626 !important;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 20;
        }

        .fade-in-up {
            animation: fadeInUp 0.4s ease-out forwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body
    class="bg-background text-on-surface min-h-screen flex flex-col items-center justify-center relative overflow-x-hidden selection:bg-secondary selection:text-on-secondary antialiased font-sans text-sm">
    <!-- Ambient Background Gradients -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none z-0">
        <div
            class="absolute -top-[15%] -left-[10%] w-[50%] h-[50%] rounded-full bg-surface-container-highest/50 opacity-40 blur-[100px]">
        </div>
        <div
            class="absolute top-[60%] -right-[10%] w-[50%] h-[50%] rounded-full bg-secondary-container/20 opacity-40 blur-[110px]">
        </div>
    </div>

    <!-- Main Slot Content -->
    <div class="relative z-10 w-full flex flex-col items-center justify-center py-6 px-4">
        {{ $slot }}
    </div>

    <!-- Sistema Global de Alertas y Notificaciones Toast (Desactivado solo en Olvidar Contraseña) -->
    @if (!request()->is('forgot-password'))
        <x-toast-alert />
    @endif
</body>

</html>