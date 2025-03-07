<?php

namespace App\Mail;

use App\Models\Purchase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PurchaseConfirmation extends Mailable
{
    use Queueable, SerializesModels;
    public $purchase;
    protected $pdfData;

    /**
     * Create a new message instance.
     */
    public function __construct(Purchase $purchase, $pdfData)
    {
        $this->purchase = $purchase;
        $this->pdfData = $pdfData;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Your Purchase Invoice from Master Magical Key')
                    ->view('emails.invoice')
                    ->attachData($this->pdfData, 'Invoice-' . $this->purchase->invoice_number . '.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Purchase Confirmation',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'view.name',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
