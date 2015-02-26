<?php namespace Mustache\Billing;

use InvalidArgumentException;
use Mustache\Contracts\Billing\Provider;

class Factory
{
    const DEFAULT_DRIVER = 'paypal';

    /**
     * The array of created "drivers".
     *
     * @var array
     */
    protected $drivers = [];

    /**
     * The application instance.
     * @var $mixed
     */
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Get default driver
     * 
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->getConfig('driver') ?: static::DEFAULT_DRIVER;
    }

    /**
     * Get a driver instance.
     *
     * @return mixed
     */
    public function driver($driver=null)
    {
        $driver = $driver ?: $this->getDefaultDriver();

        if(isset($this->drivers[$driver]))
        {
            return $this->drivers[$driver];
        }

        return $this->createDriver($driver);
    }

    /**
     * Create PaypalDriver repository.
     * 
     * @return Repository
     */
    public function createPaypalDriver()
    {
        return new Drivers\Paypal(
            $this->getConfig('client_id'), $this->getConfig('secret')
        );
    }

    /**
     * Call dynamic create driver method.
     * 
     * @param  string $driver
     * @return mixed
     */
    protected function createDriver($driver)
    {
        $method = 'create'.ucfirst($driver).'Driver';

        if(!method_exists($this, $method))
        {
            throw new InvalidArgumentException("Driver [$driver] not supported.");
        }

        return $this->$method();
    }

    /**
     * Get configuration
     * 
     * @param  string $name
     * @return string
     */
    protected function getConfig($name)
    {
        return isset($this->config[$name]) ? $this->config[$name] : null;
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $callable = [ $this->driver(), $method ];

        return call_user_func_array($callable, $parameters);
    }
}
