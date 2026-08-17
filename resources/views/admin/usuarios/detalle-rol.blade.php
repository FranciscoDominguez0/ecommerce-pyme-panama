@extends('layouts.admin')

@section('title', 'Detalle de Rol: ' . $rol->name)

@section('content')
<!-- Header Section -->
<div class="mb-8 flex flex-col md:flex-row md:items-start justify-between gap-6">
    <div>
        <div class="flex items-center gap-3 mb-2">
            <a href="{{ route('admin.usuarios.index') }}" class="text-slate-500 hover:text-slate-900 transition-colors">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">{{ $rol->name }}</h2>
        </div>
        <p class="text-xs sm:text-sm text-slate-500 font-medium ml-9">{{ $rol->descripcion ?? 'Gestión de usuarios y permisos del rol' }}</p>
    </div>
    <!-- Role Stats Bento -->
    <div class="flex gap-4 ml-9 md:ml-0">
        <div class="bg-white border border-slate-200 rounded-xl p-4 flex flex-col items-center justify-center min-w-[120px] shadow-sm">
            <span class="font-bold text-2xl text-slate-900 mb-1">{{ $rol->users()->count() }}</span>
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Usuarios</span>
        </div>
        <div class="bg-white border border-slate-200 rounded-xl p-4 flex flex-col items-center justify-center min-w-[120px] shadow-sm">
            <span class="font-bold text-2xl text-slate-900 mb-1">{{ $rol->permissions()->count() }}</span>
            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Permisos</span>
        </div>
    </div>
</div>

<!-- Tabs -->
<div class="border-b border-slate-200 mb-8 ml-9 md:ml-0">
    <nav aria-label="Tabs" class="flex space-x-8">
        <button class="border-b-2 border-slate-900 py-4 px-1 text-slate-900 font-medium flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">group</span>
            Usuarios
        </button>
        <a href="{{ route('admin.usuarios.roles-permisos', $rol->id) }}" class="border-b-2 border-transparent py-4 px-1 text-slate-500 hover:text-slate-900 hover:border-slate-300 transition-colors flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">key</span>
            Permisos
        </a>
    </nav>
</div>

<!-- Users Section -->
<div class="ml-9 md:ml-0">
    @if($usuarios->isEmpty())
        <!-- Empty State Container -->
        <div class="bg-white border border-slate-200 rounded-xl p-8 sm:p-16 flex flex-col items-center justify-center text-center min-h-[400px]">
            <div class="w-48 h-48 mb-6 opacity-80 flex items-center justify-center">
                <!-- Icono representativo para el estado vacío -->
                <span class="material-symbols-outlined text-slate-200" style="font-size: 120px;">group_off</span>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-3">No hay usuarios asignados a este rol</h3>
            <p class="text-sm text-slate-500 max-w-md mb-8">
                Actualmente no existen usuarios bajo el nivel de acceso {{ $rol->name }}. Crea un usuario nuevo para asignarle este rol.
            </p>
            <div class="flex gap-4">
                <a href="{{ route('admin.usuarios.create', $rol->id) }}" class="bg-slate-900 text-white text-sm font-semibold px-6 py-2.5 rounded-lg hover:bg-slate-800 transition-colors shadow-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Agregar usuario
                </a>
            </div>
        </div>
    @else
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-slate-900">Usuarios con este rol</h3>
            <a href="{{ route('admin.usuarios.create', $rol->id) }}" class="bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold px-4 py-2 rounded-lg transition-colors flex items-center gap-2 tracking-wide">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Agregar usuario
            </a>
        </div>
        
        <!-- Data Table -->
        <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[600px]">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="py-3 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider w-16">Foto</th>
                            <th class="py-3 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Usuario</th>
                            <th class="py-3 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Estado</th>
                            <th class="py-3 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Último Acceso</th>
                            <th class="py-3 px-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($usuarios as $user)
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="py-3 px-4">
                                @if($user->foto_perfil_ruta)
                                    <div class="h-10 w-10 rounded-full bg-slate-200 overflow-hidden border border-slate-200">
                                        <img alt="{{ $user->nombre }}" class="w-full h-full object-cover" src="{{ asset($user->foto_perfil_ruta) }}"/>
                                    </div>
                                @else
                                    <div class="h-10 w-10 rounded-full bg-slate-200 overflow-hidden border border-slate-200 flex items-center justify-center text-slate-500 font-bold text-xs uppercase">
                                        {{ $user->iniciales }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold text-slate-900">{{ $user->nombre_completo }}</span>
                                    <span class="text-xs text-slate-500">{{ $user->email }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                @if(isset($user->activo) && !$user->activo)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700 uppercase tracking-wide">
                                        Inactivo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 uppercase tracking-wide">
                                        Activo
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-500">
                                {{ $user->ultimo_login_en ? \Carbon\Carbon::parse($user->ultimo_login_en)->diffForHumans() : 'Nunca' }}
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('admin.usuarios.edit', $user->id) }}" class="text-slate-400 hover:text-slate-900 hover:bg-slate-100 p-1.5 rounded transition-colors" title="Editar">
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($usuarios->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $usuarios->links() }}
            </div>
            @endif
        </div>
    @endif
</div>
@endsection
