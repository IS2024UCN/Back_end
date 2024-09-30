<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

    
Route::post('login', [\App\Http\Controllers\AuthController::class, 'login'])->name('login');
Route::get('userlogged', [\App\Http\Controllers\AuthController::class, 'userLogged'])->middleware(['api', 'auth']);
Route::post('logout', [\App\Http\Controllers\AuthController::class, 'logout'])->middleware(['api', 'auth']);
