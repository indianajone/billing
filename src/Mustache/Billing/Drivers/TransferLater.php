<?php namespace Mustache\Billing\Drivers;

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