<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class NotificacionesBell extends Component
{
    public $notificaciones = [];
    public $conteoNoLeidas = 0;

    public function mount()
    {
        $this->cargarNotificaciones(false);
    }



    public function cargarNotificaciones($playAudio = false)
    {
        $user = Auth::user();
        if ($user) {
            $this->notificaciones = $user->unreadNotifications()->take(15)->get();
            $this->conteoNoLeidas = $user->unreadNotifications()->count();
            
            if ($playAudio) {
                $this->dispatch('nueva-notificacion-recibida');
            }

            $nuevosPedidosCount = \App\Models\Pedido::whereNull('leido_en')->count();
            
            $nuevasDevolucionesCount = \App\Models\Devolucion::where('estado', 'pendiente')->count();

            $this->dispatch('actualizar-badges-sidebar', pedidos: $nuevosPedidosCount, devoluciones: $nuevasDevolucionesCount);
        }
    }

    public function marcarComoLeida($id)
    {
        $user = Auth::user();
        if ($user) {
            $notificacion = $user->notifications()->find($id);
            if ($notificacion) {
                $notificacion->markAsRead();
            }
            $this->cargarNotificaciones();
        }
    }

    public function leerYRedirigir($id, $url)
    {
        $this->marcarComoLeida($id);
        return redirect()->to($url);
    }

    public function marcarTodasComoLeidas()
    {
        $user = Auth::user();
        if ($user) {
            $user->unreadNotifications->markAsRead();
            $this->cargarNotificaciones();
        }
    }

    public function render()
    {
        return view('livewire.admin.notificaciones-bell');
    }
}
