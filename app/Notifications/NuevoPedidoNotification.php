<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Pedido;

class NuevoPedidoNotification extends Notification
{
    use Queueable;

    public $pedido;

    /**
     * Create a new notification instance.
     */
    public function __construct(Pedido $pedido)
    {
        $this->pedido = $pedido;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'tipo' => 'nuevo_pedido',
            'titulo' => 'Nuevo Pedido',
            'mensaje' => 'Se ha creado el pedido ' . $this->pedido->numero_pedido . '.',
            'pedido_id' => $this->pedido->id,
            'numero_pedido' => $this->pedido->numero_pedido,
            'total' => $this->pedido->total,
            'cliente' => $this->pedido->usuario->nombre_completo ?? $this->pedido->usuario->nombre ?? 'Cliente',
            'url' => '/admin/pedidos/' . $this->pedido->id,
        ];
    }
}
