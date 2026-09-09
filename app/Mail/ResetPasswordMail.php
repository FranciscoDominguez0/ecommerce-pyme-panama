<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $token;
    public $email;

    /**
     * Crea una nueva instancia de mensaje.
     */
    public function __construct(string $token, string $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    /**
     * Obtiene el sobre del mensaje (remitente, asunto).
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Restablece tu contraseña - ' . config('app.name'),
        );
    }

    /**
     * Obtiene la definición del contenido del mensaje.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.reset-password',
            with: [
                'url' => url(route('password.reset', [
                    'token' => $this->token,
                    'email' => $this->email,
                ], false)),
            ],
        );
    }
}
