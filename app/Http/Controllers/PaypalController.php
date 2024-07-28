<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Setting;
use App\Services\PaypalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaypalController extends Controller
{
    public function createOrder(Request $request)
    {
        dd($request->all());
    }

    public function completeOrder(Request $request)
    {
        // dd($data);
        $paypalService = new PaypalService();
        $paypalService->captureOrder($request->data->orderID);
    }

    public function createProduct()
    {
        $paypalService = new PaypalService();
        $productId = $paypalService->createProduct('Lara Pack', 'Lara Pack Web Service', 'SOFTWARE', null, null);

        Setting::create([
            'key' => 'paypal_product_id',
            'value' => $productId
        ]);

        Log::info('Paypal Product Id: ' . $productId . ' Saved In Database');
    }

    public function createPlans()
    {
        $productId = Setting::where('key', 'paypal_product_id')->first()->value;
        $paypalService = new PaypalService();
        // dd($paypalService);

        // $planTrial = $paypalService->createPlan(
        //     $productId,
        //     'Free Trial',
        //     'Lara Pack Free Trial Subscription',
        //     [
        //         [
        //             'tenure_type' => 'TRIAL',
        //             'sequence' => 1,
        //             'total_cycles' => 1,
        //             'frequency' => [
        //                 'interval_unit' => 'DAY',
        //                 'interval_count' => 1
        //             ],
        //             'pricing_scheme' => [
        //                 'fixed_price' => [
        //                     'currency_code' => 'USD',
        //                     'value' => 0
        //                 ]
        //             ]
        //         ],
        //         [
        //             'tenure_type' => 'REGULAR',
        //             'sequence' => 2,
        //             'total_cycles' => 2,
        //             'frequency' => [
        //                 'interval_unit' => 'DAY',
        //                 'interval_count' => 1
        //             ],
        //             'pricing_scheme' => [
        //                 'fixed_price' => [
        //                     'currency_code' => 'USD',
        //                     'value' => 1.19
        //                 ]
        //             ]
        //         ]

        //     ]
        // );

        // $planTrial = $paypalService->showPlanDetails('P-7VS156627S1993915M2S7IUY');
        // $planMonthly = $paypalService->showPlanDetails('P-3WY55998CH171923DM2S7P3A');
        $planAnnual = $paypalService->showPlanDetails('P-57L73278CC1892313M2S7TEY');

        // dd($planTrial->billing_cycles[0]->pricing_scheme->fixed_price->value);
        // Plan::create([
        //     'name' => $planTrial->name,
        //     'description' => $planTrial->description,
        //     'price' => $planTrial->billing_cycles[0]->pricing_scheme->fixed_price->value,
        //     'currency' => $planTrial->billing_cycles[0]->pricing_scheme->fixed_price->currency_code,
        //     'interval_unit' => $planTrial->billing_cycles[0]->frequency->interval_unit,
        //     'interval_count' => $planTrial->billing_cycles[0]->frequency->interval_count,
        //     'status' => $planTrial->status,
        //     'paypal_plan_id' => $planTrial->id,
        // ]);

        // dd('plan trail saved to db');

        // $planMonthly = $paypalService->createPlan(
        //     $productId,
        //     'Monthly',
        //     'Lara Pack Monthly Subscription',
        //     [
        //         [
        //             'tenure_type' => 'REGULAR',
        //             'sequence' => 1,
        //             'frequency' => [
        //                 'interval_unit' => 'DAY',
        //                 'interval_count' => 2
        //             ],
        //             'pricing_scheme' => [
        //                 'fixed_price' => [
        //                     'currency_code' => 'USD',
        //                     'value' => 2
        //                 ]
        //             ]
        //         ]
        //     ]

        // );

        // dd($planMonthly->billing_cycles[0]->pricing_scheme);

        // Plan::create([
        //     'name' => $planMonthly->name,
        //     'description' => $planMonthly->description,
        //     'price' => $planMonthly->billing_cycles[0]->pricing_scheme->fixed_price->value,
        //     'currency' => $planMonthly->billing_cycles[0]->pricing_scheme->fixed_price->currency_code,
        //     'interval_unit' => $planMonthly->billing_cycles[0]->frequency->interval_unit,
        //     'interval_count' => $planMonthly->billing_cycles[0]->frequency->interval_count,
        //     'status' => $planMonthly->status,
        //     'paypal_plan_id' => $planMonthly->id,
        // ]);

        // $planAnnual = $paypalService->createPlan(
        //     $productId,
        //     'Annual',
        //     'Lara Pack Annual Subscription',
        //     [
        //         [
        //             'tenure_type' => 'REGULAR',
        //             'sequence' => 1,
        //             'frequency' => [
        //                 'interval_unit' => 'DAY',
        //                 'interval_count' => 3
        //             ],
        //             'pricing_scheme' => [
        //                 'fixed_price' => [
        //                     'currency_code' => 'USD',
        //                     'value' => 18
        //                 ]
        //             ]
        //         ]
        //     ]

        // );

        Plan::create([
            'name' => $planAnnual->name,
            'description' => $planAnnual->description,
            'price' => $planAnnual->billing_cycles[0]->pricing_scheme->fixed_price->value,
            'currency' => $planAnnual->billing_cycles[0]->pricing_scheme->fixed_price->currency_code,
            'interval_unit' => $planAnnual->billing_cycles[0]->frequency->interval_unit,
            'interval_count' => $planAnnual->billing_cycles[0]->frequency->interval_count,
            'status' => $planAnnual->status,
            'paypal_plan_id' => $planAnnual->id,
        ]);
    }
}
