@extends('layouts.admin')

@section('title', 'Mi Perfil')

@section('breadcrumbs')
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <span class="capitalize font-bold text-slate-900 truncate">Mi Perfil</span>
@endsection

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <!-- Hero de Perfil -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden relative">
        <!-- Banner Background -->
        <div class="h-28 md:h-40 w-full relative bg-[#1F2937]" style="background-image: linear-gradient(to right, #1e293b, #0f172a);">
            <div class="absolute inset-0 opacity-20 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] mix-blend-overlay"></div>
        </div>
        
        <!-- Contenido del Hero -->
        <div class="px-6 sm:px-10 pb-8 relative z-10">
            <div class="flex flex-col md:flex-row gap-4 md:gap-6">
                <!-- Avatar con botón de cambio (Alpine.js para preview) -->
                <div x-data="avatarUpload()" class="relative group shrink-0 -mt-10 md:-mt-16">
                    <form id="fotoForm" action="{{ route('admin.perfil.foto.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <label for="foto_upload" class="cursor-pointer relative block">
                            <template x-if="imageUrl">
                                <img :src="imageUrl" class="w-24 h-24 md:w-32 md:h-32 rounded-full object-cover border-4 border-white shadow-md bg-white">
                            </template>
                            <template x-if="!imageUrl">
                                @if($usuario->foto_perfil_ruta)
                                    <img src="{{ asset($usuario->foto_perfil_ruta) }}" class="w-24 h-24 md:w-32 md:h-32 rounded-full object-cover border-4 border-white shadow-md bg-white">
                                @else
                                    <div class="w-24 h-24 md:w-32 md:h-32 rounded-full bg-slate-800 text-white flex items-center justify-center text-3xl font-bold border-4 border-white shadow-md">
                                        {{ $usuario->iniciales }}
                                    </div>
                                @endif
                            </template>

                            <!-- Overlay Hover -->
                            <div class="absolute inset-0 bg-black/40 rounded-full opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity border-4 border-transparent">
                                <span class="material-symbols-outlined text-white text-3xl">photo_camera</span>
                            </div>
                        </label>
                        <input type="file" id="foto_upload" name="foto" class="hidden" accept="image/jpeg, image/png, image/jpg" @change="fileChosen">
                    </form>
                </div>

                <!-- Textos del Hero (Garantizados sobre fondo blanco) -->
                <div class="flex-1 pt-2 md:pt-4 pb-2">
                    <h2 class="text-2xl md:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $usuario->nombre_completo }}</h2>
                    <div class="flex flex-wrap items-center gap-3 mt-2 text-slate-600 text-sm md:text-base font-semibold">
                        <span class="flex items-center gap-1.5 bg-slate-100 px-3 py-1 rounded-lg text-slate-800 border border-slate-200 shadow-sm">
                            <span class="material-symbols-outlined text-[18px]">admin_panel_settings</span>
                            {{ $usuario->getRoleNames()->first() ?? 'Administrador' }}
                        </span>
                        <span class="flex items-center gap-1.5 bg-slate-50 px-3 py-1 rounded-lg border border-slate-200 text-slate-700 shadow-sm">
                            <span class="material-symbols-outlined text-[18px]">mail</span>
                            {{ $usuario->email }}
                        </span>
                    </div>
                </div>
                
                <!-- Mini estadísticas -->
                <div class="flex gap-6 sm:gap-8 border-t md:border-t-0 md:border-l border-slate-200 pt-5 md:pt-4 md:pl-8 text-center shrink-0 mt-4 md:mt-0">
                    <div class="flex flex-col items-center justify-center">
                        <div class="text-3xl font-black text-slate-800">{{ $actividadReciente->count() }}</div>
                        <div class="text-[11px] uppercase font-extrabold tracking-widest text-slate-500 mt-1">Acciones Recientes</div>
                    </div>
                    <div class="flex flex-col items-center justify-center">
                        <div class="text-3xl font-black text-emerald-600">
                            <span class="material-symbols-outlined text-[32px]" style="font-variation-settings: 'FILL' 1;">
                                {{ $usuario->two_fa_habilitado ? 'verified_user' : 'gpp_bad' }}
                            </span>
                        </div>
                        <div class="text-[11px] uppercase font-extrabold tracking-widest text-slate-500 mt-1">
                            {{ $usuario->two_fa_habilitado ? '2FA Activo' : '2FA Inactivo' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @error('foto')
            <div class="px-6 pb-4 text-sm text-red-500 font-medium">{{ $message }}</div>
        @enderror
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Columna Izquierda (Datos y Seguridad) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Tarjeta: Información Personal -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 bg-blue-100 text-blue-600 rounded-lg">
                            <span class="material-symbols-outlined text-[20px]">person</span>
                        </div>
                        <h3 class="font-bold text-slate-800">Información Personal</h3>
                    </div>
                </div>
                <div class="p-6">
                    <form action="{{ route('admin.perfil.datos.update') }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                                <input type="text" name="nombre" value="{{ old('nombre', $usuario->nombre) }}" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-slate-800 transition-all outline-none">
                                @error('nombre') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Apellido <span class="text-red-500">*</span></label>
                                <input type="text" name="apellido" value="{{ old('apellido', $usuario->apellido) }}" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-slate-800 transition-all outline-none">
                                @error('apellido') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Correo Electrónico (Solo Lectura)</label>
                                <input type="email" value="{{ $usuario->email }}" disabled class="w-full px-3.5 py-2.5 text-sm bg-slate-100 border border-slate-200 rounded-xl text-slate-500 cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Teléfono</label>
                                <input type="text" name="telefono" value="{{ old('telefono', $usuario->telefono) }}" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-slate-800 transition-all outline-none">
                                @error('telefono') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Fecha de Nacimiento</label>
                                <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $usuario->fecha_nacimiento?->format('Y-m-d')) }}" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-slate-800 transition-all outline-none">
                                @error('fecha_nacimiento') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="pt-4 flex justify-end">
                            <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors">
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tarjeta: Seguridad (Contraseña) -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 bg-amber-100 text-amber-600 rounded-lg">
                            <span class="material-symbols-outlined text-[20px]">lock</span>
                        </div>
                        <h3 class="font-bold text-slate-800">Seguridad de la Cuenta</h3>
                    </div>
                </div>
                <div class="p-6">
                    <form action="{{ route('admin.perfil.password.update') }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="space-y-4 max-w-md">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Contraseña Actual <span class="text-red-500">*</span></label>
                                <input type="password" name="current_password" class="w-full px-3.5 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-slate-800 transition-all outline-none">
                                @error('current_password') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Inputs de Contraseña con Toggle (Alpine) -->
                            <div x-data="{ show: false }">
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Nueva Contraseña <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input :type="show ? 'text' : 'password'" name="password" class="w-full pl-3.5 pr-10 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-slate-800 transition-all outline-none">
                                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                        <span class="material-symbols-outlined text-[20px]" x-text="show ? 'visibility_off' : 'visibility'"></span>
                                    </button>
                                </div>
                                @error('password') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div x-data="{ show: false }">
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">Confirmar Nueva Contraseña <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input :type="show ? 'text' : 'password'" name="password_confirmation" class="w-full pl-3.5 pr-10 py-2.5 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-slate-800 transition-all outline-none">
                                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                        <span class="material-symbols-outlined text-[20px]" x-text="show ? 'visibility_off' : 'visibility'"></span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 flex justify-start">
                            <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors">
                                Actualizar Contraseña
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

        <!-- Columna Derecha (2FA y Logs) -->
        <div class="space-y-6">
            
            <!-- Tarjeta: 2FA -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 text-sm">Autenticación de 2 Factores</h3>
                </div>
                <div class="p-5">
                    <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                        Protege tu cuenta requiriendo un código adicional cada vez que inicies sesión desde un dispositivo no reconocido.
                    </p>
                    
                    <form action="{{ route('admin.perfil.2fa.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="two_fa_habilitado" value="0">
                        
                        <label class="flex items-center gap-3 cursor-pointer p-3 border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
                            <div class="relative inline-flex items-center">
                                <input type="checkbox" name="two_fa_habilitado" value="1" class="sr-only peer" onchange="this.form.submit()" {{ $usuario->two_fa_habilitado ? 'checked' : '' }}>
                                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500"></div>
                            </div>
                            <span class="text-sm font-bold text-slate-700 select-none">
                                {{ $usuario->two_fa_habilitado ? 'Desactivar 2FA' : 'Activar 2FA' }}
                            </span>
                        </label>
                    </form>
                </div>
            </div>

            <!-- Tarjeta: Actividad Reciente -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-sm">Actividad Reciente</h3>
                </div>
                <div class="p-5">
                    @if($actividadReciente->count() > 0)
                        <div class="relative border-l border-slate-200 ml-3 space-y-6">
                            @foreach($actividadReciente as $log)
                                <div class="relative pl-6">
                                    <span class="absolute top-1.5 w-2.5 h-2.5 bg-white border-2 border-slate-300 rounded-full" style="left: -5px;"></span>
                                    <div class="text-xs text-slate-400 font-medium mb-0.5">
                                        {{ $log->creado_en->locale('es')->diffForHumans() }}
                                    </div>
                                    <div class="text-sm font-bold text-slate-700">
                                        {{ $log->accion }}
                                    </div>
                                    <div class="text-xs text-slate-500 mt-1 leading-relaxed">
                                        {{ $log->descripcion }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6">
                            <span class="material-symbols-outlined text-4xl text-slate-200 mb-2">history</span>
                            <p class="text-sm text-slate-500">No hay actividad reciente registrada.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('avatarUpload', () => ({
            imageUrl: null,
            fileChosen(event) {
                const file = event.target.files[0];
                if (file) {
                    this.imageUrl = URL.createObjectURL(file);
                    // Opcional: auto-submit
                    document.getElementById('fotoForm').submit();
                }
            }
        }));
    });

    // Mantener la posición del scroll al recargar (ej. al haber errores de validación)
    document.addEventListener("DOMContentLoaded", function() {
        const scrollKey = 'perfil_scroll_' + window.location.pathname;
        const savedScroll = sessionStorage.getItem(scrollKey);
        
        if (savedScroll !== null) {
            window.scrollTo({
                top: parseInt(savedScroll, 10),
                behavior: 'instant'
            });
            sessionStorage.removeItem(scrollKey);
        }

        // Guardar scroll antes de enviar el formulario o recargar
        window.addEventListener('beforeunload', () => {
            sessionStorage.setItem(scrollKey, window.scrollY);
        });
    });
</script>
@endpush
