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

    <script src="https://www.paypal.com/sdk/js?client-id={{ env('PAYPAL_CLIENT_ID') }}"></script>
    <script src="https://js.braintreegateway.com/web/dropin/1.43.0/js/dropin.min.js"></script>
    <!-- Load the client component. -->
    <script src="https://js.braintreegateway.com/web/3.103.0/js/client.min.js"></script>
    <!-- Load the PayPal Checkout component. -->
    <script src="https://js.braintreegateway.com/web/3.103.0/js/paypal-checkout.min.js"></script>
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
        <div id="dropin-container"></div>
        <div id="paypal-button"></div>

    </div>

    {{-- <script>
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
    </script> --}}

    {{-- Braintree --}}
    <script>
        // Step two: create a dropin instance using that container (or a string
        //   that functions as a query selector such as '#dropin-container')
        braintree.dropin.create({
            authorization: '{{ $clientToken }}',
            container: document.getElementById('dropin-container'),
            paypal: {
                flow: 'vault'
            }
            // ...plus remaining configuration
        }).then((dropinInstance) => {
            // Use 'dropinInstance' here
            // Methods documented at https://braintree.github.io/braintree-web-drop-in/docs/current/Dropin.html
        }).catch((error) => {});
    </script>

    <script>
        // Create a client.
        braintree.client.create({
            authorization: '{{ $clientToken }}',
        }, function(clientErr, clientInstance) {

            // Stop if there was a problem creating the client.
            // This could happen if there is a network error or if the authorization
            // is invalid.
            if (clientErr) {
                console.error('Error creating client:', clientErr);
                return;
            }

            // Create a PayPal Checkout component.
            braintree.paypalCheckout.create({
                client: clientInstance
            }, function(paypalCheckoutErr, paypalCheckoutInstance) {

                // Stop if there was a problem creating PayPal Checkout.
                // This could happen if there was a network error or if it's incorrectly
                // configured.
                if (paypalCheckoutErr) {
                    console.error('Error creating PayPal Checkout:', paypalCheckoutErr);
                    return;
                }

                // Load the PayPal JS SDK (see Load the PayPal JS SDK section)
            });

        });
    </script>

</body>

</html>
