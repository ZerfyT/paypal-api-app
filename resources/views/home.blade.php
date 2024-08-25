<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    @vite('resources/css/app.css')
    @vite('resources/js/app.js')

    <script src="https://www.paypal.com/sdk/js?client-id={{ env('PAYPAL_CLIENT_ID') }}&vault=true&intent=subscription">
    </script>
</head>

<body class="font-sans antialiased text-gray-700">
    <div class="container text-center">
        <h1 class="text-4xl font-bold">Plans</h1>
        <div class="flex flex-col flex-wrap content-center justify-center gap-6 mt-8 md:flex-nowrap md:flex-row"
            x-data="{ selectedPlan: '{{ $plans->first()->plan_id ?? '0' }}' }">
            @foreach ($plans as $plan)
                @component('components.plan-card', ['plan' => $plan])
                @endcomponent
            @endforeach
        </div>
        <div class="mx-auto mt-8 w-80" id="paypal-button-container"></div>
    </div>

    {{-- User data Section --}}
    @if (isset($user) && isset($userSubscription) && isset($userPayments))
        <div class="container mt-8 rounded-lg shadow-lg w-100 bg-bittersweet-50">
            <h2 class="mb-4 text-xl font-bold uppercase text-bittersweet-500">User Details</h2>
            <div class="flex flex-col gap-2">
                <p>User ID: <span class="font-bold">{{ $user->id }}</span></p>
                <p>Active Subscription status:
                    @component('components.status-badge', ['type' => 'subscription', 'data' => $userSubscription])
                    @endcomponent
                </p>
                <p>Active Subscription ID: <span class="font-bold">{{ $userSubscription->subscription_id }}</span></p>
                <p>Active Subscription Start Date:
                    @component('components.date', ['date' => $userSubscription->start_date])
                    @endcomponent
                </p>
                <p>Active Subscription End Date:
                    @component('components.date', ['date' => $userSubscription->end_date])
                    @endcomponent
                </p>
            </div>

            {{-- Recent Payment History --}}
            <table id="payment-history"
                class="w-full mt-8 text-center border border-collapse border-gray-300 rounded table-auto">
                <thead class="text-sm">
                    <tr>
                        <th>#</th>
                        <th>Payment ID</th>
                        <th>Subscription ID</th>
                        <th>Plan ID</th>
                        <th>Plan Name</th>
                        <th>Plan Price</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody class="font-bold">
                    @foreach ($userPayments as $payment)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $payment->payment_id }}</td>
                            <td>{{ $payment->subscription->subscription_id }}</td>
                            <td>{{ $payment->plan_id }}</td>
                            <td>{{ $payment->plan->name }}</td>
                            <td>{{ $payment->plan->price }}</td>
                            <td>
                                @component('components.date', ['date' => $payment->payment_date])
                                @endcomponent
                            </td>
                            <td>
                                @component('components.status-badge', ['type' => 'payment', 'data' => $payment])
                                @endcomponent
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>

    @endif


    <script>
        let selectedPlan = null;

        function setPlanId(planId) {
            console.log(planId);
            selectedPlan = planId;
        }

        paypal.Buttons({
            style: {
                shape: 'rect',
                color: 'gold',
                layout: 'horizontal',
                label: 'paypal'
            },
            onClick: function() {
                console.log('clicked');
                selectedPlan = selectedPlan;
                console.log('plan :' + selectedPlan);
            },
            createSubscription: function(data, actions) {
                return actions.subscription.create({
                    plan_id: selectedPlan,
                    custom_id: '{{ $user->id }}'
                });
            },
            onApprove: function(data, actions) {
                console.log('subscription approved');
                console.log(data);
                console.log(actions);
                alert('subscription approved');
            },
            onCancel: function(data) {
                console.log('subscription cancelled');
                console.log(data);
            },
            onError: function(data) {
                console.log('subscription error');
                console.log(data);
                alert('error');
            }

        }).render('#paypal-button-container'); // Renders the PayPal button
    </script>

</body>

</html>
