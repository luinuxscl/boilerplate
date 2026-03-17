<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['api.auth', 'api.limit'])->group(function (): void {
    Route::get('/me', function (Request $request): \Illuminate\Http\JsonResponse {
        return response()->json(['user' => $request->user()->only('id', 'name', 'email')]);
    })->middleware('api.scopes:profile.read');
});
