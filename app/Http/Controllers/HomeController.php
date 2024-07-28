<?php

namespace App\Http\Controllers;

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

        $paypalService = new PaypalService();


        return view('home', compact('plans'));
    }
}
