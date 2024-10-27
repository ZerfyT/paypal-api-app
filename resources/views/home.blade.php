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

    <div class="container px-4 py-8 mx-auto text-center">
        <h2 class="text-4xl font-semibold text-center text-black">Plans</h2>
        <div class="flex flex-col flex-wrap content-center justify-center gap-6 mt-8 md:flex-nowrap md:flex-row"
            x-data="{ selectedPlan: '{{ $plans->first()->paypal_plan_id ?? '0' }}' }">
            @foreach ($plans as $plan)
                <div class="w-80 p-4 bg-[#FF2D20]/10 rounded-lg shadow-lg px-6 py-8 text-center cursor-pointer hover:bg-[#FF2D20]/20 transition duration-300"
                    x-on:click="selectedPlan = '{{ $plan->paypal_plan_id }}'; setPlanId(selectedPlan)"
                    x-bind:class="{ 'bg-[#FF2D20]/20 border border-[#FF2D20]/50': selectedPlan === '{{ $plan->paypal_plan_id }}' }">
                    <h3 class="text-xl font-bold text-[#FF2D20] mb-4 uppercase tracking-widest">{{ $plan->name }}</h3>
                    <p class="mb-4 text-sm text-gray-500">{{ $plan->description }}</p>
                    <p class="text-[#FF2D20] mb-4 font-bold uppercase tracking-widest text-3xl">
                        {{ $plan->price }} USD</p>
                </div>
            @endforeach
        </div>

        <!-- Drop-in container -->
        <div class="mt-8">
            <h3 class="text-xl font-bold text-black mt-8">Payment Method</h3>
            <div id="dropin-container"></div>
            <button id="submit-button" class="bg-[#FF2D20] text-white font-bold py-2 px-4 rounded" name="submit"
                type="submit">Pay Now</button>
        </div>
        <!-- End Drop-in container -->

        <!-- Payment History -->
        <div class="mt-8">
            <h3 class="text-xl font-bold text-black">Payment History</h3>
            @if ($payments->count() > 0)
                <ul>
                    @foreach ($payments as $payment)
                        <li>{{ $payment->created_at }}</li>
                    @endforeach
                </ul>
            @else
                <p>No payments found.</p>
            @endif
        </div>
        <!-- End Payment History -->

    </div>

    <script>
        const button = document.querySelector('#submit-button');

        fetch('/braintree/token')
            .then(response => response.json())
            .then(data => {
                const clientToken = data.clientToken;
                console.log(clientToken);

                braintree.dropin.create({
                    authorization: clientToken,
                    container: '#dropin-container',
                    paypal: {
                        flow: 'vault',
                        buttonStyle: {
                            color: 'blue',
                            shape: 'rect',
                            size: 'medium'
                        }
                    },
                    card: {
                        cardholderName: {
                            required: true
                        },
                        vault: {
                            allowVaultCardOverride: true,
                            vaultCard: true,
                        }
                    }
                }, (createErr, instance) => {
                    if (createErr) {
                        console.error('Error creating Drop-in:', createErr);
                        return;
                    }

                    button.addEventListener('click', () => {
                        instance.requestPaymentMethod((err, payload) => {
                            if (err) {
                                console.error('Error requesting payment method:', err);
                                return;
                            }

                            fetch('/braintree/process', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        payload: {
                                            nonce: payload.nonce,
                                            planId: '{{ env('BRAINTREE_PLAN_1_ID') }}'
                                        }
                                    })
                                })
                                .then(response => response.json())
                                .then(result => {
                                    if (result.success) {
                                        console.log('Payment successful:', result);
                                        // Handle successful payment
                                    } else {
                                        console.error('Payment failed:', result);
                                        // Handle failed payment
                                    }
                                })
                                .catch(error => {
                                    console.error('Error processing payment:', error);
                                });
                        });
                    });
                });
            })
            .catch(error => {
                console.error('Error fetching client token:', error);
            });
    </script>

</body>

</html>
