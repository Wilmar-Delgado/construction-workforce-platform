<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use App\Models\WorkerProfile;
use App\Models\WorkerRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MissionDirectoryController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $user = auth()->user();
        $selectedMission = null;

        if ($request->filled('mission') || $request->filled('request')) {
            abort_unless($request->filled('mission') && $request->filled('request'), 404);

            $workerRequest = WorkerRequest::findOrFail($request->integer('request'));

            abort_unless(
                $workerRequest->mission_id === $request->integer('mission')
                    && in_array($workerRequest->status, ['pending', 'accepted', 'ongoing', 'completed'], true),
                404
            );

            $this->authorize('view', $workerRequest);

            $mission = Mission::with(['hiringCompany.owner', 'requirements'])
                ->findOrFail($workerRequest->mission_id);

            abort_if($mission->hiring_company_id === $user->company_id, 404);

            $selectedMission = $this->selectedMissionPayload($mission, $workerRequest);
        }

        $query = Mission::query()
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
                'created_at',
            ])
            ->with([
                'hiringCompany:id,name',
                'requirements:id,mission_id,name',
            ])
            ->where('hiring_company_id', '!=', $user->company_id)
            ->whereIn('status', ['open']);

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

        $missions = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $locations = Mission::select('city')
            ->distinct()
            ->pluck('city');

        $workersQuery = WorkerProfile::select('id', 'user_id', 'name', 'job');

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

        return Inertia::render('FindMissions', [
            'missions' => $missions,
            'locations' => $locations,
            'filters' => $request->only(['search', 'job', 'location']),
            'workers' => $workers,
            'selectedMission' => $selectedMission,
        ]);
    }

    private function selectedMissionPayload(Mission $mission, WorkerRequest $workerRequest): array
    {
        $payload = [
            'id' => $mission->id,
            'title' => $mission->title,
            'description' => $mission->description,
            'city' => $mission->city,
            'province' => $mission->province,
            'country' => $mission->country,
            'job_type' => $mission->job_type,
            'workers' => $mission->workers,
            'start_date' => $mission->start_date,
            'end_date' => $mission->end_date,
            'hourly_rate' => $mission->hourly_rate,
            'status' => $mission->status,
            'hiring_company' => [
                'name' => $mission->hiringCompany?->name,
            ],
            'requirements' => $mission->requirements
                ->map(fn ($requirement) => [
                    'id' => $requirement->id,
                    'name' => $requirement->name,
                ])
                ->values(),
        ];

        if (in_array($workerRequest->status, ['accepted', 'ongoing', 'completed'], true)) {
            $payload['operational_details'] = [
                'site_name' => $mission->site_name,
                'address_line_1' => $mission->address_line_1,
                'address_line_2' => $mission->address_line_2,
                'postal_code' => $mission->postal_code,
                'directions' => $mission->directions,
                'contact_name' => $mission->hiringCompany?->owner?->name,
                'contact_phone' => $mission->hiringCompany?->owner?->phone,
            ];
        }

        return $payload;
    }
}
