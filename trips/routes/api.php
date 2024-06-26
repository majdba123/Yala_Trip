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
use App\Http\Controllers\BreakingController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\ChargeBalanceController;
use App\Http\Controllers\PrivateTripController;
use App\Http\Controllers\OrderPrivateController;
use App\Http\Controllers\BusController;
use App\Http\Controllers\DriverCompanyController;
use App\Http\Controllers\CompTripController;
use App\Http\Controllers\ContuctUsController;
use App\Http\Controllers\TicktController;
use App\Http\Controllers\RateComapnyController;
use App\Http\Controllers\SubscriptionsController;
use App\Http\Controllers\UserSubscriptionController;

use App\Http\Controllers\AdminController;






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

Route::post('get_break_path/{id}',[BreakingController::class,'getBreakingsByPathId'])->middleware('auth:sanctum');

























Route::group(['prefix' => 'driver' , 'middleware' => ['driv','auth:sanctum']], function () {

    Route::post('/create_trip', [TripController::class, 'store']);
    Route::put('/trip_update/{id}', [TripController::class, 'update']);
    Route::delete('/trip_delete/{id}', [TripController::class, 'destroy']);


    Route::post('/QR_reservation', [DriverController::class, 'check_QR_COM']);
    Route::post('/QR_reservation_finished', [DriverController::class, 'check_QR_finished']);
    Route::post('/out_resevation/{id}', [DriverController::class, 'out_reservation']);
    Route::get('/current_trip', [DriverController::class, 'current_trip']);
    Route::post('/current_reservation/{id}', [DriverController::class, 'current_reservation']);
    Route::post('/current_reservation_break/{id}', [DriverController::class, 'resr_of_breaks']);
    Route::post('/trip_finished/{id}', [DriverController::class, 'finish_trip']);
    Route::post('/break_finished/{id}', [DriverController::class, 'break_finished']);
    Route::get('/get_info', [DriverController::class, 'info']);
    Route::post('/update_profile', [DriverController::class, 'updateProfile']);
    Route::post('/history_trip', [DriverController::class, 'trip_history']);


    Route::get('/display_private_trip', [PrivateTripController::class, 'driver_order']);

    Route::post('/request_private_order/{id}', [OrderPrivateController::class, 'get_private_order_by_driver']);
    Route::post('/finished_oder_private/{id}', [OrderPrivateController::class, 'finished_oder_private']);

    Route::post('/history_order_private_trip', [DriverController::class, 'history_order_private_trip']);
});
















Route::group(['prefix' => 'user' , 'middleware' => ['use','auth:sanctum']], function () {

    Route::post('/all_trip_path/{id}', [TripController::class, 'index_trip']);

    Route::post('/make_reservation/{id}', [ReservationController::class, 'booking']);
    Route::post('/panding_reservation', [ReservationController::class, 'panding_reservation']);

    Route::post('/rate_trip/{id}', [RatingController::class, 'createRating']);

    Route::post('/charge_blance', [ChargeBalanceController::class, 'store']);


    Route::get('/get_info', [UserApiController::class, 'info']);
    Route::post('/update_profile', [UserApiController::class, 'updateProfile']);

    Route::post('/book_private_trip', [PrivateTripController::class, 'store']);
    Route::put('/book_private_trip_update/{id}', [PrivateTripController::class, 'update']);
    Route::post('/book_private_trip_delete/{id}', [PrivateTripController::class, 'destroy']);

    Route::get('/my_private_trip', [PrivateTripController::class, 'my_private_trip']);

    Route::post('/get_order_private/{id}', [OrderPrivateController::class, 'get_order_private']);
    Route::post('/accept_order_private/{id}', [OrderPrivateController::class, 'accept_order_private']);

    Route::post('/history_order_private_trip', [UserApiController::class, 'history_order_private_trip']);

    Route::post('/all_unversity_trip', [CompTripController::class, 'index']);

    Route::post('/store_tickit/{id}', [TicktController::class, 'store']);


    Route::post('/contuct_us', [ContuctUsController::class, 'store']);
    Route::put('/contuct_us_update/{id}', [ContuctUsController::class, 'update']);
    Route::delete('/contuct_us_delete/{id}', [ContuctUsController::class, 'destroy']);


    Route::get('/all_available_subscription', [SubscriptionsController::class, 'index']);
    Route::post('/all_subscription_by_company/{id}', [SubscriptionsController::class, 'by_company']);


    Route::post('/get_subscription/{id}', [UserSubscriptionController::class, 'store']);

    Route::get('/compant_all', [CompanyController::class, 'company']);

    Route::post('/rate_company/{id}', [RateComapnyController::class, 'store']);


    Route::get('/history_trip_unversity', [UserApiController::class, 'history_unversity']);

    Route::get('/all_subscription', [UserApiController::class, 'subscription']);

});






