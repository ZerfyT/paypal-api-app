<?php

use App\Http\Controllers\BraintreeController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/pay', [HomeController::class, 'pay'])->name('pay');


Route::get('/braintree/token', [BraintreeController::class, 'getClientToken']);
Route::post('/braintree/process', [BraintreeController::class, 'processPayment']);

Route::post('/braintree/webhook', [WebhookController::class, 'handleWebhook']);
