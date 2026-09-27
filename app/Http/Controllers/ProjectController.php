<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class ProjectController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Project::class);

        $baseQuery = Project::notArchived()
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
        $projects = $baseQuery
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $projects->through(function (Project $project): Project {
            $project->setAttribute('can_delete', $project->canBePermanentlyDeleted());
            $project->setAttribute('can_archive', $project->canBeArchived());

            return $project;
        });

        // =========================
        // COUNTS
        // =========================
        $companyProjects = Project::notArchived()->where(
            'hiring_company_id',
            auth()->user()->company_id
        );

        return Inertia::render('Projects', [
            'projects' => $projects,

            'filters' => [
                'search' => $request->search,
                'status' => $request->status ?? 'all',
            ],

            'counts' => [
                'all' => (clone $companyProjects)->count(),

                'draft' => (clone $companyProjects)
                    ->where('status', 'draft')
                    ->count(),

                'open' => (clone $companyProjects)
                    ->where('status', 'open')
                    ->count(),

                'in_progress' => (clone $companyProjects)
                    ->where('status', 'in_progress')
                    ->count(),

                'completed' => (clone $companyProjects)
                    ->where('status', 'completed')
                    ->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Project::class);

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

        $project = Project::create([
            ...collect($validated)->except('requirements')->toArray(),
            'hiring_company_id' => auth()->user()->company_id,
            'created_by' => auth()->id(),
        ]);

        if (!empty($validated['requirements'])) {
            $project->requirements()->createMany(
                collect($validated['requirements'])->map(fn ($req) => [
                    'name' => $req
                ])->toArray()
            );
        }

        return redirect()->route('projects.index')->with('success', __('app.projects_page.success.created'));
    }

    private function ensureCompanyOperationalContext(Request $request): void
    {
        if (! $request->user()->hasCompanyOperationalContext()) {
            throw ValidationException::withMessages([
                'company' => [__('app.common.validation.company_context_required')],
            ]);
        }
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

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

        DB::transaction(function () use ($project, $validated): void {
            $lockedProject = Project::query()
                ->lockForUpdate()
                ->findOrFail($project->id);

            $this->ensureStandardUpdateIsAllowed($lockedProject, $validated);

            $lockedProject->update(
                collect($validated)->except('requirements')->toArray()
            );

            // Replace old requirements only after the lifecycle/capacity checks pass.
            $lockedProject->requirements()->delete();

            if (! empty($validated['requirements'])) {
                $lockedProject->requirements()->createMany(
                    collect($validated['requirements'])->map(fn ($requirement) => [
                        'name' => $requirement,
                    ])->toArray()
                );
            }
        });

        return redirect()->route('projects.index')->with('success', __('app.projects_page.success.updated'));
    }

    public function destroy(string $id): RedirectResponse
    {
        $project = Project::findOrFail($id);

        $this->authorize('delete', $project);

        DB::transaction(function () use ($project): void {
            $lockedProject = Project::query()
                ->lockForUpdate()
                ->findOrFail($project->id);

            if (! $lockedProject->canBePermanentlyDeleted()) {
                throw ValidationException::withMessages([
                    'project' => __('app.projects_page.validation.cannot_delete_project'),
                ]);
            }

            $lockedProject->delete();
        });

        return redirect()->route('projects.index')->with('success', __('app.projects_page.success.deleted'));
    }

    public function archive(Project $project): RedirectResponse
    {
        $this->authorize('archive', $project);

        DB::transaction(function () use ($project): void {
            $lockedProject = Project::query()
                ->lockForUpdate()
                ->findOrFail($project->id);

            if (! $lockedProject->canBeArchived()) {
                throw ValidationException::withMessages([
                    'project' => __('app.projects_page.validation.cannot_archive_project'),
                ]);
            }

            $lockedProject->update([
                'archived_at' => now(),
            ]);
        });

        return redirect()
            ->route('projects.index')
            ->with('success', __('app.projects_page.success.archived'));
    }

    private function ensureStandardUpdateIsAllowed(Project $project, array $validated): void
    {
        if (! in_array($project->status, ['draft', 'open'], true)) {
            throw ValidationException::withMessages([
                'status' => __('app.projects_page.validation.lifecycle_managed_status'),
            ]);
        }

        if ($project->status === 'open' && $validated['status'] !== 'open') {
            throw ValidationException::withMessages([
                'status' => __('app.projects_page.validation.open_project_must_remain_open'),
            ]);
        }

        $committedWorkerCount = $project->committedRequests()->count();
        $requestedCapacity = $validated['workers'] ?? null;

        if ($committedWorkerCount > 0
            && ($requestedCapacity === null || (int) $requestedCapacity < $committedWorkerCount)) {
            throw ValidationException::withMessages([
                'workers' => __('app.projects_page.validation.capacity_below_committed', [
                    'count' => $committedWorkerCount,
                ]),
            ]);
        }
    }
}
