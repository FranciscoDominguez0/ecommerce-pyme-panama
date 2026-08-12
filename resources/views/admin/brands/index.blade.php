@extends('layouts.admin')

@section('title', 'Gestión de Marcas')

@section('breadcrumbs')
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <span class="font-bold text-slate-900 truncate">Marcas</span>
@endsection

@section('content')
<div class="space-y-6 w-full min-w-0 max-w-full">

    <!-- Page Header & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-200/80">
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">Gestión de Marcas</h2>
            <p class="text-xs sm:text-sm text-slate-500 font-medium mt-0.5">Administra los fabricantes oficiales y logotipos vectoriales/imágenes del catálogo.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.brands.create') }}" 
               class="flex items-center gap-1.5 px-4 py-2 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-slate-800 transition-colors shadow-xs">
                <span class="material-symbols-outlined text-[16px]">add</span>
                <span>Nueva Marca</span>
            </a>
        </div>
    </div>



    <!-- Filtros y Búsqueda -->
    <div class="card-elevated rounded-xl p-4 sm:p-5">
        <form method="GET" action="{{ route('admin.brands.index') }}" class="flex flex-col sm:flex-row gap-3 items-center justify-between">
            
            <!-- Barra de búsqueda -->
            <div class="flex-1 w-full sm:w-auto relative">
                <div class="flex items-center bg-slate-50 border border-slate-200 rounded-lg px-3.5 py-2 w-full focus-within:border-slate-400 focus-within:bg-white focus-within:ring-2 focus-within:ring-slate-900/5 transition-all">
                    <span class="material-symbols-outlined text-slate-400 text-[18px]">search</span>
                    <input type="text" 
                           name="buscar" 
                           value="{{ $busqueda }}" 
                           placeholder="Buscar marca por nombre o slug..." 
                           class="bg-transparent border-none focus:ring-0 w-full text-xs text-slate-800 placeholder:text-slate-400 p-0 ml-2 outline-none"/>
                    @if(!empty($busqueda))
                        <a href="{{ route('admin.brands.index', ['verificada' => $filtroVerificada]) }}" class="text-slate-400 hover:text-slate-600">
                            <span class="material-symbols-outlined text-[16px]">close</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Filtros de Verificación -->
            <div class="w-full sm:w-auto flex items-center gap-2.5 flex-wrap">
                <div class="relative w-full sm:w-44">
                    <select name="verificada" 
                            onchange="this.form.submit()" 
                            class="w-full bg-slate-50 border border-slate-200 rounded-lg pl-3 pr-8 py-2 text-xs text-slate-800 font-medium focus:bg-white focus:border-slate-400 focus:ring-2 focus:ring-slate-900/5 transition-all outline-none appearance-none cursor-pointer">
                        <option value="all" {{ $filtroVerificada === 'all' ? 'selected' : '' }}>Todas (Verificación)</option>
                        <option value="yes" {{ $filtroVerificada === 'yes' ? 'selected' : '' }}>Verificadas (Oficial)</option>
                        <option value="no" {{ $filtroVerificada === 'no' ? 'selected' : '' }}>Sin verificar (Estándar)</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-[16px]">
                        expand_more
                    </span>
                </div>

                <button type="submit" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition-colors flex items-center gap-1 shrink-0 cursor-pointer">
                    <span class="material-symbols-outlined text-[15px]">filter_list</span>
                    <span>Filtrar</span>
                </button>

                @if(!empty($busqueda) || $filtroVerificada !== 'all')
                    <a href="{{ route('admin.brands.index') }}" class="px-3 py-2 text-xs text-slate-500 hover:text-slate-800 font-semibold transition-colors flex items-center gap-1 shrink-0">
                        <span class="material-symbols-outlined text-[15px]">restart_alt</span>
                        <span>Limpiar</span>
                    </a>
                @endif
            </div>

        </form>
    </div>

    <!-- Tabla Principal de Marcas -->
    <div class="card-elevated rounded-xl overflow-hidden flex flex-col w-full min-w-0 max-w-full">
        
        <div class="overflow-x-auto w-full max-w-full touch-pan-x overscroll-x-contain">
            <table class="w-full text-left border-collapse min-w-[700px]">
                <thead>
                    <tr class="border-b border-slate-100 text-[11px] font-semibold text-slate-400 uppercase tracking-wider bg-slate-50/50">
                        <th class="py-3.5 px-4 sm:px-6 max-w-[280px]">Marca & Fabricante</th>
                        <th class="py-3.5 px-3 text-center w-32">Logotipo</th>
                        <th class="py-3.5 px-4 text-center w-40">Slug</th>
                        <th class="py-3.5 px-4 text-center w-32">Productos</th>
                        <th class="py-3.5 px-4 text-center w-32">Verificada</th>
                        <th class="py-3.5 px-4 sm:px-6 text-right w-28">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-slate-100">
                    @forelse($marcas as $brand)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            
                            <!-- Nombre & Detalle -->
                            <td class="py-3.5 px-4 sm:px-6 font-semibold text-slate-900 max-w-[280px]">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="font-bold text-slate-900 text-sm truncate min-w-0" title="{{ $brand->name }}">{{ $brand->name }}</span>
                                </div>
                            </td>

                            <!-- Logotipo Preview -->
                            <td class="py-3.5 px-3 text-center">
                                <div class="w-20 h-9 mx-auto rounded-lg bg-white border border-slate-200/80 shadow-2xs flex items-center justify-center p-1.5 overflow-hidden">
                                    {!! $brand->logo_html !!}
                                </div>
                            </td>

                            <!-- Slug -->
                            <td class="py-3.5 px-4 text-center">
                                <code class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 font-mono text-[11px]">
                                    {{ $brand->slug }}
                                </code>
                            </td>

                            <!-- Productos vinculados -->
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $brand->productos_count > 0 ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-slate-50 text-slate-400' }}">
                                    {{ $brand->productos_count }}
                                </span>
                            </td>

                            <!-- Estado Verificada -->
                            <td class="py-3.5 px-4 text-center">
                                @if($brand->verified)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-semibold">
                                        <span class="material-symbols-outlined text-[12px]">verified</span>
                                        <span>Oficial</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 text-[11px] font-medium">
                                        Estándar
                                    </span>
                                @endif
                            </td>

                            <!-- Acciones -->
                            <td class="py-3.5 px-4 sm:px-6 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.brands.edit', $brand) }}" 
                                       class="p-1.5 text-slate-400 hover:text-slate-800 hover:bg-slate-100 rounded-lg transition-colors" 
                                       title="Editar Marca">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </a>

                                    <button type="button" 
                                            onclick="window.ModalEliminar.abrir({
                                                url: '{{ route('admin.brands.destroy', $brand) }}',
                                                nombre: '{{ addslashes($brand->name) }}',
                                                titulo: '¿Eliminar Marca?',
                                                mensaje: 'Esta acción eliminará la marca del catálogo. Los productos asociados no se eliminarán, pero quedarán sin marca asignada.',
                                                extra: '{{ $brand->productos_count > 0 ? '⚠️ Tiene ' . $brand->productos_count . ' producto(s) asociado(s) que quedarán sin marca asignada.' : '' }}'
                                            })" 
                                            class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer" 
                                            title="Eliminar Marca">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                    </button>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 px-6 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                    <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mb-3 text-slate-400">
                                        <span class="material-symbols-outlined text-[24px]">search_off</span>
                                    </div>
                                    <span class="text-sm font-bold text-slate-700 mb-1">No se encontraron marcas</span>
                                    <p class="text-xs text-slate-400 mb-4">No hay marcas que coincidan con los criterios de búsqueda o filtros seleccionados.</p>
                                    <a href="{{ route('admin.brands.create') }}" class="px-4 py-2 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-slate-800 transition-colors">
                                        Registrar primera marca
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        @if($marcas->hasPages())
            <div class="px-4 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $marcas->links('vendor.pagination.admin-tailwind') }}
            </div>
        @endif

    </div>

</div>

@endsection
