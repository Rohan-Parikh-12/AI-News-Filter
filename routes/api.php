<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login',    [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me',      [AuthController::class, 'me']);

    // Categories
    Route::controller(CategoryController::class)->group(function () {
        Route::get('getCategoryData',       'getCategoryData')->name('getCategoryData');
        Route::get('getCategoryDetails',    'getCategoryDetails')->name('getCategoryDetails');
        Route::post('manageCategory',       'manageCategory')->name('manageCategory');
        Route::post('categoryDelete',       'categoryDelete')->name('categoryDelete');
        Route::post('categoryStatusChange', 'categoryStatusChange')->name('categoryStatusChange');
        Route::get('categoryStatistic',     'categoryStatistic')->name('categoryStatistic');
        Route::get('categoryHistory',       'categoryHistory')->name('categoryHistory');
    });

    // Roles
    Route::controller(RoleController::class)->group(function () {
        Route::get('getRoleData',       'getRoleData')->name('getRoleData');
        Route::get('getRoleDetails',    'getRoleDetails')->name('getRoleDetails');
        Route::post('manageRole',       'manageRole')->name('manageRole');
        Route::post('roleDelete',       'roleDelete')->name('roleDelete');
        Route::get('roleStatistic',     'roleStatistic')->name('roleStatistic');
        Route::get('getAllPermissions', 'getAllPermissions')->name('getAllPermissions');
    });

    // Permissions
    Route::controller(PermissionController::class)->group(function () {
        Route::get('getPermissionData',    'getPermissionData')->name('getPermissionData');
        Route::get('getPermissionDetails', 'getPermissionDetails')->name('getPermissionDetails');
        Route::post('managePermission',    'managePermission')->name('managePermission');
        Route::post('permissionDelete',    'permissionDelete')->name('permissionDelete');
        Route::get('permissionStatistic',  'permissionStatistic')->name('permissionStatistic');
    });
});
