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
        {{-- <form id="payment-form" action="{{ route('pay') }}" method="post">
            @csrf
            <div id="paypal-button"></div>
            <input type="hidden" id="payment_method_nonce" name="payment_method_nonce">
            <input type="hidden" name="plan_id" value="nsvj">
        </form> --}}
        <div id="dropin-container"></div>
        <button id="submit-button">Pay Now</button>

    </div>

    <script>
        const button = document.querySelector('#submit-button');

        fetch('/braintree/token')
            .then(response => response.json())
            .then(data => {
                const clientToken = data.clientToken;

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
                                            planId: 'nsvj'
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

    {{-- <script>
        braintree.client.create({
            authorization: '{{ $clientToken }}'
        }, function(clientErr, clientInstance) {
            if (clientErr) {
                console.error('Error creating client:', clientErr);
                return;
            }

            braintree.paypalCheckout.create({
                client: clientInstance
            }, function(paypalCheckoutErr, paypalCheckoutInstance) {
                if (paypalCheckoutErr) {
                    console.error('Error creating PayPal Checkout:', paypalCheckoutErr);
                    return;
                }

                paypalCheckoutInstance.loadPayPalSDK({
                    vault: true,
                }, function() {
                    paypal.Buttons({
                        fundingSource: paypal.FUNDING.PAYPAL,
                        createBillingAgreement: function() {
                            return paypalCheckoutInstance.createPayment({
                                flow: 'vault',
                                billingAgreementDescription: 'Your subscription description'
                            });
                        },
                        onApprove: function(data, actions) {
                            return paypalCheckoutInstance.tokenizePayment(data,
                                function(err, payload) {
                                    document.getElementById('payment_method_nonce')
                                        .value = payload.nonce;
                                    document.getElementById('payment-form')
                                        .submit();
                                });
                        },
                        onCancel: function(data) {
                            console.log('PayPal payment canceled', JSON.stringify(data,
                                0, 2));
                        },
                        onError: function(err) {
                            console.error('PayPal error', err);
                        }
                    }).render('#paypal-button');
                });
            });
        });
    </script> --}}

</body>

</html>
