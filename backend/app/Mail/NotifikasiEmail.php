<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotifikasiEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $greeting;
    public string $messageContent;
    public ?string $actionText;
    public ?string $actionUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $subject = 'Notifikasi Email',
        string $greeting = 'Halo!',
        string $messageContent = 'Ini adalah email otomatis yang dikirim dari aplikasi.',
        ?string $actionText = null,
        ?string $actionUrl = null,
    ) {
        $this->subject = $subject;
        $this->greeting = $greeting;
        $this->messageContent = $messageContent;
        $this->actionText = $actionText;
        $this->actionUrl = $actionUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.notifikasi',
            with: [
                'greeting' => $this->greeting,
                'messageContent' => $this->messageContent,
                'actionText' => $this->actionText,
                'actionUrl' => $this->actionUrl,
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
        return [];
    }
}
