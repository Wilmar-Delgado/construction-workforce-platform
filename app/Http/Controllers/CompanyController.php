<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ]);

        $company = Company::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'owner_id' => Auth::id(),
        ]);

        Auth::user()->update([
            'company_id' => $company->id
        ]);

        return redirect()->route('home');
    }
}
