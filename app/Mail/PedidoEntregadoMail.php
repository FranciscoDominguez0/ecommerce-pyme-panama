<?php

namespace App\Mail;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PedidoEntregadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pedido;

    public function __construct(Pedido $pedido)
    {
        $this->pedido = $pedido;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Tu pedido #' . $this->pedido->numero_pedido . ' ha sido entregado!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pedidos.entregado',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
