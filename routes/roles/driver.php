<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\DriverRateController;
use App\Http\Controllers\DriverVehicleController;

// ==================== DRIVER ZONE ====================
Route::prefix('driver')->middleware('role:driver')->name('driver.')->group(function () {
    Route::get('/dashboard', [DriverController::class, 'dashboard'])->name('dashboard');
    Route::get('/jobs', [DriverController::class, 'jobs'])->name('jobs');
    Route::get('/available-jobs', [DriverController::class, 'availableJobs'])->name('available-jobs');
    Route::post('/jobs/{id}/accept', [DriverController::class, 'accept'])->name('jobs.accept');
    Route::post('/jobs/{id}/start', [DriverController::class, 'start'])->name('jobs.start');
    Route::post('/jobs/{id}/deliver', [DriverController::class, 'deliver'])->name('jobs.deliver');
    Route::post('/jobs/{id}/cancel', [DriverController::class, 'cancel'])->name('jobs.cancel');
    Route::post('/jobs/{order}/proof', [DriverController::class, 'uploadProof'])->name('jobs.proof');
    Route::get('/pickups', [DriverController::class, 'pickupJobs'])->name('pickups');
    Route::post('/pickups/{id}/accept', [DriverController::class, 'acceptPickup'])->name('accept-pickup');
    Route::post('/pickups/{id}/start', [DriverController::class, 'startPickup'])->name('start-pickup');
    Route::post('/pickups/{id}/complete', [DriverController::class, 'completePickup'])->name('complete-pickup');
    Route::post('/pickups/{id}/cancel', [DriverController::class, 'cancelPickup'])->name('cancel-pickup');
    Route::post('/propose-price/{id}', [DriverController::class, 'proposePrice'])->name('propose-price');
    Route::post('/accept-counter/{proposalId}', [DriverController::class, 'acceptCounter'])->name('accept-counter');
    Route::post('/repropose/{proposalId}', [DriverController::class, 'repropose'])->name('repropose');
    Route::get('/earnings', [DriverController::class, 'earnings'])->name('earnings');
    Route::get('/rates', [DriverRateController::class, 'index'])->name('rates');
    Route::post('/rates', [DriverRateController::class, 'store'])->name('rates.store');
    Route::post('/rates/extend', [DriverRateController::class, 'extend'])->name('rates.extend');
    Route::get('/rates/history', [DriverRateController::class, 'history'])->name('rates.history');

    Route::prefix('vehicles')->name('vehicles.')->group(function () {
        Route::get('/', [DriverVehicleController::class, 'index'])->name('index');
        Route::get('/register', [DriverVehicleController::class, 'create'])->name('create');
        Route::get('/register-vehicle', [DriverVehicleController::class, 'create'])->name('register');
        Route::post('/', [DriverVehicleController::class, 'store'])->name('store');
        Route::get('/{id}', [DriverVehicleController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [DriverVehicleController::class, 'edit'])->name('edit');
        Route::put('/{id}', [DriverVehicleController::class, 'update'])->name('update');
        Route::delete('/{id}', [DriverVehicleController::class, 'destroy'])->name('destroy');
    });
});
