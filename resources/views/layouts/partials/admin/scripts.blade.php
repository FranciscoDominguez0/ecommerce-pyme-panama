<script>
    window.toggleSidebar = function() {
        const sidebar = document.getElementById('admin-sidebar');
        const backdrop = document.getElementById('mobile-sidebar-backdrop');
        if (sidebar) sidebar.classList.toggle('-translate-x-full');
        if (backdrop) {
            backdrop.classList.toggle('opacity-0');
            backdrop.classList.toggle('pointer-events-none');
        }
    };

    window.toggleDesktopSidebar = function() {
        const html = document.documentElement;
        html.classList.toggle('sidebar-collapsed');
        const isCollapsed = html.classList.contains('sidebar-collapsed');
        localStorage.setItem('sidebarExpanded', !isCollapsed);
    };

    // Genera automáticamente un slug limpio a partir de un texto
    window.generarSlugAutomatico = function(texto, targetId = 'slug') {
        const target = document.getElementById(targetId);
        if (!target) return;
        target.value = (texto || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9 -]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    };

    // Previsualización inmediata de imagen para campos de archivo
    window.previsualizarLogo = function(input, imgId = 'preview-img', placeholderId = 'preview-placeholder', currentId = 'preview-current') {
        const file = input.files ? input.files[0] : null;
        const img = document.getElementById(imgId);
        const placeholder = document.getElementById(placeholderId);
        const current = document.getElementById(currentId);
        if (!img) return;

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                img.src = e.target.result;
                img.classList.remove('hidden');
                if (placeholder) placeholder.classList.add('hidden');
                if (current) current.classList.add('hidden');
            };
            reader.readAsDataURL(file);
        } else {
            img.src = '';
            img.classList.add('hidden');
            if (placeholder) placeholder.classList.remove('hidden');
            if (current) current.classList.remove('hidden');
        }
    };

    // Mantener la posición del scroll del sidebar entre recargas y navegación SPA
    function initSidebarScrollPersistence() {
        const nav = document.querySelector('#admin-sidebar nav');
        if (nav && !nav.dataset.scrollPersistBound) {
            nav.dataset.scrollPersistBound = 'true';
            const savedScroll = sessionStorage.getItem('adminSidebarScroll');
            if (savedScroll !== null) {
                nav.scrollTop = parseInt(savedScroll, 10);
            }
            window.addEventListener('beforeunload', () => {
                sessionStorage.setItem('adminSidebarScroll', nav.scrollTop);
            });
        }
    }

    // Reloj en vivo de Panamá (GMT-5, formato 12 horas en español)
    window.updatePanamaClock = function() {
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
    };

    if (!window.__panamaClockInterval) {
        window.__panamaClockInterval = setInterval(window.updatePanamaClock, 1000);
    }
    window.updatePanamaClock();

    // --------------------------------------------------------
    // Interceptor Global de Navegación (Transición Suave)
    // --------------------------------------------------------
    window.cleanupTransition = function() {
        document.body.classList.remove('navigating');
        const actualContent = document.getElementById('actual-page-content');
        if (actualContent) {
            actualContent.classList.remove('page-transitioning');
            setTimeout(() => {
                actualContent.classList.remove('animate-fade-in-up');
            }, 400);
        }
    };

    window.handleLoginSkeleton = function() {
        const skeleton = document.getElementById('global-admin-skeleton-wrapper');
        const actualContent = document.getElementById('actual-page-content');
        
        if (skeleton && !skeleton.classList.contains('hidden')) {
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
                }, 300);
            }, 800);
        }
    };

    // Theme Toggle Logic
    window.updateThemeToggleUI = function() {
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
    };

    window.toggleThemeHandler = function() {
        const toggleBtn = document.getElementById('theme-toggle');
        const thumb = document.getElementById('theme-toggle-thumb');
        const darkIcon = document.getElementById('theme-toggle-dark-icon');
        const lightIcon = document.getElementById('theme-toggle-light-icon');

        if (toggleBtn) toggleBtn.classList.add('transition-colors', 'duration-300');
        if (thumb) thumb.classList.add('transition-transform', 'duration-300');
        if (darkIcon) darkIcon.classList.add('transition-opacity', 'duration-300');
        if (lightIcon) lightIcon.classList.add('transition-opacity', 'duration-300');

        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            document.documentElement.classList.add('light');
            localStorage.setItem('color-theme', 'light');
            document.cookie = "admin_theme=light;path=/;max-age=31536000;SameSite=Lax";
        } else {
            document.documentElement.classList.add('dark');
            document.documentElement.classList.remove('light');
            localStorage.setItem('color-theme', 'dark');
            document.cookie = "admin_theme=dark;path=/;max-age=31536000;SameSite=Lax";
        }
        window.updateThemeToggleUI();
        window.dispatchEvent(new Event('theme-changed'));
    };

    // Inicialización de Listeners una sola vez (Protección contra duplicación en wire:navigate)
    (function() {
        function bindThemeToggleBtn() {
            const themeToggleBtn = document.getElementById('theme-toggle');
            if (themeToggleBtn && !themeToggleBtn.dataset.themeBound) {
                themeToggleBtn.dataset.themeBound = 'true';
                themeToggleBtn.addEventListener('click', window.toggleThemeHandler);
            }
        }

        function bindSidebarNavLinks() {
            const sidebarLinks = document.querySelectorAll('#admin-sidebar nav a, a.nav-transition');
            sidebarLinks.forEach(link => {
                if (!link.dataset.navBound) {
                    link.dataset.navBound = 'true';
                }
            });
        }

        function initAdminLifecycle() {
            initSidebarScrollPersistence();
            window.cleanupTransition();
            window.handleLoginSkeleton();
            window.updateThemeToggleUI();
            bindThemeToggleBtn();
            bindSidebarNavLinks();
        }

        if (!window.__adminGlobalListenersInited) {
            window.__adminGlobalListenersInited = true;

            window.addEventListener('pageshow', window.cleanupTransition);

            // Asegurar tema durante el proceso de navegación de Livewire
            function enforceActiveTheme() {
                const savedTheme = localStorage.getItem('color-theme');
                if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                    document.documentElement.classList.remove('light');
                } else {
                    document.documentElement.classList.remove('dark');
                    document.documentElement.classList.add('light');
                }
            }

            document.addEventListener('livewire:navigating', enforceActiveTheme);

            document.addEventListener('livewire:navigated', () => {
                window.cleanupTransition();
                window.handleLoginSkeleton();

                // 1. Restaurar tema exacto (oscuro o claro)
                enforceActiveTheme();
                window.updateThemeToggleUI();

                // 2. Restaurar sidebar collapsed
                if (localStorage.getItem('sidebarExpanded') === 'false') {
                    document.documentElement.classList.add('sidebar-collapsed');
                    const icon = document.getElementById('desktop-sidebar-icon');
                    if (icon) icon.style.transform = 'rotate(180deg)';
                } else {
                    document.documentElement.classList.remove('sidebar-collapsed');
                    const icon = document.getElementById('desktop-sidebar-icon');
                    if (icon) icon.style.transform = 'rotate(0deg)';
                }

                // 3. Re-bind theme toggle y links
                bindThemeToggleBtn();
                bindSidebarNavLinks();
                initSidebarScrollPersistence();
            });
        }

        // Ejecución inmediata
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAdminLifecycle);
        } else {
            initAdminLifecycle();
        }
    })();
