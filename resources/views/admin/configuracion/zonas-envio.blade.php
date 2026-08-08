@extends('layouts.admin')

@section('title', 'Zonas de Envío — Panel de Administración')

@section('breadcrumbs')
    <span class="hidden sm:inline-flex items-center gap-1.5 text-slate-500">
        <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
        <span>Configuración</span>
    </span>
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <span class="font-bold text-slate-900 truncate">Zonas de Envío</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- ── ENCABEZADO DE SECCIÓN ── -->
    <div class="card-elevated p-5 sm:p-6 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500/20 to-teal-500/10 border border-emerald-500/20 text-emerald-600 flex items-center justify-center shrink-0 shadow-2xs">
                <span class="material-symbols-outlined text-[26px]">local_shipping</span>
            </div>
            <div>
                <h1 class="text-lg sm:text-xl font-extrabold text-slate-900 tracking-tight">Zonas de Envío</h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Administra las provincias o zonas de cobertura y establece el costo correspondiente para cada una.
                </p>
            </div>
        </div>

        <button type="button" 
                onclick="abrirModalCrearZona()" 
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white text-xs font-bold rounded-xl transition-all shadow-sm hover:shadow cursor-pointer">
            <span class="material-symbols-outlined text-[18px]">add_circle</span>
            <span>Nueva zona de envío</span>
        </button>
    </div>

    <!-- ── ESTADO VACÍO ── -->
    @if($zonas->isEmpty())
        <div class="card-elevated p-8 sm:p-12 rounded-2xl text-center flex flex-col items-center justify-center space-y-4">
            <div class="w-16 h-16 rounded-full bg-slate-100 border border-slate-200 text-slate-400 flex items-center justify-center shadow-inner">
                <span class="material-symbols-outlined text-[36px]">map</span>
            </div>
            
            <div class="max-w-md space-y-1">
                <h3 class="text-base font-bold text-slate-900">Todavía no existen zonas de envío</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Configura las provincias o regiones de Panamá a las que realizas entregas y establece sus tarifas correspondientes para el checkout.
                </p>
            </div>

            <button type="button" 
                    onclick="abrirModalCrearZona()" 
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all shadow-sm hover:shadow cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">add_circle</span>
                <span>Nueva zona de envío</span>
            </button>
        </div>
    @else
        <!-- ── TABLA PRINCIPAL DE ZONAS ── -->
        <div class="card-elevated rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="py-3.5 px-5">Provincia / Zona</th>
                            <th class="py-3.5 px-5">Costo de envío</th>
                            <th class="py-3.5 px-5">Estado</th>
                            <th class="py-3.5 px-5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs text-slate-700 font-medium">
                        @foreach($zonas as $zona)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <!-- Provincia / Zona -->
                                <td class="py-4 px-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200/80 text-slate-600 flex items-center justify-center shrink-0">
                                            <span class="material-symbols-outlined text-[18px]">location_on</span>
                                        </div>
                                        <div>
                                            <span class="font-bold text-slate-900 text-sm block">{{ $zona->nombre }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Costo de envío -->
                                <td class="py-4 px-5">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 font-bold text-xs font-mono">
                                        ${{ number_format((float) ($zona->costo ?? 0), 2) }} <span class="text-[10px] text-emerald-600 font-normal">USD</span>
                                    </span>
                                </td>

                                <!-- Estado -->
                                <td class="py-4 px-5">
                                    @if($zona->activo)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-100/80 border border-emerald-200 text-emerald-800 text-[11px] font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span>Activa</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 border border-slate-200 text-slate-600 text-[11px] font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            <span>Inactiva</span>
                                        </span>
                                    @endif
                                </td>

                                <!-- Acciones -->
                                <td class="py-4 px-5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        
                                        <!-- Botón Editar -->
                                        <button type="button" 
                                                onclick="abrirModalEditarZona({{ $zona->id }}, '{{ addslashes($zona->nombre) }}', {{ (float) ($zona->costo ?? 0) }}, {{ $zona->activo ? 'true' : 'false' }})" 
                                                class="px-2.5 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 hover:border-slate-300 transition-all shadow-2xs flex items-center gap-1 cursor-pointer"
                                                title="Editar zona">
                                            <span class="material-symbols-outlined text-[15px] text-slate-500">edit</span>
                                            <span class="hidden sm:inline">Editar</span>
                                        </button>

                                        <!-- Toggle Activar / Desactivar -->
                                        <form action="{{ route('admin.zonas-envio.toggle', $zona->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" 
                                                    class="px-2.5 py-1.5 text-xs font-semibold {{ $zona->activo ? 'text-amber-700 bg-amber-50 border-amber-200 hover:bg-amber-100' : 'text-emerald-700 bg-emerald-50 border-emerald-200 hover:bg-emerald-100' }} border rounded-lg transition-all shadow-2xs flex items-center gap-1 cursor-pointer"
                                                    title="{{ $zona->activo ? 'Desactivar zona' : 'Activar zona' }}">
                                                <span class="material-symbols-outlined text-[15px]">{{ $zona->activo ? 'pause_circle' : 'play_circle' }}</span>
                                                <span class="hidden sm:inline">{{ $zona->activo ? 'Desactivar' : 'Activar' }}</span>
                                            </button>
                                        </form>

                                        <!-- Botón Eliminar -->
                                        <button type="button" 
                                                onclick="abrirModalEliminarZona({{ $zona->id }}, '{{ addslashes($zona->nombre) }}')" 
                                                class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 border border-transparent hover:border-rose-200 rounded-lg transition-colors cursor-pointer"
                                                title="Eliminar zona">
                                            <span class="material-symbols-outlined text-[17px]">delete</span>
                                        </button>

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>

<!-- ── MODAL: CREAR / EDITAR ZONA DE ENVÍO ── -->
<div id="modal-zona-envio" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-6 space-y-5 transform transition-all">
        
        <!-- Modal Header -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100/80 border border-emerald-200 text-emerald-700 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-[20px]">local_shipping</span>
                </div>
                <div>
                    <h3 id="modal-zona-titulo" class="text-base font-bold text-slate-900">Nueva zona de envío</h3>
                    <p class="text-[11px] text-slate-500">Ingresa la provincia o zona y el costo correspondiente.</p>
                </div>
            </div>
            <button type="button" onclick="cerrarModalZona()" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-100 transition-colors">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <!-- Formulario -->
        <form id="form-zona-envio" method="POST" action="{{ route('admin.zonas-envio.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="_method" id="form-zona-method" value="POST">

            <!-- Campo Provincia / Zona -->
            <div>
                <label for="input-zona-nombre" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                    Provincia / Zona <span class="text-rose-500">*</span>
                </label>
                <input type="text" 
                       id="input-zona-nombre" 
                       name="nombre" 
                       required 
                       placeholder="Ej. Panamá, Coclé, Colón, Chiriquí" 
                       class="input-panama w-full text-xs rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20 py-2.5 px-3">
            </div>

            <!-- Campo Costo de envío -->
            <div>
                <label for="input-zona-costo" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                    Costo de envío ($ USD) <span class="text-rose-500">*</span>
                </label>
                <div class="relative flex items-center">
                    <span class="absolute left-3 text-xs text-slate-400 pointer-events-none font-bold">$</span>
                    <input type="number" 
                           id="input-zona-costo" 
                           name="costo" 
                           step="0.01" 
                           min="0" 
                           max="99999999.99"
                           required 
                           placeholder="0.00" 
                           class="input-panama w-full pl-7 pr-3 py-2.5 text-xs font-mono rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500/20">
                </div>
            </div>

            <!-- Campo Estado -->
            <div class="pt-1">
                <label class="flex items-center gap-3 p-3 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-100/80 transition-colors">
                    <input type="checkbox" 
                           id="input-zona-activo" 
                           name="activo" 
                           value="1" 
                           checked
                           class="rounded text-emerald-600 focus:ring-emerald-500 h-4 w-4 border-slate-300">
                    <div>
                        <span class="text-xs font-bold text-slate-900 block">Zona activa</span>
                        <span class="text-[11px] text-slate-500 block">Permite aplicar esta tarifa durante el proceso de checkout.</span>
                    </div>
                </label>
            </div>

            <!-- Acciones Footer -->
            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" 
                        onclick="cerrarModalZona()" 
                        class="px-4 py-2.5 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
                    Cancelar
                </button>
                <button type="submit" 
                        class="px-5 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-all shadow-sm">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ── MODAL: CONFIRMACIÓN DE ELIMINACIÓN ── -->
<div id="modal-eliminar-zona" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-sm w-full p-6 space-y-4 text-center transform transition-all">
        
        <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto shadow-inner">
            <span class="material-symbols-outlined text-[28px]">warning</span>
        </div>

        <div class="space-y-1">
            <h3 class="text-base font-bold text-slate-900">¿Eliminar esta zona de envío?</h3>
            <p class="text-xs text-slate-500 leading-relaxed">
                Estás a punto de eliminar la zona <strong id="texto-nombre-zona-eliminar" class="text-slate-800"></strong>. Esta acción no se puede deshacer.
            </p>
        </div>

        <form id="form-eliminar-zona" method="POST" action="" class="flex items-center justify-center gap-3 pt-2">
            @csrf
            @method('DELETE')
            <button type="button" 
                    onclick="cerrarModalEliminarZona()" 
                    class="w-full py-2.5 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
                Cancelar
            </button>
            <button type="submit" 
                    class="w-full py-2.5 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-all shadow-sm">
                Eliminar
            </button>
        </form>
    </div>
</div>

<script>
    function abrirModalCrearZona() {
        const modal = document.getElementById('modal-zona-envio');
        const form = document.getElementById('form-zona-envio');
        const methodInput = document.getElementById('form-zona-method');
        const titulo = document.getElementById('modal-zona-titulo');

        titulo.textContent = 'Nueva zona de envío';
        form.action = "{{ route('admin.zonas-envio.store') }}";
        methodInput.value = 'POST';

        document.getElementById('input-zona-nombre').value = '';
        document.getElementById('input-zona-costo').value = '';
        document.getElementById('input-zona-activo').checked = true;

        modal.classList.remove('hidden');
    }

    function abrirModalEditarZona(id, nombre, costo, activo) {
        const modal = document.getElementById('modal-zona-envio');
        const form = document.getElementById('form-zona-envio');
        const methodInput = document.getElementById('form-zona-method');
        const titulo = document.getElementById('modal-zona-titulo');

        titulo.textContent = 'Editar zona de envío';
        form.action = `/admin/configuracion/zonas-envio/${id}`;
        methodInput.value = 'PUT';

        document.getElementById('input-zona-nombre').value = nombre;
        document.getElementById('input-zona-costo').value = costo;
        document.getElementById('input-zona-activo').checked = activo;

        modal.classList.remove('hidden');
    }

    function cerrarModalZona() {
        document.getElementById('modal-zona-envio').classList.add('hidden');
    }

    function abrirModalEliminarZona(id, nombre) {
        const modal = document.getElementById('modal-eliminar-zona');
        const form = document.getElementById('form-eliminar-zona');
        const textoNombre = document.getElementById('texto-nombre-zona-eliminar');

        form.action = `/admin/configuracion/zonas-envio/${id}`;
        textoNombre.textContent = `"${nombre}"`;

        modal.classList.remove('hidden');
    }

    function cerrarModalEliminarZona() {
        document.getElementById('modal-eliminar-zona').classList.add('hidden');
    }
</script>
@endsection
