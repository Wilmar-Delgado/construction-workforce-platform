<?php

namespace App\Http\Controllers;

use App\Models\Mission;
use App\Models\WorkerProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MissionDirectoryController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Mission::query()
            ->with(['hiringCompany', 'requirements'])
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

        $workers = WorkerProfile::where('company_id', $user->company_id)
            ->select('id', 'user_id', 'name', 'job')
            ->get();

        return Inertia::render('FindMissions', [
            'missions' => $missions,
            'locations' => $locations,
            'filters' => $request->only(['search', 'job', 'location']),
            'workers' => $workers,
        ]);
    }
}