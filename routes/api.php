<?php

use App\Http\Controllers\Api\orderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\AddMedicineController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {

Route::post('/add-medicine',[AddMedicineController::class,'store']);
Route::get('/medicine',[AddMedicineController::class,'index']);
Route::get('/medicine/{id}',[AddMedicineController::class,'show']);
Route::post('/delete-medicine/{id}',[AddMedicineController::class,'destroy']);
Route::post('/update-medicine/{id}',[AddMedicineController::class,'update']);

    Route::post('/create-order', [OrderController::class,'store']);
    Route::get('/orders',[OrderController::class,'index']);

    Route::get('/order-search',[OrderController::class,'search']);
});


