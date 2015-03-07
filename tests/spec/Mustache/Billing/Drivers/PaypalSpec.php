<?php

namespace spec\Mustache\Billing\Drivers;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use PayPal\Api\Payment;

class PayPalSpec extends ObjectBehavior
{
    function let()
    {
        $clientId = 'AfWPUBfESQO_vYJQ6zmhhOcC8s5Q-vet6ajVgsai96V5nnnd8z0WFjcTYWcQmhVNnsgtLVWWKqjpZf0B';

        $secret = 'EFtOJeHVDEQnoAN5-mtD25goNzT1wd5wi1MY_jimmkasXmi7e2N4Jablo-LaBjHS3sAa4g40AflJkHZ3';

        $config = [
            'mode' => 'sandbox',
            'log' => [
                'enabled' => true,
                'path' => 'Paypal.log',
                'level' => 'DEBUG'
            ]
        ];

        $this->beConstructedWith($clientId, $secret, $config);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Mustache\Billing\Drivers\PayPal');
    }

    function it_make_a_payment()
    {
        $order = [
            'description' => 'Coffee Order',
            'method' => 'paypal',
            'return_url' => 'http://avitez.com',
            'total' => 200,
            'items' => [
                [
                    'name' => 'coffee beans',
                    'quantity' => 2,
                    'price' => 100
                ]
            ]
        ];

        $this->make($order)->shouldReturnAnInstanceOf('PayPal\Api\Payment');
    }
}
