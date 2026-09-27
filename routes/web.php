<?php

use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyTeamController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectDirectoryController;
use App\Http\Controllers\ProjectManagementController;
use App\Http\Controllers\ProjectRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\WorkerDirectoryController;
use App\Http\Controllers\WorkerProfileController;
use App\Http\Controllers\WorkerRequestController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});


/*
|--------------------------------------------------------------------------
| Authenticated + Verified Users
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'active', 'verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Core Pages
    |--------------------------------------------------------------------------
    */

    Route::get('/home', [HomeController::class, 'index'])->name('home');


    /*
    |--------------------------------------------------------------------------
    | Onboarding
    |--------------------------------------------------------------------------
    */

    Route::get('/onboarding/company', [CompanyController::class, 'create'])
        ->name('company.onboarding');

    Route::post('/onboarding/company', [CompanyController::class, 'store'])
        ->name('company.store');
    

    /*
    |--------------------------------------------------------------------------
    | Worker Profiles
    |--------------------------------------------------------------------------
    */

    Route::resource('worker-profiles', WorkerProfileController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::put('/worker-profiles/{workerProfile}/archive', [WorkerProfileController::class, 'archive'])
        ->name('worker-profiles.archive');

    /*
    |--------------------------------------------------------------------------
    | Availability
    |--------------------------------------------------------------------------
    */

    Route::get('/availability/calendar', [AvailabilityController::class, 'calendar'])
        ->name('availability.calendar');
    Route::resource('availability', AvailabilityController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | Find Workers
    |--------------------------------------------------------------------------
    */

    Route::resource('find-workers', WorkerDirectoryController::class)
        ->only(['index']);
    Route::post('/request-worker/{worker}', [WorkerRequestController::class, 'store'])
        ->name('request-worker.store');

    /*
    |--------------------------------------------------------------------------
    | Find Projects
    |-------------------------------------------------------------------------- 
    */

    Route::resource('find-projects', ProjectDirectoryController::class)
        ->only(['index']);
    Route::post('/request-project/{project}', [ProjectRequestController::class, 'store'])
        ->name('request-project.store');
    /*
    |--------------------------------------------------------------------------
    | Projects
    |--------------------------------------------------------------------------
    */

    Route::resource('projects', ProjectController::class)
        ->only(['index', 'store', 'update', 'destroy']);
    Route::put('/projects/{project}/archive', [ProjectController::class, 'archive'])
        ->name('projects.archive');

    /*
    |--------------------------------------------------------------------------
    | Project Management
    |--------------------------------------------------------------------------
    */

    Route::resource('project-management', ProjectManagementController::class)
        ->only(['index']);
    Route::get('/project-management/projects/{project}/details', [ProjectManagementController::class, 'details'])
        ->name('project-management.projects.details');
    Route::get('/project-management/workers/{workerProfile}/details', [ProjectManagementController::class, 'workerDetails'])
        ->name('project-management.workers.details');
    Route::post('/project-management/requests/{workerRequest}/respond', [ProjectManagementController::class, 'respond'])
        ->name('project-management.respond');
    Route::post('/project-management/requests/{workerRequest}/complete', [ProjectManagementController::class, 'complete'])
        ->name('project-management.complete');
    Route::post('/project-management/requests/{workerRequest}/end-early', [ProjectManagementController::class, 'endEarly'])
        ->name('project-management.end-early');
    Route::post('/project-management/projects/{project}/close-recruiting', [ProjectManagementController::class, 'closeRecruiting'])
        ->name('project-management.close-recruiting');
    Route::post('/project-management/projects/{project}/start', [ProjectManagementController::class, 'start'])
        ->name('project-management.start');

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    */

    Route::get('/settings', fn () => Inertia::render('Settings', [
        'timezoneOptions' => config('timezones.supported'),
        'canDeactivateAccount' => request()->user()->canBeDeactivated(),
        'companyTeam' => app(CompanyTeamController::class)->payloadFor(request()->user()),
    ]))->name('settings');

    Route::put('/settings/personal', [SettingController::class, 'updatePersonalInfo'])
        ->name('settings.personal.update');

    Route::post('/settings/notifications', [SettingController::class, 'updateNotifications'])
        ->name('settings.notifications.update');

    Route::post('/company-team/invitations', [CompanyTeamController::class, 'storeInvitation'])
        ->name('company-team.invitations.store');
    Route::post('/company-team/invitations/{invitation}/cancel', [CompanyTeamController::class, 'cancelInvitation'])
        ->name('company-team.invitations.cancel');
    Route::delete('/company-team/members/{user}', [CompanyTeamController::class, 'destroyMember'])
        ->name('company-team.members.destroy');

    /*
    |--------------------------------------------------------------------------
    | User Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});


/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';
