<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkerProfile;

class WorkerProfilePolicy
{
    /**
     * Administrators are trusted to manage worker profiles across companies.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->role?->name === 'administrator' ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $this->isCompanyWorkerManager($user) || $this->isSelfEmployed($user);
    }

    public function view(User $user, WorkerProfile $workerProfile): bool
    {
        return $this->managesProfile($user, $workerProfile);
    }

    public function create(User $user): bool
    {
        return $this->isCompanyWorkerManager($user) || $this->isSelfEmployed($user);
    }

    public function update(User $user, WorkerProfile $workerProfile): bool
    {
        return $this->managesProfile($user, $workerProfile);
    }

    public function delete(User $user, WorkerProfile $workerProfile): bool
    {
        return $this->managesProfile($user, $workerProfile);
    }

    private function managesProfile(User $user, WorkerProfile $workerProfile): bool
    {
        if ($workerProfile->company_id !== null) {
            return $this->isCompanyWorkerManager($user)
                && $user->company_id === $workerProfile->company_id;
        }

        return $this->isSelfEmployed($user)
            && $workerProfile->user_id === $user->id;
    }

    private function isCompanyWorkerManager(User $user): bool
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
