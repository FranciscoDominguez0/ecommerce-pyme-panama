<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('admin-sidebar');
        const backdrop = document.getElementById('mobile-sidebar-backdrop');
        sidebar.classList.toggle('-translate-x-full');
        backdrop.classList.toggle('hidden');
    }

    function toggleDesktopSidebar() {
        const html = document.documentElement;
        html.classList.toggle('sidebar-collapsed');
        const isCollapsed = html.classList.contains('sidebar-collapsed');
        localStorage.setItem('sidebarExpanded', !isCollapsed);
        document.getElementById('desktop-sidebar-icon').textContent = isCollapsed ? 'menu' : 'menu_open';
    }

    // Initialize desktop icon
    document.addEventListener('DOMContentLoaded', () => {
        if (document.documentElement.classList.contains('sidebar-collapsed')) {
            const icon = document.getElementById('desktop-sidebar-icon');
            if (icon) icon.textContent = 'menu';
        } else {
            const icon = document.getElementById('desktop-sidebar-icon');
            if (icon) icon.textContent = 'menu_open';
        }
    });

    // Mantener la posición del scroll del sidebar entre recargas de página
    document.addEventListener("DOMContentLoaded", function() {
        const sidebarNav = document.querySelector('#admin-sidebar nav');
        if (sidebarNav) {
            const savedScroll = sessionStorage.getItem('adminSidebarScroll');
            if (savedScroll !== null) {
                sidebarNav.scrollTop = parseInt(savedScroll, 10);
            }
            window.addEventListener('beforeunload', () => {
                sessionStorage.setItem('adminSidebarScroll', sidebarNav.scrollTop);
            });
        }
    });

    // Reloj en vivo de Panamá (GMT-5, formato 12 horas en español)
    function updatePanamaClock() {
        const clockEl = document.getElementById('topbar-live-clock');
        if (!clockEl) return;

        try {
            const now = new Date();
            const formatter = new Intl.DateTimeFormat('es-PA', {
                timeZone: 'America/Panama',
                weekday: 'short',
                day: 'numeric',
                month: 'short',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });

            const formatted = formatter.format(now);
            const capitalized = formatted.charAt(0).toUpperCase() + formatted.slice(1).replace(/\./g, '');
            clockEl.textContent = capitalized;
        } catch (e) {
            // Fallback silencioso
        }
    }

    setInterval(updatePanamaClock, 1000);
    updatePanamaClock();

    // --------------------------------------------------------
    // Interceptor Global de Navegación (Transición Suave)
    // --------------------------------------------------------
    
    function cleanupTransition() {
        document.body.classList.remove('navigating');
        const actualContent = document.getElementById('actual-page-content');
        if (actualContent) {
            actualContent.classList.remove('page-transitioning');
            // Remover la clase de animación para destruir cualquier Containing Block de CSS
            setTimeout(() => {
                actualContent.classList.remove('animate-fade-in-up');
            }, 400);
        }
    }

    function handleLoginSkeleton() {
        const skeleton = document.getElementById('global-admin-skeleton-wrapper');
        const actualContent = document.getElementById('actual-page-content');
        
        if (skeleton && !skeleton.classList.contains('hidden')) {
            // Simular un tiempo de carga post-login para mostrar el efecto premium
            setTimeout(() => {
                if (actualContent) {
                    actualContent.classList.remove('opacity-0');
                    actualContent.classList.add('opacity-100');
                    actualContent.classList.add('animate-fade-in-up');
                }
                skeleton.style.opacity = '0';
                
                setTimeout(() => {
                    skeleton.classList.add('hidden');
                    if (actualContent) {
                        setTimeout(() => actualContent.classList.remove('animate-fade-in-up'), 400);
                    }
                }, 300); // 300ms debe coincidir con transition-opacity
            }, 800);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        cleanupTransition();

        const sidebarLinks = document.querySelectorAll('#admin-sidebar nav a, a.nav-transition');
        
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                
                // Ignorar anclas, js, links en blanco o la misma página
                if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
                if (this.getAttribute('target') === '_blank') return;
                if (href === window.location.href || href === window.location.pathname) return;

                document.body.classList.add('navigating');
                const actualContent = document.getElementById('actual-page-content');
                if (actualContent) {
                    actualContent.classList.add('page-transitioning');
                }
            });
        });

        handleLoginSkeleton();
    });

    // BFCache (Back/Forward Cache) Fix
    window.addEventListener('pageshow', cleanupTransition);
    
    // Livewire Navigation Fix (Borra estados de navegación tras SPA swap y restaura UI)
    document.addEventListener('livewire:navigated', () => {
        cleanupTransition();
        handleLoginSkeleton();
        
        // 1. Restaurar el estado del modo oscuro
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        if (typeof updateThemeToggleUI === 'function') {
            updateThemeToggleUI();
        }

        // 2. Restaurar el estado del sidebar
        if (localStorage.getItem('sidebarExpanded') === 'false') {
            document.documentElement.classList.add('sidebar-collapsed');
            const icon = document.getElementById('desktop-sidebar-icon');
            if (icon) icon.textContent = 'menu';
        } else {
            document.documentElement.classList.remove('sidebar-collapsed');
            const icon = document.getElementById('desktop-sidebar-icon');
            if (icon) icon.textContent = 'menu_open';
        }
        
        // 3. Re-bind theme toggle si Livewire reemplaza el DOM
        const themeToggleBtn = document.getElementById('theme-toggle');
        if (themeToggleBtn) {
            themeToggleBtn.removeEventListener('click', window.toggleThemeHandler);
            themeToggleBtn.addEventListener('click', window.toggleThemeHandler);
        }
    });

    // Theme Toggle Logic
    function updateThemeToggleUI() {
        const thumb = document.getElementById('theme-toggle-thumb');
        const darkIcon = document.getElementById('theme-toggle-dark-icon');
        const lightIcon = document.getElementById('theme-toggle-light-icon');
        
        if(!thumb || !darkIcon || !lightIcon) return;

        if (document.documentElement.classList.contains('dark')) {
            thumb.classList.remove('translate-x-1');
            thumb.classList.add('translate-x-9');
            thumb.classList.remove('bg-white');
            thumb.classList.add('bg-slate-800');
            darkIcon.classList.remove('opacity-0');
            darkIcon.classList.add('text-white');
            lightIcon.classList.add('opacity-0');
        } else {
            thumb.classList.remove('translate-x-9');
            thumb.classList.add('translate-x-1');
            thumb.classList.remove('bg-slate-800');
            thumb.classList.add('bg-white');
            darkIcon.classList.add('opacity-0');
            darkIcon.classList.remove('text-white');
            lightIcon.classList.remove('opacity-0');
        }
    }

    window.toggleThemeHandler = function() {
        // Habilitamos las transiciones solo al hacer clic manual para que haya animación
        document.getElementById('theme-toggle').classList.add('transition-colors', 'duration-300');
        document.getElementById('theme-toggle-thumb').classList.add('transition-transform', 'duration-300');
        document.getElementById('theme-toggle-dark-icon').classList.add('transition-opacity', 'duration-300');
        document.getElementById('theme-toggle-light-icon').classList.add('transition-opacity', 'duration-300');

        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('color-theme', 'light');
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('color-theme', 'dark');
        }
        updateThemeToggleUI();
        window.dispatchEvent(new Event('theme-changed'));
    };

    document.addEventListener('DOMContentLoaded', () => {
        updateThemeToggleUI(true);
        const themeToggleBtn = document.getElementById('theme-toggle');
        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', window.toggleThemeHandler);
        }
    });

