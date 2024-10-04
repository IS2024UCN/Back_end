<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('login', [\App\Http\Controllers\AuthController::class, 'login'])->name('login');
Route::get('userlogged', [\App\Http\Controllers\AuthController::class, 'userLogged'])->middleware(['api', 'auth']);
Route::post('logout', [\App\Http\Controllers\AuthController::class, 'logout'])->middleware(['api', 'auth']);

// Ruta para el registro de usuarios
Route::post('/register', [\app\Http\Controllers\AuthController::class, 'register']);