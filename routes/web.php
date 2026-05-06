<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\VisitTrackerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/tracker.js', [VisitTrackerController::class, 'trackerJs'])->name('tracker.js');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/stats', [StatsController::class, 'index'])->name('stats');
    Route::get('/stats/data/hourly', [StatsController::class, 'hourly'])->name('stats.hourly');
    Route::get('/stats/data/cities', [StatsController::class, 'cities'])->name('stats.cities');
});

require __DIR__.'/auth.php';
