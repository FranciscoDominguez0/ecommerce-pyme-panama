@extends('layouts.admin')

@section('title', 'Mi Perfil')

@section('breadcrumbs')
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <span class="capitalize font-bold text-slate-900 dark:text-white truncate">Mi Perfil</span>
@endsection

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <!-- Hero de Perfil -->
    <div class="bg-white dark:bg-[#181a1b] rounded-3xl shadow-sm border border-slate-200 dark:border-gray-700 overflow-hidden relative">
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
                                <img :src="imageUrl" class="w-24 h-24 md:w-32 md:h-32 rounded-full object-cover border-4 border-white shadow-md bg-white dark:bg-[#181a1b]">
                            </template>
                            <template x-if="!imageUrl">
                                @if($usuario->foto_perfil_ruta)
                                    <img src="{{ asset($usuario->foto_perfil_ruta) }}" class="w-24 h-24 md:w-32 md:h-32 rounded-full object-cover border-4 border-white shadow-md bg-white dark:bg-[#181a1b]">
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
                    <h2 id="hero-nombre-completo" class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $usuario->nombre_completo }}</h2>
                    <div class="flex flex-wrap items-center gap-3 mt-2 text-slate-600 dark:text-slate-400 text-sm md:text-base font-semibold">
                        <span class="flex items-center gap-1.5 bg-slate-100 dark:bg-transparent px-3 py-1 rounded-lg text-slate-800 dark:text-gray-100 border border-slate-200 dark:border-gray-700 shadow-sm">
                            <span class="material-symbols-outlined text-[18px]">admin_panel_settings</span>
                            {{ $usuario->getRoleNames()->first() ?? 'Administrador' }}
                        </span>
                        <span class="flex items-center gap-1.5 bg-slate-50 dark:bg-transparent px-3 py-1 rounded-lg border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-slate-300 shadow-sm">
                            <span class="material-symbols-outlined text-[18px]">mail</span>
                            {{ $usuario->email }}
                        </span>
                    </div>
                </div>
                
                <!-- Mini estadísticas -->
                <div class="flex gap-6 sm:gap-8 border-t md:border-t-0 md:border-l border-slate-200 dark:border-gray-700 pt-5 md:pt-4 md:pl-8 text-center shrink-0 mt-4 md:mt-0">
                    <div class="flex flex-col items-center justify-center">
                        <div class="text-3xl font-black text-slate-800 dark:text-gray-100">{{ $actividadReciente->count() }}</div>
                        <div class="text-[11px] uppercase font-extrabold tracking-widest text-slate-500 dark:text-slate-400 mt-1">Acciones Recientes</div>
                    </div>
                    <div class="flex flex-col items-center justify-center">
                        <div class="text-3xl font-black text-emerald-600">
                            <span id="stat-2fa-icon" class="material-symbols-outlined text-[32px]" style="font-variation-settings: 'FILL' 1;">
                                {{ $usuario->two_fa_habilitado ? 'verified_user' : 'gpp_bad' }}
                            </span>
                        </div>
                        <div id="stat-2fa-status" class="text-[11px] uppercase font-extrabold tracking-widest text-slate-500 dark:text-slate-400 mt-1">
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
            <div class="bg-white dark:bg-[#181a1b] rounded-2xl shadow-sm border border-slate-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-gray-700 bg-slate-50 dark:bg-transparent/50 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 bg-blue-100 text-blue-600 rounded-lg">
                            <span class="material-symbols-outlined text-[20px]">person</span>
                        </div>
                        <h3 class="font-bold text-slate-800 dark:text-gray-100">Información Personal</h3>
                    </div>
                </div>
                <div class="p-6">
                    <form action="{{ route('admin.perfil.datos.update') }}" 
                          method="POST" 
                          class="space-y-4"
                          x-data="{
                              cargando: false,
                              errores: {},
                              guardarDatos(e) {
                                  if (this.cargando) return;
                                  this.cargando = true;
                                  this.errores = {};
                                  const form = e.target;
                                  const formData = new FormData(form);

                                  fetch(form.action, {
                                      method: 'POST',
                                      headers: {
                                          'X-Requested-With': 'XMLHttpRequest',
                                          'Accept': 'application/json',
                                          'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                      },
                                      body: formData
                                  })
                                  .then(async res => {
                                      const data = await res.json().catch(() => ({}));
                                      this.cargando = false;
                                      if (res.ok && data.exito) {
                                          if (data.usuario && data.usuario.nombre_completo) {
                                              const heroNombre = document.getElementById('hero-nombre-completo');
                                              if (heroNombre) heroNombre.textContent = data.usuario.nombre_completo;
                                          }
                                          if (window.mostrarToast) {
                                              window.mostrarToast('success', data.mensaje);
                                          }
                                      } else if (res.status === 422 && data.errors) {
                                          this.errores = data.errors;
                                          if (window.mostrarToast) {
                                              window.mostrarToast('error', 'Por favor verifica los campos con error.');
                                          }
                                      } else {
                                          if (window.mostrarToast) {
                                              window.mostrarToast('error', data.mensaje || 'Error al actualizar los datos.');
                                          }
                                      }
                                  })
                                  .catch(() => {
                                      this.cargando = false;
                                      if (window.mostrarToast) {
                                          window.mostrarToast('error', 'Error de conexión con el servidor.');
                                      }
                                  });
                              }
                          }"
                          @submit.prevent="guardarDatos($event)">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                                <input type="text" name="nombre" value="{{ old('nombre', $usuario->nombre) }}" 
                                       class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-transparent border border-slate-200 dark:border-gray-700 rounded-xl focus:bg-white dark:bg-[#181a1b] focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-slate-800 dark:text-gray-100 transition-all outline-none"
                                       :class="errores.nombre ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''">
                                <template x-if="errores.nombre">
                                    <span class="text-xs text-red-500 mt-1 block" x-text="errores.nombre[0]"></span>
                                </template>
                                @error('nombre') <span class="text-xs text-red-500 mt-1 block" x-show="!errores.nombre">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Apellido <span class="text-red-500">*</span></label>
                                <input type="text" name="apellido" value="{{ old('apellido', $usuario->apellido) }}" 
                                       class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-transparent border border-slate-200 dark:border-gray-700 rounded-xl focus:bg-white dark:bg-[#181a1b] focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-slate-800 dark:text-gray-100 transition-all outline-none"
                                       :class="errores.apellido ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''">
                                <template x-if="errores.apellido">
                                    <span class="text-xs text-red-500 mt-1 block" x-text="errores.apellido[0]"></span>
                                </template>
                                @error('apellido') <span class="text-xs text-red-500 mt-1 block" x-show="!errores.apellido">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Correo Electrónico (Solo Lectura)</label>
                                <input type="email" value="{{ $usuario->email }}" disabled class="w-full px-3.5 py-2.5 text-sm bg-slate-100 dark:bg-transparent border border-slate-200 dark:border-gray-700 rounded-xl text-slate-500 dark:text-slate-400 cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Teléfono</label>
                                <input type="text" name="telefono" value="{{ old('telefono', $usuario->telefono) }}" 
                                       class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-transparent border border-slate-200 dark:border-gray-700 rounded-xl focus:bg-white dark:bg-[#181a1b] focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-slate-800 dark:text-gray-100 transition-all outline-none"
                                       :class="errores.telefono ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''">
                                <template x-if="errores.telefono">
                                    <span class="text-xs text-red-500 mt-1 block" x-text="errores.telefono[0]"></span>
                                </template>
                                @error('telefono') <span class="text-xs text-red-500 mt-1 block" x-show="!errores.telefono">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Fecha de Nacimiento</label>
                                <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $usuario->fecha_nacimiento?->format('Y-m-d')) }}" 
                                       class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-transparent border border-slate-200 dark:border-gray-700 rounded-xl focus:bg-white dark:bg-[#181a1b] focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-slate-800 dark:text-gray-100 transition-all outline-none"
                                       :class="errores.fecha_nacimiento ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''">
                                <template x-if="errores.fecha_nacimiento">
                                    <span class="text-xs text-red-500 mt-1 block" x-text="errores.fecha_nacimiento[0]"></span>
                                </template>
                                @error('fecha_nacimiento') <span class="text-xs text-red-500 mt-1 block" x-show="!errores.fecha_nacimiento">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="pt-4 flex justify-end">
                            <button type="submit" 
                                    :disabled="cargando"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-800 hover:bg-slate-900 disabled:opacity-60 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl shadow-sm transition-all outline-none focus:outline-none focus:ring-0 active:outline-none select-none">
                                <template x-if="cargando">
                                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </template>
                                <span x-text="cargando ? 'Guardando...' : 'Guardar Cambios'">Guardar Cambios</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tarjeta: Seguridad (Contraseña) -->
            <div class="bg-white dark:bg-[#181a1b] rounded-2xl shadow-sm border border-slate-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-gray-700 bg-slate-50 dark:bg-transparent/50 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="p-1.5 bg-amber-100 text-amber-600 rounded-lg">
                            <span class="material-symbols-outlined text-[20px]">lock</span>
                        </div>
                        <h3 class="font-bold text-slate-800 dark:text-gray-100">Seguridad de la Cuenta</h3>
                    </div>
                </div>
                <div class="p-6">
                    <form action="{{ route('admin.perfil.password.update') }}" 
                          method="POST" 
                          class="space-y-4"
                          x-data="{
                              cargando: false,
                              errores: {},
                              guardarPassword(e) {
                                  if (this.cargando) return;
                                  this.cargando = true;
                                  this.errores = {};
                                  const form = e.target;
                                  const formData = new FormData(form);

                                  fetch(form.action, {
                                      method: 'POST',
                                      headers: {
                                          'X-Requested-With': 'XMLHttpRequest',
                                          'Accept': 'application/json',
                                          'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                      },
                                      body: formData
                                  })
                                  .then(async res => {
                                      const data = await res.json().catch(() => ({}));
                                      this.cargando = false;
                                      if (res.ok && data.exito) {
                                          form.reset();
                                          if (window.mostrarToast) {
                                              window.mostrarToast('success', data.mensaje);
                                          }
                                      } else if (res.status === 422 && data.errors) {
                                          this.errores = data.errors;
                                          if (window.mostrarToast) {
                                              window.mostrarToast('error', data.errors.current_password ? data.errors.current_password[0] : 'Verifica los campos de contraseña.');
                                          }
                                      } else {
                                          if (window.mostrarToast) {
                                              window.mostrarToast('error', data.mensaje || 'Error al actualizar la contraseña.');
                                          }
                                      }
                                  })
                                  .catch(() => {
                                      this.cargando = false;
                                      if (window.mostrarToast) {
                                          window.mostrarToast('error', 'Error de conexión con el servidor.');
                                      }
                                  });
                              }
                          }"
                          @submit.prevent="guardarPassword($event)">
                        @csrf
                        @method('PUT')

                        <div class="space-y-4 max-w-md">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Contraseña Actual <span class="text-red-500">*</span></label>
                                <input type="password" name="current_password" 
                                       class="w-full px-3.5 py-2.5 text-sm bg-slate-50 dark:bg-transparent border border-slate-200 dark:border-gray-700 rounded-xl focus:bg-white dark:bg-[#121415] focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-slate-800 dark:text-gray-100 transition-all outline-none"
                                       :class="errores.current_password ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''">
                                <template x-if="errores.current_password">
                                    <span class="text-xs text-red-500 mt-1 block" x-text="errores.current_password[0]"></span>
                                </template>
                                @error('current_password') <span class="text-xs text-red-500 mt-1 block" x-show="!errores.current_password">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Inputs de Contraseña con Toggle (Alpine) -->
                            <div x-data="{ show: false }">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Nueva Contraseña <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input :type="show ? 'text' : 'password'" name="password" 
                                           class="w-full pl-3.5 pr-10 py-2.5 text-sm bg-slate-50 dark:bg-transparent border border-slate-200 dark:border-gray-700 rounded-xl focus:bg-white dark:bg-[#121415] focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-slate-800 dark:text-gray-100 transition-all outline-none"
                                           :class="errores.password ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''">
                                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:text-slate-400 outline-none focus:outline-none focus:ring-0">
                                        <span class="material-symbols-outlined text-[20px]" x-text="show ? 'visibility_off' : 'visibility'"></span>
                                    </button>
                                </div>
                                <template x-if="errores.password">
                                    <span class="text-xs text-red-500 mt-1 block" x-text="errores.password[0]"></span>
                                </template>
                                @error('password') <span class="text-xs text-red-500 mt-1 block" x-show="!errores.password">{{ $message }}</span> @enderror
                            </div>

                            <div x-data="{ show: false }">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Confirmar Nueva Contraseña <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <input :type="show ? 'text' : 'password'" name="password_confirmation" 
                                           class="w-full pl-3.5 pr-10 py-2.5 text-sm bg-slate-50 dark:bg-transparent border border-slate-200 dark:border-gray-700 rounded-xl focus:bg-white dark:bg-[#121415] focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 text-slate-800 dark:text-gray-100 transition-all outline-none">
                                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:text-slate-400 outline-none focus:outline-none focus:ring-0">
                                        <span class="material-symbols-outlined text-[20px]" x-text="show ? 'visibility_off' : 'visibility'"></span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 flex justify-start">
                            <button type="submit" 
                                    :disabled="cargando"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-500 hover:bg-amber-600 disabled:opacity-60 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl shadow-sm transition-all outline-none focus:outline-none focus:ring-0 active:outline-none select-none">
                                <template x-if="cargando">
                                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </template>
                                <span x-text="cargando ? 'Actualizando...' : 'Actualizar Contraseña'">Actualizar Contraseña</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

        <!-- Columna Derecha (2FA y Logs) -->
        <div class="space-y-6">
            
            <!-- Tarjeta: 2FA -->
            <div class="bg-white dark:bg-[#181a1b] rounded-2xl shadow-sm border border-slate-200 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-gray-700 bg-slate-50 dark:bg-transparent/50 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 dark:text-gray-100 text-sm">Autenticación de 2 Factores</h3>
                </div>
                <div class="p-5">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4 leading-relaxed">
                        Añade una capa de seguridad extra con un código de verificación.
                    </p>
                    
                    <div x-data="{
                        habilitado: {{ $usuario->two_fa_habilitado ? 'true' : 'false' }},
                        cargando: false,
                        toggle() {
                            if (this.cargando) return;
                            this.cargando = true;
                            const nuevoEstado = !this.habilitado;

                            fetch('{{ route('admin.perfil.2fa.update') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    _method: 'PUT',
                                    two_fa_habilitado: nuevoEstado ? 1 : 0
                                })
                            })
                            .then(res => res.json())
                            .then(data => {
                                this.cargando = false;
                                if (data.exito) {
                                    this.habilitado = nuevoEstado;
                                    const statEl = document.getElementById('stat-2fa-status');
                                    const iconEl = document.getElementById('stat-2fa-icon');
                                    if (statEl) statEl.textContent = nuevoEstado ? '2FA Activo' : '2FA Inactivo';
                                    if (iconEl) iconEl.textContent = nuevoEstado ? 'verified_user' : 'gpp_bad';
                                    if (window.mostrarToast) {
                                        window.mostrarToast('success', data.mensaje);
                                    }
                                } else {
                                    if (window.mostrarToast) {
                                        window.mostrarToast('error', data.mensaje || 'Error al actualizar 2FA.');
                                    }
                                }
                            })
                            .catch(err => {
                                this.cargando = false;
                                if (window.mostrarToast) {
                                    window.mostrarToast('error', 'Error de conexión.');
                                }
                            });
                        }
                    }">
                        <label @click.prevent="toggle()" 
                               class="flex items-center gap-3 cursor-pointer p-3 border border-slate-200 dark:border-gray-700 rounded-xl hover:bg-slate-50 dark:bg-transparent dark:hover:bg-gray-700/30 transition-all outline-none focus:outline-none focus:ring-0 focus-within:outline-none focus-within:ring-0 select-none"
                               :class="{ 'opacity-60 pointer-events-none': cargando }">
                            <div class="relative inline-flex items-center">
                                <div class="w-9 h-5 rounded-full transition-colors relative"
                                     :class="habilitado ? 'bg-emerald-500' : 'bg-slate-200 dark:bg-gray-700'">
                                    <div class="absolute top-[2px] left-[2px] bg-white dark:bg-[#181a1b] border border-slate-300 dark:border-gray-700 rounded-full h-4 w-4 transition-transform"
                                         :class="habilitado ? 'translate-x-full border-white' : ''"></div>
                                </div>
                            </div>
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-300 select-none" 
                                  x-text="habilitado ? 'Desactivar 2FA' : 'Activar 2FA'"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Tarjeta: Sesiones Activas -->
            <div class="bg-white dark:bg-[#181a1b] rounded-2xl shadow-sm border border-slate-200 dark:border-gray-700 overflow-hidden" x-data="{ modalOpen: false }">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-gray-700 bg-slate-50 dark:bg-transparent/50 flex items-center justify-between">
                    <h3 class="font-bold text-slate-800 dark:text-gray-100 text-sm">Sesiones Activas</h3>
                </div>
                <div class="p-5 space-y-4">
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                        Cierra la sesión en otros dispositivos por seguridad si notas actividad inusual.
                    </p>
                    
                    @if(isset($sesionesActivas) && count($sesionesActivas) > 0)
                        <div class="space-y-3">
                            @foreach($sesionesActivas as $sesion)
                                <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-transparent border border-slate-200 dark:border-gray-700 rounded-xl">
                                    <span class="material-symbols-outlined text-3xl {{ $sesion->is_current_device ? 'text-emerald-500' : 'text-slate-400' }}">
                                        {{ $sesion->agent->icon }}
                                    </span>
                                    <div class="flex-1 min-w-0 text-left">
                                        <div class="text-sm font-bold text-slate-800 dark:text-gray-100 truncate">
                                            {{ $sesion->agent->platform }} - {{ $sesion->agent->browser }}
                                        </div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate flex gap-1 items-center">
                                            <span>{{ $sesion->ip_address }}</span>
                                            <span>&bull;</span>
                                            @if($sesion->is_current_device)
                                                <span class="text-emerald-600 font-semibold">Este dispositivo</span>
                                            @else
                                                <span>Última actividad {{ $sesion->last_active }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="pt-2">
                        <button type="button" @click="modalOpen = true" class="w-full px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors text-center outline-none focus:outline-none focus:ring-0">
                            Cerrar Sesión en Otros Dispositivos
                        </button>
                    </div>
                </div>

                <!-- Modal de Confirmación para Cerrar Sesiones -->
                <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm" style="display: none;">
                    <div @click.outside="modalOpen = false" class="bg-white dark:bg-[#181a1b] rounded-2xl shadow-xl border border-slate-200 dark:border-gray-700 max-w-md w-full mx-4 p-6"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95">
                        
                        <div class="flex items-center gap-3 mb-4 text-slate-800 dark:text-gray-100">
                            <span class="material-symbols-outlined text-3xl text-rose-500">warning</span>
                            <h3 class="text-lg font-bold">Cerrar Sesión en Otros Dispositivos</h3>
                        </div>
                        
                        <p class="text-sm text-slate-600 dark:text-slate-400 mb-5">
                            Ingresa tu contraseña para confirmar que deseas cerrar la sesión en todos los demás dispositivos y navegadores.
                        </p>
                        
                        <form action="{{ route('admin.perfil.sesiones.destroy') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            
                            <div x-data="{ show: false }" class="mb-5">
                                <div class="relative">
                                    <input :type="show ? 'text' : 'password'" name="password" placeholder="Contraseña Actual" required class="w-full pl-3.5 pr-10 py-2.5 text-sm bg-slate-50 dark:bg-transparent border border-slate-200 dark:border-gray-700 rounded-xl focus:bg-white dark:bg-[#121415] focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 text-slate-800 dark:text-gray-100 transition-all outline-none">
                                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:text-slate-400 outline-none focus:outline-none focus:ring-0">
                                        <span class="material-symbols-outlined text-[20px]" x-text="show ? 'visibility_off' : 'visibility'"></span>
                                    </button>
                                </div>
                                @error('password') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="flex justify-end gap-2">
                                <button type="button" @click="modalOpen = false" class="px-4 py-2 bg-slate-100 dark:bg-transparent hover:bg-slate-200 dark:bg-gray-700 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-xl transition-colors outline-none focus:outline-none focus:ring-0">
                                    Cancelar
                                </button>
                                <button type="submit" class="px-4 py-2 bg-rose-500 hover:bg-rose-600 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors outline-none focus:outline-none focus:ring-0">
                                    Cerrar Sesiones
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tarjeta: Actividad Reciente -->
            <div class="bg-white dark:bg-[#181a1b] rounded-2xl shadow-sm border border-slate-200 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 dark:border-gray-700 bg-slate-50 dark:bg-transparent/50">
                    <h3 class="font-bold text-slate-800 dark:text-gray-100 text-sm">Actividad Reciente</h3>
                </div>
                <div class="p-5">
                    @if($actividadReciente->count() > 0)
                        <div class="relative border-l border-slate-200 dark:border-gray-700 ml-3 space-y-6">
                            @foreach($actividadReciente as $log)
                                <div class="relative pl-6">
                                    <span class="absolute top-1.5 w-2.5 h-2.5 bg-white dark:bg-[#181a1b] border-2 border-slate-300 dark:border-gray-700 rounded-full" style="left: -5px;"></span>
                                    <div class="text-xs text-slate-400 font-medium mb-0.5">
                                        {{ $log->creado_en->locale('es')->diffForHumans() }}
                                    </div>
                                    <div class="text-sm font-bold text-slate-700 dark:text-slate-300">
                                        {{ $log->accion }}
                                    </div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                                        {{ $log->descripcion }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6">
                            <span class="material-symbols-outlined text-4xl text-slate-200 mb-2">history</span>
                            <p class="text-sm text-slate-500 dark:text-slate-400">No hay actividad reciente registrada.</p>
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




