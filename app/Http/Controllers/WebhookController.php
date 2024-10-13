<?php

namespace App\Http\Controllers;

use Braintree\Gateway;
use Braintree\WebhookNotification;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $gateway = app(Gateway::class);

        $webhookNotification = $gateway->webhookNotification()->parse(
            $request->input('bt_signature'),
            $request->input('bt_payload')
        );

        switch($webhookNotification->kind) {
            case WebhookNotification::SUBSCRIPTION_CHARGED_SUCCESSFULLY:
                $subscription = $webhookNotification->subscription();
                break;
            case WebhookNotification::SUBSCRIPTION_CANCELED:
                $subscription = $webhookNotification->subscription();
                break;
        }

        return response('success', 200);
    }
}
