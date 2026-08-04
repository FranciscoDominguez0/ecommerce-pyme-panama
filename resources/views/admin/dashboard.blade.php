<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl text-gray-900 leading-tight flex items-center gap-2">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary-container text-white text-sm">
                    <span class="material-symbols-outlined text-base">admin_panel_settings</span>
                </span>
                Panel de Administración - PayMe Panamá
            </h2>
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                Rol: Administrador
            </span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Admin Welcome Banner -->
            <div class="bg-gradient-to-r from-[#002349] to-[#00132b] text-white rounded-2xl p-6 sm:p-8 shadow-sm">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-xl sm:text-2xl font-bold tracking-tight">
                            ¡Bienvenido al Panel de Control, {{ Auth::user()->nombre ?? Auth::user()->name }}!
                        </h3>
                        <p class="text-sm text-gray-300 mt-1">
                            Gestiona pedidos, inventario, usuarios, reportes y configuración de tu tienda PyME.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Quick Metrics Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-2xl">shopping_cart</span>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500">Pedidos Nuevos</p>
                        <p class="text-xl font-bold text-gray-900">0</p>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-2xl">payments</span>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500">Ventas del Mes</p>
                        <p class="text-xl font-bold text-gray-900">$0.00</p>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-2xl">inventory_2</span>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500">Productos Activos</p>
                        <p class="text-xl font-bold text-gray-900">0</p>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-2xl">group</span>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500">Clientes Registrados</p>
                        <p class="text-xl font-bold text-gray-900">0</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
