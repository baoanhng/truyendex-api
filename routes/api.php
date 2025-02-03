<?php

use App\Http\Controllers\Pub\CommentController;
use App\Http\Controllers\Pub\SeriesController;
use App\Http\Controllers\Pub\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Auth::loginUsingId(1);

## Unregistered
Route::get('/comment/list', [CommentController::class, 'list']);
Route::get('/comment/recent', [CommentController::class, 'recent']);
Route::post('/comment/fetch-reply', [CommentController::class, 'fetchReply']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [UserController::class, 'info']);

    Route::middleware(['verified'])->group(function () {
        Route::get('/user/read-list', [UserController::class, 'readList']);
        Route::post('/user/read-list/sync', [UserController::class, 'syncReadList']);

        Route::post('/series/check-info', [SeriesController::class, 'checkInfo']);

        Route::post('/series/follow', [SeriesController::class, 'follow']);
        Route::post('/series/follows', [SeriesController::class, 'follows']);

        Route::post('/comment/store', [CommentController::class, 'store']);
        Route::post('/comment/update', [CommentController::class, 'update']);
        Route::post('/comment/delete', [CommentController::class, 'delete']);
    });
});
