<?php

namespace App\Http\Controllers;

use App\Mail\PlanningManagerInvitation;
use App\Models\Company;
use App\Models\CompanyInvitation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CompanyTeamController extends Controller
{
    public function payloadFor(User $user): ?array
    {
        if (! $user->hasCompanyOperationalContext()) {
            return null;
        }

        $company = Company::query()->find($user->company_id);

        if (! $company || ! $company->hasValidOwner()) {
            return null;
        }

        $members = $company->members()
            ->with('role')
            ->whereIn('role_id', Role::query()
                ->whereIn('name', ['company_owner', 'planning_manager'])
                ->pluck('id'))
            ->orderByRaw('id = ? desc', [$company->owner_id])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role_id', 'company_id']);

        $invitations = $company->invitations()
            ->active()
            ->orderBy('created_at')
            ->get(['id', 'name', 'email', 'role', 'expires_at']);

        return [
            'company_name' => $company->name,
            'can_manage' => $this->isOwner($user, $company),
            'members' => $members->map(fn (User $member) => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'role' => $member->role?->name,
                'is_owner' => $member->id === $company->owner_id,
            ])->values(),
            'invitations' => $invitations->map(fn (CompanyInvitation $invitation) => [
                'id' => $invitation->id,
                'name' => $invitation->name,
                'email' => $invitation->email,
                'role' => $invitation->role,
                'expires_at' => $invitation->expires_at,
            ])->values(),
        ];
    }

    public function storeInvitation(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
        ]);

        [$invitation, $token] = DB::transaction(function () use ($request, $validated): array {
            $company = $this->ownerCompany($request->user(), true);
            $email = strtolower($validated['email']);

            if (User::query()->where('email', $email)->exists()) {
                throw ValidationException::withMessages([
                    'email' => [__('app.company_team.validation.existing_user')],
                ]);
            }

            if ($company->invitations()->active()->where('email', $email)->exists()) {
                throw ValidationException::withMessages([
                    'email' => [__('app.company_team.validation.active_invitation')],
                ]);
            }

            $token = Str::random(64);
            $invitation = $company->invitations()->create([
                'invited_by' => $request->user()->id,
                'name' => $validated['name'],
                'email' => $email,
                'role' => 'planning_manager',
                'token_hash' => hash('sha256', $token),
                'expires_at' => now()->addDays(7),
            ]);

            return [$invitation->load(['company', 'inviter']), $token];
        });

        Mail::to($invitation->email)->send(new PlanningManagerInvitation(
            $invitation,
            route('company-invitations.accept.show', ['token' => $token]),
        ));

        return redirect()->route('settings')->with('success', __('app.company_team.success.invitation_sent'));
    }

    public function cancelInvitation(Request $request, CompanyInvitation $invitation): RedirectResponse
    {
        DB::transaction(function () use ($request, $invitation): void {
            $company = $this->ownerCompany($request->user(), true);
            $lockedInvitation = CompanyInvitation::query()->lockForUpdate()->findOrFail($invitation->id);

            if ($lockedInvitation->company_id !== $company->id || ! $lockedInvitation->isActive()) {
                throw new AuthorizationException();
            }

            $lockedInvitation->update(['cancelled_at' => now()]);
        });

        return redirect()->route('settings')->with('success', __('app.company_team.success.invitation_cancelled'));
    }

    public function destroyMember(Request $request, User $user): RedirectResponse
    {
        DB::transaction(function () use ($request, $user): void {
            $company = $this->ownerCompany($request->user(), true);
            $member = User::query()->with('role')->lockForUpdate()->findOrFail($user->id);

            if ($member->id === $company->owner_id
                || $member->company_id !== $company->id
                || $member->role?->name !== 'planning_manager') {
                throw new AuthorizationException();
            }

            $member->forceFill([
                'company_id' => null,
                'is_active' => false,
            ])->save();

            DB::table('sessions')->where('user_id', $member->id)->delete();
        });

        return redirect()->route('settings')->with('success', __('app.company_team.success.member_removed'));
    }

    private function ownerCompany(User $user, bool $lock): Company
    {
        $query = Company::query()->with(['owner.role']);

        if ($lock) {
            $query->lockForUpdate();
        }

        $company = $query->find($user->company_id);

        if (! $company || ! $this->isOwner($user, $company) || ! $company->hasValidOwner()) {
            throw new AuthorizationException();
        }

        return $company;
    }

    private function isOwner(User $user, Company $company): bool
    {
        return $user->is_active
            && $user->role?->name === 'company_owner'
            && $user->company_id === $company->id
            && $company->owner_id === $user->id;
    }
}
