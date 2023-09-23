<?php

namespace Dorcas\ModulesEcommerce\Config\Providers\Logistics;

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

    private $vendor_id;
    
    public function __construct(array $providerParams)
    {
        $this->baseUrl = env('CREDENTIAL_ECOMMERCE_PROVIDER_URL', 'provider.com');
        $this->domainName = env('CREDENTIAL_ECOMMERCE_PROVIDER_DOMAIN', 'provider.com');
        $this->userName = env('CREDENTIAL_ECOMMERCE_PROVIDER_USERNAME', 'user@provider.com');
        $this->userPassword = env('CREDENTIAL_ECOMMERCE_PROVIDER_PASSWORD', 'provider.com');

        $this->vendor_id = $providerParams["vendor_id"];

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

        $input_get_bill_breakdown = (array) $response->data;

/*

array:17 [
  "currency" => {#844
    +"currency_id": 29
    +"code": "NGN"
    +"name": "Nigerian Naira"
    +"symbol": "₦"
    +"is_zero_decimal_currency": 0
    +"minimum_amount": 10
  }
  "per_task_cost" => "16730.1"
  "pickups" => array:1 [
    0 => {#843
      +"address": ""
      +"name": "Demo"
      +"latitude": "6.616106599999999"
      +"longitude": "3.3684495"
      +"time": "2023-06-28T19:41:45.367181Z"
      +"phone": null
      +"has_return_task": false
      +"is_package_insured": 0
    }
  ]
  "deliveries" => array:1 [
    0 => {#842
      +"address": "federal university of tec"
      +"name": "BOlaji Olawoye"
      +"latitude": "7.307042000000001"
      +"longitude": "5.1397549"
      +"time": "2023-06-29T00:05:10.567Z"
      +"phone": "081822334434"
      +"has_return_task": false
      +"is_package_insured": 0
      +"hadVairablePayment": 1
    }
  ]
  "insurance_amount" => 0
  "total_no_of_tasks" => 1
  "total_service_charge" => 0
  "delivery_charge_by_buyer" => 0
  "is_cod_job" => 0
  "is_loader_required" => 1
  "loaders_amount" => 40
  "delivery_instruction" => "Hey, please deliver the parcel with safety. Thanks in advance"
  "delivery_images" => "https://s3.ap-south-1.amazonaws.com/kwik-project/task_images/kjjX1603884709732-stripeconnect.png"
  "vehicle_id" => 4
  "loaders_count" => 4
  "sareaId" => 3
  "backupDeliveries" => array:1 [
    0 => {#841
      +"address": "federal university of tec"
      +"name": "BOlaji Olawoye"
      +"latitude": "7.307042000000001"
      +"longitude": "5.1397549"
      +"time": "2023-06-28T19:41:45.436341Z"
      +"phone": "081822334434"
      +"has_return_task": false
      +"is_package_insured": 0
    }
  ]
]

*/


        $params2 = $this->getProviderParams('get_bill_breakdown', $input_get_bill_breakdown);
        $response = $this->connect('/get_bill_breakdown', $params2, null);

        $output = (array) $response->data;


        /*
            +"ACTUAL_AMOUNT": "1974.50"
            +"DISCOUNT": "0.00"
            +"CREDITS_TO_ADD": 0
            +"VAT": "151.09"
            +"PENDING_AMOUNT": 0
            +"SERVICE_TAX": 0
            +"BENEFIT_TYPE": null
            +"PAYABLE_AMOUNT_WITHOUT_CREDITS": 0
            +"TIP": "0.00"
            +"DEFAULT_VAT_PERCENT": 7.5
            +"DEFAULT_SERVICE_TAX_PERCENT": 0
            +"INSURANCE_AMOUNT": 0
            +"AMOUNT_PER_TASK": "1974.50"
            +"TOTAL_NO_OF_TASKS": 1
            +"AMOUNT_FOR_FIRST_TASK": "2165.59"
            +"TOTAL_SERVICE_CHARGE": 500
            +"SURGE_PRICING": 0
            +"SURGE_TYPE": 0
            +"CREDITS_USED": "0.00"
            +"LOADER_CHARGES": 40
            +"LOADER_REQUIRED": 1
            +"LOADERS_INSTRUCTION": "Hey, please handover parcel with safety. Thanks"
            +"LOADERS_IMAGES": "https://s3.ap-south-1.amazonaws.com/kwik-project/task_images/wPqj1603886372690-stripeconnect.png"
            +"VEHICLE_ID": 4
            +"PENDING_CANCELLATION_CHARGE": 0
            +"PENDING_WAITING_CHARGES": 0
            +"LOADERS_COUNT": 4
            +"SAREA_ID": 0
            +"CURRENT_CREDITS": "60000.00"
            +"PROMO_VALUE": null
            +"DISCOUNTED_AMOUNT": "2014.50"
            +"PAYABLE_AMOUNT": "2165.59"
            +"NET_PAYABLE_AMOUNT": 2200
            +"ORDER_PAYABLE_AMOUNT": 1700
            +"ACTUAL_ORDER_PAYABLE_AMOUNT": 2200
            +"VENDOR_CREDITS": 60000
            +"NET_CREDITS_PAYABLE_AMOUNT": 0
            +"WALLET_ENABLE": 0
        */

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
                    "vendor_id" => $this->vendor_id,
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
                    "amount" => $input['per_task_cost'],
                    "insurance_amount" => $input['insurance_amount'],
                    "total_no_of_tasks" => $input['total_no_of_tasks'],
                    "total_service_charge" => $input['total_service_charge'],
                    "vehicle_id" => $input['vehicle_id'],
                    "delivery_images" => $input['delivery_images'],
                    "is_loader_required" => $input['is_loader_required'],
                    "loaders_amount" => $input['loaders_amount'],
                    "loaders_count" => $input['loaders_count'],
                    "delivery_instruction" => $input['delivery_instruction'],
                    "pickup_time" => "2020-10-28 17:35:37",
                    "user_id" => 1,
                    "form_id" => 2,
                    "promo_value" => null,
                    "credits" => 0,
                    "is_cod_job" => 0,
                    "parcel_amount" => 1000,
                    "delivery_charge_by_buyer" => 0
                ];
            break;


            default:
                $params = [];
            break;
        }

        return $params;
    }


}