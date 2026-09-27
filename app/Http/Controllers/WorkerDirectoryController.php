<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\WorkerProfile;
use App\Models\WorkerRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WorkerDirectoryController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = WorkerProfile::query()
            ->notArchived()
            ->with([
                'skills',
                'certifications',
                'company',
            ])
            ->withAvg('ratings', 'score')
            ->withCount('ratings')
            ->where(function ($q) use ($user) {
                $q->where('company_id', '!=', $user->company_id)
                    ->orWhereNull('company_id');
            });

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('job', 'like', "%{$request->search}%")
                
                ->orWhereHas('skills', function ($q2) use ($request) {
                    $q2->where('name', 'like', "%{$request->search}%");
                })

                ->orWhereHas('certifications', function ($q3) use ($request) {
                    $q3->where('name', 'like', "%{$request->search}%");
                });
            });
        }

        if ($request->job) {
            $query->where('job', $request->job);
        }

        $workers = $query
            ->latest()
            ->paginate(9)
            ->withQueryString();

        $jobs = WorkerProfile::query()
            ->notArchived()
            ->select('job')
            ->distinct()
            ->pluck('job');

        $projects = Project::query()
            ->notArchived()
            ->where('hiring_company_id', $user->company_id)
            ->select('id', 'title', 'workers', 'recruiting_closed_at', 'start_date')
            ->withCommittedWorkerCount()
            ->actionableForStaffing($user)
            ->orderBy('title')
            ->get();

        $existingRequests = WorkerRequest::where('company_id', $user->company_id)
        ->where('type', 'invite')
        ->get(['project_id', 'worker_profile_id']);

        return Inertia::render('FindWorkers', [
            'workers' => $workers,
            'jobs' => $jobs,
            'filters' => $request->only(['search', 'job']),
            'projects' => $projects,
            'existingRequests' => $existingRequests,
        ]);
    }
}
