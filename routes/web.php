<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

// Login
Route::view('/login', 'auth.login')->name('login');
Route::post('/login', fn () => redirect()->route('admin.dashboard'))->name('login.store'); // temporal
Route::post('/logout', fn () => redirect()->route('login'))->name('logout');               // temporal

// Panel
Route::prefix('admin')->name('admin.')->group(function () {

    Route::view('/', 'admin.dashboard')->name('dashboard');

    // Gestión turística
    Route::view('/destinos', 'admin.destinations.index')->name('destinations.index');
    Route::view('/destinos/crear', 'admin.destinations.create')->name('destinations.create');
    Route::view('/destinos/{id}/editar', 'admin.destinations.edit')->name('destinations.edit');

    // Moderación y reportes
    Route::view('/moderacion', 'admin.moderation.index')->name('moderation.index');
    Route::view('/moderacion/{id}/editar', 'admin.moderation.edit')->name('moderation.edit');
    Route::view('/reportes', 'admin.reports.index')->name('reports.index');
});