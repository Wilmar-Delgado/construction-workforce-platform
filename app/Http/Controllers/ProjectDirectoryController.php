<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\WorkerProfile;
use App\Models\WorkerRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProjectDirectoryController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Project::query()
            ->notArchived()
            ->select([
                'id',
                'hiring_company_id',
                'title',
                'description',
                'city',
                'province',
                'country',
                'job_type',
                'workers',
                'start_date',
                'end_date',
                'hourly_rate',
                'status',
                'recruiting_closed_at',
                'created_at',
            ])
            ->withCommittedWorkerCount()
            ->with([
                'hiringCompany:id,name',
                'requirements:id,project_id,name',
            ])
            ->where('hiring_company_id', '!=', $user->company_id)
            ->actionableForStaffing($user);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%")
                  ->orWhere('city', 'like', "%{$request->search}%")
                  ->orWhere('province', 'like', "%{$request->search}%");
            });
        }

        if ($request->job) {
            $query->where('job_type', $request->job);
        }

        if ($request->location) {
            $query->where('city', 'like', "%{$request->location}%");
        }

        $projects = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $locations = Project::notArchived()
            ->select('city')
            ->distinct()
            ->pluck('city');

        $workersQuery = WorkerProfile::query()
            ->notArchived()
            ->select('id', 'user_id', 'name', 'job');

        if ($user->role?->name === 'administrator') {
            // Administrators can access worker profiles across companies.
        } elseif (
            $user->company_id !== null
            && in_array($user->role?->name, ['company_owner', 'planning_manager'], true)
        ) {
            $workersQuery->where('company_id', $user->company_id);
        } elseif ($user->company_id === null && $user->role?->name === 'self_employed') {
            $workersQuery
                ->whereNull('company_id')
                ->where('user_id', $user->id);
        } else {
            $workersQuery->whereRaw('1 = 0');
        }

        $workers = $workersQuery->get();

        $existingRequests = WorkerRequest::query()
            ->select(['project_id', 'worker_profile_id', 'type', 'status'])
            ->whereIn('project_id', $projects->getCollection()->pluck('id'))
            ->whereIn('worker_profile_id', $workers->pluck('id'))
            ->orderBy('id')
            ->get();

        return Inertia::render('FindProjects', [
            'projects' => $projects,
            'locations' => $locations,
            'filters' => $request->only(['search', 'job', 'location']),
            'workers' => $workers,
            'existingRequests' => $existingRequests,
        ]);
    }
}
