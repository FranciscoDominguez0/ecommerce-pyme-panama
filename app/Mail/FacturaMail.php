<?php

namespace App\Mail;

use App\Models\Factura;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class FacturaMail extends Mailable
{
    use Queueable, SerializesModels;

    public Factura $factura;
    public ?string $mensajePersonalizado;

    /**
     * Inicializa el mailable con los datos de la factura y mensaje opcional.
     */
    public function __construct(Factura $factura, ?string $mensajePersonalizado = null)
    {
        $this->factura = $factura;
        $this->mensajePersonalizado = $mensajePersonalizado;
    }

    /**
     * Define el asunto y remitente del correo.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Factura ' . $this->factura->numero . ' - PayMe Panamá',
        );
    }

    /**
     * Define la vista Blade para el cuerpo del correo.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.factura',
        );
    }

    /**
     * Adjunta el archivo PDF de la factura si existe en el almacenamiento local.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        if ($this->factura->pdf_ruta && Storage::disk('local')->exists($this->factura->pdf_ruta)) {
            $attachments[] = Attachment::fromStorageDisk('local', $this->factura->pdf_ruta)
                ->as('Factura_' . $this->factura->numero . '.pdf')
                ->withMime('application/pdf');
        }

        return $attachments;
    }
}
