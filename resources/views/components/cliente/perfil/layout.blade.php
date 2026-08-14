@props([
    'active' => '',
])

@php
    $usuario = Auth::user();
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="bg-white border border-outline-variant rounded-2xl shadow-sm overflow-clip h-[calc(100vh-140px)] flex flex-col">
        <div class="md:flex flex-1 overflow-hidden">

            {{-- LEFT: Profile + Navigation (persisted across routes) --}}
            <aside x-persist="mi-cuenta-sidebar" x-data class="md:w-[32%] md:min-w-[260px] md:max-w-[320px] shrink-0 border-b md:border-b-0 md:border-r border-outline-variant/30 p-6 md:p-7 overflow-y-auto h-full">
                <a href="{{ route('dashboard') }}" wire:navigate
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-on-surface-variant hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                    Volver al Dashboard
                </a>

                <div class="flex flex-col items-center text-center mt-5">
                    <div class="relative inline-flex group cursor-pointer mb-4" id="photo-wrapper" data-has-foto="{{ $usuario->foto_perfil_url ? '1' : '0' }}">
                        <div class="w-24 h-24 rounded-full overflow-hidden border-2 border-outline-variant bg-surface-container-low flex items-center justify-center">
                            @if($usuario->foto_perfil_url)
                                <img id="avatar-preview-img" src="{{ $usuario->foto_perfil_url }}" alt="{{ $usuario->nombre_completo }}" class="w-full h-full object-cover">
                            @else
                                <span id="avatar-preview-initials" class="text-4xl font-bold text-on-surface-variant">{{ $usuario->iniciales }}</span>
                                <img id="avatar-preview-img" src="" alt="" class="w-full h-full object-cover hidden">
                            @endif
                        </div>
                        <div id="avatar-uploading" class="absolute inset-0 rounded-full bg-primary/40 items-center justify-center hidden">
                            <span class="material-symbols-outlined text-white text-2xl animate-spin">progress_activity</span>
                        </div>
                        <button type="button" id="btn-eliminar-foto"
                            class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-error text-on-error items-center justify-center shadow-sm hover:bg-red-700 transition-colors {{ $usuario->foto_perfil_url ? 'flex' : 'hidden' }}"
                            title="Eliminar foto">
                            <span class="material-symbols-outlined text-[12px]">close</span>
                        </button>
                        <div class="absolute -bottom-0.5 -right-0.5 w-7 h-7 rounded-full bg-primary text-on-primary flex items-center justify-center shadow-md cursor-pointer hover:bg-primary-container transition-colors" id="camera-badge">
                            <span class="material-symbols-outlined text-[15px]">photo_camera</span>
                        </div>
                    </div>

                    <h2 class="text-sm font-bold text-primary">{{ $usuario->nombre_completo }}</h2>
                </div>

                <nav class="mt-4 pt-4 border-t border-outline-variant/30 space-y-1">
                    <a href="{{ route('cliente.perfil.pedidos.index') }}" wire:navigate wire:current.exact="sidebar-nav-active"
                        class="flex items-start gap-3 px-3 py-2.5 rounded-lg transition-colors text-on-surface-variant hover:bg-surface-container-low hover:text-primary">
                        <span class="material-symbols-outlined text-lg mt-0.5 shrink-0">package_2</span>
                        <div class="text-left min-w-0">
                            <p class="text-sm font-medium">Historial de Pedidos</p>
                            <p class="text-[11px] text-outline leading-tight">Revisa tus compras</p>
                        </div>
                    </a>

                    <a href="{{ route('cliente.perfil.direcciones') }}" wire:navigate wire:current.exact="sidebar-nav-active"
                        class="flex items-start gap-3 px-3 py-2.5 rounded-lg transition-colors text-on-surface-variant hover:bg-surface-container-low hover:text-primary">
                        <span class="material-symbols-outlined text-lg mt-0.5 shrink-0">local_shipping</span>
                        <div class="text-left min-w-0">
                            <p class="text-sm font-medium">Direcciones de Envío</p>
                            <p class="text-[11px] text-outline leading-tight">Gestiona tus envíos</p>
                        </div>
                    </a>

                    <a href="{{ route('cliente.perfil.password') }}" wire:navigate wire:current.exact="sidebar-nav-active"
                        class="flex items-start gap-3 px-3 py-2.5 rounded-lg transition-colors text-on-surface-variant hover:bg-surface-container-low hover:text-primary">
                        <span class="material-symbols-outlined text-lg mt-0.5 shrink-0">lock</span>
                        <div class="text-left min-w-0">
                            <p class="text-sm font-medium">Cambiar Contraseña</p>
                            <p class="text-[11px] text-outline leading-tight">Seguridad de la cuenta</p>
                        </div>
                    </a>

                    <a href="{{ route('cliente.perfil.datos') }}" wire:navigate wire:current.exact="sidebar-nav-active"
                        class="flex items-start gap-3 px-3 py-2.5 rounded-lg transition-colors text-on-surface-variant hover:bg-surface-container-low hover:text-primary">
                        <span class="material-symbols-outlined text-lg mt-0.5 shrink-0">settings</span>
                        <div class="text-left min-w-0">
                            <p class="text-sm font-medium">Configuración</p>
                            <p class="text-[11px] text-outline leading-tight">Datos personales</p>
                        </div>
                    </a>
                </nav>

                <input type="file" name="foto_perfil" id="foto_perfil" accept="image/png,image/jpeg,image/webp" class="hidden">
                <span id="file-name" class="block text-xs text-outline mt-3 hidden"></span>
                @error('foto_perfil')
                    <p class="mt-2 text-xs text-error text-center flex items-center justify-center gap-1"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
                @enderror

                <script>
                    (function () {
                        var wrapper = document.getElementById('photo-wrapper');
                        var input = document.getElementById('foto_perfil');

                        if (!wrapper || !input || wrapper.getAttribute('data-avatar-bound') === '1') return;
                        wrapper.setAttribute('data-avatar-bound', '1');

                        var camera = document.getElementById('camera-badge');
                        var del = document.getElementById('btn-eliminar-foto');
                        var previewImg = document.getElementById('avatar-preview-img');
                        var previewInitials = document.getElementById('avatar-preview-initials');
                        var fileName = document.getElementById('file-name');
                        var uploading = document.getElementById('avatar-uploading');
                        var uploadUrl = '{{ route('cliente.perfil.foto.update') }}';
                        var csrf = document.querySelector('meta[name="csrf-token"]');

                        function getCsrf() {
                            return csrf ? csrf.getAttribute('content') : '';
                        }

                        function setCargando(on) {
                            if (!uploading) return;
                            if (on) {
                                uploading.classList.add('flex');
                                uploading.classList.remove('hidden');
                            } else {
                                uploading.classList.add('hidden');
                                uploading.classList.remove('flex');
                            }
                        }

                        function estaCargando() {
                            return uploading && !uploading.classList.contains('hidden');
                        }

                        function setAvatarDesdeUrl(url) {
                            if (url) {
                                previewImg.src = url;
                                previewImg.classList.remove('hidden');
                                if (previewInitials) previewInitials.classList.add('hidden');
                                wrapper.setAttribute('data-has-foto', '1');
                            } else {
                                previewImg.src = '';
                                previewImg.classList.add('hidden');
                                if (previewInitials) previewInitials.classList.remove('hidden');
                                wrapper.setAttribute('data-has-foto', '0');
                            }
                        }

                        function mostrarBotonEliminar(visible) {
                            if (!del) return;
                            if (visible) {
                                del.classList.add('flex');
                                del.classList.remove('hidden');
                                del.style.display = 'flex';
                            } else {
                                del.classList.add('hidden');
                                del.classList.remove('flex');
                                del.style.display = 'none';
                            }
                        }

                        function revertirAvatar(prevHasFoto, prevSrc) {
                            if (prevHasFoto) {
                                previewImg.src = prevSrc;
                                previewImg.classList.remove('hidden');
                                if (previewInitials) previewInitials.classList.add('hidden');
                            } else {
                                previewImg.src = '';
                                previewImg.classList.add('hidden');
                                if (previewInitials) previewInitials.classList.remove('hidden');
                            }
                            wrapper.setAttribute('data-has-foto', prevHasFoto ? '1' : '0');
                            mostrarBotonEliminar(prevHasFoto);
                        }

                        function enviarFoto(formData, okCallback, failCallback) {
                            fetch(uploadUrl, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': getCsrf(),
                                    'Accept': 'application/json',
                                },
                                body: formData,
                                credentials: 'same-origin',
                            })
                                .then(function (res) {
                                    return res.json().then(function (data) {
                                        return { ok: res.ok, data: data };
                                    });
                                })
                                .then(function (r) {
                                    setCargando(false);
                                    if (r.ok && r.data && r.data.ok) {
                                        okCallback(r.data);
                                    } else {
                                        if (failCallback) failCallback();
                                        if (window.mostrarToast) {
                                            window.mostrarToast('error', (r.data && (r.data.message || r.data.mensaje)) || 'No se pudo guardar la foto. Intenta nuevamente.');
                                        }
                                    }
                                })
                                .catch(function () {
                                    setCargando(false);
                                    if (failCallback) failCallback();
                                    if (window.mostrarToast) {
                                        window.mostrarToast('error', 'Error de conexión al guardar la foto.');
                                    }
                                });
                        }

                        function subirFoto() {
                            if (estaCargando()) return;

                            var file = input.files[0];
                            if (!file) return;

                            if (!file.type || file.type.indexOf('image/') !== 0) {
                                input.value = '';
                                if (window.mostrarToast) window.mostrarToast('error', 'Selecciona un archivo de imagen válido.');
                                return;
                            }

                            var prevHasFoto = wrapper.getAttribute('data-has-foto') === '1';
                            var prevSrc = previewImg ? previewImg.src : '';

                            if (fileName) {
                                fileName.textContent = file.name;
                                fileName.classList.remove('hidden');
                            }

                            var reader = new FileReader();
                            reader.onload = function (e2) {
                                if (previewImg) previewImg.src = e2.target.result;
                                if (previewImg) previewImg.classList.remove('hidden');
                                if (previewInitials) previewInitials.classList.add('hidden');
                            };
                            reader.readAsDataURL(file);

                            setCargando(true);

                            var fd = new FormData();
                            fd.append('foto_perfil', file);

                            enviarFoto(fd, function (data) {
                                if (fileName) {
                                    fileName.textContent = '';
                                    fileName.classList.add('hidden');
                                }
                                input.value = '';
                                setAvatarDesdeUrl(data.foto_perfil_url);
                                mostrarBotonEliminar(true);
                                if (window.mostrarToast) window.mostrarToast('success', data.mensaje || 'Foto de perfil actualizada.');
                            }, function () {
                                input.value = '';
                                revertirAvatar(prevHasFoto, prevSrc);
                            });
                        }

                        function eliminarFoto() {
                            if (estaCargando()) return;

                            var prevHasFoto = wrapper.getAttribute('data-has-foto') === '1';
                            var prevSrc = previewImg ? previewImg.src : '';

                            setAvatarDesdeUrl(null);
                            mostrarBotonEliminar(false);
                            if (fileName) {
                                fileName.textContent = '';
                                fileName.classList.add('hidden');
                            }
                            setCargando(true);

                            var fd = new FormData();
                            fd.append('eliminar_foto', '1');

                            enviarFoto(fd, function (data) {
                                input.value = '';
                                setAvatarDesdeUrl(null);
                                mostrarBotonEliminar(false);
                                if (window.mostrarToast) window.mostrarToast('success', data.mensaje || 'Foto de perfil eliminada.');
                            }, function () {
                                input.value = '';
                                revertirAvatar(prevHasFoto, prevSrc);
                            });
                        }

                        function triggerFilePicker() {
                            if (estaCargando()) return;
                            input.click();
                        }

                        wrapper.addEventListener('click', function (e) {
                            if (e.target.closest('#btn-eliminar-foto')) return;
                            triggerFilePicker();
                        });

                        if (camera) {
                            camera.addEventListener('click', function (e) {
                                e.stopPropagation();
                                triggerFilePicker();
                            });
                        }

                        input.addEventListener('change', subirFoto);

                        if (del) {
                            del.addEventListener('click', function (e) {
                                e.stopPropagation();
                                e.preventDefault();
                                eliminarFoto();
                            });
                        }
                    })();
                </script>
            </aside>

            {{-- RIGHT: Dynamic Content --}}
            <div class="flex-1 min-w-0 p-6 md:p-8 overflow-y-auto h-full relative">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
