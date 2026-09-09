<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TwoFactorCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $code;
    public string $nombreUsuario;

    /**
     * Inicializa el mailable con el código de seguridad y el nombre del usuario.
     */
    public function __construct(string $code, string $nombreUsuario)
    {
        $this->code = $code;
        $this->nombreUsuario = $nombreUsuario;
    }

    /**
     * Define el asunto del correo de verificación.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Código de Verificación - PayMe Panamá',
        );
    }

    /**
     * Define la plantilla Blade y variables del mensaje.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.2fa-code',
            with: [
                'code' => $this->code,
                'nombreUsuario' => $this->nombreUsuario,
            ]
        );
    }

    /**
     * Retorna los adjuntos del correo (vacío para este tipo de mensaje).
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
