<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\EquipmentJobController;

// ==================== EQUIPMENT OWNER ZONE ====================
Route::prefix('equipment')->middleware('role:equipment_owner')->name('equipment.')->group(function () {
    Route::get('/dashboard', [EquipmentController::class, 'dashboard'])->name('dashboard');
    Route::get('/register', [EquipmentController::class, 'create'])->name('register');
    Route::post('/', [EquipmentController::class, 'store'])->name('store');
    Route::get('/list', [EquipmentController::class, 'list'])->name('list');
    Route::get('/{id}/edit', [EquipmentController::class, 'edit'])->name('edit');
    Route::put('/{id}', [EquipmentController::class, 'update'])->name('update');
    Route::delete('/{id}', [EquipmentController::class, 'destroy'])->name('destroy');

    // Equipment Jobs & Earnings Aliases
    Route::get('/earnings', [EquipmentJobController::class, 'earnings'])->name('earnings');
    Route::get('/jobs', [EquipmentJobController::class, 'index'])->name('jobs');
    Route::prefix('jobs')->name('jobs.')->group(function () {
        Route::get('/', [EquipmentJobController::class, 'index'])->name('index');
        Route::get('/requests', [EquipmentJobController::class, 'requests'])->name('requests');
        Route::get('/active', [EquipmentJobController::class, 'active'])->name('active');
        Route::get('/history', [EquipmentJobController::class, 'history'])->name('history');
        Route::get('/earnings', [EquipmentJobController::class, 'earnings'])->name('earnings');
        Route::get('/{id}', [EquipmentJobController::class, 'show'])->name('show');
        Route::post('/{id}/accept', [EquipmentJobController::class, 'accept'])->name('accept');
        Route::post('/{id}/reject', [EquipmentJobController::class, 'reject'])->name('reject');
        Route::post('/{id}/propose-price', [EquipmentJobController::class, 'proposePrice'])->name('propose-price');
        Route::post('/{id}/start', [EquipmentJobController::class, 'start'])->name('start');
        Route::post('/{id}/complete', [EquipmentJobController::class, 'complete'])->name('complete');
        Route::post('/{id}/cancel', [EquipmentJobController::class, 'cancel'])->name('cancel');
    });
});
