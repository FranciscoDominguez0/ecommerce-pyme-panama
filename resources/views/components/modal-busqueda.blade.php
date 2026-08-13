@props([
    'id' => 'modal-selector',
    'titulo' => 'Seleccionar',
    'subtitulo' => 'Busca y selecciona una opción de la lista',
    'icono' => 'search',
    'placeholder' => 'Buscar...',
    'porPagina' => 15,
    'containerClass' => 'space-y-1.5',
])

<!-- Componente Reutilizable: Modal de Búsqueda Paginado (15 items por página con navegación) -->
<div id="{{ $id }}" 
     class="fixed inset-0 w-screen h-screen z-[9999] hidden items-center justify-center p-3 sm:p-4" 
     style="background-color: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px);"
     onclick="if(event.target === this) window.ModalBuscador.cerrar('{{ $id }}');">
    
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-lg max-h-[85vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150 relative z-10">
        
        <!-- Modal Header -->
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/70 shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-slate-900 text-white flex items-center justify-center shadow-2xs">
                    <span class="material-symbols-outlined text-[18px]">{{ $icono }}</span>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-900" id="{{ $id }}-titulo-text">{{ $titulo }}</h3>
                    <p class="text-[10px] text-slate-500">{{ $subtitulo }}</p>
                </div>
            </div>
            <button type="button" 
                    onclick="window.ModalBuscador.cerrar('{{ $id }}')" 
                    class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-slate-100 transition-colors cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        <!-- Buscador y Acciones Superiores -->
        <div class="p-3 border-b border-slate-100 bg-white shrink-0 space-y-2">
            <div class="relative flex items-center">
                <span class="material-symbols-outlined absolute left-3 text-slate-400 text-[16px]">search</span>
                <input type="text" 
                       id="{{ $id }}-input-buscar" 
                       oninput="window.ModalBuscador.filtrar('{{ $id }}', this.value)" 
                       placeholder="{{ $placeholder }}" 
                       class="w-full pl-9 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/20 text-slate-800 font-medium">
            </div>

            @if(isset($headerExtra))
                <div class="pt-0.5">
                    {{ $headerExtra }}
                </div>
            @endif
        </div>

        <!-- Contenedor Paginado de Resultados (15 items por página) -->
        <div class="p-3 overflow-y-auto flex-1 max-h-[50vh] {{ $containerClass }}" id="{{ $id }}-lista-contenido" data-limit="{{ $porPagina }}">
            {{ $slot ?? '' }}
        </div>

        <!-- Footer del Modal con Controles de Paginación (< Anterior | Pág X de Y | Siguiente >) -->
        <div class="px-4 py-2.5 border-t border-slate-100 bg-slate-50/70 flex items-center justify-between shrink-0 flex-wrap gap-2">
            <span class="text-[11px] font-medium text-slate-500" id="{{ $id }}-info-contador">Mostrando resultados</span>
            
            <div class="flex items-center gap-2">
                <div id="{{ $id }}-paginacion-controles" class="flex items-center gap-1">
                    <!-- Botones de navegación de página -->
                </div>

                @if(isset($footerExtra))
                    {{ $footerExtra }}
                @endif

                <button type="button" 
                        onclick="window.ModalBuscador.cerrar('{{ $id }}')" 
                        class="px-3 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-200 rounded-xl transition-colors cursor-pointer">
                    Cerrar
                </button>
            </div>
        </div>

    </div>
</div>

<script>
    if (!window.ModalBuscador) {
        window.ModalBuscador = {
            registros: {},

            init(id, config) {
                this.registros[id] = {
                    items: config.items || [],
                    pagina: 1,
                    porPagina: config.porPagina || 15,
                    filtro: '',
                    render: config.render || null,
                    onSelect: config.onSelect || null,
                    emptyText: config.emptyText || 'No se encontraron resultados'
                };
            },

            abrir(id) {
                const modal = document.getElementById(id);
                if (!modal) return;

                // Mover el modal directamente al <body> para asegurar que cubra el 100% del viewport
                if (modal.parentElement !== document.body) {
                    document.body.appendChild(modal);
                }

                modal.classList.remove('hidden');
                modal.classList.add('flex');

                const input = document.getElementById(`${id}-input-buscar`);
                if (input) {
                    input.value = '';
                    setTimeout(() => input.focus(), 50);
                }

                if (this.registros[id]) {
                    this.registros[id].filtro = '';
                    this.registros[id].pagina = 1;
                    this.renderizar(id);
                }
            },

            cerrar(id) {
                const modal = document.getElementById(id);
                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            },

            filtrar(id, texto) {
                if (this.registros[id]) {
                    this.registros[id].filtro = texto.trim().toLowerCase();
                    this.registros[id].pagina = 1;
                    this.renderizar(id);
                }
            },

            cambiarPagina(id, nuevaPagina) {
                if (this.registros[id]) {
                    this.registros[id].pagina = nuevaPagina;
                    this.renderizar(id);
                }
            },

            renderizar(id) {
                const reg = this.registros[id];
                if (!reg) return;

                const contenedor = document.getElementById(`${id}-lista-contenido`);
                const contador = document.getElementById(`${id}-info-contador`);
                const controles = document.getElementById(`${id}-paginacion-controles`);
                if (!contenedor) return;

                let filtrados = reg.items;
                if (reg.filtro) {
                    filtrados = reg.items.filter(item => {
                        const texto = typeof item === 'string' ? item : (item.nombre || item.name || item.titulo || '');
                        return texto.toLowerCase().includes(reg.filtro);
                    });
                }

                const total = filtrados.length;
                const limite = reg.porPagina || 15;
                const totalPaginas = Math.ceil(total / limite) || 1;

                if (reg.pagina > totalPaginas) reg.pagina = totalPaginas;
                if (reg.pagina < 1) reg.pagina = 1;

                const inicio = (reg.pagina - 1) * limite;
                const fin = Math.min(inicio + limite, total);
                const mostrados = filtrados.slice(inicio, fin);

                if (contador) {
                    contador.textContent = total > 0 
                        ? `Mostrando ${inicio + 1} - ${fin} de ${total}`
                        : '0 resultados';
                }

                if (controles) {
                    controles.innerHTML = '';
                    if (totalPaginas > 1) {
                        controles.innerHTML = `
                            <button type="button" 
                                    onclick="window.ModalBuscador.cambiarPagina('${id}', ${reg.pagina - 1})" 
                                    ${reg.pagina === 1 ? 'disabled' : ''} 
                                    class="px-2 py-0.5 text-[11px] font-semibold rounded-lg border border-slate-200 bg-white hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-all">
                                Anterior
                            </button>
                            <span class="text-[10.5px] font-bold text-slate-700 px-1">Pág. ${reg.pagina} de ${totalPaginas}</span>
                            <button type="button" 
                                    onclick="window.ModalBuscador.cambiarPagina('${id}', ${reg.pagina + 1})" 
                                    ${reg.pagina === totalPaginas ? 'disabled' : ''} 
                                    class="px-2 py-0.5 text-[11px] font-semibold rounded-lg border border-slate-200 bg-white hover:bg-slate-100 disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-all">
                                Siguiente
                            </button>
                        `;
                    }
                }

                contenedor.innerHTML = '';

                if (mostrados.length === 0) {
                    contenedor.innerHTML = `
                        <div class="py-6 text-center text-slate-400 text-xs">
                            <p>${reg.emptyText} "${reg.filtro}".</p>
                        </div>
                    `;
                    return;
                }

                mostrados.forEach((item, idx) => {
                    let el = null;
                    if (reg.render) {
                        el = reg.render(item, idx);
                    } else {
                        el = this.renderPorDefecto(id, item, idx);
                    }
                    if (el) contenedor.appendChild(el);
                });
            },

            renderPorDefecto(id, item, idx) {
                const reg = this.registros[id];
                const nombre = typeof item === 'string' ? item : (item.nombre || item.name || item.titulo || '');
                const sub = item.subtitulo || item.ruta_padres || item.descripcion || '';
                const card = document.createElement('div');
                
                const contenedor = document.getElementById(`${id}-lista-contenido`);
                const esGrid = contenedor && contenedor.classList.contains('grid');

                if (esGrid) {
                    card.className = `p-3 rounded-xl border transition-all cursor-pointer flex flex-col items-center justify-center text-center gap-1.5 group bg-white border-slate-200/90 hover:border-emerald-500 hover:bg-emerald-50/40 hover:shadow-2xs`;
                    let logoHtml = `<span class="font-bold text-xs text-slate-700">${nombre.substring(0, 2).toUpperCase()}</span>`;
                    if (window.getLogoHtmlForBrand && (item.slug || item.url || item.nombre)) {
                        logoHtml = window.getLogoHtmlForBrand(item);
                    } else if (item.url) {
                        logoHtml = `<img src="${item.url}" alt="${nombre}" class="h-6 object-contain">`;
                    }
                    card.innerHTML = `
                        <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center p-1 shrink-0 overflow-hidden shadow-2xs group-hover:scale-105 transition-transform">
                            ${logoHtml}
                        </div>
                        <span class="text-xs font-bold text-slate-800 group-hover:text-emerald-950 truncate max-w-full leading-tight">${nombre}</span>
                    `;
                } else {
                    card.className = `p-2.5 rounded-xl border transition-all cursor-pointer flex items-center justify-between group bg-white border-slate-200/90 hover:border-emerald-500 hover:bg-emerald-50/40 hover:shadow-2xs`;
                    
                    if (item.nivel && item.nivel > 0) {
                        card.style.marginLeft = `${Math.min(item.nivel * 16, 48)}px`;
                    }

                    let iconHtml = `<span class="material-symbols-outlined text-[16px]">${item.nivel > 0 ? 'subdirectory_arrow_right' : (item.icono || 'folder')}</span>`;
                    
                    if (item.imagen_ruta && !window.getImageHtmlForCategory) {
                        iconHtml = `<img src="${item.imagen_ruta}" alt="${nombre}" class="w-full h-full object-cover">`;
                    } else if (window.getImageHtmlForCategory && item.imagen_ruta) {
                        iconHtml = window.getImageHtmlForCategory(item);
                    }

                    card.innerHTML = `
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 group-hover:bg-emerald-600 group-hover:text-white flex items-center justify-center shrink-0 transition-colors shadow-2xs overflow-hidden p-0.5">
                                ${iconHtml}
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs font-bold text-slate-800 group-hover:text-emerald-950 truncate">${nombre}</div>
                                ${sub ? `<div class="text-[10px] text-slate-400 font-medium truncate">${sub}</div>` : ''}
                            </div>
                        </div>
                    `;
                }

                if (reg && reg.onSelect) {
                    card.onclick = () => reg.onSelect(item);
                }

                return card;
            }
        };
    }
</script>
