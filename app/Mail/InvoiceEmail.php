<?php

namespace App\Mail;

use App\Models\Purchase;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class InvoiceEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The purchase instance.
     *
     * @var Purchase
     */
    public $purchase;
    
    /**
     * The binary PDF data.
     *
     * @var string
     */
    protected $pdfData;

    /**
     * Create a new message instance.
     */
    public function __construct(Purchase $purchase, $pdfData)
    {
        $this->purchase = $purchase;
        $this->pdfData = $pdfData;
        
        Log::info('InvoiceEmail instantiated', [
            'invoice_number' => $purchase->invoice_number,
            'pdf_size' => strlen($pdfData)
        ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your Purchase Invoice #{$this->purchase->invoice_number} - Master Magical Key",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
            with: [
                'purchase' => $this->purchase,
                'user' => $this->purchase->user,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn() => $this->pdfData, "Invoice-{$this->purchase->invoice_number}.pdf")
                ->withMime('application/pdf'),
        ];
    }

    /**
     * Build the message.
     */
    public function build()
    {
        try {
            Log::info('Building invoice email', [
                'invoice_number' => $this->purchase->invoice_number,
                'email' => $this->purchase->user->email
            ]);
            
            return $this->subject("Your Purchase Invoice #{$this->purchase->invoice_number} - Master Magical Key")
                        ->view('emails.invoice')
                        ->attachData($this->pdfData, "Invoice-{$this->purchase->invoice_number}.pdf", [
                            'mime' => 'application/pdf',
                        ]);
        } catch (\Exception $e) {
            Log::error('Error building invoice email', [
                'invoice_number' => $this->purchase->invoice_number,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
}