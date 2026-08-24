@extends('layouts.admin')

@php
    $isEdit = isset($usuario);
    $title = $isEdit ? 'Editar Usuario' : 'Nuevo Usuario';
    $description = $isEdit ? 'Modifique la información y permisos del operador.' : 'Complete la información para registrar un nuevo operador en el sistema.';
    $actionUrl = $isEdit ? route('admin.usuarios.update', $usuario->id) : route('admin.usuarios.store', $rol?->id ?? 1);
    $backRoute = $rol ? route('admin.usuarios.por-rol', $rol->id) : route('admin.usuarios.index');
@endphp

@section('title', $title)

@section('content')
<form action="{{ $actionUrl }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ $backRoute }}" class="text-slate-500 hover:text-slate-900 transition-colors">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ $title }}</h1>
            </div>
            <p class="text-sm text-slate-500 font-medium ml-9">{{ $description }}</p>
        </div>
        <div class="flex items-center gap-4">
            @if($isEdit && $usuario->id !== auth()->id() && !($usuario->hasRole('super_admin') && !auth()->user()->hasRole('super_admin')))
                <button type="button" 
                        onclick="window.ModalEliminar.abrir({ url: '{{ route('admin.usuarios.destroy', $usuario->id) }}', nombre: '{{ addslashes($usuario->nombre) }}', titulo: 'Eliminar Usuario (Cascada)', mensaje: 'Se eliminará permanentemente este usuario junto con TODOS sus pedidos, carritos, devoluciones y facturas. Esta acción NO se puede deshacer.' })"
                        class="px-6 py-2 rounded-lg border border-red-200 text-red-600 font-semibold text-xs uppercase tracking-wide hover:bg-red-50 hover:text-red-700 transition-colors flex items-center">
                    <span class="material-symbols-outlined mr-2 text-[18px]">delete</span>
                    Eliminar
                </button>
            @endif
            <a href="{{ $backRoute }}" class="px-6 py-2 rounded-lg border border-slate-300 text-slate-700 font-semibold text-xs uppercase tracking-wide hover:bg-slate-50 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2 rounded-lg bg-slate-900 text-white font-semibold text-xs uppercase tracking-wide hover:bg-slate-800 shadow-sm transition-all flex items-center">
                <span class="material-symbols-outlined mr-2 text-[18px]">save</span>
                Guardar Usuario
            </button>
        </div>
    </div>

    <!-- Form Layout (Bento Grid approach) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Column 1: Personal & Security (Spans 2 cols on Desktop) -->
        <div class="lg:col-span-2 flex flex-col gap-6">
            
            <!-- Section 1: Personal Info -->
            <div class="bg-white border border-slate-200 rounded-xl p-8 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900 mb-6 border-b border-slate-100 pb-4">Información Personal</h2>
                <div class="flex flex-col md:flex-row gap-8 mb-6">
                    
                    <!-- Avatar Upload -->
                    <div class="flex flex-col items-center justify-start space-y-3 w-32 shrink-0">
                        <div class="w-24 h-24 rounded-full bg-slate-50 border border-dashed border-slate-300 flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:border-slate-400 cursor-pointer transition-all group overflow-hidden relative">
                            @if($isEdit && $usuario->foto_perfil_ruta)
                                <img src="{{ asset($usuario->foto_perfil_ruta) }}" class="w-full h-full object-cover">
                            @else
                                <span class="material-symbols-outlined text-3xl group-hover:hidden">add_a_photo</span>
                            @endif
                            <div class="absolute inset-0 bg-black/40 hidden group-hover:flex items-center justify-center">
                                <span class="material-symbols-outlined text-white">upload</span>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider text-center">Subir Foto</span>
                        <input type="file" name="foto_perfil" class="hidden">
                    </div>

                    <!-- Core Details Grid -->
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Nombre(s) <span class="text-red-500">*</span></label>
                            <input name="nombre" value="{{ old('nombre', $usuario->nombre ?? '') }}" required class="w-full bg-white border border-slate-300 rounded-lg p-3 text-sm text-slate-900 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-shadow" placeholder="Ej. Carlos" type="text"/>
                            @error('nombre') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Apellido(s)</label>
                            <input name="apellido" value="{{ old('apellido', $usuario->apellido ?? '') }}" class="w-full bg-white border border-slate-300 rounded-lg p-3 text-sm text-slate-900 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-shadow" placeholder="Ej. Mendoza" type="text"/>
                            @error('apellido') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-1 md:col-span-2">
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Correo Electrónico <span class="text-red-500">*</span></label>
                            <input name="email" value="{{ old('email', $usuario->email ?? '') }}" required class="w-full bg-white border border-slate-300 rounded-lg p-3 text-sm text-slate-900 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-shadow" placeholder="correo@institucion.com" type="email"/>
                            @error('email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-1 md:col-span-2">
                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Teléfono Móvil</label>
                            <div class="flex">
                                <span class="inline-flex items-center px-3 border border-r-0 border-slate-300 rounded-l-lg bg-slate-50 text-slate-500 text-sm font-medium">+507</span>
                                <input name="telefono" value="{{ old('telefono', $usuario->telefono ?? '') }}" class="w-full bg-white border border-slate-300 rounded-r-lg p-3 text-sm text-slate-900 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-shadow" placeholder="0000-0000" type="tel"/>
                            </div>
                            @error('telefono') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Security -->
            <div class="bg-white border border-slate-200 rounded-xl p-8 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Seguridad de Acceso</h2>
                        <p class="text-sm text-slate-500 mt-1">Gestión de credenciales y contraseña.</p>
                    </div>
                    <span class="material-symbols-outlined text-slate-300 text-3xl">lock</span>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Contraseña {{ $isEdit ? '(Dejar en blanco para no cambiar)' : '*' }}</label>
                        <input name="password" {{ !$isEdit ? 'required' : '' }} class="w-full bg-white border border-slate-300 rounded-lg p-3 text-sm text-slate-900 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-shadow" placeholder="••••••••" type="password"/>
                        @error('password') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Confirmar Contraseña {{ !$isEdit ? '*' : '' }}</label>
                        <input name="password_confirmation" {{ !$isEdit ? 'required' : '' }} class="w-full bg-white border border-slate-300 rounded-lg p-3 text-sm text-slate-900 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-shadow" placeholder="••••••••" type="password"/>
                    </div>
                </div>
            </div>
        </div>

        <!-- Column 2: Role & Status (Spans 1 col) -->
        <div class="flex flex-col gap-6">
            
            <!-- Section 3: Status Toggle -->
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm flex items-center justify-between">
                <div>
                    <h3 class="text-[10px] font-bold text-slate-900 uppercase tracking-wider">Estado del Usuario</h3>
                    <p class="text-sm mt-1 {{ old('estado', $usuario->activo ?? true) ? 'text-emerald-600 font-bold' : 'text-slate-500' }}" id="status-text">
                        Actualmente: {{ old('estado', $usuario->activo ?? true) ? 'Activo' : 'Inactivo' }}
                    </p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="estado" value="1" class="sr-only peer" 
                           onchange="
                               const st = document.getElementById('status-text');
                               if(this.checked) {
                                   st.innerText = 'Actualmente: Activo';
                                   st.className = 'text-sm mt-1 text-emerald-600 font-bold';
                               } else {
                                   st.innerText = 'Actualmente: Inactivo';
                                   st.className = 'text-sm mt-1 text-slate-500';
                               }
                           "
                           {{ old('estado', $usuario->activo ?? true) ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500 border border-slate-300"></div>
                </label>
            </div>

            <!-- Section 4: Role Selection -->
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900 mb-2">Asignación de Rol</h2>
                <p class="text-sm text-slate-500 mb-6">Seleccione el nivel de acceso operativo para este usuario.</p>
                
                <div class="space-y-3">
                    @php
                        $selectedRoleId = old('rol_id', $rol->id ?? null);
                    @endphp
                    
                    @foreach($roles as $r)
                        @php
                            $isSelected = $selectedRoleId == $r->id;
                            $icon = match($r->name) {
                                'super_admin' => 'admin_panel_settings',
                                'Administrador' => 'manage_accounts',
                                default => 'person'
                            };
                            $iconColor = match($r->name) {
                                'super_admin' => 'text-red-600',
                                'Administrador' => 'text-slate-900',
                                default => 'text-slate-500'
                            };
                        @endphp
                        
                        <label class="block cursor-pointer relative" onclick="selectRole('{{ $r->id }}', '{{ $r->name }}')">
                            <input class="peer sr-only" name="rol_id" type="radio" value="{{ $r->id }}" {{ $isSelected ? 'checked' : '' }}/>
                            <div id="card-{{ $r->id }}" class="p-4 rounded-lg border {{ $isSelected ? 'border-slate-900 bg-slate-50 ring-1 ring-slate-900' : 'border-slate-200 bg-white hover:bg-slate-50' }} transition-colors flex gap-4 items-start">
                                <div class="mt-0.5 {{ $iconColor }}">
                                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">{{ $icon }}</span>
                                </div>
                                <div>
                                    <div class="text-[10px] font-bold text-slate-900 uppercase tracking-wider">{{ $r->nombre ?: $r->name }}</div>
                                    <div class="text-xs text-slate-500 mt-1">{{ $r->descripcion ?? 'Acceso al sistema.' }}</div>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize based on current selection
    });

    function selectRole(roleId) {
        const rolesIds = @json($roles->pluck('id'));

        // Reset all cards
        rolesIds.forEach(id => {
            const card = document.getElementById('card-' + id);
            if (card) {
                card.className = "p-4 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 transition-colors flex gap-4 items-start";
            }
        });

        // Apply active state
        const activeCard = document.getElementById('card-' + roleId);
        if (activeCard) {
            activeCard.className = "p-4 rounded-lg border border-slate-900 bg-slate-50 ring-1 ring-slate-900 transition-colors flex gap-4 items-start";
        }

    }
</script>
@endsection
