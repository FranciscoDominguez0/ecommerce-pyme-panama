<?php

namespace App\Mail;

use App\Models\Factura;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FacturaMail extends Mailable
{
    use Queueable, SerializesModels;

    public Factura $factura;
    public ?string $mensajePersonalizado;

    /**
     * Create a new message instance.
     */
    public function __construct(Factura $factura, ?string $mensajePersonalizado = null)
    {
        $this->factura = $factura;
        $this->mensajePersonalizado = $mensajePersonalizado;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Factura ' . $this->factura->numero . ' - PayMe Panamá',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.factura',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        if ($this->factura->pdf_ruta && \Storage::disk('local')->exists($this->factura->pdf_ruta)) {
            $attachments[] = Attachment::fromStorageDisk('local', $this->factura->pdf_ruta)
                ->as('Factura_' . $this->factura->numero . '.pdf')
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}
