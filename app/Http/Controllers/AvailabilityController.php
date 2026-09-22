<?php

namespace App\Http\Controllers;

use App\Models\Availability;
use App\Models\WorkerRequest;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AvailabilityController extends Controller
{
    use AuthorizesRequests;

    private const MAX_CALENDAR_RANGE_DAYS = 62;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Availability::class);

        $sortField = $request->get('sort', 'date');
        $sortDirection = $request->get('direction', 'asc');
        $user = auth()->user();

        $workerProfilesQuery = $this->authorizedWorkerProfilesQuery($user);

        $availabilityQuery = Availability::query()
            ->join('worker_profiles', 'availabilities.worker_profile_id', '=', 'worker_profiles.id')
            ->select('availabilities.*', 'worker_profiles.name as worker_name', 'worker_profiles.job')
            ->whereIn('availabilities.worker_profile_id', (clone $workerProfilesQuery)->select('id'));

        $availability = $availabilityQuery
            ->orderBy($sortField, $sortDirection)
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Availability', [
            'availability' => $availability,
            'filters' => [
                'sort' => $sortField,
                'direction' => $sortDirection,
            ],
            'workerProfiles' => $workerProfilesQuery->get(),
        ]);
    }

    public function calendar(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Availability::class);

        $validated = $request->validate([
            'start' => 'required|date_format:Y-m-d',
            'end' => 'required|date_format:Y-m-d|after_or_equal:start',
            'worker_profile_id' => 'nullable|integer',
        ]);

        $start = Carbon::createFromFormat('Y-m-d', $validated['start'])->startOfDay();
        $end = Carbon::createFromFormat('Y-m-d', $validated['end'])->startOfDay();

        if ($start->diffInDays($end) + 1 > self::MAX_CALENDAR_RANGE_DAYS) {
            throw ValidationException::withMessages([
                'end' => __('app.availability_page.validation.calendar_range_too_large', [
                    'days' => self::MAX_CALENDAR_RANGE_DAYS,
                ]),
            ]);
        }

        $workerProfilesQuery = $this->authorizedWorkerProfilesQuery($request->user());

        if (isset($validated['worker_profile_id'])) {
            $worker = (clone $workerProfilesQuery)->find($validated['worker_profile_id']);

            if (! $worker) {
                throw ValidationException::withMessages([
                    'worker_profile_id' => __('app.availability_page.validation.worker_not_available'),
                ]);
            }

            $workerProfilesQuery->whereKey($worker->id);
        }

        $workers = $workerProfilesQuery
            ->orderBy('name')
            ->get(['id', 'name', 'job']);

        $availabilities = Availability::query()
            ->whereIn('worker_profile_id', $workers->pluck('id'))
            ->whereBetween('date', [$validated['start'], $validated['end']])
            ->orderBy('date')
            ->orderBy('start_time')
            ->get([
                'id',
                'worker_profile_id',
                'date',
                'start_time',
                'end_time',
                'status',
            ]);

        return response()->json([
            'workers' => $workers,
            'availabilities' => $availabilities,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateAvailability($request);

        $workerProfile = WorkerProfile::findOrFail($validated['worker_profile_id']);

        $this->ensureWorkerIsOperationallyAvailable($workerProfile);

        $this->authorize('create', [Availability::class, $workerProfile]);

        DB::transaction(function () use ($validated) {
            $this->ensureDoesNotOverlap($validated);
            $this->ensureNoCommittedMissionAssignment($validated);

            Availability::create($validated);
        });

        return redirect()->route('availability.index')->with('success', 'Availability slot added successfully.');
    }

    public function update(Request $request, Availability $availability): RedirectResponse
    {
        $validated = $this->validateAvailability($request);

        $targetProfile = WorkerProfile::findOrFail($validated['worker_profile_id']);

        $this->ensureWorkerIsOperationallyAvailable($availability->workerProfile);
        $this->ensureWorkerIsOperationallyAvailable($targetProfile);

        $this->authorize('update', [$availability, $targetProfile]);

        DB::transaction(function () use ($availability, $validated) {
            $this->ensureDoesNotOverlap($validated, $availability);
            $this->ensureNoCommittedMissionAssignment($validated);

            $availability->update($validated);
        });

        return redirect()->route('availability.index')->with('success', 'Availability slot updated successfully.');
    }

    public function destroy(Availability $availability): RedirectResponse
    {
        $this->authorize('delete', $availability);

        $availability->delete();

        return redirect()->route('availability.index')->with('success', 'Availability slot deleted successfully.');
    }

    private function validateAvailability(Request $request): array
    {
        $validated = $request->validate([
            'worker_profile_id' => 'required|exists:worker_profiles,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i,H:i:s',
            'end_time' => 'required|date_format:H:i,H:i:s',
            'status' => 'required|in:available,booked,unavailable',
        ]);

        if ($this->timeToSeconds($validated['end_time']) <= $this->timeToSeconds($validated['start_time'])) {
            throw ValidationException::withMessages([
                'end_time' => __('app.availability_page.validation.end_after_start'),
            ]);
        }

        return $validated;
    }

    private function timeToSeconds(string $time): int
    {
        [$hours, $minutes, $seconds] = array_pad(
            array_map('intval', explode(':', $time)),
            3,
            0
        );

        return ($hours * 3600) + ($minutes * 60) + $seconds;
    }

    private function ensureDoesNotOverlap(array $validated, ?Availability $ignoredAvailability = null): void
    {
        $overlaps = Availability::query()
            ->where('worker_profile_id', $validated['worker_profile_id'])
            ->whereDate('date', $validated['date'])
            ->when($ignoredAvailability, function ($query) use ($ignoredAvailability) {
                $query->whereKeyNot($ignoredAvailability->id);
            })
            ->where('start_time', '<', $validated['end_time'])
            ->where('end_time', '>', $validated['start_time'])
            ->exists();

        if ($overlaps) {
            throw ValidationException::withMessages([
                'start_time' => __('app.availability_page.validation.overlap'),
            ]);
        }
    }

    private function ensureNoCommittedMissionAssignment(array $validated): void
    {
        $hasActiveAssignment = WorkerRequest::query()
            ->where('worker_profile_id', $validated['worker_profile_id'])
            ->whereIn('status', ['accepted', 'ongoing'])
            ->whereHas('mission', function ($query) use ($validated) {
                $query->whereIn('status', ['open', 'in_progress'])
                    ->whereDate('start_date', '<=', $validated['date'])
                    ->whereDate('end_date', '>=', $validated['date']);
            })
            ->exists();

        if ($hasActiveAssignment) {
            throw ValidationException::withMessages([
                'date' => __('app.availability_page.validation.mission_assignment_conflict'),
            ]);
        }
    }

    private function authorizedWorkerProfilesQuery(User $user)
    {
        $query = WorkerProfile::query()->notArchived();

        if ($user->role?->name === 'administrator') {
            return $query;
        }

        if (
            $user->company_id !== null
            && in_array($user->role?->name, ['company_owner', 'planning_manager'], true)
        ) {
            return $query->where('company_id', $user->company_id);
        }

        if ($user->company_id === null && $user->role?->name === 'self_employed') {
            return $query
                ->whereNull('company_id')
                ->where('user_id', $user->id);
        }

        return $query->whereRaw('1 = 0');
    }

    private function ensureWorkerIsOperationallyAvailable(WorkerProfile $workerProfile): void
    {
        if (! $workerProfile->isOperationallyAvailable()) {
            throw ValidationException::withMessages([
                'worker_profile_id' => __('app.availability_page.validation.archived_worker_cannot_receive_availability'),
            ]);
        }
    }
}
