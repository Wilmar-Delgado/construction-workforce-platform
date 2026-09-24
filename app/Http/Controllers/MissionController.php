<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class MissionController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Mission::class);

        $selectedMission = null;

        if ($request->filled('mission')) {
            $selectedMission = Mission::notArchived()
                ->withCommittedWorkerCount()
                ->with('requirements')
                ->findOrFail($request->integer('mission'));

            $this->authorize('view', $selectedMission);
        }

        $baseQuery = Mission::notArchived()
            ->withCommittedWorkerCount()
            ->with('requirements')
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

        $missions->through(function (Mission $mission): Mission {
            $mission->setAttribute('can_delete', $mission->canBePermanentlyDeleted());
            $mission->setAttribute('can_archive', $mission->canBeArchived());

            return $mission;
        });

        // =========================
        // COUNTS
        // =========================
        $companyMissions = Mission::notArchived()->where(
            'hiring_company_id',
            auth()->user()->company_id
        );

        return Inertia::render('Missions', [
            'missions' => $missions,
            'selectedMission' => $selectedMission,

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
        $this->authorize('create', Mission::class);

        $this->ensureCompanyOperationalContext($request);

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

            'status' => 'required|in:draft,open',

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

    private function ensureCompanyOperationalContext(Request $request): void
    {
        if (! $request->user()->hasCompanyOperationalContext()) {
            throw ValidationException::withMessages([
                'company' => [__('app.common.validation.company_context_required')],
            ]);
        }
    }

    public function update(Request $request, Mission $mission): RedirectResponse
    {
        $this->authorize('update', $mission);

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

            'status' => 'required|in:draft,open',

            'requirements' => 'nullable|array',
            'requirements.*' => 'string|max:255',
        ]);

        DB::transaction(function () use ($mission, $validated): void {
            $lockedMission = Mission::query()
                ->lockForUpdate()
                ->findOrFail($mission->id);

            $this->ensureStandardUpdateIsAllowed($lockedMission, $validated);

            $lockedMission->update(
                collect($validated)->except('requirements')->toArray()
            );

            // Replace old requirements only after the lifecycle/capacity checks pass.
            $lockedMission->requirements()->delete();

            if (! empty($validated['requirements'])) {
                $lockedMission->requirements()->createMany(
                    collect($validated['requirements'])->map(fn ($requirement) => [
                        'name' => $requirement,
                    ])->toArray()
                );
            }
        });

        return redirect()->route('missions.index')->with('success', 'Mission successfully updated.');
    }

    public function destroy(string $id): RedirectResponse
    {
        $mission = Mission::findOrFail($id);

        $this->authorize('delete', $mission);

        DB::transaction(function () use ($mission): void {
            $lockedMission = Mission::query()
                ->lockForUpdate()
                ->findOrFail($mission->id);

            if (! $lockedMission->canBePermanentlyDeleted()) {
                throw ValidationException::withMessages([
                    'mission' => __('app.missions_page.validation.cannot_delete_mission'),
                ]);
            }

            $lockedMission->delete();
        });

        return redirect()->route('missions.index')->with('success', 'Mission successfully deleted.');
    }

    public function archive(Mission $mission): RedirectResponse
    {
        $this->authorize('archive', $mission);

        DB::transaction(function () use ($mission): void {
            $lockedMission = Mission::query()
                ->lockForUpdate()
                ->findOrFail($mission->id);

            if (! $lockedMission->canBeArchived()) {
                throw ValidationException::withMessages([
                    'mission' => __('app.missions_page.validation.cannot_archive_mission'),
                ]);
            }

            $lockedMission->update([
                'archived_at' => now(),
            ]);
        });

        return redirect()
            ->route('missions.index')
            ->with('success', 'Mission successfully archived.');
    }

    private function ensureStandardUpdateIsAllowed(Mission $mission, array $validated): void
    {
        if (! in_array($mission->status, ['draft', 'open'], true)) {
            throw ValidationException::withMessages([
                'status' => __('app.missions_page.validation.lifecycle_managed_status'),
            ]);
        }

        if ($mission->status === 'open' && $validated['status'] !== 'open') {
            throw ValidationException::withMessages([
                'status' => __('app.missions_page.validation.open_mission_must_remain_open'),
            ]);
        }

        $committedWorkerCount = $mission->committedRequests()->count();
        $requestedCapacity = $validated['workers'] ?? null;

        if ($committedWorkerCount > 0
            && ($requestedCapacity === null || (int) $requestedCapacity < $committedWorkerCount)) {
            throw ValidationException::withMessages([
                'workers' => __('app.missions_page.validation.capacity_below_committed', [
                    'count' => $committedWorkerCount,
                ]),
            ]);
        }
    }
}
