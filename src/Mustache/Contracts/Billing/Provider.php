<?php namespace Mustache\Contracts\Billing;

interface Provider {

    const ENV_SANDBOX = 'sandbox';

    public function make($data);

    public function get($id);

    public function pay($data);
}