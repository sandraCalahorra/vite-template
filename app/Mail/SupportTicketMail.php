<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportTicketMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $ticket;

    /**
     * Create a new message instance.
     */
    public function __construct($ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), "Soporte Template"),
            cc: [
                new Address($this->ticket->email, $this->ticket->name)
            ],
            replyTo: [
                new Address('soporte.sandracalahorra@ecomputer.es', 'Soporte Template'),
                new Address($this->ticket->email, $this->ticket->name)
            ],
            subject: $this->ticket->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.support_ticket',
            with: [
                'name' => $this->ticket->name,
                'email' => $this->ticket->email,
                'subject' => $this->ticket->subject,
                'priority' => $this->ticket->priority ?? 'normal',
                'message_content' => $this->ticket->message,
                'ticket_url' => config('app.url') . '/tickets',
                'contactForm' => $this->ticket,
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
        $attachments = [];

        foreach ($this->ticket->files as $file) {
            $path = storage_path("app/private/files/tickets/{$this->ticket->id}/{$file->name}");
            if (file_exists($path)) {
                $attachments[] = Attachment::fromPath($path)
                    ->as($file->name);
            }
        }

        return $attachments;
    }
}
