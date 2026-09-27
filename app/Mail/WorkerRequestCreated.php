<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WorkerRequestCreated extends Mailable
{
    use SerializesModels;

    public $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function build()
    {
        return $this->subject(__('app.emails.worker_request.subject'))
            ->view('emails.worker-request-created');
    }
}
