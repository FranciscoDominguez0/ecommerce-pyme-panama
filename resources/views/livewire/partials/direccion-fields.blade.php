{{-- Campos compartidos del formulario de dirección. Única fuente de verdad del markup de campos. --}}
@php
    $inputClase = $this->compact
        ? 'block w-full rounded-md border-outline-variant shadow-sm focus:border-secondary focus:ring-secondary sm:text-sm bg-surface-container-lowest'
        : 'block w-full rounded-lg border-outline-variant shadow-sm focus:border-secondary focus:ring-secondary sm:text-sm bg-white py-2.5 px-3';
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
    <div>
        <label for="alias" class="block text-xs font-semibold text-on-surface-variant mb-1.5">Alias de la dirección</label>
        <input type="text" name="alias" id="alias" wire:model="alias"
            class="{{ $inputClase }} @error('alias') border-error @enderror"
            placeholder="Ej: Casa, Oficina" required>
        @error('alias')
            <p class="mt-1 text-xs text-error flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="nombre_receptor" class="block text-xs font-semibold text-on-surface-variant mb-1.5">Nombre del receptor</label>
        <input type="text" name="nombre_receptor" id="nombre_receptor" wire:model="nombreReceptor"
            class="{{ $inputClase }} @error('nombreReceptor') border-error @enderror"
            placeholder="Nombre de quien recibe" required>
        @error('nombreReceptor')
            <p class="mt-1 text-xs text-error flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="provincia" class="block text-xs font-semibold text-on-surface-variant mb-1.5">Provincia</label>
        <select name="provincia" id="provincia" wire:model.live="provincia"
            class="{{ $inputClase }} @error('provincia') border-error @enderror" required>
            <option value="">Selecciona una provincia</option>
            @foreach($this->provincias as $prov)
                <option value="{{ $prov }}">{{ $prov }}</option>
            @endforeach
        </select>
        @error('provincia')
            <p class="mt-1 text-xs text-error flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="distrito" class="block text-xs font-semibold text-on-surface-variant mb-1.5">Distrito</label>
        <select name="distrito" id="distrito" wire:model.live="distrito"
            @if(!$this->provincia) disabled @endif
            class="{{ $inputClase }} @error('distrito') border-error @enderror">
            <option value="">Selecciona un distrito</option>
            @foreach($this->distritos as $d)
                <option value="{{ $d }}">{{ $d }}</option>
            @endforeach
        </select>
        @error('distrito')
            <p class="mt-1 text-xs text-error flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="corregimiento" class="block text-xs font-semibold text-on-surface-variant mb-1.5">Corregimiento</label>
        <select name="corregimiento" id="corregimiento" wire:model.live="corregimiento"
            @if(!$this->distrito) disabled @endif
            class="{{ $inputClase }} @error('corregimiento') border-error @enderror">
            <option value="">Selecciona un corregimiento</option>
            @foreach($this->corregimientos as $c)
                <option value="{{ $c }}">{{ $c }}</option>
            @endforeach
        </select>
        @error('corregimiento')
            <p class="mt-1 text-xs text-error flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="direccion_exacta" class="block text-xs font-semibold text-on-surface-variant mb-1.5">Dirección exacta</label>
        <textarea name="direccion_exacta" id="direccion_exacta" rows="3" wire:model="direccionExacta"
            class="{{ $inputClase }} @error('direccionExacta') border-error @enderror"
            placeholder="Calle, número, urbanización, edificio..." required></textarea>
        @error('direccionExacta')
            <p class="mt-1 text-xs text-error flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="referencia" class="block text-xs font-semibold text-on-surface-variant mb-1.5">Punto de referencia (Opcional)</label>
        <input type="text" name="referencia" id="referencia" wire:model="referencia"
            class="{{ $inputClase }} @error('referencia') border-error @enderror"
            placeholder="Ej: Frente al parque, cerca del supermercado">
        @error('referencia')
            <p class="mt-1 text-xs text-error flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">error</span> {{ $message }}</p>
        @enderror
    </div>

    @if($this->mostrarPredeterminada)
        <div class="sm:col-span-2">
            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" name="es_predeterminada" id="es_predeterminada" wire:model="esPredeterminada" value="1"
                    class="rounded border-outline-variant text-secondary focus:ring-secondary">
                <span class="text-sm text-on-surface">Establecer como dirección predeterminada</span>
            </label>
        </div>
    @endif
</div>
