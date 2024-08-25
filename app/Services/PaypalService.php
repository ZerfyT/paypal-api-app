<?php
namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use Response;
use Unirest\Exception;
use Unirest\Request;
use Unirest\Request\Body;

class PaypalService
{
    const API_URL = 'https://api.sandbox.paypal.com/';
    private string $accessToken;
    private array $headers;

    public function __construct()
    {
        $this->accessToken = $this->getAccessToken();
        $this->headers = $this->getHeaders();
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
                throw new Exception($response->body->error_description);
            }

            $this->accessToken = $response->body->access_token;

            Log::info('Paypal Access Token: ' . $this->accessToken);
        }

        return $this->accessToken;
    }

    private function getHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Prefer' => 'return=representation' // return=minimal
        ];
    }

    /**
     * @throws \Unirest\Exception
     * @return string|null Paypal Product ID
     */
    public function createProduct(string $name, ?string $description, ?string $category, ?string $image_url, ?string $home_url): ?string
    {
        $response = Request::post(self::API_URL . 'v1/catalogs/products', $this->headers, Body::json([
            'name' => $name,
            'description' => $description,
            'type' => 'DIGITAL',
            'category' => $category,
            'image_url' => $image_url,
            'home_url' => $home_url,
        ]));

        if ($response->code >= 400) {   // 201 created
            Log::error($response->body->error_description);
            throw new Exception($response->body->error_description);
        }

        Log::info('Paypal Product ID: ' . $response->body->id . ' NAME: ' . $response->body->name . ' created successfully');
        return $response->body->id;
    }



    /**
     * @throws \Unirest\Exception
     * @return string Paypal Plan ID
     */
    public function createPlan(string $productId, string $name, ?string $description, array $billingCycles): string
    {
        $response = Request::post(self::API_URL . 'v1/billing/plans', $this->headers, Body::json([
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

        if ($response->code >= 400) {   // 201 created
            Log::error($response->body->error_description);
            throw new Exception($response->body->error_description);
        }

        Log::info('Paypal Plan ID: ' . $response->body->id . ' NAME: ' . $response->body->name . ' created successfully');
        return $response->body->id;
    }

    /**
     * @throws \Unirest\Exception
     * @return array Paypal Plans
     */
    public function listPlans(string $productId): array
    {
        $response = Request::get(self::API_URL . 'v1/billing/plans?product_id=' . $productId, $this->headers);

        if ($response->code >= 400) {   // 200 OK
            Log::error($response->body->error_description);
            throw new Exception($response->body->error_description);
        }

        return $response->body->plans;
    }

    /**
     * @param string $planId
     * @throws \Unirest\Exception
     * @return mixed Paypal Plan
     */
    public function showPlanDetails(string $planId)
    {
        $response = Request::get(self::API_URL . 'v1/billing/plans/' . $planId, $this->headers);

        if ($response->code >= 400) {   // 200 OK
            Log::error($response->body->error_description);
            throw new Exception($response->body->error_description);
        }

        return $response->body;
    }

    /**
     * @throws \Unirest\Exception
     * @return mixed Paypal Subscription
     */
    public function createSubscription(string $planId, string $customerId)
    {
        $response = Request::post(self::API_URL . 'v1/billing/subscriptions', $this->headers, Body::json([
            'plan_id' => $planId,
            'custom_id' => $customerId
        ]));

        if ($response->code >= 400) {   // 201 created
            Log::error($response->body->error_description);
            throw new Exception($response->body->error_description);
        }

        return $response->body;
    }

    /**
     * @throws \Unirest\Exception
     * @return mixed Paypal Subscription
     */
    public function showSubscriptionDetails(string $subscriptionId)
    {
        $response = Request::get(self::API_URL . 'v1/billing/subscriptions/' . $subscriptionId, $this->headers);

        if ($response->code >= 400) {   // 200 OK
            Log::error($response->body->error_description);
            throw new Exception($response->body->error_description);
        }

        return $response->body;
    }

    public function captureOrder($orderId)
    {
        $response = Request::post(self::API_URL . 'v2/checkout/orders/' . $orderId . '/capture', $this->headers);

        if ($response->code != 201 && $response->code != 200) {
            Log::error($response->body->error_description);
            throw new Exception($response->body->error_description);
            // return null;
        }

        Log::info('Paypal Order ID: ' . $response->body->id . ' Captured successfully');

        return $response->body;
    }

    public function showOrderDetails($orderId)
    {

        $response = Request::get(self::API_URL . 'v2/checkout/orders/' . $orderId, $this->headers);

        if ($response->code != 200) {
            Log::error($response->body->error_description);
            throw new Exception($response->body->error_description);
            // return null;
        }

        return $response->body;
    }
}

