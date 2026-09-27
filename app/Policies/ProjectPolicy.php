<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Administrators are trusted to manage projects across companies.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->role?->name === 'administrator' ? true : null;
    }

    /**
     * The project-management list is only relevant to company users.
     */
    public function viewAny(User $user): bool
    {
        return $this->isCompanyProjectManager($user);
    }

    /**
     * Project details are limited to the company that owns the project.
     */
    public function view(User $user, Project $project): bool
    {
        return $this->managesProject($user, $project);
    }

    public function create(User $user): bool
    {
        return $this->isCompanyProjectManager($user);
    }

    public function update(User $user, Project $project): bool
    {
        return $this->managesProject($user, $project);
    }

    public function delete(User $user, Project $project): bool
    {
        return $this->managesProject($user, $project);
    }

    public function archive(User $user, Project $project): bool
    {
        return $this->managesProject($user, $project);
    }

    public function closeRecruiting(User $user, Project $project): bool
    {
        return $this->managesProject($user, $project);
    }

    public function start(User $user, Project $project): bool
    {
        return $this->managesProject($user, $project);
    }

    private function isCompanyProjectManager(User $user): bool
    {
        return $user->company_id !== null
            && in_array($user->role?->name, ['company_owner', 'planning_manager'], true);
    }

    private function managesProject(User $user, Project $project): bool
    {
        return $this->isCompanyProjectManager($user)
            && $user->company_id === $project->hiring_company_id;
    }
}
