@extends('layouts.cliente')
@section('title', 'Solicitar Devolución')

@section('content')
<div class="w-full max-w-6xl mx-auto px-4 sm:px-6 py-8">
    <!-- Encabezado -->
    <div class="mb-6">
        <a href="{{ route('cliente.perfil.pedidos.detalle', $pedido->id) }}" class="inline-flex items-center gap-2 text-on-surface hover:text-primary transition-colors mb-4">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            <span class="font-label-caps text-xs font-bold uppercase tracking-wider">Volver al pedido</span>
        </a>
        <h1 class="text-2xl font-bold text-primary mb-2">Solicitar Devolución</h1>
        <p class="text-sm text-on-surface-variant">Complete el siguiente formulario para procesar su solicitud de retorno.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Columna Izquierda: Detalles del Pedido -->
        <div class="lg:col-span-4 space-y-4">
            <div class="bg-white border border-outline-variant rounded-xl p-6 shadow-sm">
                <div class="flex justify-between items-center mb-4 pb-4 border-b border-outline-variant">
                    <span class="font-label-caps text-xs text-on-surface-variant uppercase tracking-wider">Detalles del Pedido</span>
                    <span class="font-mono text-sm font-bold text-primary">{{ $pedido->numero_pedido }}</span>
                </div>
                
                @foreach($pedido->items as $item)
                    <div class="flex gap-4 items-start mb-4">
                        <div class="w-16 h-16 bg-surface-container-low rounded-lg border border-outline-variant overflow-hidden shrink-0 flex items-center justify-center">
                            @php
                                $imgUrl = null;
                                if ($item->variante && $item->variante->imagen_ruta) {
                                    $imgUrl = asset('storage/' . $item->variante->imagen_ruta);
                                } else if ($item->producto && $item->producto->imagenes->isNotEmpty()) {
                                    $imgUrl = $item->producto->imagen_url;
                                }
                            @endphp
                            
                            @if($imgUrl)
                                <img src="{{ $imgUrl }}" class="w-full h-full object-cover">
                            @else
                                <span class="material-symbols-outlined text-outline">image</span>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-primary">{{ $item->producto->nombre }}</h3>
                            @if($item->variante)
                                <p class="text-xs text-on-surface-variant mt-0.5">
                                    {{ $item->variante->opciones->map(fn($o) => $o->valor)->join(' / ') }}
                                </p>
                            @endif
                            <p class="text-xs text-on-surface-variant mt-0.5">Cant: {{ $item->cantidad }}</p>
                        </div>
                    </div>
                @endforeach

                <div class="mt-4 pt-4 border-t border-outline-variant">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-on-surface-variant">Fecha de compra</span>
                        <span class="text-sm font-bold text-primary">{{ $pedido->creado_en->format('d M Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Indicador de Confianza -->
            <div class="bg-blue-50 text-blue-900 rounded-xl p-4 flex items-start gap-3">
                <span class="material-symbols-outlined text-blue-600">lock</span>
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider mb-1">Devolución Segura</h4>
                    <p class="text-[13px] opacity-90">Su solicitud será procesada bajo nuestros estándares de protección al consumidor.</p>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Formulario -->
        <div class="lg:col-span-8">
            <div class="bg-white/70 backdrop-blur-md border border-outline-variant/80 rounded-xl p-6 md:p-8">
                <form action="{{ route('cliente.perfil.pedidos.devolucion.store', $pedido->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    
                    <!-- Motivo -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-primary mb-2" for="motivo">Motivo de devolución <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <select class="block w-full rounded-lg border {{ $errors->has('motivo') ? 'border-red-400' : 'border-outline-variant' }} bg-white text-primary py-3 pl-4 pr-10 focus:border-emerald-600 focus:ring-emerald-600 focus:ring-1 appearance-none text-sm transition-colors" id="motivo" name="motivo" required>
                                <option disabled selected value="">Seleccione un motivo...</option>
                                <option value="defectuoso" {{ old('motivo') == 'defectuoso' ? 'selected' : '' }}>Producto Defectuoso</option>
                                <option value="incorrecto" {{ old('motivo') == 'incorrecto' ? 'selected' : '' }}>Producto Incorrecto</option>
                                <option value="talla_incorrecta" {{ old('motivo') == 'talla_incorrecta' ? 'selected' : '' }}>Talla/Tamaño no sirve</option>
                                <option value="arrepentimiento" {{ old('motivo') == 'arrepentimiento' ? 'selected' : '' }}>Arrepentimiento de compra</option>
                                <option value="otro" {{ old('motivo') == 'otro' ? 'selected' : '' }}>Otro Motivo</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-on-surface-variant">
                                <span class="material-symbols-outlined">expand_more</span>
                            </div>
                        </div>
                        @error('motivo')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-primary mb-2" for="descripcion">Descripción detallada <span class="text-red-500">*</span></label>
                        <textarea class="block w-full rounded-lg border {{ $errors->has('descripcion') ? 'border-red-400' : 'border-outline-variant' }} bg-white text-primary p-4 focus:border-emerald-600 focus:ring-emerald-600 focus:ring-1 text-sm transition-colors resize-none" id="descripcion" name="descripcion" placeholder="Por favor, explique detalladamente el problema con su pedido..." rows="4" required>{{ old('descripcion') }}</textarea>
                        @error('descripcion')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Evidencia -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-primary mb-2">Evidencia Fotográfica (Opcional pero recomendado)</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 {{ $errors->has('foto_evidencia') ? 'border-red-400' : 'border-outline-variant' }} border-dashed rounded-xl bg-slate-50 hover:border-emerald-600 hover:bg-emerald-50/10 transition-all cursor-pointer group" onclick="document.getElementById('foto_evidencia').click()">
                            <div class="space-y-1 text-center">
                                <span class="material-symbols-outlined text-4xl text-slate-400 group-hover:text-emerald-600 mb-2">add_photo_alternate</span>
                                <div class="flex text-sm text-slate-600 justify-center">
                                    <span class="relative font-medium text-primary group-hover:text-emerald-600">
                                        Subir un archivo
                                        <input accept="image/*" class="sr-only" id="foto_evidencia" name="foto_evidencia" type="file" onchange="previewImage(this)"/>
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500">PNG, JPG hasta 10MB</p>
                            </div>
                        </div>
                        @error('foto_evidencia')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror

                        <!-- Preview Area -->
                        <div class="mt-4 flex gap-4 hidden" id="image-preview-container">
                            <div class="relative w-24 h-24 rounded-lg border border-outline-variant overflow-hidden">
                                <img id="preview-img" src="#" alt="Preview" class="w-full h-full object-cover">
                                <button class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center" type="button" onclick="clearImage()">
                                    <span class="material-symbols-outlined text-sm">close</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="pt-6 flex flex-col sm:flex-row items-center justify-end gap-4 border-t border-outline-variant">
                        <a href="{{ route('cliente.perfil.pedidos.detalle', $pedido->id) }}" class="w-full sm:w-auto px-6 py-2.5 bg-transparent border border-primary text-primary text-xs font-bold uppercase tracking-wider rounded-lg hover:bg-slate-50 transition-colors text-center">
                            Cancelar
                        </a>
                        <button class="w-full sm:w-auto px-8 py-2.5 bg-emerald-600 text-white text-xs font-bold uppercase tracking-wider rounded-lg shadow-sm hover:bg-emerald-700 transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2" type="submit">
                            Solicitar devolución
                        </button>
                    </div>
                </form>
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
        document.getElementById('foto_evidencia').value = "";
        document.getElementById('image-preview-container').classList.add('hidden');
        document.getElementById('preview-img').src = "#";
    }
</script>
@endsection
