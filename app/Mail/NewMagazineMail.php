<?php

namespace App\Mail;

use App\Models\Magazine;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewMagazineMail extends Mailable
{
    use Queueable, SerializesModels;

    public Magazine $magazine;

    public function __construct(Magazine $magazine)
    {
        $this->magazine = $magazine;
    }

    public function build()
    {
        return $this
            ->subject('New Magazine Published — WACHAAR')
            ->view('emails.new-magazine');
    }
}
