<?php

namespace Tests\Feature;

use App\Mail\PlanningManagerInvitation;
use App\Models\Company;
use App\Models\CompanyInvitation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CompanyTeamInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_exposes_only_the_current_company_team_to_an_owner(): void
    {
        [$owner, $company] = $this->ownerWithCompany();
        $manager = $this->planningManager($company);
        [, $otherCompany] = $this->ownerWithCompany('Other Company');
        $otherManager = $this->planningManager($otherCompany);

        $this->actingAs($owner)
            ->get(route('settings'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Settings')
                ->where('companyTeam.company_name', $company->name)
                ->where('companyTeam.can_manage', true)
                ->has('companyTeam.members', 2)
                ->where('companyTeam.members.1.id', $manager->id)
                ->missing('companyTeam.members.2')
            );

        $this->assertNotSame($otherManager->company_id, $company->id);
    }

    public function test_planning_manager_sees_a_read_only_team_and_non_company_roles_see_no_team(): void
    {
        [$owner, $company] = $this->ownerWithCompany();
        $manager = $this->planningManager($company);
        $administrator = $this->userWithRole('administrator');
        $independent = $this->userWithRole('self_employed');

        $this->actingAs($manager)
            ->get(route('settings'))
            ->assertInertia(fn (Assert $page) => $page
                ->where('companyTeam.company_name', $company->name)
                ->where('companyTeam.can_manage', false)
            );

        $this->actingAs($administrator)
            ->get(route('settings'))
            ->assertInertia(fn (Assert $page) => $page->where('companyTeam', null));

        $this->actingAs($independent)
            ->get(route('settings'))
            ->assertInertia(fn (Assert $page) => $page->where('companyTeam', null));

        $this->assertTrue($owner->is_active);
    }

    public function test_owner_can_invite_a_new_planning_manager_and_email_contains_the_acceptance_link(): void
    {
        Mail::fake();
        [$owner, $company] = $this->ownerWithCompany();

        $this->actingAs($owner)
            ->post(route('company-team.invitations.store'), [
                'name' => 'New Planner',
                'email' => 'planner@example.test',
            ])
            ->assertRedirect(route('settings', absolute: false));

        $invitation = CompanyInvitation::query()->firstOrFail();

        $this->assertSame($company->id, $invitation->company_id);
        $this->assertSame($owner->id, $invitation->invited_by);
        $this->assertSame('planning_manager', $invitation->role);
        $this->assertNotSame(hash('sha256', 'not-the-token'), $invitation->token_hash);
        $this->assertTrue($invitation->expires_at->between(now()->addDays(6), now()->addDays(8)));

        Mail::assertSent(PlanningManagerInvitation::class, function (PlanningManagerInvitation $mail) {
            return $mail->hasTo('planner@example.test');
        });
    }

    public function test_non_owners_cannot_create_company_team_invitations(): void
    {
        [, $company] = $this->ownerWithCompany();
        $manager = $this->planningManager($company);
        $administrator = $this->userWithRole('administrator');
        $independent = $this->userWithRole('self_employed');

        foreach ([$manager, $administrator, $independent] as $user) {
            $this->actingAs($user)
                ->post(route('company-team.invitations.store'), [
                    'name' => 'Blocked Planner',
                    'email' => "blocked-{$user->id}@example.test",
                ])
                ->assertForbidden();
        }

        $this->assertDatabaseCount('company_invitations', 0);
    }

    public function test_existing_user_and_duplicate_active_invitation_are_rejected(): void
    {
        [$owner] = $this->ownerWithCompany();
        $existing = User::factory()->create();

        $this->actingAs($owner)
            ->from(route('settings'))
            ->post(route('company-team.invitations.store'), [
                'name' => 'Existing User',
                'email' => $existing->email,
            ])
            ->assertSessionHasErrors('email');

        $this->actingAs($owner)
            ->post(route('company-team.invitations.store'), [
                'name' => 'New Planner',
                'email' => 'duplicate@example.test',
            ]);

        $this->actingAs($owner)
            ->from(route('settings'))
            ->post(route('company-team.invitations.store'), [
                'name' => 'Another Planner',
                'email' => 'duplicate@example.test',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_valid_invitation_acceptance_creates_a_verified_planning_manager_for_the_invited_company(): void
    {
        [, $company] = $this->ownerWithCompany();
        [$invitation, $token] = $this->invitationFor($company);

        $this->post(route('company-invitations.accept.store', $token), [
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('home', absolute: false));

        $user = User::query()->where('email', $invitation->email)->firstOrFail();
        $this->assertSame($company->id, $user->company_id);
        $this->assertSame('planning_manager', $user->role->name);
        $this->assertTrue($user->is_active);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNotNull($invitation->fresh()->accepted_at);
        $this->assertAuthenticatedAs($user);
    }

    public function test_cancelled_expired_or_accepted_invitations_cannot_be_reused(): void
    {
        [$owner, $company] = $this->ownerWithCompany();

        [$cancelled, $cancelledToken] = $this->invitationFor($company, ['cancelled_at' => now()]);
        [$expired, $expiredToken] = $this->invitationFor($company, ['expires_at' => now()->subMinute()]);
        [$accepted, $acceptedToken] = $this->invitationFor($company, ['accepted_at' => now()]);

        foreach ([$cancelledToken, $expiredToken, $acceptedToken] as $token) {
            $this->get(route('company-invitations.accept.show', $token))->assertNotFound();
        }

        $this->actingAs($owner)
            ->post(route('company-team.invitations.cancel', $cancelled))
            ->assertForbidden();

        $this->assertSame($company->id, $cancelled->company_id);
    }

    public function test_acceptance_fails_if_the_company_no_longer_has_a_valid_owner(): void
    {
        [$owner, $company] = $this->ownerWithCompany();
        [, $token] = $this->invitationFor($company);

        $owner->forceFill(['is_active' => false])->save();

        $this->get(route('company-invitations.accept.show', $token))->assertNotFound();
    }

    public function test_owner_can_cancel_an_active_invitation(): void
    {
        [$owner, $company] = $this->ownerWithCompany();
        [$invitation] = $this->invitationFor($company);

        $this->actingAs($owner)
            ->post(route('company-team.invitations.cancel', $invitation))
            ->assertRedirect(route('settings', absolute: false));

        $this->assertNotNull($invitation->fresh()->cancelled_at);
    }

    public function test_owner_can_remove_a_planning_manager_without_deleting_their_history_or_owner_account(): void
    {
        [$owner, $company] = $this->ownerWithCompany();
        $manager = $this->planningManager($company);

        DB::table('sessions')->insert([
            'id' => 'manager-session',
            'user_id' => $manager->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'test',
            'payload' => 'payload',
            'last_activity' => now()->timestamp,
        ]);

        $this->actingAs($owner)
            ->delete(route('company-team.members.destroy', $manager))
            ->assertRedirect(route('settings', absolute: false));

        $manager->refresh();
        $this->assertFalse($manager->is_active);
        $this->assertNull($manager->company_id);
        $this->assertDatabaseMissing('sessions', ['user_id' => $manager->id]);
        $this->assertDatabaseHas('users', ['id' => $owner->id, 'company_id' => $company->id]);

        $this->actingAs($owner)
            ->delete(route('company-team.members.destroy', $owner))
            ->assertForbidden();
    }

    public function test_planning_manager_cannot_remove_colleagues_or_members_of_another_company(): void
    {
        [, $company] = $this->ownerWithCompany();
        $manager = $this->planningManager($company);
        $colleague = $this->planningManager($company);
        [, $otherCompany] = $this->ownerWithCompany('Other Company');
        $otherManager = $this->planningManager($otherCompany);

        $this->actingAs($manager)
            ->delete(route('company-team.members.destroy', $colleague))
            ->assertForbidden();

        $this->actingAs($manager)
            ->delete(route('company-team.members.destroy', $otherManager))
            ->assertForbidden();

        $this->assertTrue($colleague->fresh()->is_active);
        $this->assertTrue($otherManager->fresh()->is_active);
    }

    private function ownerWithCompany(string $companyName = 'Northstar Build Group'): array
    {
        $owner = $this->userWithRole('company_owner');
        $company = Company::create([
            'name' => $companyName,
            'phone' => '403-555-0100',
            'address' => '100 Builder Avenue',
            'owner_id' => $owner->id,
        ]);
        $owner->update(['company_id' => $company->id]);

        return [$owner->fresh(), $company];
    }

    private function planningManager(Company $company): User
    {
        return $this->userWithRole('planning_manager', ['company_id' => $company->id]);
    }

    private function userWithRole(string $roleName, array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role_id' => Role::firstOrCreate(['name' => $roleName])->id,
            'company_id' => null,
            'email_verified_at' => now(),
            'is_active' => true,
        ], $attributes));
    }

    private function invitationFor(Company $company, array $attributes = []): array
    {
        Role::firstOrCreate(['name' => 'planning_manager']);

        $token = Str::random(64);
        $invitation = CompanyInvitation::create(array_merge([
            'company_id' => $company->id,
            'invited_by' => $company->owner_id,
            'name' => 'Invited Planner',
            'email' => Str::lower(Str::random(8)).'@example.test',
            'role' => 'planning_manager',
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDays(7),
        ], $attributes));

        return [$invitation, $token];
    }
}
