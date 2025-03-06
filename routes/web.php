<?php

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
            Route::post('/open', [FileController::class, 'openFolder'])->name('folders.open');
            Route::post('/create', [FileController::class, 'createFolder'])->name('folders.create');
            Route::post('/upload', [FileController::class, 'upload'])->name('folders.upload');
            Route::post('/delete', [FileController::class, 'delete'])->name('folders.destroy');
        });
    });

    Route::get('/dashboard', function () {return Inertia::render('Dashboard');})->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
