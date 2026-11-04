<?php

namespace App\Mail;

use App\Models\Pedido;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PedidoConfirmadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Pedido $pedido
    ) {
        $this->pedido->loadMissing(['cliente', 'productos']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pago confirmado - Pedido #' . $this->pedido->id
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.pedido-confirmado'
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn () => Pdf::loadView(
                    'pdf.pedido',
                    ['pedido' => $this->pedido]
                )->output(),
                'pedido-' . $this->pedido->id . '.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