</script>

<!-- Sistema de Tooltips Profesional para el Sidebar -->
<div id="sidebar-tooltip-container" class="fixed z-[100] pointer-events-none opacity-0 transition-all duration-200 ease-out bg-slate-800 text-white text-xs font-semibold pl-3 pr-2.5 py-1.5 rounded-lg shadow-xl whitespace-nowrap border border-slate-700/80" style="transform: scale(0.95);">
    <div class="absolute w-2.5 h-2.5 bg-slate-800 border-l border-b border-slate-700/80 -left-[5px] top-1/2 rounded-sm z-0" style="transform: translateY(-50%) rotate(45deg);"></div>
    <span id="sidebar-tooltip-text" class="relative z-10 block"></span>
</div>

<script>
    (function() {
        if (!window.__adminSidebarTooltipInited) {
            window.__adminSidebarTooltipInited = true;

            document.addEventListener('mouseover', (e) => {
                if (!document.documentElement.classList.contains('sidebar-collapsed') || window.innerWidth < 768) return;
                
                const target = e.target.closest('#admin-sidebar a, #admin-sidebar button');
                if (target) {
                    window.dispatchEvent(new CustomEvent('sidebar-hover', { detail: target.closest('.nav-group') || target }));
                    if (target.closest('.nav-group')) return;

                    const tooltip = document.getElementById('sidebar-tooltip-container');
                    const tooltipText = document.getElementById('sidebar-tooltip-text');
                    if (!tooltip || !tooltipText) return;
                    
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
                    if (tooltip) {
                        tooltip.classList.add('opacity-0');
                        tooltip.classList.remove('opacity-100');
                        tooltip.style.transform = 'scale(0.95)';
                    }
                }
            });
        }

        const attachSidebarScrollTooltip = () => {
            const nav = document.querySelector('#admin-sidebar nav');
            if (nav && !nav.dataset.scrollTooltipBound) {
                nav.dataset.scrollTooltipBound = 'true';
                nav.addEventListener('scroll', () => {
                    const tooltip = document.getElementById('sidebar-tooltip-container');
                    if (tooltip && !tooltip.classList.contains('opacity-0')) {
                        tooltip.classList.add('opacity-0');
                        tooltip.classList.remove('opacity-100');
                        tooltip.style.transform = 'scale(0.95)';
                    }
                }, { passive: true });
            }
        };

        attachSidebarScrollTooltip();
        document.addEventListener('livewire:navigated', attachSidebarScrollTooltip);
    })();
</script>

<script>
    (function() {
        const registerAdminAutoFilter = () => {
            if (window.Alpine && !window.Alpine.__adminAutoFilterRegistered) {
                window.Alpine.__adminAutoFilterRegistered = true;
                window.Alpine.data('adminAutoFilter', () => ({
                    debounceTimer: null,
                    autoSubmit() {
                        if(document.activeElement && document.activeElement.name) {
                            sessionStorage.setItem('focusedElement', document.activeElement.name);
                        }
                        clearTimeout(this.debounceTimer);
                        this.debounceTimer = setTimeout(() => {
                            this.$root.submit();
                        }, 800);
                    },
                    init() {
                        let focusedName = sessionStorage.getItem('focusedElement');
                        if(focusedName) {
                            setTimeout(() => {
                                requestAnimationFrame(() => {
                                    let el = this.$root.querySelector('[name=\'' + focusedName + '\']');
                                    if(el) {
                                        el.focus();
                                        if (el.setSelectionRange) {
                                            let len = el.value.length;
                                            el.setSelectionRange(len, len);
                                        } else {
                                            let val = el.value;
                                            el.value = '';
                                            el.value = val;
                                        }
                                    }
                                    sessionStorage.removeItem('focusedElement');
                                });
                            }, 100);
                        }
                    }
                }));
            }
        };

        if (window.Alpine) {
            registerAdminAutoFilter();
        } else {
            document.addEventListener('alpine:init', registerAdminAutoFilter);
        }
    })();
</script>
