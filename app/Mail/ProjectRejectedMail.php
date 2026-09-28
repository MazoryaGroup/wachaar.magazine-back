<?php

namespace App\Mail;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProjectRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Project $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function build()
    {
        return $this
            ->subject('WACHAAR — Update Regarding Your Project')
            ->view('emails.project-rejected');
    }
}
