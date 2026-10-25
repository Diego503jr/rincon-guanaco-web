<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Login
Route::view('/login', 'auth.login')->name('login');

// Panel
Route::view('/admin', 'admin.dashboard')->name('admin.dashboard');