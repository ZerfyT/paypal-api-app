<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\User;
use App\Services\PaypalService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class PaypalWebhookController extends Controller
{
    public function index(Request $request)
    {
        Log::info('Paypal Webhook Request: ');
        $data = $request->all();
        $eventType = $data['event_type'];

        Log::debug($eventType);
        Log::info(json_encode($data));

        switch ($eventType) {
            case 'PAYMENT.SALE.COMPLETED':
                $this->paymentSaleCompleted($data);
                break;
            default:
                return response()->json(['status' => 'success'], 200);
        }
    }

    private function paymentSaleCompleted($data)
    {
        $resource = $data['resource'];
        $paymentId = $resource['id'];
        $subcriptionId = $resource['billing_agreement_id'];
        $userId = $resource['custom'];
        $amount = $resource['amount']['total'];
        $currency = $resource['amount']['currency'];
        $status = $resource['state'];

        $paypalService = new PaypalService();
        $subscriptionData = $paypalService->showSubscriptionDetails($subcriptionId);

        $planId = $subscriptionData->plan_id;

        $plan = Plan::where('plan_id', $planId)->first();
        if (!$plan) {
            Log::error('Plan Not Found: ' . $planId);
            return;
        }

        $startDate = Carbon::parse($subscriptionData->start_time);
        $endDate = Carbon::parse($startDate->addDays($plan->interval_count));
        $paymentDate = Carbon::parse($subscriptionData->billing_info->last_payment->time);

        $startDate = $startDate->format('Y-m-d H:i:s');
        $endDate = $endDate->format('Y-m-d H:i:s');
        $paymentDate = $paymentDate->format('Y-m-d H:i:s');

        $user = User::find($userId);

        if (!$user) {
            Log::error('User Not Found: ' . $userId);
            return;
        }

        $payment = $user->payments()->create([
            'plan_id' => $plan->id,
            'subscription_id' => null,  // Updates after subscription created
            'payment_id' => $paymentId,
            'amount' => $amount,
            'currency' => $currency,
            'payment_method' => 'paypal',
            'status' => $status,
            'payment_date' => $paymentDate,
        ]);

        Log::info('Payment Data Saved: ' . $paymentId);

        if ($user->subscriptions()->where('subscription_id', $subcriptionId)->doesntExist()) {
            Log::info('Subscription Not Exists && First Time Payment: ' . $subcriptionId);

            $subscription = $user->subscriptions()->create([
                'plan_id' => $plan->id,
                'payment_id' => $payment->id,
                'subscription_id' => $subcriptionId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $subscriptionData->status
            ]);

            Log::info('Subscription Data Saved: ' . $subcriptionId);
        } else {
            Log::info('Subscription Exists: ' . $subcriptionId);

            $subscription = $user->subscriptions()->where('subscription_id', $subcriptionId)->first();
            $subscription->update([
                'payment_id' => $payment->id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'status' => $subscriptionData->status
            ]);

            Log::info('Subscription Data Updated: ' . $subcriptionId);
        }

        $payment->update(['subscription_id' => $subscription->id]);

        return response()->json(['status' => 'success'], 200);
    }
}
