<?php

namespace App\Mail;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecordatorioEntregaMail extends Mailable
{
    use Queueable, SerializesModels;

    public Pedido $pedido;

    /**
     * Inicializa el mailable con el pedido a consultar.
     */
    public function __construct(Pedido $pedido)
    {
        $this->pedido = $pedido;
    }

    /**
     * Define el asunto del correo con el número de pedido.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¿Ya recibiste tu pedido ' . $this->pedido->numero_pedido . '?',
        );
    }

    /**
     * Define la plantilla Markdown del mensaje de seguimiento.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.recordatorio_entrega',
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
