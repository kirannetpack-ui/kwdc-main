<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PropertyOwnerController;

// ==================== PROPERTY OWNER ZONE ====================
Route::prefix('property')->middleware('role:property_owner')->name('property.')->group(function () {
    Route::get('/pending', [PropertyOwnerController::class, 'pending'])->name('pending');
    Route::get('/approved', [PropertyOwnerController::class, 'approved'])->name('approved');
    Route::get('/rejected', [PropertyOwnerController::class, 'rejected'])->name('rejected');
    Route::get('/analytics', [PropertyOwnerController::class, 'analytics'])->name('analytics');
    Route::prefix('requests')->name('requests.')->group(function () {
        Route::get('/', [PropertyOwnerController::class, 'requests'])->name('index');
        Route::post('/{id}/approve', [PropertyOwnerController::class, 'approveRequest'])->name('approve');
        Route::post('/{id}/reject', [PropertyOwnerController::class, 'rejectRequest'])->name('reject');
    });
    Route::prefix('warehouses')->name('warehouses.')->group(function () {
        Route::get('/', fn() => redirect()->route('warehouses.index'))->name('index');
        Route::get('/{id}', [PropertyOwnerController::class, 'showWarehouse'])->name('show');
        Route::get('/{id}/edit', [PropertyOwnerController::class, 'editWarehouse'])->name('edit');
        Route::put('/{id}', [PropertyOwnerController::class, 'updateWarehouse'])->name('update');
        Route::delete('/{id}', [PropertyOwnerController::class, 'destroyWarehouse'])->name('destroy');
    });
});
