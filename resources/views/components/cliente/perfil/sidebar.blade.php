@props(['active' => ''])

<aside class="w-full md:w-64 shrink-0">
    <div class="bg-white border border-outline-variant rounded-xl overflow-hidden shadow-sm">
        <div class="p-5 border-b border-outline-variant/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-xl">person</span>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-primary truncate">{{ Auth::user()->nombre_completo }}</p>
                    <p class="text-[11px] text-on-surface-variant truncate">Administra tu cuenta</p>
                </div>
            </div>
        </div>

        <nav class="p-2">
            <a href="{{ route('dashboard') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ $active === 'dashboard' ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-primary' }}">
                <span class="material-symbols-outlined text-lg">dashboard</span>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('cliente.perfil.pedidos.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ $active === 'pedidos' ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-primary' }}">
                <span class="material-symbols-outlined text-lg">package_2</span>
                <span>Historial de Pedidos</span>
            </a>

            <a href="{{ route('cliente.perfil.direcciones') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ $active === 'direcciones' ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-primary' }}">
                <span class="material-symbols-outlined text-lg">local_shipping</span>
                <span>Direcciones de Envío</span>
            </a>

            <a href="{{ route('cliente.perfil.pago') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ $active === 'pago' ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-primary' }}">
                <span class="material-symbols-outlined text-lg">payments</span>
                <span>Métodos de Pago</span>
            </a>

            <a href="{{ route('cliente.perfil.datos') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ $active === 'configuracion' ? 'bg-primary/10 text-primary' : 'text-on-surface-variant hover:bg-surface-container-low hover:text-primary' }}">
                <span class="material-symbols-outlined text-lg">settings</span>
                <span>Configuración</span>
            </a>
        </nav>

        <div class="p-2 border-t border-outline-variant/50">
            <a href="{{ route('cliente.lista-deseos') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors text-on-surface-variant hover:bg-surface-container-low hover:text-primary">
                <span class="material-symbols-outlined text-lg">favorite</span>
                <span>Lista de Deseos</span>
            </a>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('cliente.perfil.direcciones') }}"
            class="flex items-center justify-center gap-2 w-full py-2.5 px-4 rounded-lg bg-primary text-on-primary text-xs font-bold uppercase tracking-wider hover:bg-primary-container transition-colors shadow-sm">
            <span class="material-symbols-outlined text-[16px]">add</span>
            <span>Agregar Dirección</span>
        </a>
    </div>
</aside>
