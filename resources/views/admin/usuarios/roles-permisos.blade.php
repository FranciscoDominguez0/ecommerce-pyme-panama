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
                <span class="material-symbols-outlined text-slate-700 text-3xl" style="font-variation-settings: 'FILL' 1;">shield</span>
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

    <!-- Grupos de Módulos (Definidos en Blade) -->
    @php
        $gruposConfig = [
            'E-Commerce & Catálogo' => [
                'icon' => 'inventory_2',
                'modules' => ['productos', 'categorias', 'marcas', 'atributos', 'devoluciones']
            ],
            'Logística & Ventas' => [
                'icon' => 'local_shipping',
                'modules' => ['pedidos', 'cupones', 'zonas', 'zonas-envio', 'envios', 'facturas']
            ],
            'Administración & Equipo' => [
                'icon' => 'group',
                'modules' => ['usuarios', 'roles']
            ],
            'Sistema & Seguridad' => [
                'icon' => 'security',
                'modules' => ['auditoria', 'configuracion']
            ],
            'Otros Módulos' => [
                'icon' => 'extension',
                'modules' => []
            ]
        ];

        $modulosPorGrupo = [];
        foreach($gruposConfig as $nombre => $config) {
            $modulosPorGrupo[$nombre] = collect();
        }

        foreach($modulos as $moduloNombre => $permisos) {
            if ($moduloNombre === 'cliente') continue;
            
            $asignado = false;
            foreach($gruposConfig as $nombre => $config) {
                if (in_array($moduloNombre, $config['modules'])) {
                    $modulosPorGrupo[$nombre]->put($moduloNombre, $permisos);
                    $asignado = true;
                    break;
                }
            }
            if (!$asignado) {
                $modulosPorGrupo['Otros Módulos']->put($moduloNombre, $permisos);
            }
        }
    @endphp

    <!-- Permissions Content Area (Unified Table with Accordions) -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden ml-[4.5rem] md:ml-0 mb-12">
        <div class="overflow-x-auto overflow-y-auto max-h-[65vh] xl:max-h-[75vh]">
            <table class="w-full text-left border-collapse">
                <thead class="sticky top-0 z-30 shadow-sm">
                    <tr class="bg-slate-100 border-b border-slate-300">
                        <th class="py-3 px-6 text-[10px] font-bold text-slate-500 uppercase tracking-wider w-1/4">Módulo</th>
                        <th class="py-3 px-6 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-center w-24">Todos</th>
                        <th class="py-3 px-6 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-center">Ver</th>
                        <th class="py-3 px-6 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-center">Crear</th>
                        <th class="py-3 px-6 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-center">Editar</th>
                        <th class="py-3 px-6 text-[10px] font-bold text-slate-500 uppercase tracking-wider text-center">Eliminar</th>
                    </tr>
                </thead>
                
                @foreach($modulosPorGrupo as $nombreGrupo => $modulosDelGrupo)
                    @if($modulosDelGrupo->isEmpty()) @continue @endif
                    
                    <tbody x-data="{ open: true }" class="border-b border-slate-200">
                        <!-- Group Header Row -->
                        <tr @click="open = !open" class="bg-slate-50 hover:bg-slate-100 cursor-pointer transition-colors group">
                            <td colspan="6" class="py-3 px-6">
                                <div class="flex items-center gap-2">
                                    <span class="material-symbols-outlined text-slate-400 group-hover:text-indigo-600 transition-all duration-300 text-[20px]" :class="open ? 'rotate-180' : ''">expand_more</span>
                                    <h3 class="text-sm font-bold text-slate-900">{{ $nombreGrupo }}</h3>
                                    <span class="ml-auto text-[10px] font-bold text-slate-500 uppercase tracking-wider bg-slate-200 px-2.5 py-0.5 rounded-full">{{ $modulosDelGrupo->count() }}</span>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Module Rows -->
                        @foreach($modulosDelGrupo as $moduloNombre => $permisos)
                            @php
                                $slug = Str::slug($moduloNombre ?: 'General');
                                $pVer = $permisos->first(fn($p) => str_ends_with($p->name, '.ver'));
                                $pCrear = $permisos->first(fn($p) => str_ends_with($p->name, '.crear'));
                                $pEditar = $permisos->first(fn($p) => str_ends_with($p->name, '.editar'));
                                $pEliminar = $permisos->first(fn($p) => str_ends_with($p->name, '.eliminar'));
                            @endphp
                            @if($pVer || $pCrear || $pEditar || $pEliminar)
                            <tr x-show="open" class="hover:bg-slate-50/50 transition-colors border-t border-slate-100" id="row-{{ $slug }}">
                                <td class="py-4 px-6 pl-10">
                                    <span class="text-sm font-bold text-slate-900 capitalize block">{{ $moduloNombre ?: 'General' }}</span>
                                </td>
                                <td class="py-4 px-6 text-center bg-slate-50/30">
                                    <input type="checkbox" class="form-checkbox h-5 w-5 text-indigo-600 rounded border-slate-300 focus:ring-indigo-600 cursor-pointer select-all-row" onchange="toggleRowCheckboxes(this, 'row-{{ $slug }}')">
                                </td>
                                <td class="py-4 px-6 text-center">
                                    @if($pVer)
                                        <input type="checkbox" name="permisos[]" value="{{ $pVer->name }}" class="form-checkbox h-5 w-5 text-slate-900 rounded border-slate-300 focus:ring-slate-900 cursor-pointer perm-checkbox ver-checkbox" onchange="togglePermissionsState(this, 'row-{{ $slug }}')" {{ in_array($pVer->name, $permisosRol) ? 'checked' : '' }} title="{{ $pVer->nombre ?? $pVer->name }}">
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center">
                                    @if($pCrear)
                                        <input type="checkbox" name="permisos[]" value="{{ $pCrear->name }}" class="form-checkbox h-5 w-5 text-slate-900 rounded border-slate-300 focus:ring-slate-900 cursor-pointer perm-checkbox" {{ in_array($pCrear->name, $permisosRol) ? 'checked' : '' }} title="{{ $pCrear->nombre ?? $pCrear->name }}">
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center">
                                    @if($pEditar)
                                        <input type="checkbox" name="permisos[]" value="{{ $pEditar->name }}" class="form-checkbox h-5 w-5 text-slate-900 rounded border-slate-300 focus:ring-slate-900 cursor-pointer perm-checkbox" {{ in_array($pEditar->name, $permisosRol) ? 'checked' : '' }} title="{{ $pEditar->nombre ?? $pEditar->name }}">
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-center">
                                    @if($pEliminar)
                                        <input type="checkbox" name="permisos[]" value="{{ $pEliminar->name }}" class="form-checkbox h-5 w-5 text-red-600 rounded border-slate-300 focus:ring-red-600 cursor-pointer perm-checkbox" {{ in_array($pEliminar->name, $permisosRol) ? 'checked' : '' }} title="{{ $pEliminar->nombre ?? $pEliminar->name }}">
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                @endforeach
            </table>
        </div>
    </div>

    <!-- Otros Permisos Section -->
    <div class="ml-[4.5rem] md:ml-0 mb-24 grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <!-- Especiales de Admin -->
        <div>
            <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-600">admin_panel_settings</span>
                Permisos Especiales de Panel (Admin)
            </h3>
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($modulos as $moduloNombre => $permisos)
                    @php
                        $pOtras = $permisos->filter(fn($p) => !preg_match('/\.(ver|crear|editar|eliminar)$/', $p->name) && str_starts_with($p->name, 'admin.'));
                    @endphp
                    @foreach($pOtras as $pOtra)
                        <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-50 border border-transparent hover:border-slate-200 cursor-pointer transition-all" title="{{ $pOtra->nombre ?? $pOtra->name }}">
                            <input type="checkbox" name="permisos[]" value="{{ $pOtra->name }}" class="form-checkbox h-5 w-5 text-emerald-600 rounded border-slate-300 focus:ring-emerald-600 cursor-pointer" {{ in_array($pOtra->name, $permisosRol) ? 'checked' : '' }}>
                            <span class="text-sm font-medium text-slate-700 capitalize">{{ str_replace(['admin.', '-', '_'], ['',' ', ' '], $pOtra->name) }}</span>
                        </label>
                    @endforeach
                @endforeach
            </div>
        </div>

        <!-- Storefront Cliente -->
        <div>
            <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-indigo-600">storefront</span>
                Permisos de Tienda (Cliente)
            </h3>
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($modulos as $moduloNombre => $permisos)
                    @php
                        $pOtras = $permisos->filter(fn($p) => str_starts_with($p->name, 'cliente.'));
                    @endphp
                    @foreach($pOtras as $pOtra)
                        <label class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-50 border border-transparent hover:border-slate-200 cursor-pointer transition-all" title="{{ $pOtra->nombre ?? $pOtra->name }}">
                            <input type="checkbox" name="permisos[]" value="{{ $pOtra->name }}" class="form-checkbox h-5 w-5 text-indigo-600 rounded border-slate-300 focus:ring-indigo-600 cursor-pointer" {{ in_array($pOtra->name, $permisosRol) ? 'checked' : '' }}>
                            <span class="text-sm font-medium text-slate-700 capitalize">{{ str_replace(['cliente.', '-', '_'], ['',' ', ' '], $pOtra->name) }}</span>
                        </label>
                    @endforeach
                @endforeach
            </div>
        </div>

    </div>
