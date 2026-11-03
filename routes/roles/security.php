<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SecurityDashboardController;
use App\Http\Controllers\SecurityAgencyProfileController;
use App\Http\Controllers\SecurityPersonnelController;
use App\Http\Controllers\SecurityGoodController;
use App\Http\Controllers\SecurityAssignmentController;
use App\Http\Controllers\SecurityIncidentController;

// ==================== SECURITY AGENCY DASHBOARD (Agency's own) ====================
Route::middleware('role:security_agency')->group(function () {
    Route::get('/security/dashboard', [SecurityDashboardController::class, 'index'])->name('security.dashboard');

    // Security agency profile and compliance details
    Route::get('/security/profile', [SecurityAgencyProfileController::class, 'edit'])->name('security.profile');
    Route::post('/security/profile', [SecurityAgencyProfileController::class, 'update'])->name('security.profile.update');

    // Agency's own Personnel
    Route::resource('security/personnel', SecurityPersonnelController::class)
        ->names([
            'index'   => 'security.personnel.index',
            'create'  => 'security.personnel.create',
            'store'   => 'security.personnel.store',
            'show'    => 'security.personnel.show',
            'edit'    => 'security.personnel.edit',
            'update'  => 'security.personnel.update',
            'destroy' => 'security.personnel.destroy',
        ]);

    // Agency's own Goods
    Route::resource('security/goods', SecurityGoodController::class)
        ->names([
            'index'   => 'security.goods.index',
            'create'  => 'security.goods.create',
            'store'   => 'security.goods.store',
            'show'    => 'security.goods.show',
            'edit'    => 'security.goods.edit',
            'update'  => 'security.goods.update',
            'destroy' => 'security.goods.destroy',
        ]);

    // Agency's own Assignments
    Route::resource('security/assignments', SecurityAssignmentController::class)
        ->names([
            'index'   => 'security.assignments.index',
            'create'  => 'security.assignments.create',
            'store'   => 'security.assignments.store',
            'show'    => 'security.assignments.show',
            'edit'    => 'security.assignments.edit',
            'update'  => 'security.assignments.update',
            'destroy' => 'security.assignments.destroy',
        ]);

    // Agency's Incidents
    Route::resource('security/incidents', SecurityIncidentController::class)
        ->only(['index'])
        ->names([
            'index' => 'security.incidents.index',
        ]);
});
