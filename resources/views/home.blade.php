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

    <div class="container mx-auto px-4 py-8 text-center">
        <h2 class="text-4xl font-semibold text-black text-center">Plans</h2>
        <div class="flex flex-wrap md:flex-nowrap flex-col md:flex-row justify-center content-center gap-6 mt-8"
            x-data="{ selectedPlan: '{{ $plans->first()->paypal_plan_id ?? '0' }}' }">
            @foreach ($plans as $plan)
                <div class="w-80 p-4 bg-[#FF2D20]/10 rounded-lg shadow-lg px-6 py-8 text-center cursor-pointer hover:bg-[#FF2D20]/20 transition duration-300"
                    x-on:click="selectedPlan = '{{ $plan->paypal_plan_id }}'; setPlanId(selectedPlan)"
                    x-bind:class="{ 'bg-[#FF2D20]/20 border border-[#FF2D20]/50': selectedPlan === '{{ $plan->paypal_plan_id }}' }">
                    <h3 class="text-xl font-bold text-[#FF2D20] mb-4 uppercase tracking-widest">{{ $plan->name }}</h3>
                    <p class="text-gray-500 mb-4 text-sm">{{ $plan->description }}</p>
                    <p class="text-[#FF2D20] mb-4 font-bold uppercase tracking-widest text-3xl">
                        {{ $plan->price }} USD</p>
                </div>
            @endforeach
        </div>
        <div class="mt-8 mx-auto w-80" id="paypal-button-container"></div>
    </div>

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
                // Fetch Paypal Plan Id
                // fetch('/plans/' + selectedPlan)
                //     .then(response => response.json())
                //     .then(data => {
                //         console.log(data);
                //     });
            },
            createSubscription: function(data, actions) {
                return actions.subscription.create({
                    plan_id: selectedPlan
                });
            },
            onApprove: function(data) {
                console.log('subscription approved');
                console.log(data);
                fetch('/complete-order', {
                    method: 'post',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        data: data
                    })
                }).then(function() {
                    alert('subscription created');
                });
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
