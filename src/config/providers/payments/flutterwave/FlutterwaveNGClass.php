<?php

namespace Dorcas\ModulesEcommerce\config\providers\payments;

use Hostville\Dorcas\Sdk;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

use \Flutterwave\Flutterwave;
use \Flutterwave\Helper\Config;
use Flutterwave\Payload;
use Flutterwave\Customer;
use Flutterwave\Service\CollectionSubaccount;
use Flutterwave\Service\PayoutSubaccount;


class FlutterwaveNGClass
{

    public $accessToken = null;

    private $user_email;

    private $publicKey;

    private $privateKey;

    private $encryptionKey;

    private $env;

    private $providerParams;

    private $config;
    
    public function __construct(array $providerParams)
    {
        $this->publicKey = env('CREDENTIAL_FLUTTERWAVE_KEY_PUBLIC', 'xyz');
        $this->privateKey = env('CREDENTIAL_FLUTTERWAVE_KEY_SECRET', 'xyz');
        $this->encryptionKey = env('CREDENTIAL_FLUTTERWAVE_KEY_ENCRYPTION', 'xyz');
        $this->env = 'production';


        # your config must implement Flutterwave\Contract\ConfigInterface
        $myConfig = Config::setUp(
            $this->privateKey,
            $this->publicKey,
            $this->encryptionKey,
            $this->env
        ); 
        //Flutterwave::bootstrap($myConfig);
        $this->config = $myConfig;
        
        $this->user_email = $providerParams["user_email"];

        $this->providerParams = $providerParams;

        // Get Access Token
        // if (empty($this->accessToken)) {
        //     $this->getToken();
        // }

    }


    private function connect($path, $postParams, $accessToken = null)
    {
        // Connect To API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postParams));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json"
        ));
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response);

    }


    public function rest($path, $postParams, $accessToken = null)
    {
        // Connect To API
        $params = $this->getProviderParams('vendor_login');
        $response = $this->connect('/vendor_login', $params, null);
        
        $this->accessToken = $response->data->access_token;

    }
    

    private function getToken()
    {
        // Connect To API
        $params = $this->getProviderParams('vendor_login');
        $response = $this->connect('/vendor_login', $params, null);
        
        $this->accessToken = $response->data->access_token;

    }


    /**
     * @param Request     $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function activate()
    {
        $response = $this->createSubAccount('payout', $this->getProviderParams);

        //$output = (array) $response->data;

        return $response;

    }

    /**
     * @param Request     $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createSubAccount($type, $param)
    {
        $config = $this->config;
        //Flutterwave::bootstrap($this->config);

        switch ($type) {

            case "payout":

                $customer = new Customer();
                $customer->set("fullname", $param["fullname"]);
                $customer->set("email", $param["email"]);
                $customer->set("phone_number", $param["phone_number"]);

                $payload = new Payload();
                $payload->set("country",  $param["country"]);
                $payload->set("customer", $customer);

                $service = new PayoutSubaccount($config);
                $request = $service->create($payload);

                break;

            case "collection":
                $payload = new Payload();
                $payload->set("account_bank", "044");
                $payload->set("account_number", "06900000".mt_rand(29, 40));
                $payload->set("business_name", "Maxi Ventures");
                $payload->set("split_value", "0.5"); // 50%
                $payload->set("business_mobile", "09087930450");
                $payload->set("business_email", "vicomma@gmail.com");
                $payload->set("country", "NG");
                $service = new CollectionSubaccount($config);
                $request = $service->create($payload);
                break;
        }

        //$output = (array) $request->data;

        return $request;

    }




    /**
     * @param array $route
     *
     * @return array
     */
    public function getProviderParams($route, $input = [])
    {
        switch ($route) {

            case 'vendor_login':
                $params = [
                    'domain_name' => $this->domainName,
                    'email' => $this->userName,
                    'password' => $this->userPassword,
                    'api_login' => 1,
                ];
            break;

            default:
                $params = [];
            break;
        }

        return $params;
    }



}