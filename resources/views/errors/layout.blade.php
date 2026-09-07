<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - PayMe Panamá</title>
    
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="stylesheet" href="{{ asset('fonts/fonts.css') }}">
    <link rel="preload" href="{{ asset('fonts/material-symbols-outlined.woff2') }}" as="font" type="font/woff2" crossorigin>
    
    @vite(['resources/css/app.css'])

    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            letter-spacing: -0.011em;
            background-color: #F8FAFC;
        }
        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined';
            font-weight: normal;
            font-style: normal;
            font-size: 24px;
            display: inline-block;
            line-height: 1;
            -webkit-font-smoothing: antialiased;
        }
        
        .error-blob {
            position: absolute;
            width: 600px;
            height: 600px;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(59, 130, 246, 0.05) 100%);
            filter: blur(80px);
            border-radius: 50%;
            z-index: -1;
        }
    </style>
</head>
<body class="antialiased text-slate-900 min-h-screen flex items-center justify-center relative overflow-hidden">
    <!-- Abstract Background -->
    <div class="error-blob top-0 left-0 -translate-x-1/2 -translate-y-1/2"></div>
    <div class="error-blob bottom-0 right-0 translate-x-1/2 translate-y-1/4" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(236, 72, 153, 0.05) 100%);"></div>

    <div class="relative z-10 w-full max-w-2xl px-6 py-12 mx-auto text-center">
        <!-- Logo -->
        <div class="mb-10 flex justify-center">
            <div class="w-12 h-12 rounded-xl bg-white p-2 shadow-sm border border-slate-200 flex items-center justify-center">
                <img src="{{ asset('favicon.svg') }}" alt="PayMe Panamá" class="w-full h-full object-contain">
            </div>
        </div>

        @yield('content')

        <!-- Footer -->
        <div class="mt-16 text-sm text-slate-400 font-medium">
            &copy; {{ date('Y') }} PayMe Panamá. Todos los derechos reservados.
        </div>
    </div>
</body>
</html>
