<?php namespace Mustache\Billing\Drivers;

use Mustache\Contracts\Billing\Provider as BillingContract;

class TransferLater implements BillingContract {

    public function make($data)
    {
        return array_get($data, 'return_url');
    }

    public function get($id)
    {

    }

    public function execute($data)
    {
        return true;
    }

}