<div id="toast-container" class="fixed top-20 z-[100000] flex flex-col items-center gap-3 pointer-events-none w-full px-4 sm:max-w-md" style="left: 50%; transform: translateX(calc(-50% + var(--sidebar-offset, 0px) / 2));" aria-live="polite" aria-atomic="true">
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
                    'border' => 'border-rose-500/40 dark:border-rose-500/60',
                    'icon_color' => 'text-rose-400 dark:text-rose-300',
                    'icon_bg' => 'bg-rose-500/10 dark:bg-rose-500/20',
                    'icon' => 'error'
                ],
                'warning' => [
                    'border' => 'border-amber-500/40 dark:border-amber-500/60',
                    'icon_color' => 'text-amber-400 dark:text-amber-300',
                    'icon_bg' => 'bg-amber-500/10 dark:bg-amber-500/20',
                    'icon' => 'warning'
                ],
                'info' => [
                    'border' => 'border-blue-500/40 dark:border-blue-500/60',
                    'icon_color' => 'text-blue-400 dark:text-blue-300',
                    'icon_bg' => 'bg-blue-500/10 dark:bg-blue-500/20',
                    'icon' => 'info'
                ],
                default => [
                    'border' => 'border-emerald-500/40 dark:border-emerald-500/60',
                    'icon_color' => 'text-emerald-400 dark:text-emerald-300',
                    'icon_bg' => 'bg-emerald-500/10 dark:bg-emerald-500/20',
                    'icon' => 'check_circle'
                ]
            };
        @endphp

        <div id="toast-session-{{ $idx }}" 
             data-toast
             data-duration="4000"
             style="opacity: 0; transform: translateY(-16px) scale(0.96);"
             class="pointer-events-auto flex items-center gap-3.5 pl-2.5 pr-4 py-2.5 rounded-2xl bg-slate-900/95 dark:bg-[#252a30] dark:border-slate-600/80 backdrop-blur-md {{ $config['border'] }} border shadow-2xl shadow-slate-900/50 dark:shadow-[0_10px_30px_rgba(0,0,0,0.6)] text-white relative overflow-hidden transition-all duration-300 w-full max-w-sm">
            
            <div class="{{ $config['icon_bg'] }} w-10 h-10 rounded-xl flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[22px] {{ $config['icon_color'] }}" style="font-variation-settings: 'FILL' 1;">{{ $config['icon'] }}</span>
            </div>
            
            <div class="flex-1 leading-tight tracking-wide">
                @if(is_array($mensaje))
                    <div class="text-[13px] font-bold text-white">{{ $mensaje['title'] ?? 'Atención' }}</div>
                    @if(isset($mensaje['message']))
                        <div class="text-[11.5px] font-medium text-slate-300 dark:text-slate-200 mt-0.5">{!! $mensaje['message'] !!}</div>
                    @endif
                @else
                    <div class="text-[13px] font-bold text-white">{!! $mensaje !!}</div>
                @endif
            </div>
            
            <button type="button" 
                    onclick="cerrarToast(this.closest('[data-toast]'))" 
                    class="opacity-60 hover:opacity-100 transition-opacity p-1.5 shrink-0 rounded-lg hover:bg-slate-700/50 dark:hover:bg-slate-600/50 text-slate-300 hover:text-white" 
                    aria-label="Cerrar notificación">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
    @endforeach
</div>

