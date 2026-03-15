<?php

use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\SavedRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('workspace'));

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

// Authenticated routes (password change allowed even when must_change_password=true)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/change-password', [ChangePasswordController::class, 'show'])->name('password.change');
    Route::post('/change-password', [ChangePasswordController::class, 'update'])->name('password.change.update');
});

// Authenticated + password change enforced
Route::middleware(['auth', 'must.change.password'])->group(function () {
    Route::get('/workspace', fn () => view('workspace'))->name('workspace');

    // Collections (JSON)
    Route::get('/collections', [CollectionController::class, 'index'])->name('collections.index');
    Route::post('/collections', [CollectionController::class, 'store'])->name('collections.store');
    Route::patch('/collections/{collection}', [CollectionController::class, 'update'])->name('collections.update');
    Route::delete('/collections/{collection}', [CollectionController::class, 'destroy'])->name('collections.destroy');

    // Folders (JSON)
    Route::post('/collections/{collection}/folders', [FolderController::class, 'store'])->name('folders.store');
    Route::patch('/collections/{collection}/folders/{folder}', [FolderController::class, 'update'])->name('folders.update');
    Route::delete('/collections/{collection}/folders/{folder}', [FolderController::class, 'destroy'])->name('folders.destroy');

    // Requests (JSON)
    Route::get('/requests/{request}', [SavedRequestController::class, 'show'])->name('requests.show');
    Route::post('/requests', [SavedRequestController::class, 'store'])->name('requests.store');
    Route::patch('/requests/{savedRequest}', [SavedRequestController::class, 'update'])->name('requests.update');
    Route::delete('/requests/{savedRequest}', [SavedRequestController::class, 'destroy'])->name('requests.destroy');
    Route::get('/collections/{collection}/requests', [SavedRequestController::class, 'indexForCollection'])->name('requests.for-collection');
    Route::get('/collections/{collection}/folders/{folder}/requests', [SavedRequestController::class, 'indexForFolder'])->name('requests.for-folder');
});

// Super admin only
Route::middleware(['auth', 'must.change.password', 'super.admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::delete('/users/{id}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    });
