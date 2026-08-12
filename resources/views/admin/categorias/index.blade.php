@extends('layouts.admin')

@section('title', 'Gestión de Categorías')

@section('breadcrumbs')
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <span class="font-bold text-slate-900 truncate">Categorías</span>
@endsection

@section('content')
<div class="space-y-6 w-full min-w-0 max-w-full">

    <!-- Page Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200/80">
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Gestión de Categorías</h2>
            <p class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">Organiza tu catálogo de productos y servicios con estructura jerárquica.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.categorias.create') }}" 
               class="flex items-center gap-1.5 px-4 py-2 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-slate-800 transition-colors shadow-xs">
                <span class="material-symbols-outlined text-[16px]">add</span>
                <span>Nueva Categoría</span>
            </a>
        </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="card-elevated rounded-xl p-4 sm:p-5">
        <form method="GET" action="{{ route('admin.categorias.index') }}" class="flex flex-col sm:flex-row gap-3 items-center justify-between">
            
            <!-- Barra de búsqueda -->
            <div class="flex-1 w-full sm:w-auto relative">
                <div class="flex items-center bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 w-full focus-within:border-slate-400 focus-within:bg-white focus-within:ring-2 focus-within:ring-slate-900/5 transition-all">
                    <span class="material-symbols-outlined text-slate-400 text-[18px]">search</span>
                    <input type="text" 
                           name="buscar" 
                           value="{{ $busqueda }}" 
                           placeholder="Buscar por nombre o slug..." 
                           class="bg-transparent border-none focus:ring-0 w-full text-xs text-slate-800 placeholder:text-slate-400 p-0 ml-2 outline-none"/>
                    @if(!empty($busqueda))
                        <a href="{{ route('admin.categorias.index', ['estado' => $filtroEstado]) }}" class="text-slate-400 hover:text-slate-600">
                            <span class="material-symbols-outlined text-[16px]">close</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Filtros de Estado y Acciones -->
            <div class="w-full sm:w-auto flex items-center gap-2.5">
                <div class="relative w-full sm:w-44">
                    <select name="estado" 
                            onchange="this.form.submit()" 
                            class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-3 pr-8 py-2 text-xs text-slate-800 font-medium focus:bg-white focus:border-slate-400 focus:ring-2 focus:ring-slate-900/5 transition-all outline-none appearance-none cursor-pointer">
                        <option value="all" {{ $filtroEstado === 'all' ? 'selected' : '' }}>Todos los estados</option>
                        <option value="active" {{ $filtroEstado === 'active' ? 'selected' : '' }}>Solo Activas</option>
                        <option value="inactive" {{ $filtroEstado === 'inactive' ? 'selected' : '' }}>Solo Inactivas</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-[16px]">
                        expand_more
                    </span>
                </div>

                <button type="submit" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition-colors flex items-center gap-1 shrink-0">
                    <span class="material-symbols-outlined text-[15px]">filter_list</span>
                    <span>Filtrar</span>
                </button>

                @if(!empty($busqueda) || $filtroEstado !== 'all')
                    <a href="{{ route('admin.categorias.index') }}" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-800 font-semibold transition-colors flex items-center gap-1 shrink-0">
                        <span class="material-symbols-outlined text-[15px]">restart_alt</span>
                        <span>Limpiar</span>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- Tabla Principal de Categorías (Con Scroll Horizontal Autónomo) -->
    <div class="card-elevated rounded-xl overflow-hidden flex flex-col w-full min-w-0 max-w-full">
        
        <div class="overflow-x-auto w-full max-w-full touch-pan-x overscroll-x-contain">
            <table class="w-full text-left border-collapse min-w-[760px]">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-semibold text-slate-400 uppercase tracking-wider bg-slate-50/50">
                        <th class="py-3.5 px-4 sm:px-6">Nombre & Slug</th>
                        <th class="py-3.5 px-3 text-center w-20">Ícono</th>
                        <th class="py-3.5 px-4 text-center w-44">Categoría Padre</th>
                        <th class="py-3.5 px-4 text-center w-28">Productos</th>
                        <th class="py-3.5 px-4 text-center w-28">Estado</th>
                        <th class="py-3.5 px-4 text-center w-24">Orden</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right w-28">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-slate-100">
                    @forelse($categorias as $categoria)
                        @php
                            $esHija = !is_null($categoria->padre_id);
                            $nivel = $categoria->nivel;
                            $tieneProductos = $categoria->productos_count > 0;
                            $tieneHijas = $categoria->hijas->count() > 0;
                            $puedeEliminarse = !$tieneProductos && !$tieneHijas;

                            // Sangría jerárquica progresiva limpia sin modificar la paleta oficial
                            $paddingLeft = match($nivel) {
                                0 => '',
                                1 => 'pl-7',
                                2 => 'pl-14',
                                default => 'pl-20',
                            };
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            
                            <!-- Nombre & Slug con Sangría Jerárquica Progresiva -->
                            <td class="py-3.5 px-4 sm:px-6">
                                <div class="flex items-center gap-3 {{ $paddingLeft }}">
                                    @if($esHija)
                                        <span class="material-symbols-outlined text-slate-400 text-[18px] shrink-0">subdirectory_arrow_right</span>
                                    @else
                                        <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                                            <span class="material-symbols-outlined text-[18px]">folder</span>
                                        </div>
                                    @endif

                                    <div>
                                        <div class="font-bold text-slate-900 {{ $esHija ? 'text-xs' : 'text-sm' }} flex items-center gap-1.5">
                                            <span>{{ $categoria->nombre }}</span>
                                            @if($tieneHijas)
                                                <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-semibold">
                                                    {{ $categoria->hijas->count() }} sub
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-slate-400 font-mono">
                                            /{{ $categoria->slug }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Ícono SVG / Imagen Compacta -->
                            <td class="py-3 px-3 text-center">
                                @if($categoria->imagen_ruta)
                                    <div class="w-9 h-9 rounded-lg bg-white border border-slate-200 shadow-2xs overflow-hidden mx-auto group-hover:border-slate-300 transition-all flex items-center justify-center p-1.5" title="{{ $categoria->nombre }}">
                                        <img src="{{ asset($categoria->imagen_ruta) }}" 
                                             alt="{{ $categoria->nombre }}" 
                                             class="w-full h-full object-contain group-hover:scale-110 transition-transform duration-200" />
                                    </div>
                                @else
                                    <div class="w-9 h-9 rounded-lg bg-slate-50 border border-dashed border-slate-200 flex items-center justify-center mx-auto text-slate-300" title="Sin ícono">
                                        <span class="material-symbols-outlined text-[17px]">category</span>
                                    </div>
                                @endif
                            </td>

                            <!-- Categoría Padre -->
                            <td class="py-3.5 px-4 text-center">
                                @if($categoria->padre)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        <span class="material-symbols-outlined text-[13px] text-slate-400">folder</span>
                                        <span class="truncate max-w-[140px]">{{ $categoria->padre->nombre }}</span>
                                    </span>
                                @else
                                    <span class="text-slate-400 font-medium text-[11px]">— Raíz —</span>
                                @endif
                            </td>

                            <!-- Total Productos -->
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-bold {{ $tieneProductos ? 'bg-emerald-50 text-emerald-800 border border-emerald-100' : 'bg-slate-50 text-slate-400 border border-slate-200' }}">
                                    {{ $categoria->productos_count }} prod.
                                </span>
                            </td>

                            <!-- Estado (Toggle) -->
                            <td class="py-3.5 px-4 text-center">
                                <form method="POST" action="{{ route('admin.categorias.toggle-estado', $categoria->id) }}" class="inline-block">
                                    @csrf
                                    <button type="submit" 
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold transition-all {{ $categoria->activo ? 'bg-emerald-50 text-emerald-700 border border-emerald-100 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200 hover:bg-slate-200' }}"
                                            title="Click para cambiar estado">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $categoria->activo ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                        <span>{{ $categoria->activo ? 'Activa' : 'Inactiva' }}</span>
                                    </button>
                                </form>
                            </td>

                            <!-- Orden -->
                            <td class="py-3.5 px-4 text-center font-mono text-slate-600 text-[11px]">
                                {{ $categoria->orden_visualizacion }}
                            </td>

                            <!-- Acciones -->
                            <td class="py-3.5 px-4 sm:px-6 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    
                                    <!-- Botón Editar -->
                                    <a href="{{ route('admin.categorias.edit', $categoria->id) }}" 
                                       class="p-1.5 text-slate-500 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors" 
                                       title="Editar categoría">
                                        <span class="material-symbols-outlined text-[17px]">edit</span>
                                    </a>

                                    <!-- Botón Eliminar / Tooltip si bloqueado -->
                                    @if($puedeEliminarse)
                                        <button type="button" 
                                                onclick="window.ModalEliminar.abrir('{{ route('admin.categorias.destroy', $categoria->id) }}', '{{ addslashes($categoria->nombre) }}')" 
                                                class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer" 
                                                title="Eliminar categoría">
                                            <span class="material-symbols-outlined text-[17px]">delete</span>
                                        </button>
                                    @else
                                        <div class="relative group/tip inline-block">
                                            <button type="button" 
                                                    disabled 
                                                    class="p-1.5 text-slate-300 cursor-not-allowed rounded-lg" 
                                                    title="No se puede eliminar">
                                                <span class="material-symbols-outlined text-[17px]">delete</span>
                                            </button>
                                            <div class="absolute bottom-full right-0 mb-1.5 hidden group-hover/tip:block w-48 p-2 bg-slate-900 text-white text-[10px] rounded-lg shadow-lg z-20 text-center font-medium leading-tight">
                                                @if($tieneProductos)
                                                    Contiene {{ $categoria->productos_count }} producto(s) asignado(s).
                                                @elseif($tieneHijas)
                                                    Contiene {{ $categoria->hijas->count() }} subcategoría(s) hija(s).
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    <span class="material-symbols-outlined text-[24px]">category</span>
                                </div>
                                <p class="font-bold text-slate-700 text-sm">No se encontraron categorías</p>
                                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                                    @if(!empty($busqueda) || $filtroEstado !== 'all')
                                        No hay resultados para los filtros seleccionados. Intenta restablecer los términos de búsqueda.
                                    @else
                                        Aún no has creado ninguna categoría en el catálogo. Comienza creando la primera para clasificar tus productos.
                                    @endif
                                </p>
                                <div class="mt-4">
                                    @if(!empty($busqueda) || $filtroEstado !== 'all')
                                        <a href="{{ route('admin.categorias.index') }}" class="inline-flex items-center gap-1 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition-colors">
                                            <span>Limpiar Filtros</span>
                                        </a>
                                    @else
                                        <a href="{{ route('admin.categorias.create') }}" class="inline-flex items-center gap-1 px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-semibold transition-colors shadow-xs">
                                            <span class="material-symbols-outlined text-[15px]">add</span>
                                            <span>Crear Primera Categoría</span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($categorias->hasPages())
            <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $categorias->links('vendor.pagination.admin-tailwind') }}
            </div>
        @endif

    </div>

</div>
@endsection
