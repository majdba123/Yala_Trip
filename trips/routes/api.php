<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserApiController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\PathController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\CompanyController;
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
Route::post('register/company',[CompanyController::class,'register']);


Route::get('/all_path', [PathController::class, 'index'])->middleware('auth:sanctum');






Route::group(['prefix' => 'driver' , 'middleware' => ['driv','auth:sanctum']], function () {

    Route::post('/create_trip', [TripController::class, 'store']);
    Route::put('/trip_update/{id}', [TripController::class, 'update']);
    Route::delete('/trip_delete/{id}', [TripController::class, 'destroy']);


    Route::post('/QR_reservation', [DriverController::class, 'check_QR']);

});



Route::group(['prefix' => 'user' , 'middleware' => ['use','auth:sanctum']], function () {

    Route::post('/all_trip_path/{id}', [TripController::class, 'index_trip']);

    Route::post('/make_reservation/{id}', [ReservationController::class, 'booking']);
    Route::get('/panding_reservation', [ReservationController::class, 'panding_reservation']);

});






Route::group(['prefix' => 'admin' , 'middleware' => ['checkAdmi','auth:sanctum']], function () {
    Route::post('/path_store', [PathController::class, 'store']);
    Route::put('/path_update/{id}', [PathController::class, 'update']);
    Route::delete('/path_delete/{id}', [PathController::class, 'destroy']);

});




Route::group(['prefix' => 'company' , 'middleware' => ['checkAdmi','auth:sanctum']], function () {


});
