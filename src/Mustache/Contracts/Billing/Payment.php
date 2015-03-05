<?php namespace Mustache\Contracts\Billing;

interface Payment {

    const ENV_PRODUCTION = 'production';

    const ENV_SANDBOX = 'sandbox';

    public function make(array $config);
}