<?php

use App\Http\Controllers\Api\V1\PilgrimAppController;
use App\Http\Middleware\EnsureDeviceToken;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['auth:sanctum', EnsureDeviceToken::class, 'throttle:api'])->name('api.v1.')->group(function () {
    Route::get('me', [PilgrimAppController::class, 'me'])->name('me');
    Route::get('schedules', [PilgrimAppController::class, 'schedules'])->name('schedules');
    Route::get('meals', [PilgrimAppController::class, 'meals'])->name('meals');
    Route::get('contacts', [PilgrimAppController::class, 'contacts'])->name('contacts');
    Route::get('places', [PilgrimAppController::class, 'places'])->name('places');
    Route::post('locations', [PilgrimAppController::class, 'storeLocation'])->middleware('throttle:locations')->name('locations.store');
    Route::delete('token', [PilgrimAppController::class, 'signOut'])->name('token.destroy');
});
