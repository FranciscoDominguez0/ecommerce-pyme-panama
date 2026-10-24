@extends('layouts.cliente')

@section('title', 'Solicitar Devolución — Pedido ' . $pedido->numero_pedido)

@section('content')
<div class="min-h-[calc(100vh-160px)] bg-slate-50/70 dark:bg-[#121415] py-8 sm:py-10">
    <div class="w-full max-w-4xl lg:max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 sm:space-y-8">

        {{-- Encabezado Principal --}}
        <div>
            <a href="{{ route('cliente.perfil.pedidos.detalle', $pedido->id) }}" wire:navigate 
               class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors uppercase tracking-wider mb-2.5">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span>
                <span>Volver al detalle del pedido</span>
            </a>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Solicitar Devolución</h1>
            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Completa el siguiente formulario para registrar y gestionar el retorno de tu compra de forma segura.
            </p>
        </div>

        {{-- Contenedor Principal: Misma estructura en grid (Columna izquierda resumen / Columna derecha formulario) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 items-start">

            {{-- Columna Izquierda (lg:col-span-5): Resumen del pedido y garantía --}}
            <div class="lg:col-span-5 space-y-6">

                {{-- CARD: Detalles del Pedido y Artículos --}}
                <div class="bg-white dark:bg-[#1f2327] rounded-3xl border border-slate-200/90 dark:border-slate-800 p-6 sm:p-7 shadow-xs">
                    <div class="flex items-center justify-between pb-4 mb-5 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Detalles del Pedido</span>
                        <span class="font-mono text-xs sm:text-sm font-extrabold text-emerald-600 dark:text-emerald-400">#{{ $pedido->numero_pedido }}</span>
                    </div>

                    <div class="space-y-4">
                        @foreach($pedido->items as $item)
                            @php
                                $imgUrl = null;
                                if ($item->variante && $item->variante->imagen_ruta) {
                                    $imgUrl = asset('storage/' . $item->variante->imagen_ruta);
                                } elseif ($item->producto) {
                                    $imgUrl = $item->producto->imagen_url;
                                }
                            @endphp

                            <div class="flex gap-3.5 items-center">
                                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 overflow-hidden shrink-0 flex items-center justify-center">
                                    @if($imgUrl)
                                        <img src="{{ $imgUrl }}" alt="{{ $item->producto?->nombre ?? 'Producto' }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="material-symbols-outlined text-slate-400 text-xl">inventory_2</span>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-white line-clamp-1">
                                        {{ $item->producto?->nombre ?? 'Producto' }}
                                    </h3>
                                    @if($item->variante && $item->variante->opciones->isNotEmpty())
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 truncate">
                                            {{ $item->variante->opciones->map(fn($o) => $o->valor)->join(' · ') }}
                                        </p>
                                    @endif
                                    <div class="flex items-center justify-between mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        <span>Cantidad: <strong class="text-slate-700 dark:text-slate-300">{{ $item->cantidad }}</strong></span>
                                        <span class="font-bold text-slate-900 dark:text-white">${{ number_format($item->subtotal, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800 space-y-2">
                        <div class="flex justify-between items-center text-xs text-slate-500 dark:text-slate-400">
                            <span>Fecha de compra:</span>
                            <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $pedido->creado_en->translatedFormat('d \d\e F, Y') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs text-slate-500 dark:text-slate-400">
                            <span>Total abonado:</span>
                            <span class="font-extrabold text-slate-900 dark:text-white">${{ number_format($pedido->total, 2) }} USD</span>
                        </div>
                    </div>
                </div>

                {{-- CARD: Indicador de Confianza y Garantía --}}
                <div class="bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/50 rounded-3xl p-5 sm:p-6 text-emerald-900 dark:text-emerald-200 flex items-start gap-3.5 shadow-2xs">
                    <span class="material-symbols-outlined text-emerald-600 dark:text-emerald-400 text-2xl shrink-0 mt-0.5">verified_user</span>
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-900 dark:text-emerald-300 mb-1">Garantía y Devolución Segura</h4>
                        <p class="text-xs text-emerald-700 dark:text-emerald-400 leading-relaxed">
                            Tu solicitud será revisada por nuestro equipo en menos de 24 horas hábiles bajo las normas de protección al consumidor de la República de Panamá.
                        </p>
                    </div>
                </div>

            </div>

            {{-- Columna Derecha (lg:col-span-7): Formulario estilizado --}}
            <div class="lg:col-span-7">
                <div class="bg-white dark:bg-[#1f2327] rounded-3xl border border-slate-200/90 dark:border-slate-800 p-6 sm:p-8 shadow-xs">
                    <form action="{{ route('cliente.perfil.pedidos.devolucion.store', $pedido->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        
                        {{-- Motivo --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2" for="motivo">
                                Motivo de la Devolución <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <select class="block w-full rounded-2xl border {{ $errors->has('motivo') ? 'border-rose-400' : 'border-slate-200 dark:border-slate-700' }} bg-white dark:bg-slate-900 text-slate-900 dark:text-white py-3 pl-4 pr-10 focus:border-emerald-600 focus:ring-emerald-600 focus:ring-1 appearance-none text-xs sm:text-sm transition-colors cursor-pointer" id="motivo" name="motivo" required>
                                    <option disabled selected value="">Selecciona un motivo principal...</option>
                                    <option value="defectuoso" {{ old('motivo') == 'defectuoso' ? 'selected' : '' }}>Producto Defectuoso o Dañado</option>
                                    <option value="incorrecto" {{ old('motivo') == 'incorrecto' ? 'selected' : '' }}>Producto Incorrecto (No coincide con lo pedido)</option>
                                    <option value="talla_incorrecta" {{ old('motivo') == 'talla_incorrecta' ? 'selected' : '' }}>Talla o Dimensión no sirve</option>
                                    <option value="arrepentimiento" {{ old('motivo') == 'arrepentimiento' ? 'selected' : '' }}>Arrepentimiento de compra</option>
                                    <option value="otro" {{ old('motivo') == 'otro' ? 'selected' : '' }}>Otro Motivo</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                    <span class="material-symbols-outlined text-[20px]">expand_more</span>
                                </div>
                            </div>
                            @error('motivo')
                                <p class="text-xs text-rose-500 mt-1.5 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">error</span>
                                    <span>{{ $message }}</span>
                                </p>
                            @enderror
                        </div>

                        {{-- Descripción detallada --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2" for="descripcion">
                                Explicación Detallada <span class="text-rose-500">*</span>
                            </label>
                            <textarea class="block w-full rounded-2xl border {{ $errors->has('descripcion') ? 'border-rose-400' : 'border-slate-200 dark:border-slate-700' }} bg-white dark:bg-slate-900 text-slate-900 dark:text-white p-4 focus:border-emerald-600 focus:ring-emerald-600 focus:ring-1 text-xs sm:text-sm transition-colors resize-none placeholder:text-slate-400" id="descripcion" name="descripcion" placeholder="Por favor, describe con claridad el motivo de tu solicitud o el inconveniente presentado con el pedido..." rows="4" required>{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <p class="text-xs text-rose-500 mt-1.5 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">error</span>
                                    <span>{{ $message }}</span>
                                </p>
                            @enderror
                        </div>

                        {{-- Evidencia Fotográfica --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1">
                                Evidencia Fotográfica <span class="text-slate-400 font-normal lowercase">(opcional pero recomendado)</span>
                            </label>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-2.5">
                                Adjuntar una foto acelera la aprobación de tu devolución por parte de nuestro equipo.
                            </p>
                            
                            <div class="mt-1 flex justify-center px-6 pt-6 pb-6 border-2 {{ $errors->has('foto_evidencia') ? 'border-rose-400' : 'border-slate-200 dark:border-slate-700' }} border-dashed rounded-3xl bg-slate-50/70 dark:bg-slate-800/40 hover:border-emerald-600 hover:bg-emerald-50/20 dark:hover:bg-emerald-950/20 transition-all cursor-pointer group" onclick="document.getElementById('foto_evidencia').click()">
                                <div class="space-y-1.5 text-center">
                                    <div class="w-12 h-12 mx-auto rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-400 group-hover:text-emerald-600 group-hover:border-emerald-500 shadow-2xs transition-colors">
                                        <span class="material-symbols-outlined text-2xl">add_photo_alternate</span>
                                    </div>
                                    <div class="flex text-xs text-slate-600 dark:text-slate-300 justify-center">
                                        <span class="relative font-semibold text-emerald-600 dark:text-emerald-400 group-hover:underline">
                                            Haz clic para subir imagen
                                            <input accept="image/*" class="sr-only" id="foto_evidencia" name="foto_evidencia" type="file" onchange="previewImage(this)"/>
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-400">Archivos PNG, JPG o WEBP de hasta 10MB</p>
                                </div>
                            </div>
                            @error('foto_evidencia')
                                <p class="text-xs text-rose-500 mt-1.5 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[14px]">error</span>
                                    <span>{{ $message }}</span>
                                </p>
                            @enderror

                            {{-- Contenedor de Vista Previa --}}
                            <div class="mt-4 flex gap-4 hidden" id="image-preview-container">
                                <div class="relative w-28 h-28 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-2xs bg-white dark:bg-slate-800 p-1">
                                    <img id="preview-img" src="#" alt="Vista previa" class="w-full h-full object-cover rounded-xl">
                                    <button class="absolute top-2 right-2 bg-rose-600 hover:bg-rose-700 text-white rounded-full w-6 h-6 flex items-center justify-center shadow-md cursor-pointer transition-colors" type="button" onclick="clearImage()" title="Quitar imagen">
                                        <span class="material-symbols-outlined text-sm">close</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Acciones y Botones --}}
                        <div class="pt-6 flex flex-col-reverse sm:flex-row items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800">
                            <a href="{{ route('cliente.perfil.pedidos.detalle', $pedido->id) }}" wire:navigate
                               class="w-full sm:w-auto px-5 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl hover:border-emerald-600 hover:text-emerald-600 transition-colors text-center shadow-2xs">
                                Cancelar
                            </a>
                            <button class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-2xs transition-colors cursor-pointer" type="submit">
                                <span class="material-symbols-outlined text-[16px]">assignment_return</span>
                                <span>Enviar Solicitud de Devolución</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

    </div>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                document.getElementById('preview-img').src = e.target.result;
                document.getElementById('image-preview-container').classList.remove('hidden');
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }

    function clearImage() {
        var input = document.getElementById('foto_evidencia');
        if (input) input.value = "";
        var container = document.getElementById('image-preview-container');
        if (container) container.classList.add('hidden');
        var preview = document.getElementById('preview-img');
        if (preview) preview.src = "#";
    }
</script>
@endsection

