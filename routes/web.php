<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\TravelHistoryController;

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

Route::get('/login', function () {
    return view('auth.login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/travel-history', [TravelHistoryController::class, 'index']);
});
