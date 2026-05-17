<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AdminMagicLoginLink extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $url,
        public int $ttlMinutes,
        public string $ipAddress,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Вход в админку — одноразовая ссылка',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.admin-magic-login',
            with: [
                'url' => $this->url,
                'ttlMinutes' => $this->ttlMinutes,
                'ipAddress' => $this->ipAddress,
            ],
        );
    }
}
