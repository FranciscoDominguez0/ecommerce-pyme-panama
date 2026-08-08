@props([
    'id' => 'modal-eliminar-global',
    'titulo' => '¿Eliminar este registro?',
    'mensaje' => 'Estás a punto de eliminar este elemento. Esta acción no se puede deshacer.',
    'icono' => 'delete_forever',
    'textoBoton' => 'Sí, Eliminar',
    'textoCancelar' => 'Cancelar',
])

<!-- Componente Reutilizable: Modal Defensivo de Confirmación de Eliminación -->
<div id="{{ $id }}" 
     class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 transition-all duration-200 select-none" 
     style="background-color: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px);"
     onclick="if(event.target === this) window.ModalEliminar.cerrar('{{ $id }}');"
     aria-modal="true" 
     role="dialog">
    
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4 animate-in fade-in zoom-in-95 duration-150 relative z-10">
        
        <!-- Ícono de Alerta / Peligro -->
        <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600 mx-auto shadow-2xs">
            <span class="material-symbols-outlined text-[28px]">{{ $icono }}</span>
        </div>

        <!-- Textos del Modal -->
        <div class="text-center space-y-1.5">
            <h3 class="text-base font-bold text-slate-900" id="{{ $id }}-titulo">
                {{ $titulo }}
            </h3>
            <p class="text-xs text-slate-500 leading-relaxed" id="{{ $id }}-descripcion">
                {{ $mensaje }}
            </p>
            <div id="{{ $id }}-nombre-container" class="inline-block px-3 py-1 bg-slate-100 text-slate-800 text-xs font-bold rounded-lg mt-1 max-w-full truncate border border-slate-200/80">
                <span id="{{ $id }}-nombre"></span>
            </div>
            <div id="{{ $id }}-extra" class="hidden text-[11px] text-amber-700 bg-amber-50 border border-amber-200 rounded-xl p-2.5 mt-2 font-medium text-left">
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center gap-3 pt-2">
            <button type="button" 
                    onclick="window.ModalEliminar.cerrar('{{ $id }}')" 
                    class="flex-1 py-2.5 px-4 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 active:bg-slate-300 rounded-xl transition-all cursor-pointer">
                {{ $textoCancelar }}
            </button>
            <button type="button" 
                    id="{{ $id }}-btn-confirmar"
                    onclick="window.ModalEliminar.confirmar('{{ $id }}')" 
                    class="flex-1 py-2.5 px-4 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 active:bg-rose-800 rounded-xl shadow-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                <span class="material-symbols-outlined text-[16px]">delete</span>
                <span id="{{ $id }}-texto-btn">{{ $textoBoton }}</span>
            </button>
        </div>

    </div>
</div>

<!-- Formulario Oculto para Envío DELETE -->
<form id="{{ $id }}-form" method="POST" action="" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    window.ModalEliminar = window.ModalEliminar || {
        _urls: {},

        abrir: function(opcionesOUrl, nombreOpcional, extraOpcional) {
            let opts = {};
            if (typeof opcionesOUrl === 'object') {
                opts = opcionesOUrl;
            } else {
                opts = {
                    url: opcionesOUrl,
                    nombre: nombreOpcional || '',
                    extra: extraOpcional || ''
                };
            }

            const id = opts.id || 'modal-eliminar-global';
            const modal = document.getElementById(id);
            if (!modal) return;

            this._urls[id] = opts.url;

            // Form action
            const form = document.getElementById(id + '-form');
            if (form) form.action = opts.url || '';

            // Titulo personalizado si se provee
            const tituloEl = document.getElementById(id + '-titulo');
            if (tituloEl && opts.titulo) tituloEl.textContent = opts.titulo;

            // Mensaje personalizado si se provee
            const descEl = document.getElementById(id + '-descripcion');
            if (descEl && opts.mensaje) descEl.textContent = opts.mensaje;

            // Nombre del registro
            const nombreContainer = document.getElementById(id + '-nombre-container');
            const nombreEl = document.getElementById(id + '-nombre');
            if (nombreEl && nombreContainer) {
                if (opts.nombre) {
                    nombreEl.textContent = opts.nombre;
                    nombreContainer.classList.remove('hidden');
                } else {
                    nombreContainer.classList.add('hidden');
                }
            }

            // Información extra o advertencia
            const extraEl = document.getElementById(id + '-extra');
            if (extraEl) {
                if (opts.extra) {
                    extraEl.innerHTML = opts.extra;
                    extraEl.classList.remove('hidden');
                } else {
                    extraEl.classList.add('hidden');
                }
            }

            // Restablecer botón confirmar
            const btnConfirmar = document.getElementById(id + '-btn-confirmar');
            const textoBtn = document.getElementById(id + '-texto-btn');
            if (btnConfirmar) btnConfirmar.disabled = false;
            if (textoBtn && opts.textoBoton) textoBtn.textContent = opts.textoBoton;

            // Mostrar modal
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        },

        cerrar: function(id) {
            id = id || 'modal-eliminar-global';
            const modal = document.getElementById(id);
            if (!modal) return;

            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        },

        confirmar: function(id) {
            id = id || 'modal-eliminar-global';
            const form = document.getElementById(id + '-form');
            const url = this._urls[id];

            if (!form) return;
            if (url) form.action = url;

            const btn = document.getElementById(id + '-btn-confirmar');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="inline-block animate-spin material-symbols-outlined text-[16px]">progress_activity</span> Eliminando...';
            }

            form.submit();
        }
    };

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modales = document.querySelectorAll('[id^="modal-eliminar-"]:not(.hidden)');
            modales.forEach(m => window.ModalEliminar.cerrar(m.id));
        }
    });
</script>
