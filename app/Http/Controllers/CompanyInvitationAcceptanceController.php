<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyInvitation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CompanyInvitationAcceptanceController extends Controller
{
    public function create(string $token): Response
    {
        $invitation = $this->activeInvitation($token);

        return Inertia::render('CompanyInvitations/Accept', [
            'token' => $token,
            'invitation' => [
                'name' => $invitation->name,
                'email' => $invitation->email,
                'company_name' => $invitation->company->name,
            ],
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = DB::transaction(function () use ($token, $validated): User {
            $invitation = CompanyInvitation::query()
                ->where('token_hash', hash('sha256', $token))
                ->first();

            if (! $invitation) {
                throw ValidationException::withMessages([
                    'invitation' => [__('app.company_team.validation.invalid_invitation')],
                ]);
            }

            $company = Company::query()
                ->with(['owner.role'])
                ->lockForUpdate()
                ->find($invitation->company_id);

            if (! $company || ! $company->hasValidOwner()) {
                throw ValidationException::withMessages([
                    'invitation' => [__('app.company_team.validation.invalid_company_owner')],
                ]);
            }

            $invitation = CompanyInvitation::query()
                ->lockForUpdate()
                ->find($invitation->id);

            if (! $invitation || ! $invitation->isActive()) {
                throw ValidationException::withMessages([
                    'invitation' => [__('app.company_team.validation.invalid_invitation')],
                ]);
            }

            if (User::query()->where('email', $invitation->email)->exists()) {
                throw ValidationException::withMessages([
                    'invitation' => [__('app.company_team.validation.existing_user')],
                ]);
            }

            $planningManagerRole = Role::query()->where('name', 'planning_manager')->firstOrFail();

            $user = User::create([
                'name' => $invitation->name,
                'email' => $invitation->email,
                'password' => Hash::make($validated['password']),
                'role_id' => $planningManagerRole->id,
                'company_id' => $company->id,
            ]);

            $user->forceFill([
                'email_verified_at' => now(),
                'is_active' => true,
            ])->save();

            $invitation->update(['accepted_at' => now()]);

            return $user;
        });

        Auth::login($user);

        return redirect()->route('home')->with('success', __('app.company_team.success.invitation_accepted'));
    }

    private function activeInvitation(string $token): CompanyInvitation
    {
        $invitation = CompanyInvitation::query()
            ->with('company.owner.role')
            ->where('token_hash', hash('sha256', $token))
            ->firstOrFail();

        if (! $invitation->isActive() || ! $invitation->company->hasValidOwner()) {
            abort(404);
        }

        return $invitation;
    }
}
