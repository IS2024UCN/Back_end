<?php

use Illuminate\Support\Facades\Route;

// Rutas del AuthController
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login'])->name('login');
Route::get('/userlogged', [\App\Http\Controllers\AuthController::class, 'userLogged'])->middleware(['api', 'auth']);
Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->middleware(['api', 'auth']);

// Rutas del UserController
Route::post('/register', [\App\Http\Controllers\UserController::class, 'register'])->name('register');

// Rutas del ProductController
Route::get('/getProducts', [\App\Http\Controllers\ProductController::class, 'getProducts'])->name('getProducts');
