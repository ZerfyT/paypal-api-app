<?php

namespace App\Http\Controllers;

use App\Services\BraintreeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Plan;
use Illuminate\Support\Facades\Log;
use Storage;

class HomeController extends Controller
{
    public function index()
    {
        $plans = Cache::remember('plans', env('CACHE_EXPIRE_TIME'), function () {
            return Plan::all();
        });

        // $braintreeService = new BraintreeService();

        // Log::info('Creating Customer');
        // $result = $braintreeService->createCustomer('John', 'Doe', 'C9kQw@example.com');
        // Log::debug($result);

        // $customer = '83657761599';
        // Log::info('Retrieving Client Token');
        // $clientToken = $braintreeService->getClientToken();
        // Log::debug($clientToken);


        return view('home', compact('plans'));
    }

    public function pay(Request $request)
    {
        // dd($request->all());
        $data = $request->all();
        Log::info($data);
        $nonce = $request->input('payment_method_nonce');
        $planId = $request->input('plan_id');

        $braintreeService = new BraintreeService();
        $gateway = $braintreeService->gateway;

        $customer = $gateway->customer()->create([
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john.doe@example.com',
            'paymentMethodNonce' => $nonce
        ]);


        Log::info($customer);

        if ($customer->success) {
            $customerId = $customer->customer->id;
            $paymentMethodToken = $customer->customer->paymentMethods[0]->token;

            $subscriptionResult = $gateway->subscription()->create([
                'paymentMethodToken' => $paymentMethodToken,
                'planId' => $planId
            ]);
            // Log::info($subscriptionResult);

            if ($subscriptionResult->success) {
                echo "Subscription created successfully. Subscription ID: " . $subscriptionResult->subscription->id;
            } else {
                echo "Error creating subscription: " . $subscriptionResult->message;
            }
        } else {
            echo "Error creating customer: " . $customer->message;
        }

        return view('home');
    }
}
