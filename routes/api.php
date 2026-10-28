<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ModerationController;

// Públicas
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,11'); // app móvil
Route::post('/admin/login', [AuthController::class, 'adminLogin'])->middleware('throttle:6,1'); // panel web

// Protegidas: requieren token
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    //Reviews
    Route::get('/admin/reports', [ModerationController::class, 'getReports']);
    Route::patch('/admin/reports/{id}/status', [ModerationController::class, 'updateStatus']);
});
