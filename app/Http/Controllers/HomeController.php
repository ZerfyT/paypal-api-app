<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PaypalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Plan;

class HomeController extends Controller
{
    public function index()
    {

        $plans = Cache::remember('plans', env('CACHE_EXPIRE_TIME'), function () {
            return Plan::all();
        });

        $user = User::find(1);

        $userPayments = $user?->payments;
        $userSubscription = $user?->subscriptions()->where('status', 'ACTIVE')->first();


        return view('home', compact('plans', 'user', 'userPayments', 'userSubscription'));
    }
}
