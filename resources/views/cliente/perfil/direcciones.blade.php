@extends('layouts.cliente')

@section('title', 'Mis Direcciones')

@push('styles')
<style>
    .ambient-shadow {
        box-shadow: 0px 4px 20px rgba(0, 35, 73, 0.05);
    }
    .ambient-shadow-hover:hover {
        box-shadow: 0px 12px 32px rgba(0, 35, 73, 0.12);
    }
</style>
@endpush

@section('content')
<x-cliente.perfil.layout active="direcciones">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-4">
        <div>
            <h3 class="text-base font-bold text-primary">Direcciones de Envío</h3>
            <p class="text-xs text-on-surface-variant mt-0.5">Administra tus direcciones de entrega.</p>
        </div>
        <button type="button" id="btn-agregar"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary text-on-primary text-xs font-bold uppercase tracking-wider hover:bg-primary-container transition-colors shadow-sm shrink-0">
            <span class="material-symbols-outlined text-[16px]">add</span>
            + Agregar dirección
        </button>
    </div>

    @if($direcciones->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8" id="lista-direcciones">
            @foreach($direcciones as $dir)
                @php
                    $iconoAlias = match(mb_strtolower($dir->alias)) {
                        'casa' => 'home',
                        'oficina', 'trabajo' => 'work',
                        'apartamento' => 'apartment',
                        default => 'location_on',
                    };
                @endphp
                <div class="card-direccion bg-surface-container-lowest border border-outline-variant rounded-xl p-6 flex flex-col gap-4 ambient-shadow ambient-shadow-hover transition-all duration-300 relative group">
                    <div class="flex justify-between items-start">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-primary text-2xl">{{ $iconoAlias }}</span>
                            <span class="text-base font-semibold text-primary">{{ $dir->alias }}</span>
                            @if($dir->es_predeterminada)
                                <span class="bg-secondary/10 text-secondary font-label-caps text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-sm">Predeterminada</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-1">
                            <button type="button" onclick="editarDireccion({{ $dir->id }})"
                                class="p-1.5 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container-low transition-colors"
                                title="Editar">
                                <span class="material-symbols-outlined text-[18px]">edit</span>
                            </button>
                            <button type="button" onclick="confirmarEliminar({{ $dir->id }}, '{{ e($dir->alias) }}', '{{ e($dir->direccion_exacta) }}')"
                                class="p-1.5 rounded-lg text-on-surface-variant hover:text-error hover:bg-error-container/50 transition-colors"
                                title="Eliminar">
                                <span class="material-symbols-outlined text-[18px]">delete</span>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-1 text-sm text-on-surface-variant">
                        <p class="font-semibold text-on-background">{{ $dir->nombre_receptor }}</p>
                        <p>{{ $dir->direccion_exacta }}</p>
                        <p>{{ $dir->corregimiento }}, {{ $dir->distrito }}</p>
                        <p>{{ $dir->provincia }}</p>
                        @if($dir->referencia)
                            <p class="text-xs mt-1 text-outline">
                                <span class="font-label-caps font-semibold uppercase tracking-wider text-xs">Ref:</span> {{ $dir->referencia }}
                            </p>
                        @endif
                    </div>

                    @if(!$dir->es_predeterminada)
                        <form action="{{ route('cliente.perfil.direcciones.predeterminada', $dir->id) }}" method="POST" class="mt-auto pt-2 border-t border-outline-variant/50">
                            @csrf
                            @method('PUT')
                            <button type="submit"
                                class="w-full text-center text-xs font-semibold text-secondary hover:text-secondary-container transition-colors py-1.5 rounded-lg hover:bg-surface-container-low">
                                Establecer como predeterminada
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div id="estado-vacio" class="text-center py-12 bg-surface-container-lowest rounded-xl border border-outline-variant ambient-shadow mb-8">
            <span class="material-symbols-outlined text-6xl text-outline-variant mb-4">map</span>
            <h3 class="text-base font-bold text-primary mb-2">No tienes direcciones guardadas</h3>
            <p class="text-on-surface-variant text-sm mb-6">Agrega una dirección para facilitar tus próximas compras.</p>
            <button type="button" id="btn-agregar-vacio"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-primary text-on-primary text-xs font-bold uppercase tracking-wider hover:bg-primary-container transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[16px]">add</span>
                + Agregar dirección
            </button>
        </div>
    @endif

    <div id="form-container" class="hidden bg-white border border-outline-variant rounded-xl p-6 shadow-sm mb-8">
        <div class="flex items-center gap-2 mb-2">
            <button type="button" id="btn-volver" class="p-1 rounded-lg text-on-surface-variant hover:text-primary hover:bg-surface-container-low transition-colors">
                <span class="material-symbols-outlined">arrow_back</span>
            </button>
            <h2 id="form-titulo" class="text-xl font-bold text-primary">Agregar Dirección</h2>
        </div>
        <p id="form-subtitulo" class="text-sm text-on-surface-variant mb-6">Completa los datos de tu dirección de envío.</p>

        <form id="form-direccion" method="POST" action="{{ route('cliente.perfil.direcciones.store') }}">
            @csrf
            <input type="hidden" name="_method" value="POST" id="form-method">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">
                <div>
                    <label for="alias" class="block text-xs font-semibold text-on-surface-variant mb-1.5">Alias de la dirección</label>
                    <input type="text" name="alias" id="alias"
                        class="block w-full rounded-lg border-outline-variant shadow-sm focus:border-secondary focus:ring-secondary sm:text-sm bg-white py-2.5 px-3"
                        placeholder="Ej: Casa, Oficina" required>
                </div>

                <div>
                    <label for="nombre_receptor" class="block text-xs font-semibold text-on-surface-variant mb-1.5">Nombre del receptor</label>
                    <input type="text" name="nombre_receptor" id="nombre_receptor"
                        class="block w-full rounded-lg border-outline-variant shadow-sm focus:border-secondary focus:ring-secondary sm:text-sm bg-white py-2.5 px-3"
                        placeholder="Nombre de quien recibe" required>
                </div>

                <div>
                    <label for="provincia" class="block text-xs font-semibold text-on-surface-variant mb-1.5">Provincia</label>
                    <select name="provincia" id="provincia"
                        class="block w-full rounded-lg border-outline-variant shadow-sm focus:border-secondary focus:ring-secondary sm:text-sm bg-white py-2.5 px-3" required>
                        <option value="">Selecciona una provincia</option>
                        @foreach($provincias as $prov)
                            <option value="{{ $prov }}">{{ $prov }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="distrito" class="block text-xs font-semibold text-on-surface-variant mb-1.5">Distrito</label>
                    <select name="distrito" id="distrito"
                        class="block w-full rounded-lg border-outline-variant shadow-sm focus:border-secondary focus:ring-secondary sm:text-sm bg-white py-2.5 px-3" required disabled>
                        <option value="">Selecciona un distrito</option>
                    </select>
                </div>

                <div>
                    <label for="corregimiento" class="block text-xs font-semibold text-on-surface-variant mb-1.5">Corregimiento</label>
                    <select name="corregimiento" id="corregimiento"
                        class="block w-full rounded-lg border-outline-variant shadow-sm focus:border-secondary focus:ring-secondary sm:text-sm bg-white py-2.5 px-3" required disabled>
                        <option value="">Selecciona un corregimiento</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label for="direccion_exacta" class="block text-xs font-semibold text-on-surface-variant mb-1.5">Dirección exacta</label>
                    <textarea name="direccion_exacta" id="direccion_exacta" rows="3"
                        class="block w-full rounded-lg border-outline-variant shadow-sm focus:border-secondary focus:ring-secondary sm:text-sm bg-white py-2.5 px-3"
                        placeholder="Calle, número, urbanización, edificio..." required></textarea>
                </div>

                <div class="sm:col-span-2">
                    <label for="referencia" class="block text-xs font-semibold text-on-surface-variant mb-1.5">Punto de referencia (Opcional)</label>
                    <input type="text" name="referencia" id="referencia"
                        class="block w-full rounded-lg border-outline-variant shadow-sm focus:border-secondary focus:ring-secondary sm:text-sm bg-white py-2.5 px-3"
                        placeholder="Ej: Frente al parque, cerca del supermercado">
                </div>
            </div>

            <label class="flex items-center gap-2.5 cursor-pointer mb-6">
                <input type="checkbox" name="es_predeterminada" id="es_predeterminada" value="1"
                    class="rounded border-outline-variant text-secondary focus:ring-secondary">
                <span class="text-sm text-on-surface">Establecer como dirección predeterminada</span>
            </label>

            <div class="flex justify-end gap-3 pt-4 border-t border-outline-variant">
                <button type="button" id="btn-cancelar"
                    class="px-6 py-2.5 rounded-lg border border-outline-variant text-on-surface-variant text-xs font-semibold hover:bg-surface-container-low transition-colors">
                    Cancelar
                </button>
                <button type="submit" id="btn-guardar"
                    class="px-6 py-2.5 rounded-lg bg-primary text-on-primary text-xs font-bold uppercase tracking-wider hover:bg-primary-container transition-colors shadow-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-[16px]">save</span>
                    <span id="btn-guardar-texto">Guardar dirección</span>
                </button>
            </div>
        </form>
    </div>
</x-cliente.perfil.layout>

<x-modal-eliminar
    id="modal-eliminar-direccion"
    titulo="¿Eliminar esta dirección?"
    mensaje="Esta dirección dejará de estar disponible para tus pedidos."
    icono="delete"
    textoBoton="Eliminar dirección"
/>

@push('scripts')
<script>
    window.geoData = @json(\App\Helpers\GeolocalizacionPanama::data());

    let modoEdicion = false;
    let editandoId = null;

    const formContainer = document.getElementById('form-container');
    const formTitulo = document.getElementById('form-titulo');
    const formSubtitulo = document.getElementById('form-subtitulo');
    const formDireccion = document.getElementById('form-direccion');
    const formMethod = document.getElementById('form-method');
    const provinciaSelect = document.getElementById('provincia');
    const distritoSelect = document.getElementById('distrito');
    const corregimientoSelect = document.getElementById('corregimiento');
    const btnGuardarTexto = document.getElementById('btn-guardar-texto');
    const listaDirecciones = document.getElementById('lista-direcciones');
    const estadoVacio = document.getElementById('estado-vacio');

    function mostrarFormulario() {
        formContainer.classList.remove('hidden');
        formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function ocultarFormulario() {
        formContainer.classList.add('hidden');
        resetFormulario();
    }

    function resetFormulario() {
        modoEdicion = false;
        editandoId = null;
        formDireccion.action = '{{ route('cliente.perfil.direcciones.store') }}';
        formMethod.value = 'POST';
        formTitulo.textContent = 'Agregar Dirección';
        formSubtitulo.textContent = 'Completa los datos de tu dirección de envío.';
        btnGuardarTexto.textContent = 'Guardar dirección';
        formDireccion.reset();
        provinciaSelect.value = '';
        distritoSelect.innerHTML = '<option value="">Selecciona un distrito</option>';
        distritoSelect.disabled = true;
        corregimientoSelect.innerHTML = '<option value="">Selecciona un corregimiento</option>';
        corregimientoSelect.disabled = true;
    }

    function llenarDistritos(provincia, distritoSeleccionado) {
        distritoSelect.innerHTML = '<option value="">Selecciona un distrito</option>';
        corregimientoSelect.innerHTML = '<option value="">Selecciona un corregimiento</option>';
        corregimientoSelect.disabled = true;

        if (!provincia || !window.geoData[provincia]) {
            distritoSelect.disabled = true;
            return;
        }

        distritoSelect.disabled = false;
        const distritos = Object.keys(window.geoData[provincia].distritos);
        distritos.forEach(function(d) {
            const opt = document.createElement('option');
            opt.value = d;
            opt.textContent = d;
            if (d === distritoSeleccionado) opt.selected = true;
            distritoSelect.appendChild(opt);
        });

        if (distritoSeleccionado) {
            llenarCorregimientos(provincia, distritoSeleccionado, null);
        }
    }

    function llenarCorregimientos(provincia, distrito, corregimientoSeleccionado) {
        corregimientoSelect.innerHTML = '<option value="">Selecciona un corregimiento</option>';

        if (!provincia || !distrito || !window.geoData[provincia] || !window.geoData[provincia].distritos[distrito]) {
            corregimientoSelect.disabled = true;
            return;
        }

        corregimientoSelect.disabled = false;
        const corregimientos = window.geoData[provincia].distritos[distrito];
        corregimientos.forEach(function(c) {
            const opt = document.createElement('option');
            opt.value = c;
            opt.textContent = c;
            if (c === corregimientoSeleccionado) opt.selected = true;
            corregimientoSelect.appendChild(opt);
        });
    }

    provinciaSelect.addEventListener('change', function() {
        llenarDistritos(this.value, null);
    });

    distritoSelect.addEventListener('change', function() {
        llenarCorregimientos(provinciaSelect.value, this.value, null);
    });

    document.getElementById('btn-agregar').addEventListener('click', function() {
        resetFormulario();
        mostrarFormulario();
    });

    document.getElementById('btn-cancelar').addEventListener('click', ocultarFormulario);

    document.getElementById('btn-volver').addEventListener('click', ocultarFormulario);

    const btnAgregarVacio = document.getElementById('btn-agregar-vacio');
    if (btnAgregarVacio) {
        btnAgregarVacio.addEventListener('click', function() {
            resetFormulario();
            mostrarFormulario();
        });
    }

    window.editarDireccion = function(id) {
        const card = document.querySelector('[onclick="editarDireccion(' + id + ')"]').closest('.card-direccion');
        const datos = card.querySelector('.space-y-1');
        const alias = card.querySelector('.text-base.font-semibold').textContent.trim();
        const receptor = datos.querySelector('.font-semibold').textContent.trim();
        const direccionExacta = datos.querySelector('p:nth-child(2)').textContent.trim();
        const corregDistrito = datos.querySelector('p:nth-child(3)').textContent.trim();
        const provincia = datos.querySelector('p:nth-child(4)').textContent.trim();
        const refElem = datos.querySelector('p.text-outline');
        const referencia = refElem ? refElem.textContent.replace(/^Ref:\s*/, '').trim() : '';
        const esPredeterminada = card.querySelector('.text-secondary.text-\\[10px\\]') !== null;

        const partes = corregDistrito.split(', ');
        const corregimiento = partes[0] || '';
        const distrito = partes[1] || '';

        modoEdicion = true;
        editandoId = id;
        formDireccion.action = '{{ url('mi-cuenta/direcciones') }}/' + id;
        formMethod.value = 'PUT';
        formTitulo.textContent = 'Editar Dirección';
        formSubtitulo.textContent = 'Actualiza los datos de tu dirección de envío guardada.';
        btnGuardarTexto.textContent = 'Guardar cambios';

        document.getElementById('alias').value = alias;
        document.getElementById('nombre_receptor').value = receptor;
        document.getElementById('direccion_exacta').value = direccionExacta;
        document.getElementById('referencia').value = referencia;
        document.getElementById('es_predeterminada').checked = esPredeterminada;

        provinciaSelect.value = provincia;
        llenarDistritos(provincia, distrito);
        llenarCorregimientos(provincia, distrito, corregimiento);

        mostrarFormulario();
    };

    window.confirmarEliminar = function(id, alias, direccion) {
        window.ModalEliminar.abrir({
            url: '{{ url('mi-cuenta/direcciones') }}/' + id,
            nombre: alias + ' - ' + direccion.substring(0, 40) + (direccion.length > 40 ? '...' : ''),
            id: 'modal-eliminar-direccion'
        });
    };
</script>
@endpush
@endsection
