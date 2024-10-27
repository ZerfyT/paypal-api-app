<?php

use App\Http\Controllers\BraintreeController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/pay', [HomeController::class, 'pay'])->name('pay');


Route::controller(BraintreeController::class)->group(function () {
    Route::get('/braintree/token', 'getClientToken')->name('braintree.token');
    Route::post('/braintree/process', 'processPayment')->name('braintree.process');
});

Route::post('/braintree/webhook', [WebhookController::class, 'handleWebhook']);
