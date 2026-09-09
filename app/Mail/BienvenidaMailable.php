<?php

namespace App\Mail;

use App\Models\Usuario;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BienvenidaMailable extends Mailable
{
    use Queueable, SerializesModels;

    public Usuario $usuario;

    /**
     * Inicializa el correo de bienvenida con los datos del nuevo usuario.
     */
    public function __construct(Usuario $usuario)
    {
        $this->usuario = $usuario;
    }

    /**
     * Define el asunto del correo de bienvenida.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Bienvenido/a a PayMe!',
        );
    }

    /**
     * Define la vista Blade para el mensaje de bienvenida.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.bienvenida',
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
