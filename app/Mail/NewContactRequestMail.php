<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\ContactRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewContactRequestMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(public ContactRequest $lead)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Новая заявка с сайта — ' . $this->lead->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.new-contact-request',
            with: ['lead' => $this->lead],
        );
    }
}
