<?php

namespace App\Mail;

use App\Models\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeArtistMail extends Mailable
{
    use Queueable, SerializesModels;

    public Client $client;

    /**
     * Create a new message instance.
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this
            ->subject('Welcome to Wachaar')
            ->view('emails.welcome-artist');
    }
}
