@extends('layouts.admin')

@section('title', 'Usuarios, Roles y Permisos')

@section('content')
<!-- Page Header -->
<div class="mb-12">
    <h2 class="font-display-lg text-[32px] md:text-[48px] font-bold text-on-surface mb-2">Usuarios, Roles y Permisos</h2>
    <p class="font-body-lg text-lg text-on-surface-variant">Administra los niveles de acceso, usuarios y permisos del sistema</p>
</div>

<!-- Section Title -->
<div class="flex justify-between items-center mb-6">
    <h3 class="text-lg font-bold text-slate-900">Roles del sistema</h3>
    @can('admin.usuarios.gestionar')
        <button onclick="document.getElementById('crearRolModal').showModal()" class="bg-slate-900 text-white px-4 py-2 rounded-lg text-xs font-semibold hover:bg-slate-800 shadow-sm transition-all flex items-center gap-2 uppercase tracking-wide">
            <span class="material-symbols-outlined text-[18px]">add</span>
            Crear Rol
        </button>
    @else
        <button class="bg-slate-900 text-white px-4 py-2 rounded-lg text-xs font-semibold transition-all flex items-center gap-2 uppercase tracking-wide opacity-50 cursor-not-allowed" title="Solo el Superadmin puede crear roles">
            <span class="material-symbols-outlined text-[18px]">add</span>
            Crear Rol
        </button>
    @endcan
</div>

<!-- Bento Grid for Roles -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($roles as $role)
    @php
        $accentColor = match($role->name) {
            'Superadmin' => 'bg-error',
            'Administrador' => 'bg-brand-gold',
            'Usuario' => 'bg-secondary-fixed-dim',
            default => 'bg-primary'
        };
    @endphp
    <!-- Role Card -->
    <div class="bg-surface-container-lowest border border-outline-variant rounded-lg p-6 hover:shadow-sm transition-all flex flex-col h-full relative overflow-hidden group">
        <div class="absolute top-0 left-0 w-1 h-full {{ $accentColor }}"></div>
        <div class="flex justify-between items-start mb-4">
            <div>
                <h4 class="font-label-caps text-xs text-on-surface font-bold tracking-wider mb-1 uppercase">{{ $role->nombre ?: $role->name }}</h4>
                <p class="font-body-sm text-sm text-on-surface-variant h-10 line-clamp-2">{{ $role->descripcion ?? 'Acceso al sistema.' }}</p>
            </div>
            <span class="inline-flex items-center gap-1 bg-secondary-container text-secondary px-2 py-1 rounded-sm font-label-caps text-[10px] uppercase">
                <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
                Activo
            </span>
        </div>
        <div class="flex-grow">
            <div class="flex gap-8 mb-6 mt-4">
                <div>
                    <p class="font-body-sm text-sm text-on-surface-variant mb-1">Usuarios</p>
                    <p class="font-numeric-data text-xl font-semibold text-on-surface">{{ $role->users_count }}</p>
                </div>
                <div>
                    <p class="font-body-sm text-sm text-on-surface-variant mb-1">Permisos</p>
                    <p class="font-numeric-data text-xl font-semibold text-on-surface">{{ $role->permissions_count }}</p>
                </div>
            </div>
        </div>
        <div class="pt-4 border-t border-outline-variant flex justify-between items-center mt-auto">
            <div class="flex -space-x-2">
                {{-- Muestra los avatares de algunos usuarios (hasta 3) --}}
                @foreach($role->users()->take(3)->get() as $user)
                    @if($user->foto_perfil_ruta)
                        <img alt="{{ $user->nombre }}" class="w-8 h-8 rounded-full border-2 border-surface-container-lowest object-cover" src="{{ asset($user->foto_perfil_ruta) }}"/>
                    @else
                        <div class="w-8 h-8 rounded-full border-2 border-surface-container-lowest bg-surface-container flex items-center justify-center text-xs text-on-surface-variant font-medium">
                            {{ $user->iniciales }}
                        </div>
                    @endif
                @endforeach
                @if($role->users_count > 3)
                    <div class="w-8 h-8 rounded-full border-2 border-surface-container-lowest bg-surface-container flex items-center justify-center text-xs text-on-surface-variant font-medium">
                        +{{ $role->users_count - 3 }}
                    </div>
                @endif
            </div>
            <a href="{{ route('admin.usuarios.por-rol', $role->id) }}" class="text-primary font-label-caps text-xs font-medium hover:text-primary-fixed-variant transition-colors flex items-center gap-1 group-hover:underline">
                Administrar
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
        </div>
    </div>
    @endforeach
