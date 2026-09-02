<?php

namespace App\Policies;

use App\Models\Availability;
use App\Models\User;
use App\Models\WorkerProfile;

class AvailabilityPolicy
{
    /**
     * Administrators are trusted to manage availability across companies.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->role?->name === 'administrator' ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $this->isCompanyAvailabilityManager($user) || $this->isSelfEmployed($user);
    }

    public function view(User $user, Availability $availability): bool
    {
        return $this->managesProfile($user, $availability->workerProfile);
    }

    public function create(User $user, WorkerProfile $workerProfile): bool
    {
        return $this->managesProfile($user, $workerProfile);
    }

    public function update(User $user, Availability $availability, WorkerProfile $targetProfile): bool
    {
        return $this->managesProfile($user, $availability->workerProfile)
            && $this->managesProfile($user, $targetProfile);
    }

    public function delete(User $user, Availability $availability): bool
    {
        return $this->managesProfile($user, $availability->workerProfile);
    }

    private function managesProfile(User $user, ?WorkerProfile $workerProfile): bool
    {
        if (! $workerProfile) {
            return false;
        }

        if ($workerProfile->company_id !== null) {
            return $this->isCompanyAvailabilityManager($user)
                && $user->company_id === $workerProfile->company_id;
        }

        return $this->isSelfEmployed($user)
            && $workerProfile->user_id === $user->id;
    }

    private function isCompanyAvailabilityManager(User $user): bool
    {
        return $user->company_id !== null
            && in_array($user->role?->name, ['company_owner', 'planning_manager'], true);
    }

    private function isSelfEmployed(User $user): bool
    {
        return $user->company_id === null
            && $user->role?->name === 'self_employed';
    }
}
