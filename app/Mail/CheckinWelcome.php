<?php

namespace App\Mail;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CheckinWelcome extends Mailable
{
    use Queueable, SerializesModels;

    public string $userName;
    public string $checkinTime;

    public function __construct(User $user)
    {
        $this->userName    = $user->name;
        $this->checkinTime = Carbon::createFromFormat('H:i', $user->checkin_time ?? '11:00')
                                   ->format('g:i A');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You\'re set up to receive Ezefone check-ins for ' . $this->userName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.checkin-welcome',
        );
    }
}
