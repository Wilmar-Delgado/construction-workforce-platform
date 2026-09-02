<?php

namespace App\Policies;

use App\Models\Mission;
use App\Models\User;

class MissionPolicy
{
    /**
     * Administrators are trusted to manage missions across companies.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->role?->name === 'administrator' ? true : null;
    }

    /**
     * The mission-management list is only relevant to company users.
     */
    public function viewAny(User $user): bool
    {
        return $this->isCompanyMissionManager($user);
    }

    /**
     * Mission details are limited to the company that owns the mission.
     */
    public function view(User $user, Mission $mission): bool
    {
        return $this->managesMission($user, $mission);
    }

    public function create(User $user): bool
    {
        return $this->isCompanyMissionManager($user);
    }

    public function update(User $user, Mission $mission): bool
    {
        return $this->managesMission($user, $mission);
    }

    public function delete(User $user, Mission $mission): bool
    {
        return $this->managesMission($user, $mission);
    }

    public function archive(User $user, Mission $mission): bool
    {
        return $this->managesMission($user, $mission);
    }

    private function isCompanyMissionManager(User $user): bool
    {
        return $user->company_id !== null
            && in_array($user->role?->name, ['company_owner', 'planning_manager'], true);
    }

    private function managesMission(User $user, Mission $mission): bool
    {
        return $this->isCompanyMissionManager($user)
            && $user->company_id === $mission->hiring_company_id;
    }
}
