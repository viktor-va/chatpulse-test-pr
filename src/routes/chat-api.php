<?php

use Modules\Chat\Http\Controllers\Api\{MessageController,RoomController,RoomMemberController};
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


// health for the service
Route::get('/up', fn () => response()->json(['ok' => true]));

Route::middleware('auth:api')->group(function () {
    Route::post('/messages', [MessageController::class, 'store']);
    Route::get('/rooms/{room}/messages', [MessageController::class, 'index']);

    Route::post('/rooms', [RoomController::class, 'store']);
    Route::get('/organizations/{org}/rooms', [RoomController::class, 'index']);

    Route::post('/rooms/{room}/members',  [RoomMemberController::class, 'store']);   // add
    Route::delete('/rooms/{room}/members/{user}', [RoomMemberController::class, 'destroy']); // remove
});
