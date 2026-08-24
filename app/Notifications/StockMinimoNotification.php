<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class StockMinimoNotification extends Notification
{
    use Queueable;

    public $producto;
    public $variante;

    /**
     * Create a new notification instance.
     */
    public function __construct($producto, $variante = null)
    {
        $this->producto = $producto;
        $this->variante = $variante;
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
        $nombreProducto = $this->producto->nombre;
        if ($this->variante) {
            $nombreProducto .= ' (' . $this->variante->valor . ')';
        }

        $stockActual = $this->variante ? $this->variante->stock : $this->producto->stock;

        return [
            'tipo' => 'stock_minimo',
            'titulo' => 'Stock Mínimo Alcanzado',
            'mensaje' => "El producto {$nombreProducto} ha llegado al stock mínimo ({$stockActual} disp).",
            'producto_id' => $this->producto->id,
            'variante_id' => $this->variante ? $this->variante->id : null,
            'url' => '/admin/productos/' . $this->producto->id . '/editar',
        ];
    }
}
