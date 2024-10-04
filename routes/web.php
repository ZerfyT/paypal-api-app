<?php

use App\Models\Plan;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaypalController;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::controller(PaypalController::class)->group(function () {
    Route::get('/create-order', 'createOrder');
    Route::post('/complete-order', 'completeOrder');
    Route::get('/create-product', 'createProduct');
    Route::get('/create-plans', 'createPlans');
});

Route::get('/buy', function (Request $request) {
    $user = User::find(1);
    $checkout = $user->subscribe('496340');

    $plans = Cache::remember('plans', env('CACHE_EXPIRE_TIME'), function () {
        return Plan::all();
    });
    return view('home', compact('checkout', 'plans'));
});
