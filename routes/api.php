<?php

use App\Http\Controllers\ApiFetchController;
use App\Http\Controllers\VisitTrackerController;
use Illuminate\Support\Facades\Route;

Route::get('/api-fetches', [ApiFetchController::class, 'index']);
Route::post('/visit', [VisitTrackerController::class, 'collect']);

