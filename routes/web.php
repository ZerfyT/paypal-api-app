<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaypalController;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/pay', [HomeController::class, 'pay'])->name('pay');

Route::controller(PaypalController::class)->group(function () {
    Route::get('/create-order', 'createOrder');
    Route::post('/complete-order', 'completeOrder');
    Route::get('/create-product', 'createProduct');
    Route::get('/create-plans', 'createPlans');
});

