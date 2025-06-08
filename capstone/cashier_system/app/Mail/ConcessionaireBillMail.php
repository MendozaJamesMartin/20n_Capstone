<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConcessionaireBillMail extends Mailable
{
    use Queueable, SerializesModels;

    public $bill_amount;
    public $utility_type;
    public $due_date;
    public $pdfContent;

    /**
     * Create a new message instance.
     */
    public function __construct($bill_amount, $utility_type, $due_date, $pdfContent)
    {
        $this->bill_amount = $bill_amount;
        $this->utility_type = $utility_type;
        $this->due_date = $due_date;
        $this->pdfContent = $pdfContent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Utility Bill Notice',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.bill_email',
            with: [
                'bill_amount' => $this->bill_amount,
                'utility_type' => $this->utility_type,
                'due_date' => $this->due_date
            ]
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
            Attachment::fromData(fn () => $this->pdfContent, 'BillingStatement.pdf')
            ->withMime('application/pdf'),
        ];
    }
}
