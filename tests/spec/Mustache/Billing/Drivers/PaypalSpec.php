<?php

namespace spec\Mustache\Billing\Drivers;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use PayPal\Rest\ApiContext;
use PayPal\Api\Payment;
use PayPal\Auth\OAuthTokenCredential as PaypalToken;


class PaypalSpec extends ObjectBehavior
{
    function let(PaypalToken $token, ApiContext $apicontext)
    {
        $clientId = 'ARpcRhBPHtGyRnQu4n6lyvgwRTYDfgHXsIK5YsMw3OA-8FQ-TjUicIMC8wWO';
        $secret = 'EMEJ0RCgwH_JgX6bdB9t33AqZLzmf-INX4B0036X5p-zA7Rw7JNna-KgxFrd';

        $this->beConstructedWith($clientId, $secret);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Mustache\Billing\Drivers\Paypal');
    }

    function it_request_payment_for_business(ApiContext $apicontext, Payment $payment)
    {
        $order = [
            'description' => '500ml (24 per pack) One-time delivery',
            'total' => 2568.00,
            'intent' => 'sale',
            'redirects' => [
                'success' => 'https://devtools-paypal.com/guide/pay_paypal/php?success=true',
                'fail' => 'https://devtools-paypal.com/guide/pay_paypal/php?cancel=true'
            ],
            'payer' => [
                'email' => 'john@example.com',
                'firstname' => 'John',
                'lastname' => 'Doe',
                'phone' => '0819101234',
                'address' => [
                    'address' => '123/2 soi 1 Foo Rd.,',
                    'city' => 'Dindang',
                    'country_code' => 'TH',
                    'postcode' => 10200
                ]
            ]
        ];

        $payment->create($apicontext)->shouldBeCalled();

        $this->payment($order);
    }
}
