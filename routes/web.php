<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::middleware('auth')->group(function () {
    Route::prefix('api')->group(function () {
        Route::prefix('file')->group(function () {
            Route::post('/open', [FileController::class, 'openFolder'])->name('files.open');
            Route::post('/search', [FileController::class, 'searchFiles'])->name('files.search');
            Route::post('/create', [FileController::class, 'createFolder'])->name('files.create');
            Route::post('/upload', [FileController::class, 'upload'])->name('files.upload');
            Route::post('/delete', [FileController::class, 'delete'])->name('files.destroy');
        });
    });

    Route::get('/dashboard', function () {return Inertia::render('Dashboard/Dashboard');})->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
