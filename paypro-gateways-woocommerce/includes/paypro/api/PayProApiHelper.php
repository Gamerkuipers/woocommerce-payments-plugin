<?php

class PayProApiHelper
{
    var $apiKey;
    var $api;

    var $testMode;

    protected const RES_API_APIKEYINVALID = "API key not valid";

    public function __construct() {}

    public function init($apiKey, $testMode = false)
    {
        $this->api = new \PayPro\Client($apiKey);
        $this->testMode = $testMode ? true : false;
    }

    public function getIdealIssuers()
    {
        $this->api->setCommand('get_all_pay_methods');
        $result = $this->execute();
        
        $result['issuers'] = $result['data']['data']['ideal']['methods'];
        unset($result['data']);
        return $result;
    }

    public function createPayment(array $data)
    {
        $this->api->setCommand('create_payment');
        $this->api->setParams($data);
        return $this->execute();
    }

    public function getSaleStatus($payment_hash)
    {
        $this->api->setCommand('get_sale');
        $this->api->setParam('payment_hash', $payment_hash);
        return $this->execute();
    }

    private function execute()
    {
        $this->api->setParam('test_mode', ($this->testMode ? 'true' : 'false'));

        $result = $this->api->execute();

        if($result['return'] == self::RES_API_APIKEYINVALID) {
            $result['errors'] = 'true';
            PayPro_WC_Plugin::debug('Paypro - ' . $result['return']);
        }

        if(isset($result['errors']))
        {
            if(strcmp($result['errors'], 'false') === 0)
                return array('errors' => false, 'data' => $result['return']);
            else
                return array('errors' => true, 'message' => $result['return']);
        }
        else
        {
            return array('errors' => true, 'message' => 'Invalid return from the API');
        }
    }
}
