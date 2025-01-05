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
Route::put('/toggleWorkerStatus', [\App\Http\Controllers\UserController::class, 'toggleWorkerStatus'])->name('toggleWorkerStatus');
Route::put('/updateWorker', [\App\Http\Controllers\UserController::class, 'updateWorker'])->name('updateWorker');
Route::get('/getClients', [\App\Http\Controllers\UserController::class, 'getClients'])->name('getClients');
Route::put('/updateClient', [\App\Http\Controllers\UserController::class, 'updateClient'])->name('updateClient');

// Rutas del ProductController
Route::get('/getProducts', [\App\Http\Controllers\ProductController::class, 'getProducts'])->name('getProducts');
Route::post('/registerProduct', [\App\Http\Controllers\ProductController::class, 'registerProduct'])->name('registerProduct');
Route::put('/updateProductPrice', [\App\Http\Controllers\ProductController::class, 'updateProductPrice'])->name('updateProductPrice');
Route::put('/replenishStock', [\App\Http\Controllers\ProductController::class, 'replenishStock'])->name('replenishStock');

// Rutas del RentController
Route::get('/getRents', [\App\Http\Controllers\RentController::class, 'getRents'])->name('getRents');
Route::put('/updateRentStatus', [\App\Http\Controllers\RentController::class, 'updateRentStatus'])->name('updateRentStatus');
Route::post('/rentProduct', [\App\Http\Controllers\RentController::class, 'rentProduct'])->name('rentProduct');