</script>

<!-- Sistema de Tooltips Profesional para el Sidebar -->
<div id="sidebar-tooltip-container" class="fixed z-[100] pointer-events-none opacity-0 transition-all duration-200 ease-out bg-slate-800 text-white text-xs font-semibold pl-3 pr-2.5 py-1.5 rounded-lg shadow-xl whitespace-nowrap border border-slate-700/80" style="transform: scale(0.95);">
    <div class="absolute w-2.5 h-2.5 bg-slate-800 border-l border-b border-slate-700/80 -left-[5px] top-1/2 rounded-sm z-0" style="transform: translateY(-50%) rotate(45deg);"></div>
    <span id="sidebar-tooltip-text" class="relative z-10 block"></span>
</div>

<script>
    document.addEventListener('mouseover', (e) => {
        if (!document.documentElement.classList.contains('sidebar-collapsed') || window.innerWidth < 768) return;
        
        const target = e.target.closest('#admin-sidebar a, #admin-sidebar button');
        if (target) {
            const tooltip = document.getElementById('sidebar-tooltip-container');
            const tooltipText = document.getElementById('sidebar-tooltip-text');
            
            let text = '';
            const textSpan = target.querySelector('.sidebar-text');
            if (textSpan) {
                text = textSpan.textContent.trim();
            } else if (target.id === 'theme-toggle') {
                text = document.documentElement.classList.contains('dark') ? 'Modo Claro' : 'Modo Oscuro';
            }
            
            if (!text) return;
            
            tooltipText.textContent = text;
            
            const rect = target.getBoundingClientRect();
            tooltip.style.left = (rect.right + 14) + 'px';
            tooltip.style.top = (rect.top + (rect.height / 2) - (tooltip.offsetHeight / 2)) + 'px';
            
            tooltip.classList.remove('opacity-0');
            tooltip.classList.add('opacity-100');
            tooltip.style.transform = 'scale(1)';
        }
    });

    document.addEventListener('mouseout', (e) => {
        const target = e.target.closest('#admin-sidebar a, #admin-sidebar button');
        if (target) {
            const tooltip = document.getElementById('sidebar-tooltip-container');
            tooltip.classList.add('opacity-0');
            tooltip.classList.remove('opacity-100');
            tooltip.style.transform = 'scale(0.95)';
        }
    });
    
    const sidebarNav = document.querySelector('#admin-sidebar nav');
    if (sidebarNav) {
        sidebarNav.addEventListener('scroll', () => {
            const tooltip = document.getElementById('sidebar-tooltip-container');
            if (!tooltip.classList.contains('opacity-0')) {
                tooltip.classList.add('opacity-0');
                tooltip.classList.remove('opacity-100');
                tooltip.style.transform = 'scale(0.95)';
            }
        }, { passive: true });
    }
</script>
