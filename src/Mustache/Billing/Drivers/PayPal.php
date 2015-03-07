<?php namespace Mustache\Billing\Drivers;

use PayPal\Api\Amount;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
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

    protected $apiContext;

    public function __construct($clientId, $secret, array $config)
    { 
        $this->apiContext = $this->apiContext($clientId, $secret, $config);
    }

    public function make($data)
    {
        $payerInfo = $this->payerInfo(array_get($data, 'payer'));

        $payer = $this->payer(array_get($data, 'method', 'paypal'), $payerInfo);

        $items = $this->items(array_get($data, 'items', []));

        $amount = $this->amount(array_get($data, 'total'), array_get($data, 'currency'));

        $transaction = $this->transaction($amount, $items, array_get($data, 'description'));

        $redirectUrls = $this->redirect(array_get($data, 'return_url'), array_get($data, 'cancel_url'));

        $payment = $this->payment()->setIntent('sale')
                        ->setPayer($payer)
                        ->setRedirectUrls($redirectUrls)
                        ->setTransactions([$transaction]);

        return $payment->create($this->apiContext);
    }

    public function get($id)
    {
        return $this->payment()->get($id, $this->apiContext);
    }

    public function pay($data)
    {
        $payment = $this->get($data['paymentId']);

        return $this->execute($payment, $data['PayerID']);
    }

    protected function apiContext($clientId, $secret, $config)
    {
        if($this->apiContext)
        {
            return $this->apiContext;
        }

        $config = [
            'mode' => array_get($config, 'mode', BillingContract::ENV_SANDBOX),
            'log.LogEnabled' => array_get($config, 'log.enabled', false),
            'log.FileName' => array_get($config, 'log.path', 'PayPal.log'),
            'log.LogLevel' => array_get($config, 'log.level', 'FINE')
        ];

        $credential = $this->getApiCredential($clientId, $secret, $config);

        $apiContext = new ApiContext($credential, 'Request-'.time());

        $apiContext->setConfig($config);

        return $apiContext;
    }

    protected function execute(Payment $payments, $payerId)
    {   
        $execution = new PaymentExecution;

        $execution->setPayerId($payerId);

        return $payments->execute($execution, $this->apiContext);
    }

    protected function getApiCredential($clientId, $secret, $config)
    {
        $credential =  new OAuthTokenCredential($clientId, $secret);

        $credential->getAccessToken($config);

        return $credential;
    }

    protected function getFullname(PayerInfo $payer)
    {
        $fullname = $payer->getFirstName();

        if($lastname = $payer->getLastName())
        {
            $fullname .= " $lastname"; 
        }

        return $fullname;
    }

    protected function payer($method, PayerInfo $info=null)
    {
        $payer = new Payer;

        return $payer->setPaymentMethod($method)
                     ->setPayerInfo($info);
    }

    protected function payerInfo($payer=null)
    {
        if(empty($payer))
        {
            return null;
        }

        $payerInfo = new PayerInfo;

        $payerInfo->setFirstName(array_get($payer, 'firstname'))
                  ->setLastName(array_get($payer, 'lastname'));

        if($address = array_get($payer, 'address'))
        {
            $shippingAddress = $this->address($this->getFullname($payerInfo), $payerInfo->getPhone(), $address);

            $payerInfo->setShippingAddress($shippingAddress);
        }

        return $payerInfo;
    }

    protected function address($name, $phone, $address)
    {
        $shippingAddress = new ShippingAddress;

        return $shippingAddress->setRecipientName($name)
                               ->setLine1(array_get($address, 'line1'))
                               ->setLine2(array_get($address, 'line2'))
                               ->setCity(array_get($address, 'city'))
                               ->setState(array_get($address, 'state'))
                               ->setCountryCode(array_get($address, 'country', 'TH'))
                               ->setPostalCode(array_get($address, 'postcode'))
                               ->setPhone($phone);

    }

    protected function amount($total, $currency)
    {
        $amount = new Amount;

        return $amount->setTotal($total)
                      ->setCurrency($currency ?: 'THB');
    }

    protected function items(array $items)
    {
        $list = new ItemList;

        foreach ($items as $item) 
        {
           $list->addItem($this->item($item));
        }

        return $list;
    }

    protected function item(array $data)
    {
        $item = new Item;

        return $item->setName(array_get($data, 'name'))
                    ->setQuantity(array_get($data, 'quantity'))
                    ->setCurrency(array_get($data, 'currency', 'THB'))
                    ->setPrice(array_get($data, 'price'));
    }

    protected function payment()
    {
        return new Payment;
    }

    protected function redirect($returnUrl, $cancelUrl=null)
    {
        $redirectUrls = new RedirectUrls;

        $redirectUrls->setReturnUrl($returnUrl);

        if(is_null($cancelUrl))
        {
            $redirectUrls->setCancelUrl($returnUrl);
        }

        return $redirectUrls;

    }

    protected function transaction(Amount $amount, ItemList $items, $description)
    {
        $transaction = new Transaction;

        return $transaction->setAmount($amount)
                           ->setInvoiceNumber(uniqid())
                           ->setItemList($items)
                           ->setDescription($description);
    }
}
