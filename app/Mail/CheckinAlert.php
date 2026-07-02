<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CheckinAlert extends Mailable
{
    use Queueable, SerializesModels;

    public string $userName;

    public function __construct(User $user)
    {
        $this->userName = $user->name;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'A gentle note from Ezefone',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.checkin-alert',
        );
    }
}
