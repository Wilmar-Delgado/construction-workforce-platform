<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class MissionController extends Controller
{
    public function index(Request $request): Response
    {
        $baseQuery = Mission::with('requirements')
            ->where('hiring_company_id', auth()->user()->company_id);

        // =========================
        // SEARCH
        // =========================
        $baseQuery->when($request->search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        });

        // =========================
        // STATUS FILTER
        // =========================
        $baseQuery->when(
            $request->status &&
            $request->status !== 'all',

            function ($query) use ($request) {
                $query->where('status', $request->status);
            }
        );

        // =========================
        // PAGINATION
        // =========================
        $missions = $baseQuery
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // =========================
        // COUNTS
        // =========================
        $companyMissions = Mission::where(
            'hiring_company_id',
            auth()->user()->company_id
        );

        return Inertia::render('Missions', [
            'missions' => $missions,

            'filters' => [
                'search' => $request->search,
                'status' => $request->status ?? 'all',
            ],

            'counts' => [
                'all' => (clone $companyMissions)->count(),

                'draft' => (clone $companyMissions)
                    ->where('status', 'draft')
                    ->count(),

                'open' => (clone $companyMissions)
                    ->where('status', 'open')
                    ->count(),

                'in_progress' => (clone $companyMissions)
                    ->where('status', 'in_progress')
                    ->count(),

                'completed' => (clone $companyMissions)
                    ->where('status', 'completed')
                    ->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',

            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',

            'city' => 'required|string|max:255',
            'province' => 'required|string|max:100',
            'country' => 'required|string|max:100',

            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'site_name' => 'nullable|string|max:255',
            'directions' => 'nullable|string',

            'job_type' => 'required|string|max:255',
            'workers' => 'nullable|integer|min:1',
            'hourly_rate' => 'nullable|numeric|min:0',

            'status' => 'required|in:draft,open,in_progress,completed,cancelled',

            'requirements' => 'nullable|array',
            'requirements.*' => 'string|max:255',
        ]);

        $mission = Mission::create([
            ...collect($validated)->except('requirements')->toArray(),
            'hiring_company_id' => auth()->user()->company_id,
            'created_by' => auth()->id(),
        ]);

        if (!empty($validated['requirements'])) {
            $mission->requirements()->createMany(
                collect($validated['requirements'])->map(fn ($req) => [
                    'name' => $req
                ])->toArray()
            );
        }

        return redirect()->route('missions.index')->with('success', 'Mission successfully created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): Response
    {
        $mission = Mission::with(['hiringCompany', 'lendingCompany', 'creator', 'workerProfile', 'requirements'])
            ->findOrFail($id);

        return Inertia::render('MissionDetails', [
            'mission' => $mission,
        ]);
    }

    public function update(Request $request, Mission $mission): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',

            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',

            'city' => 'required|string|max:255',
            'province' => 'required|string|max:100',
            'country' => 'required|string|max:100',

            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'site_name' => 'nullable|string|max:255',
            'directions' => 'nullable|string',

            'job_type' => 'required|string|max:255',
            'workers' => 'nullable|integer|min:1',
            'hourly_rate' => 'nullable|numeric|min:0',

            'status' => 'required|in:draft,open,in_progress,completed,cancelled',

            'requirements' => 'nullable|array',
            'requirements.*' => 'string|max:255',
        ]);

        $mission->update(
            collect($validated)->except('requirements')->toArray()
        );

        // replace old requirements
        $mission->requirements()->delete();

        if (!empty($validated['requirements'])) {
            $mission->requirements()->createMany(
                collect($validated['requirements'])->map(fn ($req) => [
                    'name' => $req
                ])->toArray()
            );
        }

        return redirect()->route('missions.index')->with('success', 'Mission successfully updated.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $mission = Mission::findOrFail($id);

        if ($mission->hiring_company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $mission->delete();

        return redirect()->route('missions.index')->with('success', 'Mission successfully deleted.');
    }
}
