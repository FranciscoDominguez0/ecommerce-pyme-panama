{{-- Componente Reutilizable: Alertas Toast Flotantes (Éxito, Error/Fallo, Advertencia, Información) --}}
<div id="toast-container" class="fixed top-5 right-5 sm:right-6 z-[9999] flex flex-col gap-2.5 pointer-events-none w-full max-w-[280px] sm:max-w-xs" aria-live="polite" aria-atomic="true">
    @php
        $toasts = [];
        if (session('success')) {
            $toasts[] = ['tipo' => 'success', 'mensaje' => session('success')];
        } elseif (session('toast_success')) {
            $toasts[] = ['tipo' => 'success', 'mensaje' => session('toast_success')];
        }

        if (session('error')) {
            $toasts[] = ['tipo' => 'error', 'mensaje' => session('error')];
        } elseif (session('toast_error')) {
            $toasts[] = ['tipo' => 'error', 'mensaje' => session('toast_error')];
        }

        if (isset($errors) && $errors->any()) {
            foreach ($errors->all() as $err) {
                if (session('error') !== $err) {
                    $toasts[] = ['tipo' => 'error', 'mensaje' => $err];
                }
            }
        }

        if (session('warning')) {
            $toasts[] = ['tipo' => 'warning', 'mensaje' => session('warning')];
        } elseif (session('toast_warning')) {
            $toasts[] = ['tipo' => 'warning', 'mensaje' => session('toast_warning')];
        }

        if (session('info')) {
            $toasts[] = ['tipo' => 'info', 'mensaje' => session('info')];
        } elseif (session('toast_info')) {
            $toasts[] = ['tipo' => 'info', 'mensaje' => session('toast_info')];
        } elseif (session('status') && !in_array(session('status'), ['profile-updated', 'password-updated', 'verification-link-sent']) && !str_contains(session('status'), 'restablecer tu contraseña')) {
            $toasts[] = ['tipo' => 'info', 'mensaje' => session('status')];
        }
    @endphp

    @foreach($toasts as $idx => $t)
        @php
            $tipo = $t['tipo'];
            $mensaje = $t['mensaje'];
            
            $config = match($tipo) {
                'error' => [
                    'textIcon' => 'text-rose-500',
                    'icon' => 'error'
                ],
                'warning' => [
                    'textIcon' => 'text-amber-500',
                    'icon' => 'warning'
                ],
                'info' => [
                    'textIcon' => 'text-blue-400',
                    'icon' => 'info'
                ],
                default => [
                    'textIcon' => 'text-emerald-500',
                    'icon' => 'check_circle'
                ]
            };
        @endphp

        <div id="toast-session-{{ $idx }}" 
             data-toast
             data-duration="4000"
             class="pointer-events-auto flex items-center gap-2.5 px-4 py-3 rounded-full bg-slate-900 shadow-xl text-white relative overflow-hidden transition-all duration-300 transform translate-y-0 opacity-100 ring-1 ring-white/10">
            
            <span class="material-symbols-outlined {{ $config['textIcon'] }} text-[20px] shrink-0" style="font-variation-settings: 'FILL' 1;">{{ $config['icon'] }}</span>
            
            <div class="flex-1 text-xs font-medium leading-tight tracking-wide pr-2">
                {{ $mensaje }}
            </div>
            
            <button type="button" 
                    onclick="cerrarToast(this.closest('[data-toast]'))" 
                    class="text-slate-400 hover:text-white transition-colors p-0.5 shrink-0" 
                    aria-label="Cerrar notificación">
                <span class="material-symbols-outlined text-[16px]">close</span>
            </button>
        </div>
    @endforeach
</div>

