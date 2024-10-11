<?php

namespace App\Http\Controllers;

use App\Services\BraintreeService;
use App\Services\PaypalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Plan;
use Illuminate\Support\Facades\Log;
use Storage;

class HomeController extends Controller
{
    public function index()
    {
        // dd(Storage::get('my.jpg'));
        $plans = Cache::remember('plans', env('CACHE_EXPIRE_TIME'), function () {
            return Plan::all();
        });

        // $paypalService = new PaypalService();

        $braintreeService = new BraintreeService();

        // Log::info('Creating Customer');
        // $customer = $braintreeService->createCustomer('John', 'Doe', 'C9kQw@example.com');
        // Log::debug($customer);

        $customer = '83657761599';
        Log::info('Retrieving Client Token');
        $clientToken = $braintreeService->getClientToken($customer);
        Log::debug($clientToken);


        return view('home', compact('plans', 'clientToken'));
    }
}
