@props([
    'active' => '',
    'editableAvatar' => false,
    'formId' => 'datos-form',
])

@php
    $usuario = Auth::user();
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="bg-white border border-outline-variant rounded-2xl shadow-sm overflow-hidden">
        <div class="md:flex">

            {{-- LEFT: Profile + Navigation --}}
            <aside class="md:w-[32%] md:min-w-[260px] md:max-w-[320px] shrink-0 border-b md:border-b-0 md:border-r border-outline-variant/30 p-6 md:p-7">
                <a href="{{ route('dashboard') }}"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-on-surface-variant hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                    Volver al Dashboard
                </a>

                <div class="flex flex-col items-center text-center mt-5">
                    <div class="relative inline-flex {{ $editableAvatar ? 'group cursor-pointer' : '' }} mb-4" id="photo-wrapper">
                        <div class="w-24 h-24 rounded-full overflow-hidden border-2 border-outline-variant bg-surface-container-low flex items-center justify-center">
                            @if($usuario->foto_perfil_url)
                                <img id="avatar-preview-img" src="{{ $usuario->foto_perfil_url }}" alt="{{ $usuario->nombre_completo }}" class="w-full h-full object-cover">
                            @else
                                <span id="avatar-preview-initials" class="text-4xl font-bold text-on-surface-variant">{{ $usuario->iniciales }}</span>
                                <img id="avatar-preview-img" src="" alt="" class="w-full h-full object-cover hidden">
                            @endif
                        </div>
                        @if($editableAvatar)
                            <button type="button" id="btn-eliminar-foto"
                                class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-error text-on-error items-center justify-center shadow-sm hover:bg-red-700 transition-colors {{ $usuario->foto_perfil_url ? 'flex' : 'hidden' }}"
                                title="Eliminar foto">
                                <span class="material-symbols-outlined text-[12px]">close</span>
                            </button>
                            <div class="absolute -bottom-0.5 -right-0.5 w-7 h-7 rounded-full bg-primary text-on-primary flex items-center justify-center shadow-md cursor-pointer hover:bg-primary-container transition-colors" id="camera-badge">
                                <span class="material-symbols-outlined text-[15px]">photo_camera</span>
                            </div>
                        @endif
                    </div>

                    <h2 class="text-sm font-bold text-primary">{{ $usuario->nombre_completo }}</h2>
                    <span class="inline-flex items-center gap-1 mt-1.5 px-2.5 py-0.5 rounded-full bg-secondary/10 text-secondary text-[10px] font-bold uppercase tracking-wider">
                        <span class="material-symbols-outlined text-[12px]">check_circle</span>
                        Cuenta activa
                    </span>
                </div>

                <nav class="mt-6 pt-5 border-t border-outline-variant/30 space-y-1">
                    <a href="{{ route('cliente.perfil.pedidos.index') }}"
                        class="flex items-start gap-3 px-3 py-2.5 rounded-lg transition-colors {{ $active === 'pedidos' ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-primary' }}">
                        <span class="material-symbols-outlined text-lg mt-0.5 shrink-0">package_2</span>
                        <div class="text-left min-w-0">
                            <p class="text-sm font-medium">Historial de Pedidos</p>
                            <p class="text-[11px] text-outline leading-tight">Revisa tus compras</p>
                        </div>
                    </a>

                    <a href="{{ route('cliente.perfil.direcciones') }}"
                        class="flex items-start gap-3 px-3 py-2.5 rounded-lg transition-colors {{ $active === 'direcciones' ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-primary' }}">
                        <span class="material-symbols-outlined text-lg mt-0.5 shrink-0">local_shipping</span>
                        <div class="text-left min-w-0">
                            <p class="text-sm font-medium">Direcciones de Envío</p>
                            <p class="text-[11px] text-outline leading-tight">Gestiona tus envíos</p>
                        </div>
                    </a>

                    <a href="{{ route('cliente.perfil.password') }}"
                        class="flex items-start gap-3 px-3 py-2.5 rounded-lg transition-colors {{ $active === 'password' ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-primary' }}">
                        <span class="material-symbols-outlined text-lg mt-0.5 shrink-0">lock</span>
                        <div class="text-left min-w-0">
                            <p class="text-sm font-medium">Cambiar Contraseña</p>
                            <p class="text-[11px] text-outline leading-tight">Seguridad de la cuenta</p>
                        </div>
                    </a>

                    <a href="{{ route('cliente.perfil.datos') }}"
                        class="flex items-start gap-3 px-3 py-2.5 rounded-lg transition-colors {{ $active === 'configuracion' ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-primary' }}">
                        <span class="material-symbols-outlined text-lg mt-0.5 shrink-0">settings</span>
                        <div class="text-left min-w-0">
                            <p class="text-sm font-medium">Configuración</p>
                            <p class="text-[11px] text-outline leading-tight">Datos personales</p>
                        </div>
                    </a>
                </nav>

                @if($editableAvatar)
                    <input type="file" name="foto_perfil" id="foto_perfil" accept="image/png,image/jpeg,image/webp" form="{{ $formId }}" class="hidden">
                    <input type="hidden" name="eliminar_foto" id="eliminar_foto" value="0" form="{{ $formId }}">
                    <span id="file-name" class="block text-xs text-outline mt-3 hidden"></span>
                    @error('foto_perfil')
                        <p class="mt-2 text-xs text-error text-center flex items-center justify-center gap-1"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
                    @enderror
                @endif
            </aside>

            {{-- RIGHT: Dynamic Content --}}
            <div class="flex-1 min-w-0 p-6 md:p-8">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
