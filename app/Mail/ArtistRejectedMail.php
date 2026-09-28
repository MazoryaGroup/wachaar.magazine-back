<?php

namespace App\Mail;

use App\Models\Artist;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ArtistRejectedMail extends Mailable
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
            ->subject('WACHAAR — Update Regarding Your Artist Profile')
            ->view('emails.artist-rejected');
    }
}
