<?php

namespace App\Mail;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PedidoEntregadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public Pedido $pedido;

    /**
     * Inicializa el correo con el pedido que fue entregado.
     */
    public function __construct(Pedido $pedido)
    {
        $this->pedido = $pedido;
    }

    /**
     * Define el asunto del correo de confirmación de entrega.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Tu pedido #' . $this->pedido->numero_pedido . ' ha sido entregado!',
        );
    }

    /**
     * Define la vista Blade para el mensaje de entrega.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.pedidos.entregado',
        );
    }

    /**
     * Retorna los adjuntos del correo (vacío para este mensaje).
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
