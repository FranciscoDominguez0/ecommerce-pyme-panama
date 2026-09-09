<?php

namespace App\Notifications;

use App\Models\Devolucion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NuevaDevolucionNotification extends Notification
{
    use Queueable;

    public $devolucion;

    /**
     * Inicializa la notificación con la devolución creada.
     */
    public function __construct(Devolucion $devolucion)
    {
        $this->devolucion = $devolucion;
    }

    /**
     * Define los canales de entrega (almacenamiento en base de datos).
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Prepara el arreglo de datos para la campanita del panel administrativo.
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
