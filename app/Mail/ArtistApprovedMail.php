<?php

namespace App\Mail;

use App\Models\Artist;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ArtistApprovedMail extends Mailable
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
            ->subject('WACHAAR — Your Artist Profile Has Been Approved')
            ->view('emails.artist-approved');
    }
}
