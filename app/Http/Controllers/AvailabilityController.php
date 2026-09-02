<?php

namespace App\Http\Controllers;

use App\Models\Availability;
use App\Models\WorkerProfile;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AvailabilityController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Availability::class);

        $sortField = $request->get('sort', 'date');
        $sortDirection = $request->get('direction', 'asc');
        $user = auth()->user();

        $availabilityQuery = Availability::query()
            ->join('worker_profiles', 'availabilities.worker_profile_id', '=', 'worker_profiles.id')
            ->select('availabilities.*', 'worker_profiles.name as worker_name', 'worker_profiles.job');

        $workerProfilesQuery = WorkerProfile::query();

        if ($user->role?->name === 'administrator') {
            // Administrators can manage availability across all worker profiles.
        } elseif (
            $user->company_id !== null
            && in_array($user->role?->name, ['company_owner', 'planning_manager'], true)
        ) {
            $availabilityQuery->where('worker_profiles.company_id', $user->company_id);
            $workerProfilesQuery->where('company_id', $user->company_id);
        } elseif ($user->company_id === null && $user->role?->name === 'self_employed') {
            $availabilityQuery
                ->whereNull('worker_profiles.company_id')
                ->where('worker_profiles.user_id', $user->id);
            $workerProfilesQuery
                ->whereNull('company_id')
                ->where('user_id', $user->id);
        } else {
            $availabilityQuery->whereRaw('1 = 0');
            $workerProfilesQuery->whereRaw('1 = 0');
        }

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

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'worker_profile_id' => 'required|exists:worker_profiles,id',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'status' => 'required|in:available,booked,unavailable',
        ]);

        $workerProfile = WorkerProfile::findOrFail($validated['worker_profile_id']);

        $this->authorize('create', [Availability::class, $workerProfile]);

        Availability::create($validated);

        return redirect()->route('availability.index')->with('success', 'Availability slot added successfully.');
    }

    public function update(Request $request, Availability $availability): RedirectResponse
    {
        $validated = $request->validate([
            'worker_profile_id' => 'required|exists:worker_profiles,id',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'status' => 'required|in:available,booked,unavailable',
        ]);

        $targetProfile = WorkerProfile::findOrFail($validated['worker_profile_id']);

        $this->authorize('update', [$availability, $targetProfile]);

        $availability->update($validated);

        return redirect()->route('availability.index')->with('success', 'Availability slot updated successfully.');
    }

    public function destroy(Availability $availability): RedirectResponse
    {
        $this->authorize('delete', $availability);

        $availability->delete();

        return redirect()->route('availability.index')->with('success', 'Availability slot deleted successfully.');
    }
}
