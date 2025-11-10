<?php

use Modules\Chat\Http\Controllers\Api\{MessageController,RoomController,RoomMemberController};
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


Route::middleware('auth:api')->group(function () {
    Route::get("/me", function (Request $request) { return $request->user(); });
});

