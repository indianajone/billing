<?php

namespace spec\Mustache\Billing;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FactorySpec extends ObjectBehavior
{
    function let()
    {
        $config = [
            'paypal' => [
                'client_id' => 'ARpcRhBPHtGyRnQu4n6lyvgwRTYDfgHXsIK5YsMw3OA-8FQ-TjUicIMC8wWO',
                'secret' => 'EMEJ0RCgwH_JgX6bdB9t33AqZLzmf-INX4B0036X5p-zA7Rw7JNna-KgxFrd',
                'settings' => [
                    'mode' => 'sandbox'
                ]
            ]
        ];

        $this->beConstructedWith($config);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Mustache\Billing\Factory');
    }

    function it_create_driver()
    {
        $this->driver()->shouldHaveType('Mustache\Contracts\Billing\Provider');
    }

    function it_should_throw_invalid_argument_exception_when_driver_are_not_supported()
    {
        $this->shouldThrow('\InvalidArgumentException')->during('driver', ['foo']);
    }
}
