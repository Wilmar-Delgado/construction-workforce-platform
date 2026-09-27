<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkerRequest;
use Database\Seeders\DevelopmentDataSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevelopmentSeederCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_worker_requests_match_their_project_job_type(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(DevelopmentDataSeeder::class);

        $mismatches = WorkerRequest::query()
            ->join('worker_profiles', 'worker_profiles.id', '=', 'requests.worker_profile_id')
            ->join('projects', 'projects.id', '=', 'requests.project_id')
            ->whereColumn('projects.job_type', '!=', 'worker_profiles.job')
            ->count();

        $this->assertSame(0, $mismatches);
    }

    public function test_seeded_external_company_application_is_viewable_by_its_company_owner_and_planner(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(DevelopmentDataSeeder::class);

        $request = WorkerRequest::query()
            ->whereHas('project', fn ($project) => $project->where(
                'title',
                'Commercial Roofing Crew for School Addition',
            ))
            ->whereHas('worker', fn ($worker) => $worker->where('name', 'Sophia Grant'))
            ->firstOrFail();

        $this->assertSame($request->worker->company_id, $request->company_id);

        foreach ([
            'tessa.ward@ironridge.test',
            'grant.wilson@ironridge.test',
        ] as $email) {
            $viewer = User::where('email', $email)->firstOrFail();

            $this->actingAs($viewer)
                ->getJson(route('project-management.projects.details', [
                    'project' => $request->project_id,
                    'request' => $request->id,
                ]))
                ->assertOk()
                ->assertJsonPath('project.id', $request->project_id);
        }
    }
}
