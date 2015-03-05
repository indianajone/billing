<?php namespace Mustache\Billing\Drivers;

use PayPal\Api\Amount;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Details;
use PayPal\Api\Payer;
use PayPal\Api\PayerInfo;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\ShippingAddress;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Mustache\Contracts\Billing\Provider as BillingContract;

class PayPal implements BillingContract {

    protected $apicontext;

    protected $credential;

    protected $environment;

    public function __construct($clientId, $secret)
    {
        $mode = $this->getEnvironment();
        
        $this->credential = $this->getApiCredential($clientId, $secret);

        $this->apicontext = $this->getApiContext(compact('mode'));
    }

    public function payment($order)
    {
        $payer = $this->registerPayer($order['payer']);

        $amount = $this->amount($order['total']);

        $transaction = $this->transaction($amount, $order['description']);

        $redirector = $this->redirector($order['redirects']['return'], $order['redirects']['cancel']);
    
        return $this->payments($payer, $transaction, $redirector, $order['intent'])
                    ->create($this->apicontext);
    }

    public function pay(Payment $payments, $payerId)
    {   
        $execution = (new PaymentExecution)->setPayerId($payerId);

        return $payments->execute($execution);
    }

    public function getEnvironment()
    {
        return $this->environment ?: BillingContract::ENV_SANDBOX;
    }

    public function setEnvironment($env)
    {
        $this->environment = $env;
    }

    protected function registerPayer($payer)
    {
        $fullname = $payer['firstname'] . ' ' . $payer['lastname'];

        $addr = $payer['address'];

        $address = $this->address($fullname, $addr['address'], $addr['city'], $addr['country_code'], $addr['postcode']); 

        $payerInfo = $this->payerInfo(
            $payer['email'], $payer['firstname'], $payer['lastname'], $payer['phone'], $address
        );

        return $this->payer($payerInfo);
    }

    protected function getApiContext($config=[])
    {
        if($this->apicontext)
        {
            return $this->apicontext;
        }

        $apicontext = new ApiContext($this->credential, 'Request-'.time());

        $apicontext->setConfig([
            'mode' => $this->getEnvironment(),
            'log.LogEnabled' => true,
            'log.FileName' => __DIR__.'/../../../PayPal.log',
            'log.LogLevel' => 'DEBUG'
        ]);

        return $apicontext;
    }

    protected function getApiCredential($clientId, $secret)
    {
        $credential =  new OAuthTokenCredential($clientId, $secret);

        $mode = $this->getEnvironment();

        $credential->getAccessToken(compact('mode'));

        return $credential;
    }

    protected function address($name, $address, $city, $code, $postcode)
    {
        return new ShippingAddress([
            'recipient_name' => $name,
            'line1' => $address,
            'city' => $city,
            'country_code' => $code,
            'postal_code' => $postcode
        ]);

    }

    protected function amount($total, $currency='THB', $details=[])
    {
        $details = $this->details($details);

        return new Amount(compact('total', 'currency', 'details'));
    }

    protected function details($attributes=[])
    {
        return new Details($attributes);
    }

    protected function payer(PayerInfo $info, $method='paypal')
    {
        return new Payer([
            'payment_method' => $method,
            'payer_info' => $info
        ]);
    }

    protected function payerInfo($email, $firstname, $lastname, $phone, ShippingAddress $address)
    {
        return new PayerInfo([
            'first_name' => $firstname,
            'last_name' => $lastname,
            'shipping_address' => $address
        ]);
    }

    protected function payments($payer, $transaction, $redirector, $intent='sale')
    {
        return new Payment([
            'payer' => $payer,
            'redirect_urls' => $redirector,
            'transactions' => [ $transaction ],
            'intent' => $intent
        ]);
    }

    protected function redirector($returnUrl, $cancelUrl=null)
    {
        $redirectUrl = new RedirectUrls([
            'return_url' => $returnUrl
        ]);

        if($cancelUrl)
        {
            $redirectUrl->setCancelUrl($cancelUrl);
        }

        return $redirectUrl;
    }

    protected function transaction($amount, $description=null)
    {
        return new Transaction(compact('amount', 'description'));
    }
}
