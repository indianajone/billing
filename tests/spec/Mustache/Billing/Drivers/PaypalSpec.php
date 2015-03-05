<?php

namespace spec\Mustache\Billing\Drivers;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use PayPal\Rest\ApiContext;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;

class PaypalSpec extends ObjectBehavior
{
    function let()
    {
        $clientId = 'AfWPUBfESQO_vYJQ6zmhhOcC8s5Q-vet6ajVgsai96V5nnnd8z0WFjcTYWcQmhVNnsgtLVWWKqjpZf0B';
        $secret = 'EFtOJeHVDEQnoAN5-mtD25goNzT1wd5wi1MY_jimmkasXmi7e2N4Jablo-LaBjHS3sAa4g40AflJkHZ3';

        $this->beConstructedWith($clientId, $secret);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Mustache\Billing\Drivers\Paypal');
    }

    function it_request_payment_for_business()
    {
        $order = [
            'description' => '500ml (24 per pack) One-time delivery',
            'total' => 2568.00,
            'intent' => 'sale',
            'redirects' => [
                'return' => "http://avitez.app?success=true",
                'cancel' => "http://avitez.app?cancel=true"
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

        $this->payment($order)->shouldReturnAnInstanceOf('PayPal\Api\Payment');
    }

    function it_execute_payment_for_business(ApiContext $apicontext, Payment $payments, PaymentExecution $executor)
    {
        $payerId = 'USJ4CUH867BV6';

        // $payments->get('PAY-2JJ78327JX616254RKTYDONA')->shouldBeCalled();

        // $executor->setPayerId($payerId)->willReturn($executor);

        // $payments->execute($executor)->shouldBeCalled();

        $this->pay($payments, $payerId);
    }
}
