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
        // Siempre guardar en la base de datos para los usuarios del sistema
        $canales = $notifiable instanceof \Illuminate\Notifications\AnonymousNotifiable ? [] : ['database'];
        
        $activo = \App\Models\Configuracion::obtenerBool('notificaciones.stock.email.activo', false);
        if ($activo) {
            if ($notifiable instanceof \Illuminate\Notifications\AnonymousNotifiable) {
                // Correos adicionales (on-demand)
                $canales[] = 'mail';
            } elseif (method_exists($notifiable, 'hasAnyRole')) {
                // Usuarios del sistema: verificar si tienen el rol configurado para correos
                $rolesSeleccionados = json_decode(\App\Models\Configuracion::obtener('notificaciones.stock.email.roles', '[]'), true) ?? [];
                if (!empty($rolesSeleccionados) && $notifiable->hasAnyRole($rolesSeleccionados)) {
                    $canales[] = 'mail';
                }
            }
        }
        
        return $canales;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): \Illuminate\Notifications\Messages\MailMessage
    {
        $nombreProducto = $this->producto->nombre;
        if ($this->variante) {
            $nombreProducto .= ' (' . $this->variante->valor . ')';
        }

        $stockActual = $this->variante ? $this->variante->stock : $this->producto->stock;
        $url = url('/admin/inventario/stock?buscar=' . urlencode($this->producto->sku ?? $this->producto->nombre));

        return (new \Illuminate\Notifications\Messages\MailMessage)
                    ->subject('Alerta: Stock Mínimo Alcanzado - ' . $nombreProducto)
                    ->view('emails.stock_minimo', [
                        'nombreProducto' => $nombreProducto,
                        'stockActual' => $stockActual,
                        'url' => $url
                    ]);
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
