<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use Unirest\Request;
use Unirest\Request\Body;
use Braintree\Gateway;

class BraintreeService
{
    // public static function getClientToken($gateway)
    // {
    //     return response()->json([
    //         'clientToken' => $gateway->clientToken()->generate(),
    //     ]);
    // }

    // public function createCustomer($firstName, $lastName, $email): string
    // {
    //     $result = $this->gateway->customer()->create([
    //         'firstName' => $firstName,
    //         'lastName' => $lastName,
    //         'email' => $email
    //     ]);

    //     if ($result->success) {
    //         return $result->customer->id;
    //     } else {
    //         Log::error($result);
    //         throw new \Exception($result->message);
    //     }
    // }
}
