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

<body class="font-sans antialiased">
    <div class="container mx-auto px-4 py-8">
        <h2 class="text-4xl font-semibold text-black text-center">Plans</h2>
        <div class="flex flex-wrap md:flex-nowrap flex-col md:flex-row justify-center content-center gap-6 mt-8"
            x-data="{ selectedPlan: '{{ $plans->first()->plan_id ?? '0' }}' }">
            @foreach ($plans as $plan)
                <div class="w-80 p-4 bg-[#FF2D20]/10 rounded-lg shadow-lg px-6 py-8 text-center cursor-pointer hover:bg-[#FF2D20]/20 transition duration-300"
                    x-on:click="selectedPlan = '{{ $plan->plan_id }}'; setPlanId(selectedPlan)"
                    x-bind:class="{ 'bg-[#FF2D20]/20 border border-[#FF2D20]/50': selectedPlan === '{{ $plan->plan_id }}' }">
                    <h3 class="text-xl font-bold text-[#FF2D20] mb-4 uppercase tracking-widest">{{ $plan->name }}</h3>
                    <p class="text-gray-500 mb-4 text-sm">{{ $plan->description }}</p>
                    <p class="text-[#FF2D20] mb-4 font-bold uppercase tracking-widest text-3xl">
                        {{ $plan->price }} USD</p>
                </div>
            @endforeach
        </div>
        <div class="mt-8 mx-auto w-80" id="paypal-button-container"></div>
    </div>

    @if (isset($user) && isset($userSubscription) && isset($userPayments))
        {{-- User data Section --}}
        <div class="container mx-auto w-100 mt-8 px-4 py-6 bg-neutral-100 rounded-lg shadow-lg">

            <h3 class="text-xl font-bold text-[#FF2D20] mb-4 uppercase tracking-widest">User Details</h3>
            <div class="text-gray-500 flex flex-col gap-2">
                <p>User ID: <span class="font-bold">{{ $user->id }}</span></p>
                <p>Active Subscription status: <span
                        class="px-1 py-1 rounded text-white text-sm font-bold {{ $userSubscription->status === 'ACTIVE' ? 'bg-green-500' : (in_array($userSubscription->status, ['APPROVAL_PENDING', 'APPROVED']) ? 'bg-yellow-500' : 'bg-red-500') }}">{{ $userSubscription->status }}</span>
                </p>
                <p>Active Subscription ID: <span class="font-bold">{{ $userSubscription->subscription_id }}</span></p>
                <p>Active Subscription Start Date: <span
                        class="font-bold">{{ \Carbon\Carbon::parse($userSubscription->start_date)->format('Y-m-d') }}</span>
                </p>
                <p>Active Subscription End Date: <span
                        class="font-bold">{{ \Carbon\Carbon::parse($userSubscription->end_date)->format('Y-m-d') }}</span>
                </p>
            </div>

            {{-- Recent Payment History --}}
            <table id="payment-history"
                class="w-full table-auto mt-8 rounded border border-gray-300 border-collapse text-center">
                <thead class="text-gray-500 text-sm font-bold">
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
                <tbody class="text-gray-800">
                    @foreach ($userPayments as $payment)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $payment->payment_id }}</td>
                            <td>{{ $payment->subscription->subscription_id }}</td>
                            <td>{{ $payment->plan_id }}</td>
                            <td>{{ $payment->plan->name }}</td>
                            <td>{{ $payment->plan->price }}</td>
                            <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}</td>
                            <td><span
                                    class="px-1 py-1 rounded text-white text-sm font-bold uppercase {{ $payment->status === 'completed' ? 'bg-green-500' : 'bg-red-500' }}">{{ $payment->status }}</span>
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
                    custom_id: '1'
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
