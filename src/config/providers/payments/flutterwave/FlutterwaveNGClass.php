<?php

namespace Dorcas\ModulesEcommerce\config\providers\payments;

use Hostville\Dorcas\Sdk;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;


class FlutterwaveNGClass
{

    public $accessToken = null;

    private $publicKey;

    private $secretKey;

    private $encryptionKey;

    private $env;

    private $providerParams;

    private $config;

    private $baseUrl;
    
    public function __construct(array $providerParams)
    {

        $this->baseUrl = 'https://api.flutterwave.com/v3';
        $this->publicKey = env('CREDENTIAL_FLUTTERWAVE_KEY_PUBLIC', 'xyz');
        $this->secretKey = env('CREDENTIAL_FLUTTERWAVE_KEY_SECRET', 'xyz');
        $this->encryptionKey = env('CREDENTIAL_FLUTTERWAVE_KEY_ENCRYPTION', 'xyz');
        $this->env = 'production';

        $this->providerParams = $providerParams;

        // Get Access Token
        // if (empty($this->accessToken)) {
        //     $this->getToken();
        // }

    }


    private function connect($path, $method = 'POST', $postParams)
    {
        // Connect To API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_ENCODING, '');

        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postParams));
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer " . $this->secretKey
        ));
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response);

    }


    /**
     * @param Request     $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function activate()
    {
        $response = $this->createSubAccount('payout', $this->providerParams);

        return $response;

    }

    /**
     * @param Request     $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createSubAccount($type, $params)
    {
        $config = $this->config;
        //Flutterwave::bootstrap($this->config);

        switch ($type) {

            case "payout":
                
                $response = $this->connect('/payout-subaccounts', 'POST', $params);

                break;

            case "collection":
                // $payload = new Payload();
                // $payload->set("account_bank", "044");
                // $payload->set("account_number", "06900000".mt_rand(29, 40));
                // $payload->set("business_name", "Maxi Ventures");
                // $payload->set("split_value", "0.5"); // 50%
                // $payload->set("business_mobile", "09087930450");
                // $payload->set("business_email", "vicomma@gmail.com");
                // $payload->set("country", "NG");
                // $service = new CollectionSubaccount($config);
                $response = [];
                break;
        }

        //$output = (array) $request->data;

        return $response;

    }

    /**
     * @param Request     $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWalletBalances()
    {
        $params = $this->providerParams;
        
        $response = $this->connect('/payout-subaccounts/' . $params['account_reference'] . '/balances', 'GET', $params);

        return $response;

    }

    /**
     * @param Request     $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTransferEstimate()
    {
        $params = $this->providerParams;
        
        $response = $this->connect('/transfers/fee', 'GET', $params);

        return $response;

    }

    /**
     * @param Request     $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function doTransfer()
    {
        $params = $this->providerParams;
        
        $response = $this->connect('/transfers', 'POST', $params["params_transfer"]);

        return $response;

    }




}