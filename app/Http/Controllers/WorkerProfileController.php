<?php

namespace App\Http\Controllers;

use App\Models\Certification;
use App\Models\Skill;
use App\Models\WorkerProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkerProfileController extends Controller
{
    public function index(Request $request): Response
    {
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');

        $workerProfiles = WorkerProfile::with(['company', 'skills', 'certifications'])
            ->where('user_id', auth()->id())
            ->orderBy($sortField, $sortDirection)
            ->paginate(10)
            ->withQueryString();

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

        return redirect()->route('worker-profiles.index')->with('success', 'Worker profile created successfully.');
    }

    public function update(Request $request, WorkerProfile $workerProfile): RedirectResponse
    {
        if ($workerProfile->user_id !== auth()->id()) {
            abort(403);
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

        return redirect()->route('worker-profiles.index')->with('success', 'Worker profile updated successfully.');
    }

    public function destroy(WorkerProfile $workerProfile): RedirectResponse
    {
        if ($workerProfile->user_id !== auth()->id()) {
            abort(403);
        }

        $workerProfile->delete();

        return redirect()->route('worker-profiles.index')->with('success', 'Worker profile deleted successfully.');
    }
}