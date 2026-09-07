@extends('errors.layout')

@section('title', 'Acceso Denegado')

@section('content')
    <div class="flex flex-col items-center">
        <!-- Minimalist Typographic Error Code -->
        <div class="relative mb-6 select-none">
            <div class="text-[120px] md:text-[150px] font-extrabold leading-none tracking-tighter text-transparent bg-clip-text bg-gradient-to-br from-amber-500 to-amber-700 drop-shadow-sm">
                403
            </div>
            <!-- Subtle Overlay Icon -->
            <div class="absolute inset-0 flex items-center justify-center mix-blend-overlay opacity-30">
                <span class="material-symbols-outlined text-[100px] text-white">lock</span>
            </div>
        </div>
        
        <h1 class="text-3xl font-bold text-slate-900 mb-4 tracking-tight">Acceso Restringido</h1>
        
        <p class="text-base text-slate-500 max-w-md mx-auto mb-10 leading-relaxed">
            No tienes los permisos necesarios para acceder a esta sección o realizar esta acción. Si consideras que es un error, contacta con tu administrador.
        </p>
        
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <button onclick="history.back()" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3 border border-slate-200 rounded-full text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 hover:text-slate-900 hover:border-slate-300 transition-all shadow-sm">
                <span class="material-symbols-outlined text-[18px] mr-2">arrow_back</span>
                Regresar
            </button>
            
            <a href="{{ url('/') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3 border border-transparent rounded-full text-sm font-semibold text-white bg-amber-600 hover:bg-amber-700 transition-all shadow-sm hover:shadow-lg shadow-amber-500/30">
                <span class="material-symbols-outlined text-[18px] mr-2">home</span>
                Ir al Inicio
            </a>
        </div>
    </div>
@endsection
