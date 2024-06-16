<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DonationController;
/*
Web Routes
--------------------------------------------------------------------------
Here is where you can register web routes for your application. These
routes are loaded by the RouteServiceProvider within a group which
contains the "web" middleware group. Now create something great!
*/

Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::get('register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::get('home', [AuthController::class, 'showHome'])->name('home');
Route::post('/donations/token', [DonationController::class, 'getToken'])->name('donations.token');
Route::post('/donations/notification', [DonationController::class, 'notificationHandler'])->name('donations.notification');

Route::middleware('auth')->group(function () {
Route::get('/donations', [DonationController::class, 'index'])->name('donations.index');
Route::get('/donations/create', [DonationController::class, 'create'])->name('donations.create');
Route::get('/donations/edit/{id}', [DonationController::class, 'edit'])->name('donations.edit');
});

Route::get('/', function () {
return redirect()->route('home');
});
