<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleRowController;

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

Route::get('/google-rows/generate', [GoogleRowController::class, 'generateRows'])
    ->name('google-rows.generate');

Route::get('/google-rows/remove', [GoogleRowController::class, 'removeRows'])
    ->name('google-rows.remove');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::resource('google-rows', GoogleRowController::class);

