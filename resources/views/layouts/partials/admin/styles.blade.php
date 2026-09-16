<style>
    html, body {
        max-width: 100%;
        overflow-x: clip;
    }
    body { 
        font-family: 'Plus Jakarta Sans', 'Figtree', sans-serif; 
        letter-spacing: -0.011em;
        background-color: #F8FAFC;
        color: #111827;
    }
    .material-symbols-outlined {
        font-family: 'Material Symbols Outlined';
        font-weight: normal;
        font-style: normal;
        font-size: 20px;
        line-height: 1;
        display: inline-block;
        -webkit-font-smoothing: antialiased;
    }

    :root {
        --admin-bg-light: #f8fafc;
        --admin-bg-dark: #181a1b;
        --admin-bg: var(--admin-bg-light);
    }
    html.dark {
        --admin-bg: var(--admin-bg-dark);
    }

    ::-webkit-scrollbar { width: 4px; height: 4px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 4px; }
    ::-webkit-scrollbar-thumb:hover { background: #94A3B8; }
    /* Firefox Scrollbar */
    * {
        scrollbar-width: thin;
        scrollbar-color: #475569 transparent;
    }
    
    /* Ocultar barra de scroll en el Sidebar */
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .card-elevated {
        background-color: #FFFFFF;
        border: 1px solid #E5E7EB;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04), 0 1px 2px -1px rgba(0, 0, 0, 0.03);
    }
    :root {
        --sidebar-offset: 0px;
    }
    @media (min-width: 768px) {
        :root {
            --sidebar-offset: 256px;
        }
    }
    
    /* Barra de progreso de navegación superior */
    #top-progress-bar {
        position: fixed;
        top: 0;
        left: 0;
        height: 3px;
        background: #10B981;
        z-index: 99999;
        width: 0%;
        opacity: 0;
        pointer-events: none;
        box-shadow: 0 0 10px #10B981, 0 0 4px #10B981;
    }
    
    .navigating #top-progress-bar {
        opacity: 1;
        width: 75%;
        transition: width 15s cubic-bezier(0.1, 0.05, 0, 1);
    }

    /* Animaciones Suaves de Página */
    @keyframes subtleFadeIn {
        0% { opacity: 0; transform: translateY(6px); }
        100% { opacity: 1; transform: none; }
    }
    .animate-fade-in-up {
        animation: subtleFadeIn 0.35s ease-out;
    }
    
    /* Animación de salida */
    .page-transitioning {
        opacity: 0.65 !important;
        pointer-events: none;
        transition: opacity 0.2s ease-out !important;
    }

    /* Seamless Active Sidebar Item */
    .sidebar-active-item .material-symbols-outlined {
        color: #059669 !important;
        font-variation-settings: 'FILL' 1;
    }

    @media (min-width: 768px) {
        .sidebar-active-item {
            background-color: var(--admin-bg) !important;
            color: #059669 !important;
            border-top-left-radius: 9999px;
            border-bottom-left-radius: 9999px;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            position: relative;
            margin-right: 0 !important;
            padding-right: 1.625rem !important;
        }
        html.dark .sidebar-active-item {
            color: #10b981 !important;
        }
        .sidebar-active-item::before,
        .sidebar-active-item::after {
            content: '';
            position: absolute;
            right: 0;
            width: 20px;
            height: 20px;
            z-index: -1;
        }
        .sidebar-active-item::before {
            top: -20px;
            background-image: radial-gradient(circle at top left, transparent 20px, var(--admin-bg) 20.5px) !important;
        }
        .sidebar-active-item::after {
            bottom: -20px;
            background-image: radial-gradient(circle at bottom left, transparent 20px, var(--admin-bg) 20.5px) !important;
        }
    }
    @media (max-width: 767px) {
        .sidebar-active-item {
            background-color: var(--admin-bg) !important;
            color: #059669 !important;
            border-radius: 9999px;
            margin-right: 0.75rem !important;
        }
    }
</style>

<!-- Prevención de Parpadeo (FOUC) para el Sidebar -->
<script>
    if (localStorage.getItem('sidebarExpanded') === 'false') {
        document.documentElement.classList.add('sidebar-collapsed');
    }
</script>

<style>
    :root {
        --sidebar-width: 256px;
    }
    html.sidebar-collapsed {
        --sidebar-width: 64px;
    }
    @media (min-width: 768px) {
        #admin-sidebar { width: var(--sidebar-width) !important; }
        #main-content { margin-left: var(--sidebar-width) !important; }
    }
    /* Collapsible Sidebar Styles */
    html.sidebar-collapsed #admin-sidebar .sidebar-text { display: none; }
    html.sidebar-collapsed #admin-sidebar .sidebar-group-title { display: none; }
    html.sidebar-collapsed #admin-sidebar .brand-text { display: none; }
    html.sidebar-collapsed #admin-sidebar a, html.sidebar-collapsed #admin-sidebar button:not(#theme-toggle) { 
        justify-content: center; 
        padding-left: 0; 
        padding-right: 0; 
        width: 40px; 
        height: 40px; 
        margin: 0 auto; 
    }
    html.sidebar-collapsed #admin-sidebar .sidebar-header { justify-content: center; padding-left: 0; padding-right: 0; }
    html.sidebar-collapsed #admin-sidebar .brand-logo-container { margin: 0 auto; }
    html.sidebar-collapsed #admin-sidebar .sidebar-version { display: none; }
    html.sidebar-collapsed #admin-sidebar .sidebar-footer { padding-left: 0; padding-right: 0; justify-content: center; }
    html.sidebar-collapsed #admin-sidebar #theme-toggle { transform: scale(0.85); margin: 0 auto; }
    html.sidebar-collapsed #admin-sidebar .sidebar-active-item { 
        padding-right: 0 !important; 
        width: 52px !important; 
    }
</style>

<!-- Dark Mode Initializer -->
<script>
    if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
</script>
