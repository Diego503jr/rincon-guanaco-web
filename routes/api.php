<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// Públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,11'); // app móvil
Route::post('/admin/login', [AuthController::class, 'adminLogin'])->middleware('throttle:6,1'); // panel web

// Protegidas: requieren token
Route::middleware('auth:sactum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
