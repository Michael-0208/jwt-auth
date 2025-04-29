<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\TravelHistoryController;
use App\Http\Controllers\Web\AuthController;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['jwt.web'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    Route::get('/travel-history', [TravelHistoryController::class, 'index'])->name('travel-history');
    
    Route::get('/travel-distance', [TravelHistoryController::class, 'totalTravelingDistance'])->name('traveling-distance');
    // Add more protected routes here
});
