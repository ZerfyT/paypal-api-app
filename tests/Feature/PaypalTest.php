<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\PaypalService;
use Tests\TestCase;

class PaypalTest extends TestCase
{
    // public function test_the_application_returns_a_successful_response(): void
    // {
    //     $response = $this->get('/');

    //     $response->assertStatus(200);
    // }

    // public function test_paypal_create_product(): void
    // {
    //     $response = $this->get('/create-product');

    //     $response->assertStatus(200);
    // }

    // public function test_paypal_create_plans(): void
    // {
    //     $response = $this->get('/create-plans');

    //     $response->assertStatus(200);
    // }

    public function test_paypal_capture_order(): void
    {
        $orderId = '0B75443883556935B';
        $paypalService = new PaypalService();
        $order = $paypalService->showOrderDetails($orderId);
        info($order);
    }
}
