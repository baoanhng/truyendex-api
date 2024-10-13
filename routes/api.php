<?php

use App\Http\Controllers\Pub\SeriesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/series/follow', [SeriesController::class, 'follow']);
});
