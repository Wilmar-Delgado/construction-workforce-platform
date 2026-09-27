<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProjectRequestCreated extends Mailable
{
    use SerializesModels;

    public $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function build()
    {
        return $this
            ->subject(__('app.emails.project_request.subject'))
            ->view('emails.project-request-created');
    }
}
