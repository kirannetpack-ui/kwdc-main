<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientRequestHandler;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientProposalController;
use App\Http\Controllers\ClientEquipmentController;
use App\Http\Controllers\EquipmentRequestController;
use App\Http\Controllers\PickupController;

// ==================== CLIENT WAREHOUSE REQUESTS ====================
Route::prefix('my-requests')->name('my-requests.')->group(function () {
    Route::get('/', [ClientRequestHandler::class, 'index'])->name('index');
    Route::get('/create', [ClientRequestHandler::class, 'create'])->name('create');
    Route::post('/', [ClientRequestHandler::class, 'store'])->name('store');
    Route::get('/{id}', [ClientRequestHandler::class, 'show'])->name('show');
});

// ==================== CLIENT ZONE ====================
Route::prefix('client')->middleware('role:client')->name('client.')->group(function () {
    Route::get('/driver-rates', [ClientController::class, 'driverRates'])->name('driver.rates');
    Route::get('/reports', [ClientController::class, 'reports'])->name('reports');
    Route::get('/my-stock', [ClientRequestHandler::class, 'myStock'])->name('my-stock');
    Route::get('/my-insurance', [ClientRequestHandler::class, 'myInsurance'])->name('my-insurance');
    Route::get('/pickups', [PickupController::class, 'index'])->name('pickups.index');
    Route::get('/proposals', [ClientProposalController::class, 'index'])->name('proposals');

    Route::prefix('proposals')->name('proposals.')->group(function () {
        Route::get('/', [ClientProposalController::class, 'index'])->name('index');
        Route::get('/create/{requestId}', [ClientProposalController::class, 'create'])->name('create');
        Route::post('/', [ClientProposalController::class, 'store'])->name('store');
        Route::get('/{id}', [ClientProposalController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ClientProposalController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ClientProposalController::class, 'update'])->name('update');
        Route::delete('/{id}', [ClientProposalController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/accept', [ClientProposalController::class, 'accept'])->name('accept');
        Route::post('/{id}/reject', [ClientProposalController::class, 'reject'])->name('reject');
        Route::post('/{id}/negotiate', [ClientProposalController::class, 'negotiate'])->name('negotiate');
    });

    Route::prefix('equipment')->name('equipment.')->group(function () {
        Route::get('/requests', [ClientEquipmentController::class, 'index'])->name('requests');
        Route::get('/request', [ClientEquipmentController::class, 'create'])->name('request');
        Route::post('/request', [ClientEquipmentController::class, 'store'])->name('request.store');
        Route::post('/request/{id}/accept', [ClientEquipmentController::class, 'acceptQuote'])->name('request.accept');
        Route::post('/request/{id}/reject', [ClientEquipmentController::class, 'rejectQuote'])->name('request.reject');
        Route::post('/request/{id}/negotiate', [ClientEquipmentController::class, 'negotiatePrice'])->name('request.negotiate');
    });
});

// ==================== EQUIPMENT REQUESTS (Client side) ====================
Route::prefix('equipment-requests')->name('equipment-requests.')->group(function () {
    Route::get('/', [EquipmentRequestController::class, 'index'])->name('index');
    Route::get('/create', [EquipmentRequestController::class, 'create'])->name('create');
    Route::post('/', [EquipmentRequestController::class, 'store'])->name('store');
    Route::get('/{id}', [EquipmentRequestController::class, 'show'])->name('show');
    Route::put('/{id}', [EquipmentRequestController::class, 'update'])->name('update');
    Route::delete('/{id}', [EquipmentRequestController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/approve', [EquipmentRequestController::class, 'approve'])->name('approve');
    Route::post('/{id}/reject', [EquipmentRequestController::class, 'reject'])->name('reject');
    Route::post('/{id}/fulfill', [EquipmentRequestController::class, 'fulfill'])->name('fulfill');
    Route::post('/{id}/return', [EquipmentRequestController::class, 'return'])->name('return');
});
