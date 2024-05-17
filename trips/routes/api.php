<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserApiController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\PathController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register',[UserApiController::class,'register']);
Route::post('login',[UserApiController::class,'login']);
Route::post('logout',[UserApiController::class,'logout'])->middleware('auth:sanctum');
Route::post('register/driver',[DriverController::class,'register']);




Route::group(['prefix' => 'driver' , 'middleware' => ['driv']], function () {


});




Route::group(['prefix' => 'admin' , 'middleware' => ['checkAdmi','auth:sanctum']], function () {

    Route::get('/all_path', [PathController::class, 'index']);
    Route::post('/path_store', [PathController::class, 'store']);
    Route::put('/path_update/{id}', [PathController::class, 'update']);
    Route::delete('/path_delete/{id}', [PathController::class, 'destroy']);


});
