<?php

namespace Dorcas\ModulesEcommerce\config\providers\logistics;

use Hostville\Dorcas\Sdk;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class KwikNGClass
{

    public $accessToken = null;

    private $baseUrl;

    private $domainName;

    private $userName;

    private $userPassword;
    
    public function __construct()
    {
        $this->baseUrl = env('CREDENTIAL_ECOMMERCE_PROVIDER_URL', 'provider.com');
        $this->domainName = env('CREDENTIAL_ECOMMERCE_PROVIDER_DOMAIN', 'provider.com');
        $this->userName = env('CREDENTIAL_ECOMMERCE_PROVIDER_USERNAME', 'user@provider.com');
        $this->userPassword = env('CREDENTIAL_ECOMMERCE_PROVIDER_PASSWORD', 'provider.com');

        // Get Access Token
        if (empty($this->accessToken)) {
            $this->getToken();
        }

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
    public function getCost($fromAddress, $toAddress)
    {
        /*
        - /send_payment_for_task (get charge details according to google distance)
        - /get_bill_breakdown (get bill details)
        */

        $input_send_payment_for_task = [
            'from_address' => [
                0 => $fromAddress
            ],
            'to_address' => [
                0 => $toAddress
            ]
        ];

        $params1 = $this->getProviderParams('send_payment_for_task', $input_send_payment_for_task);
        $response = $this->connect('/send_payment_for_task', $params1, null);

        $input_get_bill_breakdown = $response->data;

        $params2 = $this->getProviderParams('get_bill_breakdown', $input_get_bill_breakdown);
        $response = $this->connect('/get_bill_breakdown', $params2, null);

        $output = (array) $response->data;

        return $output;

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

            case 'send_payment_for_task';
                $params = [
                    "access_token" => $this->accessToken,
                    "domain_name" => $this->domainName,
                    "pickups" => $input['from_address'],
                    "deliveries" => $input['to_address'],
                    "custom_field_template" => "pricing-template",
                    "pickup_custom_field_template" => "pricing-template",
                    "vendor_id" => 151,
                    "auto_assignment" => 1,
                    "layout_type" => 0,
                    "timezone" => -330,
                    "is_multiple_tasks" => 1,
                    "has_pickup" => 1,
                    "has_delivery" => 1,
                    "user_id" => 1,
                    "payment_method" => 32,
                    "form_id" => 2,
                    "vehicle_id" => 4,
                    "delivery_instruction" => "Hey, please deliver the parcel with safety. Thanks in advance",
                    "delivery_images" => "https://s3.ap-south-1.amazonaws.com/kwik-project/task_images/kjjX1603884709732-stripeconnect.png",
                    "is_loader_required" => 1,
                    "loaders_amount" => 40,
                    "loaders_count" => 4,
                    "is_cod_job" => 0,
                    "parcel_amount" => 1000
                ];
            break;

            case 'get_bill_breakdown':
                $params = [
                    "access_token" => $this->accessToken,
                    "domain_name" => $this->domainName,
                    "benefit_type" => null,
                    "amount" => "1474.5",
                    "insurance_amount" => 0,
                    "total_no_of_tasks" => 1,
                    "pickup_time" => "2020-10-28 17:35:37",
                    "user_id" => 1,
                    "form_id" => 2,
                    "promo_value" => null,
                    "credits" => 0,
                    "total_service_charge" => 500,
                    "vehicle_id" => 4,
                    "delivery_images" => "https://s3.ap-south-1.amazonaws.com/kwik-project/task_images/wPqj1603886372690-stripeconnect.png",
                    "is_loader_required" => 1,
                    "loaders_amount" => 40,
                    "loaders_count" => 4,
                    "is_cod_job" => 0,
                    "parcel_amount" => 1000,
                    "delivery_charge_by_buyer" => 0,
                    "delivery_instruction" => "Hey, please handover parcel with safety. Thanks"
                ];
            break;


            default:
                $params = [];
            break;
        }

        return $params;
    }


}