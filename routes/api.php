<?php

use App\Http\Controllers\Pub\SeriesController;
use App\Http\Controllers\Pub\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/user/read-list', [UserController::class, 'readList']);

    Route::post('/series/check-follow', [SeriesController::class, 'checkFollow']);
    Route::post('/series/follow', [SeriesController::class, 'follow']);
});
