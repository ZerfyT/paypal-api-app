<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\User;
use Braintree\Gateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        Log::debug($payload);

        $customerResult = $this->gateway->customer()->create([
            'firstName' => fake()->firstName(),
            'lastName' => fake()->lastName(),
            'email' => fake()->email(),
            'paymentMethodNonce' => $nonce,
        ]);

        if (!$customerResult->success) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create customer: ' . $customerResult->message,
            ], 422);
        }

        $subscriptionResult = $this->gateway->subscription()->create([
            'paymentMethodToken' => $customerResult->customer->paymentMethods[0]->token,
            'planId' => $planId,
        ]);

        if (!$subscriptionResult->success) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create subscription: ' . $subscriptionResult->message,
            ], 422);
        }

        // dd($customerResult, $subscriptionResult);

        /**
         * Save the subscription to the database
         */

        $customer = $customerResult->customer;
        $subscription = $subscriptionResult->subscription;
        $transaction = $subscription->transactions[0];

        $planDb = Plan::where('braintree_plan_id', $planId)->first();

        // $userDb = User::create([
        //     'name' => $customer->firstName . ' ' . $customer->lastName,
        //     'email' => $customer->email,
        //     'customer_id' => $customer->id
        // ]);

        $userDb = User::factory()->create();
        $userDb->update([
            'customer_id' => $customer->id
        ]);

        $paymentDb = $userDb->payments()->create([
            'plan_id' => $planDb->id,
            'transaction_id' => $transaction->id,
            'amount' => $transaction->amount,
            'currency' => $transaction->currencyIsoCode,
            'payment_method' => isset($transaction->creditCard) && isset($transaction->creditCard->cardType) ? 'credit_card' : 'paypal',
            'payment_method_token' => $subscription->paymentMethodToken,
            'status' => $transaction->status,
            'payment_date' => $transaction->createdAt,
        ]);

        $subscriptionDb = $userDb->subscriptions()->create([
            'plan_id' => $planDb->id,
            'payment_id' => $paymentDb->id,
            'braintree_subscription_id' => $subscription->id,
            'start_date' => $subscription->billingPeriodStartDate,
            'end_date' => $subscription->billingPeriodEndDate,
            'status' => $subscription->status,
        ]);

        return response()->json([
            'success' => true,
            'subscription' => $subscriptionResult->subscription,
        ]);
    }
}
