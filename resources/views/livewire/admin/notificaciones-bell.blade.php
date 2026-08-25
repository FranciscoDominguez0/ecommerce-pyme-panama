<div class="relative shrink-0" 
     x-data="{ open: false }" 
     @click.outside="open = false"
     wire:poll.5s="cargarNotificaciones"
     @nueva-notificacion-recibida.window="
         console.log('🔔 Evento de notificación recibido en Livewire');
         let audio = document.getElementById('notification-sound');
         if (audio) {
             audio.play().then(() => console.log('🔊 Sonido reproducido con éxito')).catch(e => console.warn('⚠️ Audio bloqueado por el navegador. Debes hacer clic en la página primero:', e));
         }
     ">
     
    <!-- Audio para notificaciones -->
    <audio id="notification-sound" src="{{ asset('sounds/Notificacion.mp3') }}" preload="auto" style="display: none;"></audio>
    <!-- Notifications Toggle Button -->
    <button @click="open = !open" 
            class="relative p-1.5 rounded-lg transition-colors shrink-0 outline-none"
            :class="open ? 'bg-emerald-50 text-emerald-600' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100'"
            title="Notificaciones">
        <span class="material-symbols-outlined text-[20px] transition-transform duration-200" :class="open ? 'scale-110' : ''">
            notifications
        </span>
        @if($unreadCount > 0)
            <span class="absolute top-1.5 right-1.5 flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
            </span>
        @endif
    </button>

    <!-- Dropdown Panel -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-2"
         style="display:none;"
         class="absolute right-0 sm:-right-2 top-full mt-3 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-[100] origin-top-right overflow-hidden">
        
        <!-- Header -->
        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-white/50 backdrop-blur-sm">
            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                Notificaciones
                @if($unreadCount > 0)
                    <span class="bg-emerald-100 text-emerald-700 py-0.5 px-2 rounded-full text-[10px] font-bold">{{ $unreadCount }} nuevas</span>
                @endif
            </h3>
            @if($unreadCount > 0)
                <button wire:click="marcarTodasComoLeidas" class="text-[11px] font-semibold text-emerald-600 hover:text-emerald-700 transition-colors">
                    Marcar todas leídas
                </button>
            @endif
        </div>

        <!-- List -->
        <div class="max-h-[400px] overflow-y-auto overscroll-contain">
            @forelse($notificaciones as $notificacion)
                <div class="px-4 py-3 hover:bg-slate-50 transition-colors border-b border-slate-50 last:border-0 relative group {{ is_null($notificacion->read_at) ? 'bg-slate-50/50' : '' }}">
                    @if(is_null($notificacion->read_at))
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-emerald-500"></div>
                    @endif
                    
                    <div class="flex items-start gap-3">
                        <!-- Icon -->
                        <div class="shrink-0 mt-1 flex items-center justify-center w-8 h-8 rounded-full shadow-sm border
                            @if($notificacion->data['tipo'] === 'nuevo_pedido') bg-blue-50 border-blue-100 text-blue-600
                            @elseif($notificacion->data['tipo'] === 'stock_minimo') bg-amber-50 border-amber-100 text-amber-600
                            @elseif($notificacion->data['tipo'] === 'nueva_devolucion') bg-rose-50 border-rose-100 text-rose-600
                            @else bg-slate-50 border-slate-200 text-slate-600 @endif">
                            <span class="material-symbols-outlined text-[16px]">
                                @if($notificacion->data['tipo'] === 'nuevo_pedido') shopping_bag
                                @elseif($notificacion->data['tipo'] === 'stock_minimo') warning
                                @elseif($notificacion->data['tipo'] === 'nueva_devolucion') assignment_return
                                @else notifications @endif
                            </span>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <a href="#" 
                               wire:click.prevent="leerYRedirigir('{{ $notificacion->id }}', '{{ $notificacion->data['url'] ?? '#' }}')"
                               class="block focus:outline-none cursor-pointer">
                                <p class="text-xs font-bold text-slate-800 mb-0.5 truncate group-hover:text-emerald-600 transition-colors">
                                    {{ $notificacion->data['titulo'] ?? 'Notificación' }}
                                </p>
                                <p class="text-[11px] text-slate-500 leading-snug line-clamp-2">
                                    {{ $notificacion->data['mensaje'] ?? '' }}
                                </p>
                            </a>
                            <p class="text-[10px] font-medium text-slate-400 mt-1.5 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[12px]">schedule</span>
                                {{ $notificacion->created_at->diffForHumans() }}
                            </p>
                        </div>

                        <!-- Acciones (Marcar como leída individualmente) -->
                        @if(is_null($notificacion->read_at))
                            <button wire:click.stop="marcarComoLeida('{{ $notificacion->id }}')" 
                                    class="shrink-0 p-1 text-slate-300 hover:text-emerald-600 hover:bg-emerald-50 rounded transition-colors opacity-0 group-hover:opacity-100" 
                                    title="Marcar como leída">
                                <span class="material-symbols-outlined text-[14px]">done</span>
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-center flex flex-col items-center justify-center">
                    <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mb-3">
                        <span class="material-symbols-outlined text-slate-300 text-[24px]">notifications_paused</span>
                    </div>
                    <p class="text-xs font-semibold text-slate-500">No tienes notificaciones</p>
                    <p class="text-[10px] text-slate-400 mt-1">Cuando ocurra algo importante aparecerá aquí.</p>
                </div>
            @endforelse
        </div>
        
        <!-- Footer -->
        @if(count($notificaciones) > 0)
            <div class="px-4 py-2.5 border-t border-slate-100 bg-slate-50/50 text-center">
                <a href="#" class="text-[11px] font-bold text-slate-600 hover:text-emerald-600 transition-colors flex items-center justify-center gap-1 group">
                    Ver todas las notificaciones
                    <span class="material-symbols-outlined text-[14px] group-hover:translate-x-0.5 transition-transform">arrow_forward</span>
                </a>
            </div>
        @endif
    </div>
</div>
