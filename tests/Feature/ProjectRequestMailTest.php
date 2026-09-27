<?php

namespace Tests\Feature;

use App\Mail\ProjectRequestCreated;
use App\Models\Company;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ProjectRequestMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_email_is_sent_to_the_hiring_company_owner(): void
    {
        $companyOwnerRole = Role::create(['name' => 'company_owner']);
        $selfEmployedRole = Role::create(['name' => 'self_employed']);

        $hiringCompanyOwner = User::factory()->create([
            'email' => 'owner@hiring-company.test',
            'language' => 'es',
            'role_id' => $companyOwnerRole->id,
        ]);
        $hiringCompany = Company::create([
            'name' => 'Hiring Company Ltd.',
            'owner_id' => $hiringCompanyOwner->id,
        ]);
        $hiringCompanyOwner->update(['company_id' => $hiringCompany->id]);

        $project = Project::create([
            'hiring_company_id' => $hiringCompany->id,
            'created_by' => $hiringCompanyOwner->id,
            'title' => 'Commercial Framing Crew Needed',
            'description' => 'Framing support is required for a commercial project.',
            'city' => 'Calgary',
            'province' => 'Alberta',
            'job_type' => 'Carpenter',
            'workers' => 1,
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeeks(3)->toDateString(),
            'hourly_rate' => 42,
            'status' => 'open',
        ]);

        $applicant = User::factory()->create([
            'role_id' => $selfEmployedRole->id,
            'company_id' => null,
        ]);
        $worker = WorkerProfile::create([
            'user_id' => $applicant->id,
            'company_id' => null,
            'name' => 'Alex Morgan',
            'job' => 'Carpenter',
            'years_experience' => 7,
            'hourly_rate' => 38,
        ]);

        Mail::fake();

        $response = $this->actingAs($applicant)->post(route('request-project.store', $project), [
            'worker_profile_id' => $worker->id,
            'message' => 'Available for the requested dates.',
        ]);

        $response->assertSessionHasNoErrors();
        Mail::assertSent(ProjectRequestCreated::class, function (ProjectRequestCreated $mail) use ($hiringCompanyOwner) {
            return $mail->hasTo($hiringCompanyOwner->email)
                && $mail->locale === 'es'
                && ! $mail->hasTo('gabhenriquezmor@gmail.com');
        });
    }

    public function test_request_mail_subjects_use_the_recipient_locale(): void
    {
        App::setLocale('es');

        $mail = new ProjectRequestCreated((object) []);

        $this->assertSame(
            'Nueva solicitud para unirse a una misión',
            $mail->build()->subject
        );
    }
}
