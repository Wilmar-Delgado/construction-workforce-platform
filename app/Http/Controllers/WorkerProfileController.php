<?php

namespace App\Http\Controllers;

use App\Models\Certification;
use App\Models\Skill;
use App\Models\WorkerProfile;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class WorkerProfileController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', WorkerProfile::class);

        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        $user = auth()->user();

        $workerProfilesQuery = WorkerProfile::query()
            ->notArchived()
            ->with(['company', 'skills', 'certifications'])
            ->withAvg('ratings', 'score')
            ->withCount('ratings');

        if ($user->role?->name === 'administrator') {
            // Administrators can manage profiles across all companies.
        } elseif ($user->company_id !== null) {
            $workerProfilesQuery->where('company_id', $user->company_id);
        } else {
            $workerProfilesQuery
                ->whereNull('company_id')
                ->where('user_id', $user->id);
        }

        $workerProfiles = $workerProfilesQuery
            ->orderBy($sortField, $sortDirection)
            ->paginate(10)
            ->withQueryString();

        $workerProfiles->through(function (WorkerProfile $workerProfile): WorkerProfile {
            $workerProfile->setAttribute('can_delete', $workerProfile->canBePermanentlyDeleted());
            $workerProfile->setAttribute('can_archive', $workerProfile->canBeArchived());

            return $workerProfile;
        });

        return Inertia::render('WorkerProfiles', [
            'workerProfiles' => $workerProfiles,
            'filters' => [
                'sort' => $sortField,
                'direction' => $sortDirection,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', WorkerProfile::class);

        if (! $request->user()->hasCompanyOperationalContext()
            && ! $request->user()->isSelfEmployed()) {
            throw ValidationException::withMessages([
                'company' => [__('app.common.validation.company_context_required')],
            ]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'job' => 'required|string|max:255',
            'experience' => 'required|integer|min:0',
            'rate' => 'required|numeric|min:0',
            'certifications' => 'nullable|array',
            'skills' => 'array'
        ]);

        $worker = WorkerProfile::create([
            'user_id' => auth()->id(),
            'company_id' => auth()->user()->company_id,
            'name' => $validated['name'],
            'job' => $validated['job'],
            'years_experience' => $validated['experience'],
            'hourly_rate' => $validated['rate'],
        ]);
        
        // Attach certifications
        if (!empty($validated['certifications'])) {
            $certificationIds = [];

            foreach ($validated['certifications'] as $certName) {
                // $certName = ucwords(strtolower(trim($certName)));
                $cert = Certification::firstOrCreate([
                    'name' => $certName
                ]);

                $certificationIds[] = $cert->id;
            }

            $worker->certifications()->sync($certificationIds);
        }

        // Attach skills
        if (!empty($validated['skills'])) {

            $skillIds = [];

            foreach ($validated['skills'] as $skillName) {
                // $skillName = ucwords(strtolower(trim($skillName)));
                $skill = Skill::firstOrCreate([
                    'name' => $skillName
                ]);

                $skillIds[] = $skill->id;
            }

            $worker->skills()->sync($skillIds);
        }

        return redirect()->route('worker-profiles.index')->with('success', __('app.profiles_page.success.created'));
    }

    public function update(Request $request, WorkerProfile $workerProfile): RedirectResponse
    {
        $this->authorize('update', $workerProfile);

        if (! $workerProfile->isOperationallyAvailable()) {
            throw ValidationException::withMessages([
                'worker_profile' => [__('app.profiles_page.validation.cannot_edit_archived_worker')],
            ]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'job' => 'required|string|max:255',
            'experience' => 'required|integer|min:0',
            'rate' => 'required|numeric|min:0',
            'certifications' => 'nullable|array',
            'skills' => 'array'
        ]);

        $workerProfile->update([
            'name' => $validated['name'],
            'job' => $validated['job'],
            'years_experience' => $validated['experience'],
            'hourly_rate' => $validated['rate'],
        ]);

        // Update certifications
        if (!empty($validated['certifications'])) {
            $certificationIds = [];

            foreach ($validated['certifications'] as $certName) {
                // $certName = ucwords(strtolower(trim($certName)));
                $cert = Certification::firstOrCreate([
                    'name' => $certName
                ]);
                $certificationIds[] = $cert->id;
            }

            $workerProfile->certifications()->sync($certificationIds);
        } else {
            $workerProfile->certifications()->sync([]);
        }

        // Update skills
        if (!empty($validated['skills'])) {
            $skillIds = [];

            foreach ($validated['skills'] as $skillName) {
                // $skillName = ucwords(strtolower(trim($skillName)));
                $skill = Skill::firstOrCreate([
                    'name' => $skillName
                ]);
                $skillIds[] = $skill->id;
            }

            $workerProfile->skills()->sync($skillIds);
        } else {
            $workerProfile->skills()->sync([]);
        }

        return redirect()->route('worker-profiles.index')->with('success', __('app.profiles_page.success.updated'));
    }

    public function destroy(WorkerProfile $workerProfile): RedirectResponse
    {
        $this->authorize('delete', $workerProfile);

        DB::transaction(function () use ($workerProfile): void {
            $lockedWorkerProfile = WorkerProfile::query()
                ->lockForUpdate()
                ->findOrFail($workerProfile->id);

            if (! $lockedWorkerProfile->canBePermanentlyDeleted()) {
                throw ValidationException::withMessages([
                    'worker_profile' => [__('app.profiles_page.validation.cannot_delete_worker')],
                ]);
            }

            $lockedWorkerProfile->delete();
        });

        return redirect()->route('worker-profiles.index')->with('success', __('app.profiles_page.success.deleted'));
    }

    public function archive(WorkerProfile $workerProfile): RedirectResponse
    {
        $this->authorize('archive', $workerProfile);

        DB::transaction(function () use ($workerProfile): void {
            $lockedWorkerProfile = WorkerProfile::query()
                ->lockForUpdate()
                ->findOrFail($workerProfile->id);

            if (! $lockedWorkerProfile->canBeArchived()) {
                throw ValidationException::withMessages([
                    'worker_profile' => [__('app.profiles_page.validation.cannot_archive_worker')],
                ]);
            }

            $lockedWorkerProfile->update([
                'archived_at' => now(),
            ]);
        });

        return redirect()->route('worker-profiles.index')->with('success', __('app.profiles_page.success.archived'));
    }
}
