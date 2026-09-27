<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    /**
     * Update personal information
     */
    public function updatePersonalInfo(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $personalDetails = $request->validate([
            'phone' => 'nullable|string|max:50',
        ]);

        $user->fill([
            ...$request->validated(),
            'phone' => $personalDetails['phone'] ?? null,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()
            ->route('settings')
            ->with('success', __('app.settings_page.personal.success'));
    }

    /**
     * Update notification & preference settings
     */
    public function updateNotifications(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'email' => 'boolean',
            'sms' => 'boolean',
            'projectAlerts' => 'boolean',
            'language' => ['required', 'string', Rule::in(config('locales.supported'))],
            'timezone' => [
                'required',
                'string',
                Rule::in(array_keys(config('timezones.supported'))),
            ],
        ]);

        $user->update([
            'email_notifications' => $validated['email'],
            'sms_notifications' => $validated['sms'],
            'project_alerts' => $validated['projectAlerts'],
            'language' => $validated['language'],
            'timezone' => $validated['timezone'],
        ]);

        session(['locale' => $validated['language']]);

        $updatedUser = $user->fresh()->only([
            'email_notifications',
            'sms_notifications',
            'project_alerts',
            'language',
            'timezone',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('app.settings_page.notifications.success'),
                'user' => $updatedUser,
            ]);
        }

        return redirect()
            ->route('settings')
            ->with('success', __('app.settings_page.notifications.success'));
    }

}
