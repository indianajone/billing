<?php namespace Mustache\Billing;

use Mustache\Contracts\Billing\Payment;
use Mustache\Contracts\Billing\Provider;

class Repository implements Payment {

    /**
     * Provider instance
     * 
     * @var \Mustache\Contracts\Billing\Provider
     */
    protected $provider;

    /**
     * Create new payment instance
     * 
     * @param Provider $provider
     */
    public function __construct(Provider $provider)
    {
        $this->provider = $provider;
    }

    public function make(array $config)
    {
        $this->provider->setConfig($config);

        return $this->provider;
    }

    /**
     * Dynamically call the default provider instance.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $callable = [ $this->provider, $method ];

        return call_user_func_array($callable, $parameters);
    }

}