</form>

<script>
    function toggleRowCheckboxes(selectAllCheckbox, rowId) {
        const row = document.getElementById(rowId);
        if (row) {
            const verCb = row.querySelector('.ver-checkbox');
            // Si el "ver" no está chequeado, no se puede chequear "todos"
            if (verCb && !verCb.checked && selectAllCheckbox.checked) {
                selectAllCheckbox.checked = false;
                if (typeof window.mostrarToast === 'function') {
                    window.mostrarToast('Debe otorgar el permiso de Ver primero.', 'warning');
                } else {
                    alert('Debe otorgar el permiso de Ver primero.');
                }
                return;
            }

            const checkboxes = row.querySelectorAll('.perm-checkbox');
            checkboxes.forEach(cb => {
                if (!cb.disabled) {
                    cb.checked = selectAllCheckbox.checked;
                }
            });
            
            // Re-evaluar estado si desmarcamos todo
            if (!selectAllCheckbox.checked && verCb) {
                togglePermissionsState(verCb, rowId);
            }
        }
    }

    function togglePermissionsState(verCheckbox, rowId) {
        const row = document.getElementById(rowId);
        if (!row) return;

        const otherCheckboxes = row.querySelectorAll('.perm-checkbox:not(.ver-checkbox)');
        const selectAllCheckbox = row.querySelector('.select-all-row');
        
        if (!verCheckbox.checked) {
            // Si desactiva ver, desactiva y deshabilita los demás
            otherCheckboxes.forEach(cb => {
                cb.checked = false;
                cb.disabled = true;
                cb.classList.add('opacity-50');
            });
            if(selectAllCheckbox) selectAllCheckbox.checked = false;
        } else {
            // Habilita los demás
            otherCheckboxes.forEach(cb => {
                cb.disabled = false;
                cb.classList.remove('opacity-50');
            });
        }
    }

    // Inicializar estado de las filas según si "Ver" está activo
    document.addEventListener('DOMContentLoaded', () => {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const selectAll = row.querySelector('.select-all-row');
            const checkboxes = row.querySelectorAll('.perm-checkbox');
            const verCb = row.querySelector('.ver-checkbox');
            
            if (verCb) {
                togglePermissionsState(verCb, row.id);
            }

            if(selectAll && checkboxes.length > 0) {
                const updateSelectAll = () => {
                    const allChecked = Array.from(checkboxes).every(cb => cb.checked || cb.disabled);
                    const someChecked = Array.from(checkboxes).some(cb => cb.checked);
                    selectAll.checked = allChecked && someChecked;
                    selectAll.indeterminate = someChecked && !allChecked;
                };

                checkboxes.forEach(cb => cb.addEventListener('change', updateSelectAll));
                updateSelectAll(); // initial state
            }
        });
    });
</script>
@endsection
