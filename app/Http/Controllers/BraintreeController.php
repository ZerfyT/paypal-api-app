<?php

namespace App\Http\Controllers;

use Braintree\Gateway;
use Illuminate\Http\Request;

class BraintreeController extends Controller
{
    protected $gateway;

    public function __construct(Gateway $gateway)
    {
        $this->gateway = $gateway;
    }

    public function getClientToken()
    {
        return response()->json([
            'clientToken' => $this->gateway->clientToken()->generate(),
        ]);
    }

    public function processPayment(Request $request)
    {
        $payload = $request->input('payload', false);
        $nonce = $payload['nonce'];
        $planId = $payload['planId'];

        $customerResult = $this->gateway->customer()->create([
            'paymentMethodNonce' => $nonce,
        ]);

        if(!$customerResult->success) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create customer: ' . $customerResult->message,
            ], 422);
        }

        $customerId = $customerResult->customer->id;

        $subscriptionResult = $this->gateway->subscription()->create([
            'paymentMethodToken' => $customerResult->customer->paymentMethods[0]->token,
            'planId' => $planId,
        ]);

        if(!$subscriptionResult->success) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create subscription: ' . $subscriptionResult->message,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'subscription' => $subscriptionResult->subscription,
        ]);
    }
}
