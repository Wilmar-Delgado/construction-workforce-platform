<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    /**
     * Update personal information
     */
    public function updatePersonalInfo(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Personal information updated successfully.'
        ]);
    }

    /**
     * Update notification & preference settings
     */
    public function updateNotifications(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'email' => 'boolean',
            'sms' => 'boolean',
            'missionAlerts' => 'boolean',
            'language' => 'required|string',
            'timezone' => 'required|string',
        ]);

        $user->update([
            'notification_email' => $validated['email'],
            'notification_sms' => $validated['sms'],
            'mission_alerts' => $validated['missionAlerts'],
            'language' => $validated['language'],
            'timezone' => $validated['timezone'],
        ]);

        return response()->json([
            'message' => 'Notification preferences updated successfully.'
        ]);
    }

    public function deleteAccount(Request $request)
    {
        $user = Auth::user();
        // Optionally, you can add password confirmation here
        $user->delete();

        return response()->json([
            'message' => 'Account deleted successfully.'
        ]);
    }
}