<script>
    (function() {
        // Inicializar toasts provenientes del render inicial de Blade
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('#toast-container [data-toast]').forEach(toast => {
                iniciarTemporizadorToast(toast);
            });
        });

        // Función para iniciar la barra de progreso y auto-cierre
        window.iniciarTemporizadorToast = function(toast, duracion = 4000) {
            if (!toast) return;
            const barra = toast.querySelector('.toast-progress-bar');
            
            if (barra) {
                barra.style.transitionDuration = `${duracion}ms`;
                // Forzar reflow antes de animar a 0%
                requestAnimationFrame(() => {
                    barra.style.width = '0%';
                });
            }

            const timeoutId = setTimeout(() => {
                cerrarToast(toast);
            }, duracion);

            toast.dataset.timeoutId = timeoutId;
        };

        // Función para cerrar y animar la salida del Toast
        window.cerrarToast = function(toast) {
            if (!toast) return;
            if (toast.dataset.timeoutId) {
                clearTimeout(Number(toast.dataset.timeoutId));
            }
            
            // Animación de salida suave (fade-out + slide-up)
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-16px) scale(0.96)';
            toast.style.marginBottom = `-${toast.offsetHeight}px`;
            
            setTimeout(() => {
                toast.remove();
            }, 300);
        };

        // API JavaScript Global reutilizable e inteligente: window.mostrarToast(mensaje, tipo, duracion) o window.mostrarToast(tipo, mensaje, duracion)
        window.mostrarToast = function(arg1, arg2 = 'success', duracion = 4000) {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.className = 'fixed top-5 right-5 z-[9999] flex flex-col gap-3 pointer-events-none max-w-sm sm:max-w-md w-full px-3 sm:px-0';
                document.body.appendChild(container);
            }

            const tiposValidos = ['success', 'error', 'fallo', 'warning', 'info'];
            let mensaje = '';
            let tipo = 'success';

            if (typeof arg1 === 'object' && arg1 !== null) {
                mensaje = arg1.mensaje || arg1.message || '';
                tipo = arg1.tipo || arg1.type || 'success';
                duracion = arg1.duracion || duracion;
            } else if (tiposValidos.includes(String(arg1).toLowerCase())) {
                tipo = String(arg1).toLowerCase();
                mensaje = String(arg2 || '');
            } else {
                mensaje = String(arg1 || '');
                tipo = (arg2 && tiposValidos.includes(String(arg2).toLowerCase())) ? String(arg2).toLowerCase() : 'success';
            }

            // Normalizar alias
            if (tipo === 'fallo') tipo = 'error';

            const configs = {
                success: {
                    textIcon: 'text-emerald-500',
                    icon: 'check_circle'
                },
                error: {
                    textIcon: 'text-rose-500',
                    icon: 'error'
                },
                fallo: {
                    textIcon: 'text-rose-500',
                    icon: 'error'
                },
                warning: {
                    textIcon: 'text-amber-500',
                    icon: 'warning'
                },
                info: {
                    textIcon: 'text-blue-400',
                    icon: 'info'
                }
            };

            const cfg = configs[tipo] || configs.success;
            const toast = document.createElement('div');
            toast.dataset.toast = 'true';
            toast.className = `pointer-events-auto flex items-center gap-2.5 px-4 py-3 rounded-full bg-slate-900 shadow-xl text-white relative overflow-hidden transition-all duration-300 transform translate-y-[-10px] opacity-0 ring-1 ring-white/10`;

            toast.innerHTML = `
                <span class="material-symbols-outlined ${cfg.textIcon} text-[20px] shrink-0" style="font-variation-settings: 'FILL' 1;">${cfg.icon}</span>
                <div class="flex-1 text-xs font-medium leading-tight tracking-wide pr-2">
                    ${mensaje}
                </div>
                <button type="button" 
                        onclick="cerrarToast(this.closest('[data-toast]'))" 
                        class="text-slate-400 hover:text-white transition-colors p-0.5 shrink-0" 
                        aria-label="Cerrar notificación">
                    <span class="material-symbols-outlined text-[16px]">close</span>
                </button>
            `;

            container.appendChild(toast);

            // Animar entrada
            requestAnimationFrame(() => {
                toast.style.transform = 'translateY(0)';
                toast.style.opacity = '1';
                iniciarTemporizadorToast(toast, duracion);
            });
        };
    })();
</script>
