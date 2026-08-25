<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Notifications\DatabaseNotification;

class NotificacionesBell extends Component
{
    public $notificaciones = [];
    public $unreadCount = 0;

    public function mount()
    {
        $this->cargarNotificaciones();
    }

    #[On('echo:admin,.NuevaNotificacion')]
    public function cargarNotificaciones()
    {
        $user = Auth::user();
        if ($user) {
            $conteoAnterior = $this->unreadCount;
            
            $this->notificaciones = $user->unreadNotifications()->take(15)->get();
            $this->unreadCount = $user->unreadNotifications()->count();
            
            if ($this->unreadCount > $conteoAnterior) {
                $this->dispatch('nueva-notificacion-recibida');
            }
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
