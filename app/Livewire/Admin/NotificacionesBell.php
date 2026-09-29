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
        $this->cargarNotificaciones(false);
    }

    public function getListeners()
    {
        $userId = Auth::id();
        return [
            "echo-private:App.Models.Usuario.{$userId},.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated" => 'cargarNotificaciones',
        ];
    }

    public function cargarNotificaciones($playAudio = true)
    {
        $user = Auth::user();
        if ($user) {
            $conteoAnterior = $this->unreadCount;
            
            $this->notificaciones = $user->unreadNotifications()->take(15)->get();
            $this->unreadCount = $user->unreadNotifications()->count();
            
            if ($playAudio && $this->unreadCount > $conteoAnterior) {
                $this->dispatch('nueva-notificacion-recibida');
            }

            $nuevosPedidosCount = \App\Models\Pedido::whereNotExists(function ($query) {
                $query->select(\Illuminate\Support\Facades\DB::raw(1))
                      ->from('estados_pedido')
                      ->whereColumn('estados_pedido.pedido_id', 'pedidos.id')
                      ->whereNotIn('estados_pedido.estado', ['pendiente', 'pago_confirmado']);
            })->count();
            
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
