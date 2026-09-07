@extends('errors.layout')

@section('title', 'Página no encontrada')

@section('content')
    <div class="flex flex-col items-center">
        <!-- Minimalist Typographic Error Code -->
        <div class="text-[120px] md:text-[150px] font-extrabold leading-none tracking-tighter text-transparent bg-clip-text bg-gradient-to-br from-slate-800 to-slate-400 mb-6 drop-shadow-sm select-none">
            404
        </div>
        
        <h1 class="text-3xl font-bold text-slate-900 mb-4 tracking-tight">Página no encontrada</h1>
        
        <p class="text-base text-slate-500 max-w-md mx-auto mb-10 leading-relaxed">
            Lo sentimos, no hemos podido encontrar la página que buscas. Es posible que haya sido eliminada, movida o que la dirección sea incorrecta.
        </p>
        
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <button onclick="history.back()" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3 border border-slate-200 rounded-full text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 hover:text-slate-900 hover:border-slate-300 transition-all shadow-sm">
                <span class="material-symbols-outlined text-[18px] mr-2">arrow_back</span>
                Regresar
            </button>
            
            <a href="{{ url('/') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3 border border-transparent rounded-full text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition-all shadow-sm hover:shadow-lg shadow-emerald-500/30">
                <span class="material-symbols-outlined text-[18px] mr-2">home</span>
                Ir al Inicio
            </a>
        </div>
    </div>
@endsection
