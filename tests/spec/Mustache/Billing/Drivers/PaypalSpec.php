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
        $clientId = 'AfWPUBfESQO_vYJQ6zmhhOcC8s5Q-vet6ajVgsai96V5nnnd8z0WFjcTYWcQmhVNnsgtLVWWKqjpZf0B';
        $secret = 'EFtOJeHVDEQnoAN5-mtD25goNzT1wd5wi1MY_jimmkasXmi7e2N4Jablo-LaBjHS3sAa4g40AflJkHZ3';

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
                'success' => 'http://avitez.app?success=true',
                'fail' => 'http://avitez.app?cancel=true'
            ],
            'payer' => [
                'email' => 'mmer555-buyer@hotmail.com',
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
