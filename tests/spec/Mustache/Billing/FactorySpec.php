<?php

namespace spec\Mustache\Billing;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class FactorySpec extends ObjectBehavior
{
    function let()
    {
        $config = [];

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
