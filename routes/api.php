<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PreferenceController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login',    [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me',      [AuthController::class, 'me']);

    // Dashboard
    Route::controller(DashboardController::class)->group(function () {
        Route::get('getDashboardStatistic', 'getDashboardStatistic')->name('getDashboardStatistic');
        Route::get('getRecentArticles',     'getRecentArticles')->name('getRecentArticles');
        Route::get('getDigestHistory',      'getDigestHistory')->name('getDigestHistory');
    });

    // Articles
    Route::controller(ArticleController::class)->group(function () {
        Route::get('getArticleData',    'getArticleData')->name('getArticleData');
        Route::get('getArticleDetails', 'getArticleDetails')->name('getArticleDetails');
        Route::post('articleSave',      'articleSave')->name('articleSave');
        Route::post('articleUnsave',    'articleUnsave')->name('articleUnsave');
        Route::get('articleStatistic',  'articleStatistic')->name('articleStatistic');
    });

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

    // Preferences
    Route::controller(PreferenceController::class)->group(function () {
        Route::get('getUserPreferences',    'getUserPreferences')->name('getUserPreferences');
        Route::post('manageUserPreferences','manageUserPreferences')->name('manageUserPreferences');
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
