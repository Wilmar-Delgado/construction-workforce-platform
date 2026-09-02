<?php

namespace App\Policies;

use App\Models\Mission;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Models\WorkerRequest;

class WorkerRequestPolicy
{
    /**
     * Administrators are trusted to manage requests across companies.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->role?->name === 'administrator' ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $this->isCompanyManager($user) || $this->isSelfEmployed($user);
    }

    public function view(User $user, WorkerRequest $workerRequest): bool
    {
        return $this->isOriginator($user, $workerRequest)
            || $this->isRecipient($user, $workerRequest);
    }

    public function createInvite(User $user, Mission $mission, WorkerProfile $worker): bool
    {
        return $this->isCompanyManager($user)
            && $mission->hiring_company_id === $user->company_id
            && $mission->status === 'open'
            && $worker->company_id !== $user->company_id;
    }

    public function createApplication(User $user, Mission $mission, WorkerProfile $worker): bool
    {
        if ($mission->status !== 'open') {
            return false;
        }

        if ($this->isCompanyManager($user)) {
            return $worker->company_id === $user->company_id
                && $mission->hiring_company_id !== $user->company_id;
        }

        return $this->isSelfEmployed($user)
            && $worker->company_id === null
            && $worker->user_id === $user->id
            && $mission->hiring_company_id !== $user->company_id;
    }

    public function respond(User $user, WorkerRequest $workerRequest, string $action): bool
    {
        if ($workerRequest->status !== 'pending' || ! $this->isRecipient($user, $workerRequest)) {
            return false;
        }

        return $action !== 'accept' || $workerRequest->mission?->status === 'open';
    }

    public function complete(User $user, WorkerRequest $workerRequest): bool
    {
        return $workerRequest->status === 'accepted'
            && $this->isCompanyManager($user)
            && $workerRequest->mission?->hiring_company_id === $user->company_id;
    }

    private function isOriginator(User $user, WorkerRequest $workerRequest): bool
    {
        if ($workerRequest->company_id !== null) {
            return $this->isCompanyManager($user)
                && $workerRequest->company_id === $user->company_id;
        }

        return $this->isSelfEmployed($user)
            && $workerRequest->requested_by === $user->id;
    }

    private function isRecipient(User $user, WorkerRequest $workerRequest): bool
    {
        if ($workerRequest->type === 'invite') {
            $worker = $workerRequest->worker;

            if (! $worker) {
                return false;
            }

            if ($worker->company_id !== null) {
                return $this->isCompanyManager($user)
                    && $worker->company_id === $user->company_id;
            }

            return $this->isSelfEmployed($user)
                && $worker->user_id === $user->id;
        }

        if ($workerRequest->type === 'apply') {
            return $this->isCompanyManager($user)
                && $workerRequest->mission?->hiring_company_id === $user->company_id;
        }

        return false;
    }

    private function isCompanyManager(User $user): bool
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
