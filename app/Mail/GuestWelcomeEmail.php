<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GuestWelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $tempPassword;

    public function __construct(User $user, string $tempPassword)
    {
        $this->user         = $user;
        $this->tempPassword = $tempPassword;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Master Magical Key Account & Purchase Access',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.guest-welcome',
            with: [
                'user'         => $this->user,
                'tempPassword' => $this->tempPassword,
                'loginUrl'     => route('login'),
            ],
        );
    }
}