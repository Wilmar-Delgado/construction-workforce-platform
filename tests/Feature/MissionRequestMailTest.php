<?php

namespace Tests\Feature;

use App\Mail\MissionRequestCreated;
use App\Models\Company;
use App\Models\Mission;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MissionRequestMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_email_is_sent_to_the_hiring_company_owner(): void
    {
        $companyOwnerRole = Role::create(['name' => 'company_owner']);
        $selfEmployedRole = Role::create(['name' => 'self_employed']);

        $hiringCompanyOwner = User::factory()->create([
            'email' => 'owner@hiring-company.test',
            'role_id' => $companyOwnerRole->id,
        ]);
        $hiringCompany = Company::create([
            'name' => 'Hiring Company Ltd.',
            'owner_id' => $hiringCompanyOwner->id,
        ]);
        $hiringCompanyOwner->update(['company_id' => $hiringCompany->id]);

        $mission = Mission::create([
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

        $response = $this->actingAs($applicant)->post(route('request-mission.store', $mission), [
            'worker_profile_id' => $worker->id,
            'message' => 'Available for the requested dates.',
        ]);

        $response->assertSessionHasNoErrors();
        Mail::assertSent(MissionRequestCreated::class, function (MissionRequestCreated $mail) use ($hiringCompanyOwner) {
            return $mail->hasTo($hiringCompanyOwner->email)
                && ! $mail->hasTo('gabhenriquezmor@gmail.com');
        });
    }
}