<script>
    (function() {
        // Asegurar que el contenedor esté directamente en document.body para evitar que quede atrapado bajo el header
        const asegurarContenedorEnBody = () => {
            const container = document.getElementById('toast-container');
            if (container && container.parentElement !== document.body) {
                document.body.appendChild(container);
            }
        };

        // Inicializar toasts y animar entrada limpia desde arriba
        const inicializarToasts = () => {
            asegurarContenedorEnBody();
            document.querySelectorAll('#toast-container [data-toast]:not([data-inited])').forEach(toast => {
                toast.dataset.inited = 'true';
                requestAnimationFrame(() => {
                    toast.style.opacity = '1';
                    toast.style.transform = 'translateY(0) scale(1)';
                });
                window.iniciarTemporizadorToast(toast, parseInt(toast.dataset.duration || 4000));
            });
        };

        window.iniciarTemporizadorToast = function(toast, duracion = 4000) {
            if (!toast) return;
            const timeoutId = setTimeout(() => {
                window.cerrarToast(toast);
            }, duracion);

            toast.dataset.timeoutId = timeoutId;
        };

        window.cerrarToast = function(toast) {
            if (!toast) return;
            if (toast.dataset.timeoutId) {
                clearTimeout(Number(toast.dataset.timeoutId));
            }
            
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(-16px) scale(0.96)';
            toast.style.marginBottom = `-${toast.offsetHeight}px`;
            
            setTimeout(() => {
                toast.remove();
            }, 300);
        };

        // Ejecutar inmediatamente
        inicializarToasts();
        
        // Ejecutar en DOMContentLoaded o navegación Livewire
        document.addEventListener('DOMContentLoaded', inicializarToasts);
        document.addEventListener('livewire:navigated', inicializarToasts);

        // API JavaScript Global reutilizable
        window.mostrarToast = function(arg1, arg2 = 'success', duracion = 4000) {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.className = 'fixed top-20 z-[100000] flex flex-col items-center gap-3 pointer-events-none w-full px-4 sm:max-w-md';
                container.style.cssText = 'left: 50%; transform: translateX(calc(-50% + var(--sidebar-offset, 0px) / 2));';
                document.body.appendChild(container);
            } else if (container.parentElement !== document.body) {
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

            if (tipo === 'fallo') tipo = 'error';

            const configs = {
                success: {
                    border: 'border-emerald-500/40 dark:border-emerald-500/60',
                    icon_color: 'text-emerald-400 dark:text-emerald-300',
                    icon_bg: 'bg-emerald-500/10 dark:bg-emerald-500/20',
                    icon: 'check_circle'
                },
                error: {
                    border: 'border-rose-500/40 dark:border-rose-500/60',
                    icon_color: 'text-rose-400 dark:text-rose-300',
                    icon_bg: 'bg-rose-500/10 dark:bg-rose-500/20',
                    icon: 'error'
                },
                warning: {
                    border: 'border-amber-500/40 dark:border-amber-500/60',
                    icon_color: 'text-amber-400 dark:text-amber-300',
                    icon_bg: 'bg-amber-500/10 dark:bg-amber-500/20',
                    icon: 'warning'
                },
                info: {
                    border: 'border-blue-500/40 dark:border-blue-500/60',
                    icon_color: 'text-blue-400 dark:text-blue-300',
                    icon_bg: 'bg-blue-500/10 dark:bg-blue-500/20',
                    icon: 'info'
                }
            };

            const cfg = configs[tipo] || configs.success;
            const toast = document.createElement('div');
            toast.dataset.toast = 'true';
            toast.dataset.inited = 'true';
            toast.style.cssText = 'opacity: 0; transform: translateY(-16px) scale(0.96);';
            toast.className = `pointer-events-auto flex items-center gap-3.5 pl-2.5 pr-4 py-2.5 rounded-2xl bg-slate-900/95 dark:bg-[#252a30] dark:border-slate-600/80 backdrop-blur-md ${cfg.border} border shadow-2xl shadow-slate-900/50 dark:shadow-[0_10px_30px_rgba(0,0,0,0.6)] text-white relative overflow-hidden transition-all duration-300 w-full max-w-sm`;

            toast.innerHTML = `
                <div class="${cfg.icon_bg} w-10 h-10 rounded-xl flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[22px] ${cfg.icon_color}" style="font-variation-settings: 'FILL' 1;">${cfg.icon}</span>
                </div>
                <div class="flex-1 text-[13px] font-bold leading-tight tracking-wide text-white">
                    ${mensaje}
                </div>
                <button type="button" 
                        onclick="cerrarToast(this.closest('[data-toast]'))" 
                        class="opacity-60 hover:opacity-100 transition-opacity p-1.5 shrink-0 rounded-lg hover:bg-slate-700/50 dark:hover:bg-slate-600/50 text-slate-300 hover:text-white" 
                        aria-label="Cerrar notificación">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            `;

            container.appendChild(toast);

            requestAnimationFrame(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateY(0) scale(1)';
            });

            window.iniciarTemporizadorToast(toast, duracion);
        };
    })();
</script>

