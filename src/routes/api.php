<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Modules\Auth\Http\Controllers\AuthController;
use Modules\Auth\Models\User;
use Modules\Org\Models\Membership;
use Modules\Org\Models\Organization;


Route::middleware('auth:sanctum')->group(function () {
    Route::get("/me", function (Request $request) { return $request->user(); });

    Route::get('/organizations', function () {
        $userId = auth()->id();
        $orgIds = Membership::where('user_id', $userId)
            ->pluck('organization_id');
        $orgs = Organization::select('id', 'name', 'slug')
            ->whereIn('id', $orgIds)
            ->orderBy('name')
            ->get();

        return response()->json($orgs);
    });

    Route::get('/users', function () {
        return User::select('id', 'name', 'email')->get();
    });

    Route::delete('/token', [AuthController::class, 'revokeCurrentToken']);
});

Route::post('/token', [AuthController::class, 'issueToken']);


