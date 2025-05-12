<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\AddMedicineController;

Route::group(['prefix' => 'auth'], function ($router) {
    Route::post('/login', [AuthController::class,'login']);
    Route::post('/register', [AuthController::class,'register']); 
});

Route::middleware(['auth:api'])->group(function(){
Route::post('/refresh', [AuthController::class,'refresh']);
Route::get('/my-profile', [AuthController::class,'myProfile']);
Route::post('/logout', [AuthController::class,'logout']);

});

Route::post('/add-medicine',[AddMedicineController::class,'store']);
Route::get('/medicine',[AddMedicineController::class,'index']);
Route::get('/medicine/{id}',[AddMedicineController::class,'show']);
Route::post('/delete-medicine/{id}',[AddMedicineController::class,'destroy']);
Route::post('/update-medicine/{id}',[AddMedicineController::class,'update']);