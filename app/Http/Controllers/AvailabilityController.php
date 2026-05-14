<?php

namespace App\Http\Controllers;

use App\Models\Availability;
use App\Models\WorkerProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AvailabilityController extends Controller
{
    public function index(Request $request): Response
    {
        $sortField = $request->get('sort', 'date');
        $sortDirection = $request->get('direction', 'asc');
        // Fetch availability data for workers belonging to the authenticated user
        $availability = Availability::query()
            ->join('worker_profiles', 'availabilities.worker_profile_id', '=', 'worker_profiles.id')
            ->where('worker_profiles.user_id', auth()->id())
            ->select('availabilities.*', 'worker_profiles.name as worker_name', 'worker_profiles.job')
            ->orderBy($sortField, $sortDirection)
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Availability', [
            'availability' => $availability,
            'filters' => [
                'sort' => $sortField,
                'direction' => $sortDirection,
            ],
            'workerProfiles' => WorkerProfile::where('user_id', auth()->id())->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'worker_profile_id' => 'required|exists:worker_profiles,id',
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'status' => 'required|string'
        ]);

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
            'status' => 'required|string'
        ]);

        $availability->update($validated);

        return redirect()->route('availability.index')->with('success', 'Availability slot updated successfully.');
    }

    public function destroy(Availability $availability): RedirectResponse
    {
        $availability->delete();

        return redirect()->route('availability.index')->with('success', 'Availability slot deleted successfully.');
    }
}