Route::group(['prefix' => 'admin' , 'middleware' => ['checkAdmi','auth:sanctum']], function () {
    Route::post('/path_store', [PathController::class, 'store']);
    Route::put('/path_update/{id}', [PathController::class, 'update']);
    Route::delete('/path_delete/{id}', [PathController::class, 'destroy']);

    Route::post('/break_store/{id}', [BreakingController::class, 'store']);
    Route::put('/break_update/{id}', [BreakingController::class, 'update']);
    Route::delete('/break_delete/{id}', [BreakingController::class, 'destroy']);
    Route::get('/all_break', [BreakingController::class, 'index']);

    Route::get('/all_user', [AdminController::class, 'all_user']);
    Route::post('/this_user/{id}', [AdminController::class, 'show_user']);
    Route::delete('/block_user/{id}', [AdminController::class, 'block_user']);

    Route::get('/all_company', [AdminController::class, 'all_company']);
    Route::post('/show_company/{id}', [AdminController::class, 'company_info']);
    Route::delete('/delete_company/{id}', [AdminController::class, 'delete_company']);


    Route::get('/all_driver', [AdminController::class, 'all_driver']);
    Route::post('/show_driver/{id}', [AdminController::class, 'driver_info']);
    Route::delete('/delete_driver/{id}', [AdminController::class, 'delete_driver']);

    Route::get('/all_trip', [AdminController::class, 'all_trip']);
    Route::post('/show_trip/{id}', [AdminController::class, 'trip_info']);


    Route::get('/all_reservation', [AdminController::class, 'all_reservation']);
    Route::post('/show_reservation/{id}', [AdminController::class, 'reservation_info']);

    Route::get('/all_private_trip', [AdminController::class, 'all_private_trip']);
    Route::post('/show_private_trip/{id}', [AdminController::class, 'private_trip_info']);

    Route::get('/all_contact_as', [AdminController::class, 'contuct_as']);
    Route::post('/change_status_contuct/{id}', [AdminController::class, 'change_status_contuct']);






});




Route::group(['prefix' => 'company' , 'middleware' => ['company','auth:sanctum']], function () {
    Route::post('register/driver',[CompanyController::class,'register_driver']);

    Route::post('/bus_store', [BusController::class, 'store']);
    Route::put('/bus_update/{id}', [BusController::class, 'update']);
    Route::delete('/bus_delete/{id}', [BusController::class, 'destroy']);
    Route::get('/all_bus', [BusController::class, 'index']);
    Route::post('/bus_by_status', [BusController::class, 'bus_by_status']);
    Route::post('/show_bus/{id}', [BusController::class, 'show']);

    Route::get('/all_driver', [DriverCompanyController::class, 'index']);
    Route::post('/all_driver_by_status', [DriverCompanyController::class, 'driver_by_status']);
    Route::post('/block_driver/{id}', [DriverCompanyController::class, 'block_driver']);
    Route::post('/show_driver/{id}', [DriverCompanyController::class, 'show']);
    Route::post('/trip_of_driver/{id}', [DriverCompanyController::class, 'trip_driver']);




    Route::get('/all_trip_company', [CompTripController::class, 'all_comp_trip']);
    Route::post('/store_trip', [CompTripController::class, 'store']);
    Route::post('/show_trip/{id}', [CompTripController::class, 'show']);
    Route::put('/company_trip_update/{id}', [CompTripController::class, 'update']);
    Route::delete('/company_trip_delete/{id}', [CompTripController::class, 'destroy']);


    Route::post('/show_bus_trip/{id}', [CompTripController::class, 'show_bus_trip']);


    Route::post('/store_subscription', [SubscriptionsController::class, 'store']);
    Route::put('/update_subscription/{id}', [SubscriptionsController::class, 'update']);
    Route::delete('/subscription_delete/{id}', [SubscriptionsController::class, 'destroy']);

    Route::get('/dashboard', [CompanyController::class, 'dashboard']);

    Route::get('/ALL_SUBSCRIPTION', [SubscriptionsController::class, 'index_company']);
    Route::post('/show_SUBSCRIPTION/{id}', [SubscriptionsController::class, 'show']);


    Route::delete('/delete_client/{id}', [UserSubscriptionController::class, 'delete_user']);



});







Route::group(['prefix' => 'driver_company' , 'middleware' => ['driv_comp','auth:sanctum']], function () {

    Route::get('/my_trip', [DriverCompanyController::class, 'my_trip']);
    Route::post('/ticket_of_trip/{id}', [DriverCompanyController::class, 'ticket_trip']);
    Route::post('/qr_ticket', [DriverCompanyController::class, 'get_QR']);

    Route::post('/stat_trip_going/{id}', [DriverCompanyController::class, 'start_trip']);
    Route::post('/finished_going_trip/{id}', [DriverCompanyController::class, 'finished_going_trip']);

    Route::post('/stat_trip_returnde/{id}', [DriverCompanyController::class, 'start_trip_return']);
    Route::post('/finished_return_trip/{id}', [DriverCompanyController::class, 'finished_return_trip']);


    Route::get('/my_profile', [UserApiController::class, 'info']);
    Route::put('/update_profile', [UserApiController::class, 'updateProfile']);

    Route::get('/all_bus_trip', [DriverCompanyController::class, 'all_bus_trip']);


});
