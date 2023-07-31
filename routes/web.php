<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// User Authentication Routes
Route::get('login', 'App\Http\Controllers\Auth\LoginController@showLoginForm')->name('login');
Route::get('proxy-login/{id}', 'App\Http\Controllers\Auth\LoginController@proxylogin');

Route::post('login', 'App\Http\Controllers\Auth\LoginController@login');
Route::post('logout', 'App\Http\Controllers\Auth\LoginController@logout')->name('logout');

// User Registration Routes
Route::get('register', 'App\Http\Controllers\Auth\RegisterController@showRegistrationForm')->name('register');
Route::post('register', 'App\Http\Controllers\Auth\RegisterController@register');

// User Password Reset Routes
Route::get('password/reset', 'App\Http\Controllers\Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'App\Http\Controllers\Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}', 'App\Http\Controllers\Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'App\Http\Controllers\Auth\ResetPasswordController@reset')->name('password.update');

// User Verification Routes
Route::get('email/verify', 'App\Http\Controllers\Auth\VerificationController@show')->name('verification.notice');
Route::get('email/verify/{id}/{hash}', 'App\Http\Controllers\Auth\VerificationController@verify')->name('verification.verify');
Route::post('email/resend', 'App\Http\Controllers\Auth\VerificationController@resend')->name('verification.resend');

Auth::routes();

Route::group(['middleware' => ['auth:app,staff']], function() {
    Route::get('/','App\Http\Controllers\CalenderController@index')->name('calender');
    Route::post('/saveProjectSchedule', [App\Http\Controllers\CalenderController::class, 'saveProjectSchedule'])->name('saveProjectSchedule');
    Route::post('/deleteProjectSchedule', [App\Http\Controllers\CalenderController::class, 'deleteProjectSchedule'])->name('saveProjectSchedule');
    Route::post('/modal-data', [App\Http\Controllers\CalenderController::class, 'modalData']);
    Route::post('/foreman-staff', [App\Http\Controllers\CalenderController::class, 'getStaff']);
    Route::get('/staff-management', [App\Http\Controllers\StaffController::class, 'index'])->name('staff.list');
    Route::post('/staff', [App\Http\Controllers\StaffController::class, 'staffs'])->name('staff.get');
    Route::post('/add-staff', [App\Http\Controllers\StaffController::class, 'add_staff'])->name('staff.add');
    Route::post('/edit-staff', [App\Http\Controllers\StaffController::class, 'edit_staff'])->name('staff.edit');
    Route::post('/update-staff', [App\Http\Controllers\StaffController::class, 'update_staff'])->name('staff.update');
    Route::get('/assign-team', [App\Http\Controllers\StaffController::class, 'assign_team'])->name('team.assign');
    Route::post('/save-team', [App\Http\Controllers\StaffController::class, 'save_team'])->name('team.save');
    Route::get('/send-notification', [App\Http\Controllers\StaffController::class, 'notification'])->name('notification');
    Route::post('/send-notification', [App\Http\Controllers\StaffController::class, 'send_notification'])->name('send.notification');
    Route::get('/team-notification', [App\Http\Controllers\StaffController::class, 'team_notification'])->name('team.notification');
    Route::post('/send-team-notification', [App\Http\Controllers\StaffController::class, 'send_team_notification'])->name('send.team.notification');

  });
    
