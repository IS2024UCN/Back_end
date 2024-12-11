<?php

use Illuminate\Support\Facades\Route;

// Rutas del AuthController
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'login'])->name('login');
Route::get('/userlogged', [\App\Http\Controllers\AuthController::class, 'userLogged'])->middleware(['api', 'auth']);
Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->middleware(['api', 'auth']);
Route::post('/register', [\App\Http\Controllers\AuthController::class, 'register'])->name('register');

// Rutas del UserController
Route::post('/update-Password', [\App\Http\Controllers\UserController::class, 'updatePassword'])->middleware(['api', 'auth']);
Route::post('/registerWorker', [\App\Http\Controllers\UserController::class, 'registerWorker'])->name('registerWorker');
Route::get('/getWorkers', [\App\Http\Controllers\UserController::class, 'getWorkers'])->name('getWorkers');
Route::put('/workers/{id}/toggle-status', [\App\Http\Controllers\UserController::class, 'toggleWorkerStatus'])->name('toggleWorkerStatus');

// Rutas del ProductController
Route::get('/getProducts', [\App\Http\Controllers\ProductController::class, 'getProducts'])->name('getProducts');

// Rutas para gestionar trabajadores
Route::middleware('auth')->group(function () {
    Route::put('/workers/{id}', [\App\Http\Controllers\UserController::class, 'updateWorker']);
});