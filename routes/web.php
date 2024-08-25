<?php

use App\Http\Controllers\PaypalWebhookController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaypalController;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::controller(PaypalController::class)->group(function () {
    Route::get('/create-product', 'createProduct');
    Route::get('/create-plans', 'createPlans');
    Route::get('/save-plans', 'savePlanstoDB');
    Route::get('/get-subscription-data', 'getSubscriptionDetails');
});

Route::post('/webhook-paypal', [PaypalWebhookController::class, 'index'])->name('webhook-paypal');

