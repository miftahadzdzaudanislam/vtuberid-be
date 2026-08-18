<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Organization\OrganizationController;
use App\Http\Controllers\Organization\OrganizationSocialAccountController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Vtuber\VtuberController;
use App\Http\Controllers\Vtuber\VtuberOrganizationController;
use App\Http\Controllers\Vtuber\VtuberSocialAccountController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ========================= AUTH ===============================
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/refresh', [AuthController::class, 'refresh']);

// ========================= PUBLIC ROUTES ===============================
Route::get('/vtubers', [VtuberController::class, 'daftarVtuber']);
Route::get('/vtubers/{slug}', [VtuberController::class, 'detailVtuber']);
Route::get('/organizations', [OrganizationController::class, 'daftarOrganization']);
Route::get('/organizations/{slug}', [OrganizationController::class, 'detailOrganization']);
Route::apiResource('/tags', TagController::class)->only('index');

// ========================= AUTH ROUTES ===============================
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/me', [AuthController::class, 'me']);

    // ========================= ADMIN + EDITOR ROUTES ===============================
    Route::middleware('role:admin,editor')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);

        Route::apiResource('/vtubers', VtuberController::class)->except('destroy');
        Route::apiResource('/vtubers/{vtuber}/organizations', VtuberOrganizationController::class);
        Route::apiResource('/vtubers/{vtuber}/social-accounts', VtuberSocialAccountController::class);

        Route::apiResource('/organizations', OrganizationController::class)->except('destroy');
        Route::apiResource('/organizations/{organization}/social-accounts', OrganizationSocialAccountController::class);

        Route::apiResource('/tags', TagController::class);
    });

    // ========================= ADMIN ONLY ROUTES ===============================
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::delete('/vtubers/{id}', [VtuberController::class, 'destroy']);
        Route::delete('/organizations/{id}', [OrganizationController::class, 'destroy']);

        Route::apiResource('/platforms', PlatformController::class);
        Route::apiResource('/users', UserController::class);
    });
});
