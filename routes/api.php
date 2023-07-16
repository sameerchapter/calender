<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/auth/login', [App\Http\Controllers\Api\ApiController::class, 'loginUser']);
Route::post('/event-data', [App\Http\Controllers\Api\ApiController::class, 'eventData']);
Route::post('/booking-data', [App\Http\Controllers\Api\ApiController::class, 'bookingData']);
Route::post('/save-token', [App\Http\Controllers\Api\ApiController::class, 'saveToken']);
Route::post('/notifications', [App\Http\Controllers\Api\ApiController::class, 'notificationData']);


// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
