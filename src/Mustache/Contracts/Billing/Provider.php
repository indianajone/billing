<?php namespace Mustache\Contracts\Billing;

interface Provider {

    const ENV_PRODUCTION = 'production';

    const ENV_SANDBOX = 'sandbox';

    public function payment($order);
}