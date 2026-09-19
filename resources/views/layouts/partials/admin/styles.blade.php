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
    html.dark .card-elevated {
        background-color: #181a1b;
        border-color: #374151;
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
    /* Animaciones optimizadas para el panel lateral */
    #admin-sidebar .sidebar-text,
    #admin-sidebar .brand-text,
    #admin-sidebar .sidebar-version {
        transition: opacity 0.2s ease, max-width 0.2s ease, margin 0.2s ease;
        will-change: opacity, max-width;
        white-space: nowrap;
        opacity: 1;
        max-width: 200px;
        overflow: hidden;
    }
    
    #admin-sidebar .sidebar-group-title,
    #admin-sidebar .submenu-container {
        /* Se removieron transiciones de height/max-height que causaban lag al no ser animables desde "auto" */
        transition: max-width 0.2s ease;
        will-change: max-width;
        white-space: nowrap;
        opacity: 1;
        max-width: 250px;
    }
    
    /* Ocultar desbordamiento al colapsar para evitar saltos visuales */
    html.sidebar-collapsed #admin-sidebar .submenu-container:not(:hover) {
        overflow: hidden;
    }

    html.sidebar-collapsed #admin-sidebar .sidebar-text,
    html.sidebar-collapsed #admin-sidebar .brand-text,
    html.sidebar-collapsed #admin-sidebar .sidebar-version { 
        opacity: 0;
        max-width: 0;
        margin: 0;
        padding: 0;
        pointer-events: none;
        position: absolute;
    }
    
    html.sidebar-collapsed #admin-sidebar .sidebar-group-title,
    html.sidebar-collapsed #admin-sidebar .submenu-container {
        opacity: 0;
        max-width: 0;
        max-height: 0;
        height: 0;
        margin: 0;
        padding: 0;
        pointer-events: none;
        border: none;
    }
    
    /* Líneas conectoras para submenús */
    #admin-sidebar .submenu-item::before {
        content: '';
        position: absolute;
        left: 23px;
        top: 0;
        bottom: 50%;
        width: 12px;
        border-bottom: 1.5px solid #334155;
        border-left: 1.5px solid #334155;
        border-bottom-left-radius: 8px;
        pointer-events: none;
        transition: border-color 0.2s ease;
    }
    
    #admin-sidebar .submenu-item:not(:last-child)::after {
        content: '';
        position: absolute;
        left: 23px;
        top: 50%;
        bottom: 0;
        width: 1.5px;
        background-color: #334155;
        pointer-events: none;
    }
    
    #admin-sidebar .submenu-item.is-active::before {
        border-color: #10B981;
    }

    /* Estilos del panel colapsado */
    html.sidebar-collapsed #admin-sidebar nav > div > a, 
    html.sidebar-collapsed #admin-sidebar nav > .nav-group > button { 
        padding: 0 !important;
        justify-content: center !important;
        width: 40px !important; 
        height: 40px !important; 
        margin-left: auto !important;
        margin-right: auto !important;
    }
    
    /* Eliminar gaps internos que desfasaban el centrado */
    html.sidebar-collapsed #admin-sidebar nav > div > a,
    html.sidebar-collapsed #admin-sidebar nav > .nav-group > button,
    html.sidebar-collapsed #admin-sidebar nav > .nav-group > button > div {
        gap: 0 !important;
    }
    
    /* Permitir menús flotantes fuera del contenedor */
    html.sidebar-collapsed #admin-sidebar nav {
        overflow: visible !important;
    }
    
    /* Menús flotantes (Popovers) al pasar el cursor o fijar con click */
    html.sidebar-collapsed #admin-sidebar .nav-group:hover .submenu-container,
    html.sidebar-collapsed #admin-sidebar .nav-group.is-pinned .submenu-container {
        display: flex !important;
        opacity: 1 !important;
        max-width: 250px !important;
        max-height: 800px !important;
        height: auto !important;
        pointer-events: auto !important;
        position: absolute;
        left: 56px;
        top: 0;
        background-color: #1f2937;
        border-radius: 12px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.2);
        z-index: 9999;
        padding: 8px 0 !important;
        border: 1px solid #374151;
        will-change: opacity, transform;
    }
    
    html.dark.sidebar-collapsed #admin-sidebar .nav-group:hover .submenu-container,
    html.dark.sidebar-collapsed #admin-sidebar .nav-group.is-pinned .submenu-container {
        background-color: #111827;
        border-color: #1f2937;
    }
    
    /* Tooltips oscuros (etiquetas) solo para items individuales */
    html.sidebar-collapsed #admin-sidebar a.group .sidebar-text {
        position: absolute;
        left: 52px;
        top: 50%;
        transform: translateY(-50%);
        background-color: #1e293b;
        color: #f8fafc;
        padding: 4px 12px !important;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
        z-index: 10000;
        pointer-events: none;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s ease, visibility 0.2s ease;
        max-width: 200px !important;
        margin: 0;
    }
    
    html.dark.sidebar-collapsed #admin-sidebar a.group .sidebar-text {
        background-color: #f8fafc;
        color: #0f172a;
    }

    html.sidebar-collapsed #admin-sidebar a.group:hover .sidebar-text {
        opacity: 1 !important;
        visibility: visible !important;
    }
    /* Cabecera del popover flotante */
    .popover-header { 
        display: none; 
    }
    html.sidebar-collapsed #admin-sidebar .popover-header { 
        display: block; 
        padding: 8px 16px 8px 16px;
        margin-bottom: 4px;
        font-size: 13px;
        font-weight: 800;
        color: #f8fafc;
        border-bottom: 1px solid #374151;
        position: relative;
    }
    
    html.sidebar-collapsed #admin-sidebar .popover-header::after {
        content: '';
        position: absolute;
        left: 23px;
        top: 32px;
        bottom: -4px;
        width: 1.5px;
        background-color: #334155;
    }

    html.dark.sidebar-collapsed #admin-sidebar .popover-header {
        border-bottom-color: #1f2937;
    }
    
    html.sidebar-collapsed #admin-sidebar .nav-group:hover > button .sidebar-text:not(.material-symbols-outlined) {
        top: -16px;
        transform: none;
    }
    
    /* Centrado de elementos generales */
    html.sidebar-collapsed #admin-sidebar .sidebar-header,
    html.sidebar-collapsed #admin-sidebar .sidebar-footer { 
        padding: 0;
        justify-content: center;
    }
    html.sidebar-collapsed #admin-sidebar .sidebar-footer {
        padding-top: 16px;
        padding-bottom: 16px;
    }
    html.sidebar-collapsed #admin-sidebar .sidebar-header a {
        justify-content: center;
        gap: 0;
    }
    html.sidebar-collapsed #admin-sidebar .brand-logo-container { margin: 0 auto; }
    html.sidebar-collapsed #admin-sidebar #theme-toggle { transform: scale(0.85); margin: 0 auto; }
    
    /* Ítem activo en modo colapsado */
    html.sidebar-collapsed #admin-sidebar nav > div > a.sidebar-active-item,
    html.sidebar-collapsed #admin-sidebar nav > .nav-group > button.is-active-parent { 
        padding: 0 !important; 
        width: 40px !important; 
        height: 40px !important; 
        margin-left: auto !important;
        margin-right: auto !important;
        border-radius: 9999px !important;
        background-color: var(--admin-bg) !important;
    }
    
    html.sidebar-collapsed #admin-sidebar nav > div > a.sidebar-active-item::before,
    html.sidebar-collapsed #admin-sidebar nav > div > a.sidebar-active-item::after,
    html.sidebar-collapsed #admin-sidebar nav > .nav-group > button.is-active-parent::before,
    html.sidebar-collapsed #admin-sidebar nav > .nav-group > button.is-active-parent::after {
        display: none !important;
    }

    html.sidebar-collapsed #admin-sidebar nav > .nav-group > button.is-active-parent .material-symbols-outlined {
        color: #059669 !important;
    }
    html.dark.sidebar-collapsed #admin-sidebar nav > .nav-group > button.is-active-parent .material-symbols-outlined {
        color: #10B981 !important;
    }
    
    /* Desactivar corte transparente (seamless) en sub-ítems del menú flotante */
    html.sidebar-collapsed #admin-sidebar .submenu-container .sidebar-active-item {
        border-radius: 8px !important;
        margin-right: 12px !important;
        background-color: rgba(255, 255, 255, 0.05) !important;
        color: #34D399 !important;
    }
    html.sidebar-collapsed #admin-sidebar .submenu-container .sidebar-active-item::before,
    html.sidebar-collapsed #admin-sidebar .submenu-container .sidebar-active-item::after {
        display: none !important;
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
