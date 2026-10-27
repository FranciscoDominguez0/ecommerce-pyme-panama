<?php

namespace App\Notifications;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class NuevoPedidoNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $pedido;

    /**
     * Inicializa la notificación con el pedido creado.
     */
    public function __construct(Pedido $pedido)
    {
        $this->pedido = $pedido;
    }

    /**
     * Define los canales de entrega (almacenamiento en base de datos).
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Prepara el arreglo de datos para la campanita del panel administrativo.
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
