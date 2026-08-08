@extends('layouts.admin')

@section('title', 'Reglas de Envío Gratis — PayMe Panamá')

@section('breadcrumbs')
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <span class="text-slate-600">Promociones</span>
    <span class="material-symbols-outlined text-[13px] text-slate-300 shrink-0">chevron_right</span>
    <span class="font-bold text-slate-900 truncate">Envío Gratis</span>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-600 text-[24px]">local_shipping</span>
                Promociones Especiales
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Configura promociones de flete bonificado por zona de cobertura.</p>
        </div>
    </div>

    <!-- Navigation Tabs (Screen 3 Stitch) -->
    <div class="flex border-b border-slate-200 gap-6">
        <a href="{{ route('admin.promociones.envio-gratis') }}" 
           class="pb-3 text-xs font-bold border-b-2 border-emerald-600 text-emerald-700 flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">local_shipping</span>
            <span>Reglas de Envío Gratis</span>
        </a>
        <a href="{{ route('admin.promociones.producto-del-mes') }}" 
           class="pb-3 text-xs font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-900 transition-colors flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">star</span>
            <span>Producto del Mes</span>
        </a>
    </div>

    <!-- Main Content Header & Action -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-sm font-extrabold text-slate-900">Reglas de Envío Gratis por Zona</h2>
            <p class="text-xs text-slate-500">Establece montos mínimos de compra por zona logística.</p>
        </div>
        <button type="button" 
                onclick="abrirModalEnvioGratis()" 
                class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition-all shadow-xs flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">add</span>
            <span>Nueva Regla</span>
        </button>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/70 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-5">Zona de Envío</th>
                        <th class="py-3.5 px-5">Monto Mínimo</th>
                        <th class="py-3.5 px-5">Vigencia</th>
                        <th class="py-3.5 px-5">Estado</th>
                        <th class="py-3.5 px-5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    @forelse($promociones as $promo)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-5 font-bold text-slate-900">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 shadow-2xs">
                                        <span class="material-symbols-outlined text-[18px]">distance</span>
                                    </div>
                                    <div>
                                        <span class="block text-xs font-bold text-slate-900">{{ $promo->zonaEnvio ? $promo->zonaEnvio->nombre : 'Todas las zonas' }}</span>
                                        <span class="text-[10px] text-slate-400 font-normal">Tarifa estándar: ${{ number_format($promo->zonaEnvio->costo ?? 0, 2) }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-5 font-bold text-emerald-700 text-sm">
                                ${{ number_format($promo->monto_minimo, 2) }}
                            </td>
                            <td class="py-3.5 px-5 text-slate-600">
                                {{ $promo->inicio_en ? $promo->inicio_en->format('d/m/Y') : 'Inmediata' }}
                                <span class="text-slate-400 mx-0.5">&rarr;</span>
                                {{ $promo->fin_en ? $promo->fin_en->format('d/m/Y') : 'Indefinida' }}
                            </td>
                            <td class="py-3.5 px-5">
                                @if($promo->esVigente())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Inactivo / Expirado
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" 
                                            onclick='editarReglaEnvio(@json($promo))' 
                                            class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors"
                                            title="Editar">
                                        <span class="material-symbols-outlined text-[18px]">edit</span>
                                    </button>

                                    <form action="{{ route('admin.promociones.envio-gratis.toggle', $promo->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="p-1.5 {{ $promo->activo ? 'text-emerald-600' : 'text-slate-400' }} hover:bg-slate-100 rounded-lg transition-colors">
                                            <span class="material-symbols-outlined text-[18px]">{{ $promo->activo ? 'toggle_on' : 'toggle_off' }}</span>
                                        </button>
                                    </form>

                                    <button type="button" 
                                            onclick="window.ModalEliminar.abrir({
                                                url: '{{ route('admin.promociones.envio-gratis.eliminar', $promo->id) }}',
                                                nombre: 'Envío gratis: {{ addslashes($promo->zonaEnvio ? $promo->zonaEnvio->nombre : 'Todas las zonas') }} (Min. ${{ number_format($promo->monto_minimo, 2) }})',
                                                titulo: '¿Eliminar Regla de Envío Gratis?'
                                            })" 
                                            class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                            title="Eliminar regla">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    <span class="material-symbols-outlined text-[24px]">local_shipping</span>
                                </div>
                                <p class="text-sm font-semibold text-slate-700 mb-1">No hay reglas de envío gratis</p>
                                <p class="text-xs text-slate-400 mb-4">Crea promociones de flete bonificado por zona geográfica.</p>
                                <button type="button" onclick="abrirModalEnvioGratis()" class="px-4 py-2 bg-emerald-600 text-white text-xs font-bold rounded-xl hover:bg-emerald-700 transition-colors inline-flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px]">add</span>
                                    Nueva Regla
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- MODAL: CREAR / EDITAR REGLA DE ENVÍO GRATIS -->
<div id="modal-envio-gratis" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs transition-opacity">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-md w-full p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 id="modal-title-envio" class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <span class="material-symbols-outlined text-emerald-600 text-[20px]">local_shipping</span>
                Nueva Regla de Envío Gratis
            </h3>
            <button type="button" onclick="cerrarModalEnvioGratis()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <form id="form-envio-gratis" action="{{ route('admin.promociones.envio-gratis.guardar') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" id="envio_id" name="id">

            <!-- Zona de Envío -->
            <div>
                <label for="zona_envio_id" class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Zona de Envío</label>
                <select id="zona_envio_id" name="zona_envio_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-semibold text-slate-900 focus:bg-white focus:border-emerald-500 outline-none">
                    <option value="">-- Selecciona una zona --</option>
                    @foreach($zonasEnvio as $zona)
                        <option value="{{ $zona->id }}">{{ $zona->nombre }} (${{ number_format($zona->costo, 2) }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Monto mínimo -->
            <div>
                <label for="monto_minimo_envio" class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Monto Mínimo de Compra ($ PAB)</label>
                <input type="number" id="monto_minimo_envio" name="monto_minimo" step="0.01" min="0" required placeholder="50.00" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-900 focus:bg-white focus:border-emerald-500 outline-none">
            </div>

            <!-- Fechas -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="inicio_en_envio" class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Inicio</label>
                    <input type="date" id="inicio_en_envio" name="inicio_en" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900 focus:bg-white focus:border-emerald-500 outline-none">
                </div>
                <div>
                    <label for="fin_en_envio" class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Fin (Opcional)</label>
                    <input type="date" id="fin_en_envio" name="fin_en" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-900 focus:bg-white focus:border-emerald-500 outline-none">
                </div>
            </div>

            <!-- Estado -->
            <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-800">
                <input type="checkbox" id="activo_envio" name="activo" value="1" checked class="text-emerald-600 rounded">
                <span>Activar regla inmediatamente</span>
            </label>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="cerrarModalEnvioGratis()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs">Guardar Regla</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function abrirModalEnvioGratis() {
        const modal = document.getElementById('modal-envio-gratis');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.getElementById('modal-title-envio').textContent = 'Nueva Regla de Envío Gratis';
        document.getElementById('form-envio-gratis').reset();
        document.getElementById('inicio_en_envio').value = new Date().toISOString().split('T')[0];
    }

    function cerrarModalEnvioGratis() {
        const modal = document.getElementById('modal-envio-gratis');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function editarReglaEnvio(promo) {
        abrirModalEnvioGratis();
        document.getElementById('modal-title-envio').textContent = 'Editar Regla de Envío Gratis';
        document.getElementById('envio_id').value = promo.id;
        document.getElementById('zona_envio_id').value = promo.zona_envio_id;
        document.getElementById('monto_minimo_envio').value = promo.monto_minimo;
        document.getElementById('activo_envio').checked = !!promo.activo;
        if (promo.inicio_en) document.getElementById('inicio_en_envio').value = promo.inicio_en.split('T')[0];
        if (promo.fin_en) document.getElementById('fin_en_envio').value = promo.fin_en.split('T')[0];
    }
</script>
@endpush
@endsection
