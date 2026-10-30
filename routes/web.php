<?php

use App\Http\Controllers\Admin\AgencyController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeviceController;
use App\Http\Controllers\Admin\FlightGroupController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\MealController;
use App\Http\Controllers\Admin\PilgrimController;
use App\Http\Controllers\Admin\PlaceController;
use App\Http\Controllers\Admin\PrayerController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\StaffController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('flight-groups', FlightGroupController::class)->except('show');
    Route::resource('groups', GroupController::class)->except('show');
    Route::resource('pilgrims', PilgrimController::class);
    Route::resource('schedules', ScheduleController::class)->except('show');
    Route::resource('meals', MealController::class)->except('show');
    Route::resource('contacts', ContactController::class)->except('show');

    Route::get('devices', [DeviceController::class, 'index'])->name('devices.index');
    Route::post('pilgrims/{pilgrim}/devices', [DeviceController::class, 'store'])->name('pilgrims.devices.store');
    Route::delete('devices/{device}', [DeviceController::class, 'destroy'])->name('devices.destroy');

    Route::get('locations', [LocationController::class, 'index'])->name('locations.index');

    Route::resource('staff', StaffController::class)->except('show')->parameters(['staff' => 'user']);

    Route::middleware('can:manage-system')->group(function () {
        Route::resource('agencies', AgencyController::class)->except('show');
        Route::resource('places', PlaceController::class)->except('show');
        Route::post('places/{place}/prayers', [PrayerController::class, 'store'])->name('places.prayers.store');
        Route::put('prayers/{prayer}', [PrayerController::class, 'update'])->name('prayers.update');
        Route::delete('prayers/{prayer}', [PrayerController::class, 'destroy'])->name('prayers.destroy');
    });
});

require __DIR__.'/settings.php';
