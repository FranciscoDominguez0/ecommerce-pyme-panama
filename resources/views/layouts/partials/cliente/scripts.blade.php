<script data-navigate-once>
    (function () {
        // Evitar múltiples ejecuciones de inicialización en el ciclo de vida SPA
        if (window.__clienteScriptsInited) return;
        window.__clienteScriptsInited = true;

        // Función global para abrir el Drawer del Carrito desde cualquier vista o botón
        window.abrirCarritoDrawer = function () {
            if (window.Livewire) {
                Livewire.dispatch('abrir-carrito-drawer');
            } else {
                window.dispatchEvent(new CustomEvent('abrir-carrito'));
            }
        };

        // Registrar listener para eventos toast emitidos por Livewire
        function registrarListenerToast() {
            if (window.Livewire && !window._toastListenerRegistrado) {
                window._toastListenerRegistrado = true;
                Livewire.on('mostrar-toast', (event) => {
                    const data = Array.isArray(event) ? event[0] : (event.detail ? (Array.isArray(event.detail) ? event.detail[0] : event.detail) : event);
                    if (window.mostrarToast && data) {
                        window.mostrarToast(data.tipo || 'info', data.mensaje || '');
                    }
                });
            }
        }

        if (window.Livewire) {
            registrarListenerToast();
        } else {
            document.addEventListener('livewire:init', registrarListenerToast);
        }

        // Sincronizar badges de carrito y lista de deseos al usar el historial del navegador (atrás/adelante)
        let navegacionPorHistorial = false;

        window.addEventListener('popstate', () => {
            navegacionPorHistorial = true;
        });

        document.addEventListener('livewire:navigated', () => {
            if (navegacionPorHistorial) {
                navegacionPorHistorial = false;
                if (window.Livewire) {
                    Livewire.dispatch('carrito-actualizado');
                    Livewire.dispatch('deseos-actualizado');
                }
            }
        });

        window.addEventListener('pageshow', (event) => {
            if (event.persisted && window.Livewire) {
                Livewire.dispatch('carrito-actualizado');
                Livewire.dispatch('deseos-actualizado');
            }
        });

        // Control de skeleton tras login
        window.handleLoginSkeleton = function () {
            const skeleton = document.getElementById('global-cliente-skeleton');
            const actualContent = document.getElementById('actual-page-content');

            if (skeleton && actualContent && !skeleton.classList.contains('hidden')) {
                setTimeout(() => {
                    skeleton.style.opacity = '0';
                    actualContent.classList.remove('opacity-0');
                    actualContent.classList.add('opacity-100');
                    setTimeout(() => {
                        skeleton.classList.add('hidden');
                    }, 300);
                }, 800);
            }
        };

        // Efecto de máquina de escribir en el input de búsqueda del header
        window.initTypewriterSearch = function () {
            const searchInput = document.getElementById('global-search-input');
            if (!searchInput) return;

            if (searchInput.typewriterTimeout) clearTimeout(searchInput.typewriterTimeout);

            if (searchInput.value.trim() !== '') return;

            const phrases = [
                "Buscar laptops...",
                "Buscar celulares...",
                "Buscar componentes...",
                "Buscar monitores...",
                "Buscar audífonos..."
            ];

            let phraseIndex = 0;
            let charIndex = 0;
            let isDeleting = false;

            function type() {
                if (document.activeElement === searchInput || searchInput.value.trim() !== '') {
                    searchInput.setAttribute('placeholder', 'Buscar productos, categorías...');
                    return;
                }

                const currentPhrase = phrases[phraseIndex];

                if (isDeleting) {
                    searchInput.setAttribute('placeholder', currentPhrase.substring(0, charIndex - 1));
                    charIndex--;
                } else {
                    searchInput.setAttribute('placeholder', currentPhrase.substring(0, charIndex + 1));
                    charIndex++;
                }

                let typeSpeed = isDeleting ? 30 : 80;

                if (!isDeleting && charIndex === currentPhrase.length) {
                    typeSpeed = 2000;
                    isDeleting = true;
                } else if (isDeleting && charIndex === 0) {
                    isDeleting = false;
                    phraseIndex = (phraseIndex + 1) % phrases.length;
                    typeSpeed = 400;
                }

                searchInput.typewriterTimeout = setTimeout(type, typeSpeed);
            }

            searchInput.setAttribute('placeholder', '');
            type();

            if (!searchInput.dataset.typewriterBound) {
                searchInput.dataset.typewriterBound = 'true';
                searchInput.addEventListener('blur', function () {
                    if (this.value.trim() === '') {
                        clearTimeout(searchInput.typewriterTimeout);
                        isDeleting = false;
                        charIndex = 0;
                        type();
                    }
                });
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            window.initTypewriterSearch();
            window.handleLoginSkeleton();
        });

        document.addEventListener('livewire:navigated', () => {
            window.initTypewriterSearch();
            window.handleLoginSkeleton();
        });

        // Ejecución inmediata inicial
        window.initTypewriterSearch();
        window.handleLoginSkeleton();
    })();
</script>