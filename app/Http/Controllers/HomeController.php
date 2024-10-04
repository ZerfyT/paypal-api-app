<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PaypalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Plan;

class HomeController extends Controller
{
    public function index()
    {
        $user = User::find(1);
        Auth::login($user, $remember = true);

        $plans = Cache::remember('plans', env('CACHE_EXPIRE_TIME'), function () {
            return Plan::all();
        });

        $paypalService = new PaypalService();


        return view('home', compact('plans'));
    }
}
