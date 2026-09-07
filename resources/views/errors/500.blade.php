@extends('errors.layout')

@section('title', 'Error del Servidor')

@section('content')
    <div class="flex flex-col items-center">
        <!-- Minimalist Typographic Error Code -->
        <div class="relative mb-6 select-none">
            <div class="text-[120px] md:text-[150px] font-extrabold leading-none tracking-tighter text-transparent bg-clip-text bg-gradient-to-br from-red-500 to-rose-700 drop-shadow-sm">
                500
            </div>
            <!-- Subtle Overlay Icon -->
            <div class="absolute inset-0 flex items-center justify-center mix-blend-overlay opacity-30">
                <span class="material-symbols-outlined text-[100px] text-white">warning</span>
            </div>
        </div>
        
        <h1 class="text-3xl font-bold text-slate-900 mb-4 tracking-tight">Error Interno del Servidor</h1>
        
        <p class="text-base text-slate-500 max-w-md mx-auto mb-10 leading-relaxed">
            Algo salió mal de nuestro lado y no pudimos procesar tu solicitud en este momento. Nuestro equipo técnico ha sido notificado automáticamente y estamos trabajando para solucionarlo.
        </p>
        
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <button onclick="location.reload()" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3 border border-slate-200 rounded-full text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 hover:text-slate-900 hover:border-slate-300 transition-all shadow-sm">
                <span class="material-symbols-outlined text-[18px] mr-2">refresh</span>
                Reintentar
            </button>
            
            <a href="{{ url('/') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3 border border-transparent rounded-full text-sm font-semibold text-white bg-slate-800 hover:bg-slate-900 transition-all shadow-sm hover:shadow-lg shadow-slate-900/30">
                <span class="material-symbols-outlined text-[18px] mr-2">home</span>
                Ir al Inicio
            </a>
        </div>
    </div>
@endsection
