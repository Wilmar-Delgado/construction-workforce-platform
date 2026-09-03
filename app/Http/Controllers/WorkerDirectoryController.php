<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use App\Models\WorkerProfile;
use App\Models\WorkerRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WorkerDirectoryController extends Controller {
    use AuthorizesRequests;
    
    public function index(Request $request)
    {
        $user = auth()->user();
        $selectedWorker = null;

        if ($request->filled('worker') || $request->filled('request')) {
            abort_unless($request->filled('worker') && $request->filled('request'), 404);

            $workerRequest = WorkerRequest::findOrFail($request->integer('request'));

            abort_unless($workerRequest->worker_profile_id === $request->integer('worker'), 404);

            $this->authorize('view', $workerRequest);

            $worker = WorkerProfile::with(['skills:id,name', 'certifications:id,name', 'company:id,name'])
                ->withAvg('ratings', 'score')
                ->withCount('ratings')
                ->findOrFail($workerRequest->worker_profile_id);

            $selectedWorker = [
                'id' => $worker->id,
                'name' => $worker->name,
                'job' => $worker->job,
                'years_experience' => $worker->years_experience,
                'hourly_rate' => $worker->hourly_rate,
                'rating' => $worker->rating,
                'ratings_count' => $worker->ratings_count,
                'company' => $worker->company ? [
                    'id' => $worker->company->id,
                    'name' => $worker->company->name,
                ] : null,
                'skills' => $worker->skills,
                'certifications' => $worker->certifications,
            ];
        }

        $query = WorkerProfile::with([
            'skills',
            'certifications',
            'company'
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

        $jobs = WorkerProfile::select('job')
            ->distinct()
            ->pluck('job');

        $missions = Mission::where('hiring_company_id', $user->company_id)
            ->whereIn('status', ['draft', 'open'])
            ->select('id', 'title')
            ->orderBy('title')
            ->get();

        $existingRequests = WorkerRequest::where('company_id', $user->company_id)
        ->where('type', 'invite')
        ->get(['mission_id', 'worker_profile_id']);

        return Inertia::render('FindWorkers', [
            'workers' => $workers,
            'jobs' => $jobs,
            'filters' => $request->only(['search', 'job']),
            'missions' => $missions,
            'existingRequests' => $existingRequests,
            'selectedWorker' => $selectedWorker,
        ]);
    }
}
