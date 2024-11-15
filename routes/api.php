<?php

use Illuminate\Support\Facades\Route;

Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login'])->name('login');
Route::get('/userlogged', [\App\Http\Controllers\AuthController::class, 'userLogged'])->middleware(['api', 'auth']);
Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->middleware(['api', 'auth']);
// Ruta para el restablecimiento de contraseña
Route::post('/resetPassword', [\App\Http\Controllers\AuthController::class, 'resetPassword'])->name('resetPassword');

// Ruta para el registro de usuarios
Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register'])->name('register');


