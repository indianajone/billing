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
        return $this->getConfig('driver') ?: self::DEFAULT_DRIVER;
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
    public function createPaypalDriver(array $config)
    {
        return new Drivers\Paypal($config['client_id'], $config['secret'], $config['settings']);
    }

    public function createTransferDriver()
    {
        return new Driver\TransferLater;
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

        $config = $this->getConfig($driver);

        return $this->$method($config);
    }

    /**
     * Get configuration
     * 
     * @param  string $name
     * @return string
     */
    protected function getConfig($name)
    {
        return isset($this->config[$name]) ? $this->config[$name] : [];
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
