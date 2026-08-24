<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Devolucion;

class NuevaDevolucionNotification extends Notification
{
    use Queueable;

    public $devolucion;

    /**
     * Create a new notification instance.
     */
    public function __construct(Devolucion $devolucion)
    {
        $this->devolucion = $devolucion;
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
        $numeroPedido = $this->devolucion->pedido->numero_pedido ?? 'Desconocido';
        return [
            'tipo' => 'nueva_devolucion',
            'titulo' => 'Nueva Solicitud de Devolución',
            'mensaje' => "El cliente ha solicitado una devolución para el pedido {$numeroPedido}.",
            'devolucion_id' => $this->devolucion->id,
            'pedido_id' => $this->devolucion->pedido_id,
            'url' => '/admin/devoluciones',
        ];
    }
}
