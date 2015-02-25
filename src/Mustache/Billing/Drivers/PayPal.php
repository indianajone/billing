<?php namespace Mustache\Billing\Drivers;

use PayPal\Api\Amount;
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

    protected $environment;

    public function __construct($clientId, $secret)
    {
        $credential = $this->getApiCredential($clientId, $secret);

        $mode = $this->getEnvironment();

        $this->apicontext = $this->getApiContext($credential, compact('mode'));
    }

    public function payment($order)
    {
        $payer = $this->registerPayer($order['payer']);

        $amount = $this->amount($order['total']);

        $transaction = $this->transaction($amount, $order['description']);

        $redirector = $this->redirector($order['redirects']['success'], $order['redirects']['fail']);

        $this->payments($payer, $transaction, $redirector, $order['intent'])
             ->create($this->apicontext);
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

    protected function getApiContext($credential, $config=[])
    {
        if($this->apicontext)
        {
            return $this->apicontext;
        }

        $apicontext = new ApiContext($credential, 'Request-'.time());

        $apicontext->setConfig($config);

        return $apicontext;
    }

    protected function getApiCredential($clientId, $secret)
    {
        return new OAuthTokenCredential($clientId, $secret);
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
            'email' => $email,
            'first_name' => $firstname,
            'last_name' => $lastname,
            'phone' => $phone,
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

    protected function redirector($success, $fail)
    {
        return new RedirectUrls([
            'return_url' => $success,
            'cancel_url' => $fail
        ]);
    }

    protected function transaction($amount, $description=null)
    {
        return new Transaction(compact('amount', 'description'));
    }
}