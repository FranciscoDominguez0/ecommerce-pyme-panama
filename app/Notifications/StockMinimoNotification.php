<?php

namespace App\Notifications;

use App\Models\Configuracion;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StockMinimoNotification extends Notification
{
    use Queueable;

    public $producto;
    public $variante;

    /**
     * Inicializa la notificación con el producto y variante afectados.
     */
    public function __construct($producto, $variante = null)
    {
        $this->producto = $producto;
        $this->variante = $variante;
    }

    /**
     * Define los canales de entrega (base de datos y/o correo según configuración).
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Guardamos en la base de datos para los usuarios registrados del panel
        $canales = $notifiable instanceof AnonymousNotifiable ? [] : ['database'];
        
        $activo = Configuracion::obtenerBool('notificaciones.stock.email.activo', false);
        if ($activo) {
            if ($notifiable instanceof AnonymousNotifiable) {
                // Notificación por correo bajo demanda a destinatarios externos
                $canales[] = 'mail';
            } elseif (method_exists($notifiable, 'hasAnyRole')) {
                // Usuarios del sistema: verificar si tienen el rol autorizado para recibir alertas
                $rolesSeleccionados = json_decode(Configuracion::obtener('notificaciones.stock.email.roles', '[]'), true) ?? [];
                if (!empty($rolesSeleccionados) && $notifiable->hasAnyRole($rolesSeleccionados)) {
                    $canales[] = 'mail';
                }
            }
        }
        
        return $canales;
    }

    /**
     * Construye la representación por correo electrónico de la alerta.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $nombreProducto = $this->producto->nombre;
        if ($this->variante) {
            $nombreProducto .= ' (' . $this->variante->valor . ')';
        }

        $stockActual = $this->variante ? $this->variante->stock : $this->producto->stock;
        $url = url('/admin/inventario/stock?buscar=' . urlencode($this->producto->sku ?? $this->producto->nombre));

        return (new MailMessage)
            ->subject('Alerta: Stock Mínimo Alcanzado - ' . $nombreProducto)
            ->view('emails.stock_minimo', [
                'nombreProducto' => $nombreProducto,
                'stockActual' => $stockActual,
                'url' => $url
            ]);
    }

    /**
     * Construye los datos estructurados para almacenar en la base de datos.
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
