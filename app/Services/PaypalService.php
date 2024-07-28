<?php
namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use Unirest\Request;
use Unirest\Request\Body;

class PaypalService
{
    const API_URL = 'https://api.sandbox.paypal.com/';
    public string $accessToken;

    public function __construct()
    {

        $this->accessToken = $this->getAccessToken();
    }

    private function getHeaders(): array
    {

        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
            // 'Prefer' => 'return=minimal'
        ];
    }

    private function getAccessToken(): string
    {
        if (empty($this->accessToken)) {
            $credentials = base64_encode(env('PAYPAL_CLIENT_ID') . ':' . env('PAYPAL_CLIENT_SECRET'));
            $headers = [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Basic ' . $credentials
            ];

            $response = Request::post(self::API_URL . 'v1/oauth2/token', $headers, Body::form([
                'grant_type' => 'client_credentials',
            ]));

            if ($response->code != 200) {
                Log::error($response->body->error_description);
                throw new \Exception($response->body->error_description);
                // return null;
            }

            $this->accessToken = $response->body->access_token;

            Log::info('Paypal Access Token: ' . $this->accessToken);
        }

        return $this->accessToken;
    }

    /**
     * @param string $name
     * @param mixed $description
     * @param mixed $category
     * @param mixed $image_url
     * @param mixed $home_url
     * @throws \Exception
     * @return string|null Paypal Product ID
     */
    public function createProduct(string $name, ?string $description, ?string $category, ?string $image_url, ?string $home_url): ?string
    {
        $response = Request::post(self::API_URL . 'v1/catalogs/products', $this->getHeaders(), Body::json([
            'name' => $name,
            'description' => $description,
            'type' => 'DIGITAL',
            'category' => $category,
            'image_url' => $image_url,
            'home_url' => $home_url,
        ]));

        if ($response->code != 201) {
            Log::error($response->body->error_description);
            throw new \Exception($response->body->error_description);
            // return null;
        }

        Log::info('Paypal Product ID: ' . $response->body->id . ' NAME: ' . $response->body->name . ' created successfully');
        return $response->body->id;
    }



    public function createPlan(string $productId, string $name, ?string $description, array $billingCycles)
    {
        // dd($billingCycles);

        $response = Request::post(self::API_URL . 'v1/billing/plans', $this->getHeaders(), Body::json([
            'product_id' => $productId,
            'name' => $name,
            'status' => 'ACTIVE',
            'description' => $description,
            'billing_cycles' => $billingCycles,
            'payment_preferences' => [
                'auto_bill_outstanding' => true,
                'setup_fee_failure_action' => 'CANCEL',
                'payment_failure_threshold' => 2,
                'setup_fee' => [
                    'currency_code' => 'USD',
                    'value' => 0
                ],
            ]
        ]));

        if ($response->code != 201) {
            dd($response->body);
            Log::error($response->body->error_description);
            throw new \Exception($response->body->error_description);
            // return null;
        }

        Log::info('Paypal Plan ID: ' . $response->body->id . ' NAME: ' . $response->body->name . ' created successfully');
        return $response->body;
    }

    public function listPlans($productId)
    {

        $response = Request::get(self::API_URL . 'v1/billing/plans', $this->getHeaders(), Body::json([
            'product_id' => $productId
        ]));

        if ($response->code != 200) {
            Log::error($response->body->error_description);
            throw new \Exception($response->body->error_description);
            // return null;
        }

        return $response->body->plans;
    }

    public function showPlanDetails($planId)
    {

        $response = Request::get(self::API_URL . 'v1/billing/plans/' . $planId, $this->getHeaders());

        if ($response->code != 200) {
            dd($response->body);
            Log::error($response->body->error_description);
            throw new \Exception($response->body->error_description);
            // return null;
        }

        return $response->body;
    }

    public function createSubscription($planId, $customerId)
    {

        $response = Request::post(self::API_URL . 'v1/billing/subscriptions', $this->getHeaders(), Body::json([
            'plan_id' => $planId,
            'customer_id' => $customerId
        ]));
    }

    public function captureOrder($orderId)
    {

        $response = Request::post(self::API_URL . 'v2/checkout/orders/' . $orderId . '/capture', $this->getHeaders());

        if ($response->code != 201 && $response->code != 200) {
            dd($response->body);
            Log::error($response->body->error_description);
            throw new \Exception($response->body->error_description);
            // return null;
        }

        Log::info('Paypal Order ID: ' . $response->body->id . ' Captured successfully');

        return $response->body;
    }

    public function showOrderDetails($orderId)
    {

        $response = Request::get(self::API_URL . 'v2/checkout/orders/' . $orderId, $this->getHeaders());

        if ($response->code != 200) {
            dd($response->body);
            Log::error($response->body->error_description);
            throw new \Exception($response->body->error_description);
            // return null;
        }

        return $response->body;
    }
}

