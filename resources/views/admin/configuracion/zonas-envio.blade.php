@extends('layouts.admin')

@section('title', 'Sucursales de Courier — Panel de Administración')

@section('breadcrumbs')
    <span class="hidden sm:inline-flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
        <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
        <span>Logística</span>
    </span>
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <span class="font-bold text-slate-900 dark:text-white truncate">Sucursales Courier</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- ── ENCABEZADO DE SECCIÓN ── -->
    <div class="card-elevated p-5 sm:p-6 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500/20 to-teal-500/10 border border-emerald-500/20 text-emerald-600 flex items-center justify-center shrink-0 shadow-2xs">
                <span class="material-symbols-outlined text-[26px]">storefront</span>
            </div>
            <div>
                <h1 class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">Sucursales Courier</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Administra las agencias de Fletes Chavale y otros couriers disponibles para retiro.
                </p>
            </div>
        </div>

        <button type="button" 
                onclick="abrirModalCrearZona()" 
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold rounded-xl transition-all shadow-sm hover:shadow cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">add_circle</span>
            <span>Nueva Sucursal</span>
        </button>
    </div>

    <!-- ── ESTADO VACÍO ── -->
    @if($zonas->isEmpty())
        <div class="card-elevated p-8 sm:p-12 rounded-2xl text-center flex flex-col items-center justify-center space-y-4">
            <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-transparent border border-slate-200 dark:border-gray-700 text-slate-400 flex items-center justify-center shadow-inner">
                <span class="material-symbols-outlined text-[36px]">store</span>
            </div>
            
            <div class="max-w-md space-y-1">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">No hay sucursales registradas</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Añade sucursales para que tus clientes puedan elegir dónde retirar su pedido.
                </p>
            </div>

            <button type="button" 
                    onclick="abrirModalCrearZona()" 
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm hover:shadow cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">add_circle</span>
                <span>Nueva Sucursal</span>
            </button>
        </div>
    @else
        <!-- ── TABLA PRINCIPAL ── -->
        <div class="card-elevated rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-transparent/80 border-b border-slate-200 dark:border-gray-700/80 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            <th class="py-3.5 px-5">Courier & Sucursal</th>
                            <th class="py-3.5 px-5">Zona</th>
                            <th class="py-3.5 px-5">Tarifa</th>
                            <th class="py-3.5 px-5">Estado</th>
                            <th class="py-3.5 px-5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-gray-700/50 text-xs text-slate-700 dark:text-slate-300 dark:text-slate-300 font-medium">
                        @foreach($zonas as $zona)
                            <tr class="hover:bg-slate-50 dark:bg-transparent dark:hover:bg-gray-700/30 transition-colors">
                                <td class="py-4 px-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-transparent border border-emerald-200 dark:border-gray-700 text-emerald-600 flex items-center justify-center shrink-0">
                                            <span class="material-symbols-outlined text-[18px]">store</span>
                                        </div>
                                        <div>
                                            <span class="font-bold text-slate-900 dark:text-white text-sm block">{{ $zona->courier }} - {{ $zona->sucursal }}</span>
                                            <span class="text-[10px] text-slate-500 truncate max-w-[200px] block" title="{{ $zona->direccion }}">{{ $zona->direccion ?? 'Sin dirección' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-4 px-5">
                                    <span class="inline-flex px-2 py-1 rounded bg-slate-100 text-slate-700 text-[11px]">{{ $zona->zona }}</span>
                                </td>

                                <td class="py-4 px-5">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 font-bold text-xs font-mono">
                                        ${{ number_format((float) ($zona->tarifa_uno_hasta_7lb ?? 0), 2) }} <span class="text-[10px] text-emerald-600 font-normal">USD</span>
                                    </span>
                                </td>

                                <td class="py-4 px-5">
                                    @if($zona->activo)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100/80 border border-emerald-200 text-emerald-800 text-[11px] font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>Activa</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 dark:bg-transparent border border-slate-200 dark:border-gray-700 text-slate-600 dark:text-slate-400 text-[11px] font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            <span>Inactiva</span>
                                        </span>
                                    @endif
                                </td>

                                <td class="py-4 px-5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" 
                                                onclick="abrirModalEditarZona({{ $zona->id }}, '{{ addslashes($zona->zona) }}', '{{ addslashes($zona->courier) }}', '{{ addslashes($zona->sucursal) }}', '{{ addslashes($zona->direccion) }}', '{{ addslashes($zona->telefono) }}', {{ (float) ($zona->tarifa_uno_hasta_7lb ?? 0) }}, {{ $zona->activo ? 'true' : 'false' }})" 
                                                class="px-2.5 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 dark:text-slate-300 bg-white dark:bg-[#181a1b] border border-slate-200 dark:border-gray-700 rounded-lg hover:bg-slate-50 dark:bg-transparent dark:hover:bg-gray-700/30 transition-all shadow-2xs flex items-center gap-1 cursor-pointer">
                                            <span class="material-symbols-outlined text-[15px] text-slate-500">edit</span>
                                            <span class="hidden sm:inline">Editar</span>
                                        </button>

                                        <form action="{{ route('admin.zonas-envio.toggle', $zona->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="px-2.5 py-1.5 text-xs font-semibold {{ $zona->activo ? 'text-amber-700 bg-amber-50 border-amber-200 hover:bg-amber-100' : 'text-emerald-700 bg-emerald-50 border-emerald-200 hover:bg-emerald-100' }} border rounded-lg transition-all shadow-2xs flex items-center gap-1 cursor-pointer">
                                                <span class="material-symbols-outlined text-[15px]">{{ $zona->activo ? 'pause_circle' : 'play_circle' }}</span>
                                                <span class="hidden sm:inline">{{ $zona->activo ? 'Desactivar' : 'Activar' }}</span>
                                            </button>
                                        </form>

                                        <button type="button" 
                                                onclick="window.ModalEliminar.abrir('/admin/zonas-envio/{{ $zona->id }}', '{{ addslashes($zona->sucursal) }}')" 
                                                class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-200 rounded-lg transition-colors cursor-pointer">
                                            <span class="material-symbols-outlined text-[17px]">delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- Paginación -->
            @if($zonas->hasPages())
                <div class="px-5 py-4 border-t border-slate-100 dark:border-gray-700/80 bg-white dark:bg-transparent">
                    {{ $zonas->links() }}
                </div>
            @endif
        </div>
    @endif
</div>

<!-- ── MODAL: CREAR / EDITAR SUCURSAL ── -->
<div id="modal-zona-envio" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity">
    <div class="bg-white dark:bg-[#181a1b] rounded-2xl border border-slate-200 dark:border-gray-700 shadow-2xl max-w-lg w-full p-6 space-y-5 transform transition-all">
        
        <div class="flex items-center justify-between border-b border-slate-100 dark:border-gray-700 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 border border-emerald-200 text-emerald-700 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">store</span>
                </div>
                <div>
                    <h3 id="modal-zona-titulo" class="text-base font-bold text-slate-900 dark:text-white">Nueva Sucursal Courier</h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Ingresa los detalles y tarifa para esta agencia.</p>
                </div>
            </div>
            <button type="button" onclick="cerrarModalZona()" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <form id="form-zona-envio" method="POST" action="{{ route('admin.zonas-envio.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="_method" id="form-zona-method" value="POST">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Zona <span class="text-rose-500">*</span></label>
                    <input type="text" id="input-zona" name="zona" required class="input-panama w-full text-xs rounded-xl border-slate-200 dark:border-gray-700 dark:bg-[#121415] dark:text-white focus:border-emerald-500 focus:ring-emerald-500/20 py-2.5 px-3">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Courier <span class="text-rose-500">*</span></label>
                    <input type="text" id="input-courier" name="courier" required placeholder="Ej: Fletes Chavale" class="input-panama w-full text-xs rounded-xl border-slate-200 dark:border-gray-700 dark:bg-[#121415] dark:text-white focus:border-emerald-500 focus:ring-emerald-500/20 py-2.5 px-3">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Sucursal <span class="text-rose-500">*</span></label>
                    <input type="text" id="input-sucursal" name="sucursal" required class="input-panama w-full text-xs rounded-xl border-slate-200 dark:border-gray-700 dark:bg-[#121415] dark:text-white focus:border-emerald-500 focus:ring-emerald-500/20 py-2.5 px-3">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Tarifa (hasta 7lb) <span class="text-rose-500">*</span></label>
                    <div class="relative flex items-center">
                        <span class="absolute left-3 text-xs text-slate-400 font-bold">$</span>
                        <input type="number" id="input-costo" name="tarifa_uno_hasta_7lb" step="0.01" min="0" required class="input-panama w-full pl-7 py-2.5 text-xs font-mono rounded-xl border-slate-200 dark:border-gray-700 dark:bg-[#121415] dark:text-white focus:border-emerald-500 focus:ring-emerald-500/20">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Dirección Exacta</label>
                <input type="text" id="input-direccion" name="direccion" class="input-panama w-full text-xs rounded-xl border-slate-200 dark:border-gray-700 dark:bg-[#121415] dark:text-white focus:border-emerald-500 focus:ring-emerald-500/20 py-2.5 px-3">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Teléfono</label>
                <input type="text" id="input-telefono" name="telefono" class="input-panama w-full text-xs rounded-xl border-slate-200 dark:border-gray-700 dark:bg-[#121415] dark:text-white focus:border-emerald-500 focus:ring-emerald-500/20 py-2.5 px-3">
            </div>

            <div class="pt-1">
                <label class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-[#181a1b] border border-slate-200 dark:border-gray-700 rounded-xl cursor-pointer hover:bg-slate-100 dark:hover:bg-gray-800 transition-colors">
                    <input type="checkbox" id="input-zona-activo" name="activo" value="1" checked class="rounded text-emerald-600 focus:ring-emerald-500 h-4 w-4 bg-white dark:bg-[#121415] border-gray-300 dark:border-gray-600">
                    <div>
                        <span class="text-xs font-bold text-slate-900 dark:text-white block">Sucursal Activa</span>
                        <span class="text-[11px] text-slate-500 dark:text-slate-400 block">Estará visible en el selector de sucursales durante el checkout.</span>
                    </div>
                </label>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100 dark:border-gray-700">
                <button type="button" onclick="cerrarModalZona()" class="px-4 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-300 bg-white dark:bg-[#121415] border border-slate-200 dark:border-gray-700 rounded-xl hover:bg-slate-50 dark:hover:bg-gray-800 transition-colors">Cancelar</button>
                <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-all">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
    function abrirModalCrearZona() {
        const modal = document.getElementById('modal-zona-envio');
        const form = document.getElementById('form-zona-envio');
        document.getElementById('modal-zona-titulo').textContent = 'Nueva Sucursal';
        form.action = "{{ route('admin.zonas-envio.store') }}";
        document.getElementById('form-zona-method').value = 'POST';

        document.getElementById('input-zona').value = '';
        document.getElementById('input-courier').value = '';
        document.getElementById('input-sucursal').value = '';
        document.getElementById('input-costo').value = '';
        document.getElementById('input-direccion').value = '';
        document.getElementById('input-telefono').value = '';
        document.getElementById('input-zona-activo').checked = true;

        modal.classList.remove('hidden');
    }

    function abrirModalEditarZona(id, zona, courier, sucursal, direccion, telefono, costo, activo) {
        const modal = document.getElementById('modal-zona-envio');
        const form = document.getElementById('form-zona-envio');
        document.getElementById('modal-zona-titulo').textContent = 'Editar Sucursal';
        form.action = `/admin/zonas-envio/${id}`;
        document.getElementById('form-zona-method').value = 'PUT';

        document.getElementById('input-zona').value = zona;
        document.getElementById('input-courier').value = courier;
        document.getElementById('input-sucursal').value = sucursal;
        document.getElementById('input-costo').value = costo;
        document.getElementById('input-direccion').value = direccion !== 'null' ? direccion : '';
        document.getElementById('input-telefono').value = telefono !== 'null' ? telefono : '';
        document.getElementById('input-zona-activo').checked = activo;

        modal.classList.remove('hidden');
    }

    function cerrarModalZona() {
        document.getElementById('modal-zona-envio').classList.add('hidden');
    }
</script>
@endsection



