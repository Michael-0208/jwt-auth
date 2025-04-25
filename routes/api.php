<?php

use App\Http\Controllers\Api\AuthController;
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

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// throttle for forgot password 2 times per minute
Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:throttle-forgot-password');
Route::post('reset-password/{id}', [AuthController::class, 'resetPassword'])->middleware('throttle:throttle-forgot-password');

Route::middleware(['jwt.auth','api','throttle:10,1'])->group(function () {
    
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', function (Request $request) {
        return response()->json(['user' => $request->user()]);
    });
});