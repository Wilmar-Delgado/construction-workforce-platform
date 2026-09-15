<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class WorkerProfilesPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_self_employed_profile_creation_is_reflected_in_the_next_worker_profiles_page_props(): void
    {
        $user = User::factory()->create([
            'role_id' => Role::firstOrCreate(['name' => 'self_employed'])->id,
            'company_id' => null,
        ]);

        $this->actingAs($user)
            ->get(route('worker-profiles.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('WorkerProfiles')
                ->where('hasWorkerProfile', false)
                ->has('workerProfiles.data', 0)
            );

        $this->actingAs($user)
            ->post(route('worker-profiles.store'), [
                'name' => $user->name,
                'job' => 'Electrician',
                'experience' => 5,
                'rate' => 40,
                'certifications' => [],
                'skills' => [],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('worker-profiles.index'));

        $this->actingAs($user)
            ->get(route('worker-profiles.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('WorkerProfiles')
                ->where('hasWorkerProfile', true)
                ->has('workerProfiles.data', 1)
                ->where('workerProfiles.data.0.user_id', $user->id)
            );
    }
}
