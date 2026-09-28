<?php

namespace App\Mail;

use App\Models\Artist;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ArtistdraftMail extends Mailable
{
    use Queueable, SerializesModels;

    public Artist $artist;

    public function __construct(Artist $artist)
    {
        $this->artist = $artist;
    }

    public function build()
    {
        return $this
            ->subject('WACHAAR — Your Artist Profile Is Under Review')
            ->view('emails.artist-draft');
    }
}
