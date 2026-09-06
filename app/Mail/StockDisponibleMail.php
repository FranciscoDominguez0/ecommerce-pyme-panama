<?php

namespace App\Mail;

use App\Models\Producto;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StockDisponibleMail extends Mailable
{
    use Queueable, SerializesModels;

    public Producto $producto;

    /**
     * Crea una nueva instancia del mensaje.
     */
    public function __construct(Producto $producto)
    {
        $this->producto = $producto;
    }

    /**
     * Define el asunto del correo.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Ya hay stock disponible! ' . $this->producto->nombre . ' - ' . config('app.name', 'PayMe Panamá'),
        );
    }

    /**
     * Define la vista del correo.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.stock-disponible',
        );
    }
}
