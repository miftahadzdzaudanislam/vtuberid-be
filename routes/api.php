<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Organization\OrganizationController;
use App\Http\Controllers\Organization\OrganizationSocialAccountController;
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

    // ========================= ADMIN ROUTES ===============================
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', [AdminController::class, 'dashboardAdmin']);

        Route::apiResource('/admin/vtubers', VtuberController::class);
        Route::apiResource('/admin/vtubers/{vtuber}/organizations', VtuberOrganizationController::class);
        Route::apiResource('/admin/vtubers/{vtuber}/social-accounts', VtuberSocialAccountController::class);
        Route::apiResource('/admin/organizations', OrganizationController::class);
        Route::apiResource('/admin/organizations/{organization}/social-accounts', OrganizationSocialAccountController::class);
        Route::apiResource('/admin/tags', TagController::class);

        Route::apiResource('/admin/users', UserController::class);
    });

    // ========================= EDITOR ROUTES ===============================
    Route::middleware('role:editor')->group(function () {
        Route::get('/editor/dashboard', [AdminController::class, 'dashboardEditor']);

        Route::apiResource('/editor/vtubers', VtuberController::class)
            ->only(['index', 'store', 'show', 'update']);
        Route::apiResource('/editor/organizations', OrganizationController::class)
            ->only(['index', 'store', 'show', 'update']);
        Route::apiResource('/editor/tags', TagController::class)
            ->only(['index', 'store', 'show', 'update']);
    });
});
