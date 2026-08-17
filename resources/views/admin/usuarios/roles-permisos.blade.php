@extends('layouts.admin')

@section('title', 'Configuración de Permisos: ' . ($rol->nombre ?: $rol->name))

@section('content')
<form action="{{ route('admin.usuarios.update-permisos', $rol->id) }}" method="POST">
    @csrf
    @method('PUT')

    <!-- Header / Summary Section -->
    <div class="mb-8 flex flex-col md:flex-row md:items-start justify-between gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('admin.usuarios.index') }}" class="text-slate-500 hover:text-slate-900 transition-colors">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                @php
                    $icon = match($rol->name) {
                        'super_admin' => 'admin_panel_settings',
                        'Admin' => 'manage_accounts',
                        default => 'person'
                    };
                    $iconColor = match($rol->name) {
                        'super_admin' => 'text-red-600',
                        'Admin' => 'text-slate-900',
                        default => 'text-slate-500'
                    };
                @endphp
                <span class="material-symbols-outlined {{ $iconColor }} text-3xl" style="font-variation-settings: 'FILL' 1;">{{ $icon }}</span>
                <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">{{ $rol->nombre ?: $rol->name }}</h2>
            </div>
            <p class="text-sm text-slate-500 max-w-2xl ml-[4.5rem]">
                {{ $rol->descripcion ?? 'Acceso al sistema.' }}
            </p>
        </div>
        <div class="flex gap-4">
            <div class="bg-white px-4 py-3 rounded-xl border border-slate-200 flex flex-col items-center min-w-[120px] shadow-sm">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Usuarios</span>
                <span class="font-bold text-2xl text-slate-900">{{ $rol->users()->count() }}</span>
            </div>
            <div class="bg-white px-4 py-3 rounded-xl border border-slate-200 flex flex-col items-center min-w-[120px] shadow-sm">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Nivel</span>
                @if($rol->name === 'super_admin')
                    <span class="font-bold text-xl text-red-600">Crítico</span>
                @elseif($rol->name === 'Admin')
                    <span class="font-bold text-xl text-orange-500">Elevado</span>
                @else
                    <span class="font-bold text-xl text-slate-900">Estándar</span>
                @endif
            </div>
            <div class="flex items-center ml-2">
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-slate-900 text-white font-semibold text-xs uppercase tracking-wide hover:bg-slate-800 shadow-sm transition-all flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    Guardar
                </button>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-slate-200 mb-8 flex gap-8 ml-[4.5rem] md:ml-0">
        <a href="{{ route('admin.usuarios.por-rol', $rol->id) }}" class="pb-3 border-b-2 border-transparent text-slate-500 hover:text-slate-900 hover:border-slate-300 font-medium transition-colors flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">group</span>
            Usuarios
        </a>
        <button type="button" class="pb-3 border-b-2 border-slate-900 text-slate-900 font-medium flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px]">key</span>
            Permisos
        </button>
    </div>

    <!-- super_admin warning removed as per user request -->

    <!-- Permissions Content Area (Bento-ish Grid) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pb-24 ml-[4.5rem] md:ml-0">
        @foreach($modulos as $moduloNombre => $permisos)
            @php
                $moduloIcon = match(strtolower($moduloNombre)) {
                    'productos', 'producto' => 'inventory_2',
                    'inventario' => 'warehouse',
                    'pedidos', 'pedido' => 'receipt_long',
                    'usuarios', 'usuario' => 'group',
                    'configuracion' => 'settings',
                    'promociones', 'cupones' => 'local_offer',
                    'devoluciones' => 'assignment_return',
                    'facturacion', 'facturas' => 'request_quote',
                    default => 'extension'
                };
            @endphp
            <!-- Module Card -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50 rounded-t-xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center border border-slate-200 shadow-xs">
                            <span class="material-symbols-outlined">{{ $moduloIcon }}</span>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 capitalize">{{ $moduloNombre ?: 'General' }}</h3>
                    </div>
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-wider cursor-pointer hover:text-slate-900 transition-colors">
                        <input type="checkbox" class="form-checkbox text-slate-900 rounded border-slate-300 focus:ring-slate-900" onchange="toggleModuleCheckboxes(this, '{{ Str::slug($moduloNombre) }}')" >
                        Todos
                    </label>
                </div>
                <div class="p-6">
                    <ul class="space-y-4" id="module-{{ Str::slug($moduloNombre) }}">
                        @foreach($permisos as $permiso)
                            <li>
                                <label class="flex justify-between items-center cursor-pointer group">
                                    <div>
                                        <span class="text-sm font-semibold text-slate-900 block group-hover:text-blue-600 transition-colors">{{ $permiso->nombre ?? $permiso->name }}</span>
                                        <span class="text-xs text-slate-500">{{ $permiso->descripcion ?? 'Acceso a ' . $permiso->name }}</span>
                                    </div>
                                    <input type="checkbox" name="permisos[]" value="{{ $permiso->name }}" class="form-checkbox h-5 w-5 text-slate-900 rounded border-slate-300 focus:ring-slate-900 cursor-pointer" {{ in_array($permiso->name, $permisosRol) ? 'checked' : '' }} >
                                </label>
                            </li>
                            @if(!$loop->last)
                                <li class="h-px w-full bg-slate-100"></li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
        @endforeach
    </div>
</form>

<script>
    function toggleModuleCheckboxes(selectAllCheckbox, moduleSlug) {
        const container = document.getElementById('module-' + moduleSlug);
        if (container) {
            const checkboxes = container.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(cb => {
                if (!cb.disabled) {
                    cb.checked = selectAllCheckbox.checked;
                }
            });
        }
    }
</script>
@endsection
