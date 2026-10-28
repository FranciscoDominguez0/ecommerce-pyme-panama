@extends('layouts.admin')

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
                <a href="{{ $backRoute }}" class="text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:text-white transition-colors">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $title }}</h1>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium ml-9">{{ $description }}</p>
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
            <a href="{{ $backRoute }}" class="px-6 py-2 rounded-lg border border-slate-300 dark:border-gray-700 text-slate-700 dark:text-slate-300 font-semibold text-xs uppercase tracking-wide hover:bg-slate-50 dark:bg-transparent dark:hover:bg-gray-700/30 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2 rounded-lg bg-slate-900 text-white font-semibold text-xs uppercase tracking-wide hover:bg-slate-800 shadow-sm transition-all flex items-center">
                <span class="material-symbols-outlined mr-2 text-[18px]">save</span>
                Guardar Usuario
            </button>
        </div>
    </div>

    <!-- Diseño del formulario (cuadrícula Bento) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Columna 1: Datos personales y seguridad -->
        <div class="lg:col-span-2 flex flex-col gap-6">
            
            <!-- Sección 1: Información personal -->
            <div class="bg-white dark:bg-[#181a1b] border border-slate-200 dark:border-gray-700 rounded-xl p-8 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-6 border-b border-slate-100 dark:border-gray-700 pb-4">Información Personal</h2>
                <div class="flex flex-col md:flex-row gap-8 mb-6">
                    
                    <!-- Carga de avatar -->
                    <div class="flex flex-col items-center justify-start space-y-3 w-32 shrink-0">
                        <div class="w-24 h-24 rounded-full bg-slate-50 dark:bg-transparent border border-dashed border-slate-300 dark:border-gray-700 flex items-center justify-center text-slate-400 hover:bg-slate-100 dark:hover:bg-gray-800 hover:border-slate-400 cursor-pointer transition-all group overflow-hidden relative">
                            @if($isEdit && $usuario->foto_perfil_ruta)
                                <img src="{{ asset($usuario->foto_perfil_ruta) }}" class="w-full h-full object-cover">
                            @else
                                <span class="material-symbols-outlined text-3xl group-hover:hidden">add_a_photo</span>
                            @endif
                            <div class="absolute inset-0 bg-black/40 hidden group-hover:flex items-center justify-center">
                                <span class="material-symbols-outlined text-white">upload</span>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">Subir Foto</span>
                        <input type="file" name="foto_perfil" class="hidden">
                    </div>

                    <!-- Cuadrícula de detalles principales -->
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nombre(s) <span class="text-red-500">*</span></label>
                            <input name="nombre" value="{{ old('nombre', $usuario->nombre ?? '') }}" required class="w-full bg-white dark:bg-[#181a1b] border border-slate-300 dark:border-gray-700 rounded-lg p-3 text-sm text-slate-900 dark:text-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-shadow" placeholder="Ej. Carlos" type="text"/>
                            @error('nombre') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Apellido(s)</label>
                            <input name="apellido" value="{{ old('apellido', $usuario->apellido ?? '') }}" class="w-full bg-white dark:bg-[#181a1b] border border-slate-300 dark:border-gray-700 rounded-lg p-3 text-sm text-slate-900 dark:text-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-shadow" placeholder="Ej. Mendoza" type="text"/>
                            @error('apellido') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-1 md:col-span-2">
                            <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Correo Electrónico <span class="text-red-500">*</span></label>
                            <input name="email" value="{{ old('email', $usuario->email ?? '') }}" required class="w-full bg-white dark:bg-[#181a1b] border border-slate-300 dark:border-gray-700 rounded-lg p-3 text-sm text-slate-900 dark:text-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-shadow" placeholder="correo@institucion.com" type="email"/>
                            @error('email') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div class="space-y-1 md:col-span-2">
                            <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Teléfono Móvil</label>
                            <div class="flex">
                                <span class="inline-flex items-center px-3 border border-r-0 border-slate-300 dark:border-gray-700 rounded-l-lg bg-slate-50 dark:bg-transparent text-slate-500 dark:text-slate-400 text-sm font-medium">+507</span>
                                <input name="telefono" value="{{ old('telefono', $usuario->telefono ?? '') }}" class="w-full bg-white dark:bg-[#181a1b] border border-slate-300 dark:border-gray-700 rounded-r-lg p-3 text-sm text-slate-900 dark:text-white focus:border-slate-900 focus:ring-1 focus:ring-slate-900 outline-none transition-shadow" placeholder="0000-0000" type="tel"/>
                            </div>
                            @error('telefono') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección 2: Seguridad -->
            <div class="bg-white dark:bg-[#181a1b] border border-slate-200 dark:border-gray-700 rounded-xl p-8 shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-gray-700 pb-4 mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 dark:text-white">Seguridad de Acceso</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Gestión de credenciales y contraseña.</p>
                    </div>
                    <span class="material-symbols-outlined text-slate-300 text-3xl">lock</span>
                </div>
                
                @if($isEdit)
                    <div class="p-4 sm:p-5 rounded-xl border border-emerald-100 dark:border-emerald-900/40 bg-gradient-to-r from-emerald-50/50 via-white to-emerald-50/20 dark:from-emerald-950/20 dark:via-transparent dark:to-transparent flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-2xs">
                        <div class="flex items-start gap-3.5 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100/80 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/60 flex items-center justify-center shrink-0 shadow-2xs">
                                <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' 1;">lock_reset</span>
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Restablecer Contraseña</h3>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-white dark:bg-gray-800 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 shadow-2xs truncate">
                                        <span class="material-symbols-outlined text-[13px] text-emerald-600">mail</span>
                                        {{ $usuario->email }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 leading-relaxed">
                                    Envía un enlace seguro al correo registrado para que el usuario defina o actualice su contraseña.
                                </p>
                            </div>
                        </div>
                        <button type="button" 
                                id="btn-enviar-restablecimiento"
                                onclick="enviarEnlaceRestablecimiento(this)"
                                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-semibold text-xs tracking-wide shadow-sm hover:shadow transition-all shrink-0 cursor-pointer disabled:opacity-75 disabled:pointer-events-none group">
                            <span class="spinner-icono hidden w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span class="normal-icono material-symbols-outlined text-[17px] group-hover:translate-x-0.5 transition-transform">send</span>
                            <span class="btn-texto">Enviar Enlace</span>
                        </button>
                    </div>
                @else
                    <div class="p-4 sm:p-5 rounded-xl border border-blue-100 dark:border-blue-900/40 bg-blue-50/40 dark:bg-blue-950/20 flex items-start gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-xl" style="font-variation-settings: 'FILL' 1;">mark_email_read</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Configuración de Contraseña</h3>
                            <p class="text-xs text-slate-600 dark:text-slate-400 mt-1 leading-relaxed">
                                El usuario recibirá un correo electrónico con un enlace seguro para establecer su propia contraseña una vez creada la cuenta.
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Columna 2: Rol y estado -->
        <div class="flex flex-col gap-6">
            
            <!-- Sección 3: Selector de estado -->
            <div class="bg-white dark:bg-[#181a1b] border border-slate-200 dark:border-gray-700 rounded-xl p-6 shadow-sm flex items-center justify-between">
                <div>
                    <h3 class="text-[10px] font-bold text-slate-900 dark:text-white uppercase tracking-wider">Estado del Usuario</h3>
                    <p class="text-sm mt-1 {{ old('estado', $usuario->activo ?? true) ? 'text-emerald-600 font-bold' : 'text-slate-500 dark:text-slate-400' }}" id="status-text">
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
                                   st.className = 'text-sm mt-1 text-slate-500 dark:text-slate-400';
                               }
                           "
                           {{ old('estado', $usuario->activo ?? true) ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-slate-200 dark:bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white dark:bg-[#181a1b] after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500 border border-slate-300 dark:border-gray-700"></div>
                </label>
            </div>

            <!-- Sección 4: Selección de rol -->
            <div class="bg-white dark:bg-[#181a1b] border border-slate-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
                <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-2">Asignación de Rol</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Seleccione el nivel de acceso operativo para este usuario.</p>
                
                <div class="space-y-3">
                    @foreach($roles as $r)
                        @php $isSelected = (old('rol_id', $rol->id ?? null) == $r->id); @endphp
                        
                        <label class="block cursor-pointer relative" onclick="window.selectRole('{{ $r->id }}')">
                            <input class="peer sr-only" name="rol_id" type="radio" value="{{ $r->id }}" {{ $isSelected ? 'checked' : '' }}/>
                            <div id="card-{{ $r->id }}" class="p-4 rounded-xl border transition-all flex gap-3.5 items-start {{ $isSelected ? 'border-emerald-500 dark:border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/25 ring-2 ring-emerald-500/20 dark:ring-emerald-500/30 shadow-xs' : 'border-slate-200 dark:border-gray-700 bg-white dark:bg-[#181a1b] hover:border-slate-300 dark:hover:border-gray-600 hover:bg-slate-50 dark:hover:bg-gray-800/40' }}">
                                <div class="mt-0.5 {{ $r->icono_color }} p-1.5 rounded-lg bg-slate-100 dark:bg-gray-800 shrink-0">
                                    <span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">{{ $r->icono }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div id="title-{{ $r->id }}" class="text-xs font-bold uppercase tracking-wider {{ $isSelected ? 'text-emerald-700 dark:text-emerald-400' : 'text-slate-900 dark:text-white' }}">{{ $r->nombre ?: $r->name }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">{{ $r->descripcion ?? 'Acceso al sistema.' }}</div>
                                </div>
                                <div class="shrink-0 pt-0.5">
                                    <div id="check-{{ $r->id }}" class="w-5 h-5 rounded-full border flex items-center justify-center transition-all {{ $isSelected ? 'border-emerald-500 bg-emerald-500 text-white' : 'border-slate-300 dark:border-gray-600 bg-transparent' }}">
                                        <span class="material-symbols-outlined text-[13px] {{ $isSelected ? '' : 'hidden' }}">check</span>
                                    </div>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</form>

@if($isEdit)
    <!-- Formulario para enviar correo de restablecimiento de contraseña -->
    <form id="form-enviar-restablecimiento" action="{{ route('admin.usuarios.enviar-restablecimiento', $usuario->id) }}" method="POST" class="hidden">
        @csrf
    </form>
@endif

<script>
    window.selectRole = function(roleId) {
        const rolesIds = @json($roles->pluck('id'));

        const unselectedCardClass = "p-4 rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-[#181a1b] hover:border-slate-300 dark:hover:border-gray-600 hover:bg-slate-50 dark:hover:bg-gray-800/40 transition-all flex gap-3.5 items-start";
        const selectedCardClass = "p-4 rounded-xl border border-emerald-500 dark:border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/25 ring-2 ring-emerald-500/20 dark:ring-emerald-500/30 shadow-xs transition-all flex gap-3.5 items-start";

        rolesIds.forEach(id => {
            const card = document.getElementById('card-' + id);
            const check = document.getElementById('check-' + id);
            const title = document.getElementById('title-' + id);

            if (card) {
                card.className = unselectedCardClass;
            }
            if (check) {
                check.className = "w-5 h-5 rounded-full border border-slate-300 dark:border-gray-600 bg-transparent flex items-center justify-center transition-all";
                const icon = check.querySelector('.material-symbols-outlined');
                if (icon) icon.classList.add('hidden');
            }
            if (title) {
                title.className = "text-xs font-bold uppercase tracking-wider text-slate-900 dark:text-white";
            }
        });

        const activeCard = document.getElementById('card-' + roleId);
        const activeCheck = document.getElementById('check-' + roleId);
        const activeTitle = document.getElementById('title-' + roleId);

        if (activeCard) {
            activeCard.className = selectedCardClass;
        }
        if (activeCheck) {
            activeCheck.className = "w-5 h-5 rounded-full border border-emerald-500 bg-emerald-500 text-white flex items-center justify-center transition-all";
            const icon = activeCheck.querySelector('.material-symbols-outlined');
            if (icon) icon.classList.remove('hidden');
        }
        if (activeTitle) {
            activeTitle.className = "text-xs font-bold uppercase tracking-wider text-emerald-700 dark:text-emerald-400";
        }
    };

    @if($isEdit)
    window.enviarEnlaceRestablecimiento = function(btn) {
        if (!btn || btn.disabled) return;
        
        const originalHtml = btn.innerHTML;
        const spinner = btn.querySelector('.spinner-icono');
        const normalIcon = btn.querySelector('.normal-icono');
        const texto = btn.querySelector('.btn-texto');
        
        // Estado de carga visual
        btn.disabled = true;
        if (spinner) spinner.classList.remove('hidden');
        if (normalIcon) normalIcon.classList.add('hidden');
        if (texto) texto.textContent = 'Enviando enlace...';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
            || document.querySelector('input[name="_token"]')?.value;

        fetch('{{ route('admin.usuarios.enviar-restablecimiento', $usuario->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (typeof window.mostrarToast === 'function') {
                    window.mostrarToast(data.message, 'success');
                }
                if (texto) texto.textContent = '¡Enlace Enviado!';
                if (spinner) spinner.classList.add('hidden');
                if (normalIcon) {
                    normalIcon.textContent = 'check_circle';
                    normalIcon.classList.remove('hidden');
                }
                setTimeout(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }, 3500);
            } else {
                throw new Error(data.message || 'Error al enviar');
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            if (typeof window.mostrarToast === 'function') {
                window.mostrarToast('Ocurrió un error al enviar el enlace. Intente nuevamente.', 'error');
            }
        });
    };
    @endif
</script>
@endsection




