<?php

namespace App\Mail;

use App\Models\CompanyInvitation;
use Illuminate\Mail\Mailable;

class PlanningManagerInvitation extends Mailable
{
    public function __construct(
        public CompanyInvitation $invitation,
        public string $acceptanceUrl,
    ) {
    }

    public function build(): self
    {
        return $this
            ->subject(__('app.company_team.email.subject', [
                'company' => $this->invitation->company->name,
            ]))
            ->view('emails.planning-manager-invitation');
    }
}
