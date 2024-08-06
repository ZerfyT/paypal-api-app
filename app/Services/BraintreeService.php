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
    private $gateway;
    public function __construct()
    {
        $this->gateway = new Gateway([
            'environment' => 'sandbox',
            'merchantId' => env('BRAINTREE_MERCHANT_ID'),
            'publicKey' => env('BRAINTREE_PUBLIC_KEY'),
            'privateKey' => env('BRAINTREE_PRIVATE_KEY')
        ]);
    }

    public function getClientToken($customerId)
    {
        return $this->gateway->clientToken()->generate([
            'customerId' => $customerId
        ]);
    }

    public function createCustomer($firstName, $lastName, $email): string
    {
        $result = $this->gateway->customer()->create([
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email
        ]);

        if ($result->success) {
            return $result->customer->id;
        } else {
            Log::error($result);
            throw new \Exception($result->message);
        }
    }


}
