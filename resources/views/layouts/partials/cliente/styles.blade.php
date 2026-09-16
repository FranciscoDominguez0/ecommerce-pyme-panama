<style>
    html {
        scroll-behavior: smooth;
    }

    body {
        font-family: 'Figtree', sans-serif;
        letter-spacing: -0.015em;
    }

    .material-symbols-outlined {
        font-family: 'Material Symbols Outlined';
        font-weight: normal;
        font-style: normal;
        font-size: 20px;
        line-height: 1;
        letter-spacing: normal;
        text-transform: none;
        display: inline-block;
        white-space: nowrap;
        word-wrap: normal;
        direction: ltr;
        -webkit-font-feature-settings: 'liga';
        -webkit-font-smoothing: antialiased;
        font-feature-settings: 'liga';
        text-rendering: optimizeLegibility;
    }

    .whatsapp-float {
        position: fixed;
        width: 52px;
        height: 52px;
        bottom: 20px;
        right: 20px;
        background-color: #25d366;
        color: #FFF;
        border-radius: 50px;
        text-align: center;
        font-size: 26px;
        box-shadow: 0 4px 16px rgba(37, 211, 102, 0.4);
        z-index: 100;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .whatsapp-float:hover {
        transform: scale(1.08);
        box-shadow: 0 8px 24px rgba(37, 211, 102, 0.6);
    }

    .sidebar-nav-active {
        background-color: rgba(0, 35, 73, 0.10) !important;
        color: #002349 !important;
    }
</style>
