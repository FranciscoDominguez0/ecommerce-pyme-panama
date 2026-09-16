<script>
    window.abrirCarritoDrawer = function () {
        if (window.Livewire) {
            Livewire.dispatch('abrir-carrito-drawer');
        } else {
            window.dispatchEvent(new CustomEvent('abrir-carrito'));
        }
    };

    document.addEventListener('livewire:init', () => {
        Livewire.on('mostrar-toast', (event) => {
            const data = Array.isArray(event) ? event[0] : (event.detail ? (Array.isArray(event.detail) ? event.detail[0] : event.detail) : event);
            if (window.mostrarToast && data) {
                window.mostrarToast(data.tipo || 'info', data.mensaje || '');
            }
        });
    });

    function handleLoginSkeleton() {
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
    }

    function initTypewriterSearch() {
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
        
        searchInput.addEventListener('blur', function() {
            if (this.value.trim() === '') {
                clearTimeout(searchInput.typewriterTimeout);
                isDeleting = false;
                charIndex = 0;
                type();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', initTypewriterSearch);
    document.addEventListener('livewire:navigated', initTypewriterSearch);
    
    document.addEventListener('DOMContentLoaded', handleLoginSkeleton);
    document.addEventListener('livewire:navigated', handleLoginSkeleton);
</script>
