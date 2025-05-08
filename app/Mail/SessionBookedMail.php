<?php

namespace App\Mail;

use App\Models\BookedSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SessionBookedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $bookedSession;
    public $recipient;

    /**
     * Create a new message instance.
     *
     * @param BookedSession $bookedSession
     * @param string $recipient 'coach' or 'user'
     * @return void
     */
    public function __construct(BookedSession $bookedSession, string $recipient)
    {
        $this->bookedSession = $bookedSession;
        $this->recipient = $recipient;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if ($this->recipient === 'coach') {
            return $this->subject('New Session Booking - Action Required')
                        ->view('emails.session-booked-coach');
        } else {
            return $this->subject('Your Session Booking Confirmation')
                        ->view('emails.session-booked-user');
        }
    }
}