</div>

<!-- Recent Users Quick List (Decorative Context) -->
<div class="mt-12 bg-surface-container-lowest border border-outline-variant rounded-lg p-6">
    <div class="flex justify-between items-center border-b border-outline-variant pb-4 mb-4">
        <h3 class="font-headline-md text-lg font-semibold text-on-surface">Usuarios Recientes</h3>
    </div>
    <div class="space-y-0">
        @forelse($usuariosRecientes as $user)
        <div class="flex items-center justify-between py-3 border-b border-surface-variant hover:bg-surface-dim transition-colors px-2">
            <div class="flex items-center gap-4">
                @if($user->foto_perfil_ruta)
                    <img src="{{ asset($user->foto_perfil_ruta) }}" class="w-10 h-10 rounded-full object-cover">
                @else
                    <div class="w-10 h-10 bg-primary-container rounded-full flex items-center justify-center text-on-primary font-bold">
                        {{ $user->iniciales }}
                    </div>
                @endif
                <div>
                    <p class="font-body-md text-base font-medium text-on-surface">{{ $user->nombre_completo }}</p>
                    <p class="font-body-sm text-sm text-on-surface-variant">{{ $user->email }}</p>
                </div>
            </div>
            <div class="flex items-center gap-6">
                @foreach($user->roles as $r)
                    <span class="font-label-caps text-xs text-on-surface-variant bg-surface-container px-2 py-1 rounded-sm uppercase">{{ $r->name }}</span>
                @endforeach
                <a href="{{ route('admin.usuarios.edit', $user->id) }}" class="text-outline hover:text-primary">
                    <span class="material-symbols-outlined">edit</span>
                </a>
            </div>
        </div>
        @empty
        <div class="py-6 text-center text-slate-500">
            No hay usuarios recientes.
        </div>
        @endforelse
    </div>
</div>

@can('admin.usuarios.gestionar')
<dialog id="crearRolModal" class="p-0 rounded-xl shadow-xl backdrop:bg-slate-900/50 open:animate-in open:fade-in-90 open:zoom-in-95 border border-slate-200">
    <div class="bg-white p-6 w-[400px] max-w-[90vw]">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold text-slate-900">Crear Nuevo Rol</h3>
            <button onclick="document.getElementById('crearRolModal').close()" type="button" class="text-slate-400 hover:text-slate-900 transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form action="{{ route('admin.usuarios.roles.store') }}" method="POST">
            @csrf
            <div class="space-y-4 mb-8">
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Nombre del Rol <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required class="w-full bg-white border border-slate-300 rounded-lg p-3 text-sm focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none" placeholder="Ej. Editor">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-2">Descripción</label>
                    <input type="text" name="descripcion" class="w-full bg-white border border-slate-300 rounded-lg p-3 text-sm focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none" placeholder="Breve resumen de acceso">
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('crearRolModal').close()" class="px-5 py-2.5 rounded-lg border border-slate-300 text-slate-700 font-semibold text-xs uppercase tracking-wide hover:bg-slate-50 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-lg bg-slate-900 text-white font-semibold text-xs uppercase tracking-wide hover:bg-slate-800 shadow-sm transition-all">
                    Guardar Rol
                </button>
            </div>
        </form>
    </div>
</dialog>
@endcan
@endsection
