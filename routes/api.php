<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TravelHistoryController;
use App\Jobs\StoreUserLocation;
use App\Models\UserCoordinate;
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

    // Add route to receive location updates
    Route::post('location-update', function (Request $request) {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180'
        ]);

        StoreUserLocation::dispatch(
            $request->user(),
            $request->latitude,
            $request->longitude
        );

        return response()->json(['message' => 'Location update queued successfully']);
    });

    // Get user's travel history
    Route::get('travel-history', [TravelHistoryController::class, 'getTravelHistory']);
});