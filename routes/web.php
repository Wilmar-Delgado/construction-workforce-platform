<?php

use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MissionController;
use App\Http\Controllers\MissionDirectoryController;
use App\Http\Controllers\MissionManagementController;
use App\Http\Controllers\MissionRequestController;
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

Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Core Pages
    |--------------------------------------------------------------------------
    */

    Route::get('/home', [HomeController::class, 'index'])->name('home');
    // Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');


    /*
    |--------------------------------------------------------------------------
    | Onboarding
    |--------------------------------------------------------------------------
    */

    Route::get('/onboarding/company', fn () => Inertia::render('Onboarding/Company'))
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

    /*
    |--------------------------------------------------------------------------
    | Availability
    |--------------------------------------------------------------------------
    */

    Route::resource('availability', AvailabilityController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | Find Workers
    |--------------------------------------------------------------------------
    */

    Route::resource('find-workers', WorkerDirectoryController::class);
    Route::post('/request-worker/{worker}', [WorkerRequestController::class, 'store'])
        ->name('request-worker.store');

    /*
    |--------------------------------------------------------------------------
    | Find Missions
    |-------------------------------------------------------------------------- 
    */

    Route::resource('find-missions', MissionDirectoryController::class);
    Route::post('/request-mission/{mission}', [MissionRequestController::class, 'store'])
        ->name('request-mission.store');
    /*
    |--------------------------------------------------------------------------
    | Missions
    |--------------------------------------------------------------------------
    */

    Route::resource('missions', MissionController::class)
        ->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::put('/missions/{mission}/archive', [MissionController::class, 'archive'])
        ->name('missions.archive');

    /*
    |--------------------------------------------------------------------------
    | Mission Management
    |--------------------------------------------------------------------------
    */

    Route::resource('mission-management', MissionManagementController::class)
        ->only(['index']);
    Route::post('/mission-management/requests/{workerRequest}/respond', [MissionManagementController::class, 'respond'])
        ->name('mission-management.respond');
    Route::post('/mission-management/requests/{workerRequest}/complete', [MissionManagementController::class, 'complete'])
        ->name('mission-management.complete');

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    */

    Route::get('/settings', fn () => Inertia::render('Settings'))->name('settings');

    Route::post('/settings/personal', [SettingController::class, 'updatePersonalInfo'])
        ->name('settings.personal.update');

    Route::post('/settings/notifications', [SettingController::class, 'updateNotifications'])
        ->name('settings.notifications.update');

    Route::delete('/settings/delete-account', [SettingController::class, 'deleteAccount'])
        ->name('settings.delete_account');


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
