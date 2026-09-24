<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    public function create(Request $request): Response|RedirectResponse
    {
        if (! $request->user()->canEstablishCompany()) {
            return redirect()->route('home');
        }

        return Inertia::render('Onboarding/Company');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $user = User::query()
                ->with('role')
                ->lockForUpdate()
                ->findOrFail($request->user()->id);

            if (! $user->canEstablishCompany()) {
                throw ValidationException::withMessages([
                    'company' => [__('app.onboarding.company.validation.not_eligible')],
                ]);
            }

            $company = Company::create([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'owner_id' => $user->id,
            ]);

            $user->update([
                'company_id' => $company->id,
            ]);
        });

        return redirect()->route('home');
    }
}
