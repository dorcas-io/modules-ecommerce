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

    private $order_key;

    private $timezone;

    private $cod;

    private $codAmount;

    private $returnResponse = false;
    
    public function __construct(array $providerParams, bool $returnResponse = false)
    {
        $this->baseUrl = env('CREDENTIAL_ECOMMERCE_PROVIDER_URL', 'provider.com');
        $this->domainName = env('CREDENTIAL_ECOMMERCE_PROVIDER_DOMAIN', 'provider.com');
        $this->userName = env('CREDENTIAL_ECOMMERCE_PROVIDER_USERNAME', 'user@provider.com');
        $this->userPassword = env('CREDENTIAL_ECOMMERCE_PROVIDER_PASSWORD', 'provider.com');

        $this->vendor_id = $providerParams["vendor_id"];

        $this->timezone = 60; //take from controller or params later

        $this->cod = 0; //take from controller or params later

        $this->codAmount = 1000; //take from controller or params later

        $this->order_key = $providerParams["order_key"] ?? null;

        $this->returnResponse = $returnResponse;

        // Get Access Token
        if (empty($this->accessToken)) {
            $this->getToken();
        }

    }


    private function connect($path, $params, $method = 'POST')
    {
        // Connect To API
        $ch = curl_init();

        if ($method == 'GET') {
            curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $path . '?' . http_build_query($params));
        } else {
            curl_setopt($ch, CURLOPT_URL, $this->baseUrl . $path);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        }
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
        $response = $this->connect('/vendor_login', $params, 'POST');
        
        $this->accessToken = $response->data->access_token;

    }


    /**
     * @param Request     $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCost($fromAddress, $toAddress, $vehicleSize)
    {

        $input_get_vehicle = [
            'size' => $vehicleSize
        ];

        $paramsVehicle = $this->getProviderParams('getVehicle', $input_get_vehicle);
        $response0 = $this->connect('/getVehicle', $paramsVehicle, 'GET');
        $responseVehicle = (array) $response0;

        $input_send_payment_for_task = [
            'from_address' => [
                0 => $fromAddress
            ],
            'to_address' => [
                0 => $toAddress
            ],
            'vehicle_id' => ($responseVehicle["data"][0])->vehicle_id
        ];

        $params1 = $this->getProviderParams('send_payment_for_task', $input_send_payment_for_task);
        $response1 = $this->connect('/send_payment_for_task', $params1, 'POST');

        $input_get_bill_breakdown = (array) $response1->data;

        $params2 = $this->getProviderParams('get_bill_breakdown', $input_get_bill_breakdown);
        $response2 = $this->connect('/get_bill_breakdown', $params2, 'POST');

        $output = (array) $response2->data;

        $tempOrder = Cache::get($this->order_key);
        $tempOrder["logistics"]["meta"]["getVehicle"] = $response0->data;
        $tempOrder["logistics"]["meta"]["send_payment_for_task"] = $response1->data;
        $tempOrder["logistics"]["meta"]["get_bill_breakdown"] = $response2->data;
        Cache::forever($this->order_key, $tempOrder);

        return $this->returnResponse ? $response2 : $output;

    }


    /**
     * @param Request     $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPickupTask($orderID)
    {
        $cachedOrder = Cache::get('cacheOrderManagement_' . $orderID);

        //dd($cachedOrder);

        $input_create_task_via_vendor = [
            "getVehicle" => (array) $cachedOrder["logistics"]["meta"]["getVehicle"],
            "send_payment_for_task" => (array) $cachedOrder["logistics"]["meta"]["send_payment_for_task"],
            "get_bill_breakdown" => (array) $cachedOrder["logistics"]["meta"]["get_bill_breakdown"],
        ];

        dd($input_create_task_via_vendor);

        $params_create_task_via_vendor = $this->getProviderParams('create_task_via_vendor', $input_create_task_via_vendor);
        dd($params_create_task_via_vendor);
        $response = $this->connect('/create_task_via_vendor', $params_create_task_via_vendor, 'POST');

        $output = (array) $response->data;

        return $this->returnResponse ? $response : $output;

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
                    "timezone" => $this->timezone,
                    "is_cod_job" => $this->cod,
                    "auto_assignment" => 1,
                    "layout_type" => 0,
                    "is_multiple_tasks" => 1,
                    "has_pickup" => 1,
                    "has_delivery" => 1,
                    "user_id" => 1,
                    "payment_method" => 32,
                    "form_id" => 2,
                    "vehicle_id" => $input['vehicle_id'],
                    "delivery_instruction" => "Hey, please deliver the parcel with safety. Thanks in advance",
                    "delivery_images" => "https://s3.ap-south-1.amazonaws.com/kwik-project/task_images/kjjX1603884709732-stripeconnect.png",
                    "is_loader_required" => 0,
                    "loaders_amount" => 0,
                    "loaders_count" => 0,
                    "parcel_amount" => $this->codAmount
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
                    "pickup_time" => \Carbon\Carbon::now()->format('Y-m-d H:i:s'),
                    "user_id" => 1,
                    "form_id" => 2,
                    "promo_value" => null,
                    "credits" => 0,
                    "is_cod_job" => 0,
                    "parcel_amount" => $this->codAmount,
                    "delivery_charge_by_buyer" => 0
                ];
            break;

            case 'getVehicle':
                $params = [
                    "access_token" => $this->accessToken,
                    "is_vendor" => 1,
                    "size" => $input['size']
                ];
            break;

            case 'create_task_via_vendor':

                // modify pickup and delivery times ?
                //$input['send_payment_for_task']['pickups']['time'] = \Carbon\Carbon::now();
                //$input['send_payment_for_task']['deliveries']['time'] = \Carbon\Carbon::now();

                $params = [
                    "access_token" => $this->accessToken,
                    "domain_name" => $this->domainName,
                    "vendor_id" => $this->vendor_id,
                    "is_multiple_tasks" => 1,
                    "fleet_id" => $input[''][''], //"",
                    "latitude" => $input[''][''], //0,
                    "longitude" => $input[''][''], //0,
                    "timezone" => $this->timezone,
                    "is_cod_job" => $this->cod,
                    "has_pickup" => 1,
                    "has_delivery" => 1,
                    "layout_type" => 0,
                    "auto_assignment" => 1,
                    "insurance_amount" => $input['send_payment_for_task']['insurance_amount'],
                    "total_no_of_tasks" => $input['send_payment_for_task']['total_no_of_tasks'],
                    "total_service_charge" => $input['send_payment_for_task']['total_service_charge'],
                    "payment_method" => 32,
                    "amount" => $input['send_payment_for_task']['per_task_cost'],
                    "loaders_amount" => $input['send_payment_for_task']['loaders_amount'],
                    "loaders_count" => $input['send_payment_for_task']['loaders_count'],
                    "is_loader_required" => $input['send_payment_for_task']['is_loader_required'],
                    "delivery_instruction" => $input['send_payment_for_task']['delivery_instruction'],
                    "vehicle_id" => $input["send_payment_for_task"]['vehicle_id'],
                    "delivery_images" => $input['send_payment_for_task']['delivery_images'],
                    "pickup_delivery_relationship" => $input[''][''], //0,
                    "team_id" => $input[''][''], //"",
                    "parcel_amount" => $input['input_get_bill_breakdown']['PARCEL_AMOUNT'],
                    "pickups" => $input['send_payment_for_task']['pickups'],
                    "deliveries" => $input['send_payment_for_task']['deliveries'],
                    "surge_cost" => $input['input_get_bill_breakdown']['SURGE_PRICING'],
                    "surge_type" => $input['input_get_bill_breakdown']['SURGE_TYPE'],
                    // "is_task_otp_required" => 0,
                    // "cash_handling_charges" => 0,
                    // "cash_handling_percentage" => 0,
                    // "net_processed_amount" => 0,
                    // "kwister_cash_handling_charge" => "0",
                    // "delivery_charge_by_buyer" => 1,
                    // "delivery_charge" => 0,
                    // "collect_on_delivery" => 0,
                ];
            break;


            default:
                $params = [];
            break;
        }

        return $params;
    }


}