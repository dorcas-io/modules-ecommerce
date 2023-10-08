<?php

namespace Dorcas\ModulesEcommerce\Http\Controllers;

use Hostville\Dorcas\Sdk;
use Illuminate\Http\Request;
use App\Dorcas\Support\CartManager;
use App\Http\Controllers\Controller;
//use App\Http\Controllers\ECommerce\OnlineStore;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Dorcas\ModulesEcommerce\Http\Controllers\ModulesEcommerceStoreController as Dashboard;
use Dorcas\ModulesDashboard\Http\Controllers\ModulesDashboardController as Dash;
use App\Dorcas\Hub\Enum\Banks;
use Dorcas\ModulesDashboard\Classes\Checklists;
use Ramsey\Uuid\Uuid;


class ModulesEcommerceStore extends Controller
{

    public $COMPANY_DAILY_ORDER_MANAGEMENT_KEY;


    public function __construct()
    {
        parent::__construct();
        $this->data['page']['title'] = 'Store';
        $this->data['page']['header'] = ['title' => 'Store'];
    }

    public function getCompanyDailyOrderManagementKey($company)
    {
       return 'cacheOrderManagement_' . $company->id . "." . Carbon::now()->format('Y_m_d');
    }

    public function getRandomOrderKey()
    {
       return Uuid::uuid1()->toString();
    }

    public function getOrderManagementKey($order)
    {
       return 'cacheOrderManagement_' . $order->id;
    }

    /**
     * @param Request     $request
     * @param string|null $slug
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request, Sdk $sdk, string $slug = null)
    {
 
        $storeOwner = $this->getCompanyViaDomain();
    
        # get the store owner
        if (empty($storeOwner)) {
            abort(404, 'Could not find a store at this URL.');
        }
        $this->data['categorySlug'] = $slug;
        $this->data['storeSettings'] = Dashboard::getStoreSettings((array) $storeOwner->extra_data);
        # our store settings container
        $this->data['defaultSearch'] = $request->get('q', '');
        $this->data['storeOwner'] = $storeOwner;
        $this->data['page']['title'] = $storeOwner->name . ' ' . $this->data['page']['title'];
        $this->data['page']['header']['title'] = $storeOwner->name . ' Store';
        $this->data['cart'] = self::getCartContent($request);

        $storeIsReady = false;

        $userDashboardStatus = [];
        $storeOwnerUsers = $storeOwner->users;
        $userId = $storeOwnerUsers["data"][0]["id"];
        $userDashboardStatusKey = 'userDashboardStatus.' . $userId;
        $user_dashboard_status = Cache::get($userDashboardStatusKey, [
            'preferences' => [
                'guide_needed' => true,
            ],
            'checklists' => [],

        ]);

        $checklists = [];

        $checklists = Dash::processChecklists($request, $sdk, $user_dashboard_status, $storeOwner);

        $count = collect($checklists)->count();
        $done = collect($checklists)->where('verification', true)->count();

        $storeIsReady = ($count==$done) ? true : $storeIsReady;
        
        
        $storeIsReady = true; //fix

        $this->data['storeIsReady'] = $storeIsReady;

        $sOwnerx = (array) $storeOwner;
        $newSdk = $this->authorizeSdkByCompany($sdk, $sOwnerx);

        $this->data['readinessChecks'] = Dash::readinessChecks($request, $sdk, $storeOwner);

        $loggedInUser = !empty(auth()->user()) ? auth()->user() : null;

        $this->data['storeAdminLoggedIn'] = !empty($loggedInUser) ? true : false;

        $this->data['setupRemainingMessage'] = "";

        $this->storeViewComposer($this->data, $request, $sdk, $storeOwner);

        return view('modules-ecommerce::webstore.shop', $this->data);
    }
    
    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function categories()
    {
        return redirect()->route('webstore');
    }
    
    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function products()
    {
        return redirect()->route('webstore');
    }
    
    public function variant_type_get(Request $request, Sdk $sdk)
    {

        $company = $this->getCompanyViaDomain();
        # get the company information
        $salesConfig = !empty($company->extra_data['salesConfig']) ? $company->extra_data['salesConfig'] : [];
        $variantTypes = !empty($salesConfig) ? $salesConfig['variant_types'] : [];
        return $variantTypes;
        //return response()->json($variantTypes);
    }


    /**
     * @param Request $request
     * @param Sdk     $sdk
     * @param string  $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function productDetails(Request $request, Sdk $sdk, string $id)
    {
        $storeOwner = $this->getCompanyViaDomain();
       
        # get the store owner
        $this->data['storeSettings'] = Dashboard::getStoreSettings((array) $storeOwner->extra_data);
        # our store settings container
        if (empty($storeOwner)) {
            abort(404, 'Could not find a store at this URL.');
        }
        $query = $sdk->createStoreService()->addQueryArgument('id', $id)->send('GET', [$storeOwner->id, 'product']);

        if (!$query->isSuccessful()) {
            abort(500, $query->getErrors()[0]['title'] ?? 'Something went wrong while fetching the product.');
        }
        $this->data['product'] = $product = $query->getData(true);
        $this->data['storeOwner'] = $storeOwner;
        $this->data['cart'] = self::getCartContent($request);

        //$subdomain = get_dorcas_subdomain_via_owner($storeOwner,$sdk);
        //dd($storeOwner);
        //$storeURL = "https://store.".$subdomain;
        //$this->data['storeDomain'] = $storeURL;
        $this->data['storeDomain'] = "#";

        $this->data['variantTypes'] = $this->variant_type_get($request,$sdk);

        //check requests params
        $search = $request->query('search', '');
        $sort = $request->query('sort', '');
        $order = $request->query('order', 'asc');
        $offset = (int) $request->query('offset', 0);
        $limit = (int) $request->query('limit', 10);
        $type = $request->query('type', 'variant');
        $parent = $request->query('parent', $id);

        $this->data['productType'] = $product->product_type;

        $isParent = $product->product_type=="default" ? true : false;
        $isVariant = $product->product_type=="variant" ? true : false;

        if ($isParent) {

            $this->data['variantProducts'] = [];

        }  elseif ($isVariant) {
            //get variant parent
            $qparent = $sdk->createStoreService()->addQueryArgument('id', $product->product_parent)->send('GET', [$storeOwner->id, 'product']);
            if (!$qparent->isSuccessful()) {
                abort(500, $query->getErrors()[0]['title'] ?? 'Something went wrong while fetching the product.');
            }
            $this->data['variantParent'] = $qparent->getData(true);
        }



        $this->data['page']['title'] = 'Product Details | '.$product->name;
        $this->data['page']['header']['title'] = $storeOwner->name . ' Store' . ' | ' . $product->name;
        return view('modules-ecommerce::webstore.product-details', $this->data);
    }

    /*
     * @param Request $request
     * @param string  $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function quickView(Request $request, string $id)
    {
        $storeOwner = $this->getCompanyViaDomain();
        # get the store owner
        if (empty($storeOwner)) {
            abort(404, 'Could not find a store at this URL.');
        }
        $this->data['storeOwner'] = $storeOwner;
        $url = config('dorcas-api.url') . '/store/' . $storeOwner->id . '/product?id=' . $id;
        # compose the query URL

        $skip_verify = env('DORCAS_CURL_SSL_VERIFY',true) === false && env('APP_ENV','production') !== "production" && env('DORCAS_ENV','production') !== "production";
        //dd(array(env('DORCAS_CURL_SSL_VERIFY'), env('APP_ENV'), env('DORCAS_ENV'), $skip_verify));
        if ($skip_verify) {
            $json = json_decode(file_get_contents($url, false, stream_context_create(array('ssl' => array('verify_peer' => false, 'verify_peer_name' => false)))));
        } else {
            $json = json_decode(file_get_contents($url));
        }

        # request the data
        if (empty($json->data)) {
            abort(500, 'Something went wrong while getting the product.');
        }

        $this->data['storeDomain'] = "#";
        
        $this->data['product'] = $product = (object) $json->data;
        $this->data['price'] = collect($product->prices->data)->where('currency', 'NGN')->first();
        return view('modules-ecommerce::webstore.quick-view', $this->data);
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public static function getCartContent(Request $request): array
    {
        return (new CartManager($request))->getCart() ?:
            ['items' => [], 'total' => ['raw' => 0, 'formatted' => 0], 'currency' => 'NGN'];
    }


    public function redirectRoute(Request $request)
    {
        return '';
    }



    /**
     * @param Request $request
     * @param Sdk     $sdk
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function cart(Request $request, Sdk $sdk)
    {
        $storeOwner = $this->getCompanyViaDomain();
        //$company = $this->getCompany();
        # get the store owner
        $this->data['storeSettings'] = Dashboard::getStoreSettings((array) $storeOwner->extra_data);
        # our store settings container
        if (empty($storeOwner)) {
            abort(404, 'Could not find a store at this URL.');
        }
        $this->data['storeOwner'] = $storeOwner;
        //$this->data['page']['title'] = $storeOwner->name . ' ' . $this->data['page']['title'];
        $this->data['page']['title'] = $storeOwner->name . ' | Shopping Cart';

        $sOwner = (array) $storeOwner;

        $sdk = $this->authorizeSdkByCompany($sdk, $sOwner);

        $this->data['countries'] = $countries = $this->getCountries($sdk);
        # get the countries listing
        $nigeria = !empty($countries) && $countries->count() > 0 ? $countries->where('iso_code', 'NG')->first() : null;
        # get the nigeria country model
        if (!empty($nigeria)) {
            $this->data['states'] = $this->getDorcasStates($sdk, $nigeria->id);
            # get the states
        } else {
            $this->data['states'] = [];
        }

        $company_data = (array) $storeOwner->extra_data;
        //$this->data['company_data'] = $company_data;
        $logistics_settings = $company_data['logistics_settings'] ?? ["logistics_shipping" => env("SETTINGS_ECOMMERCE_LOGISTICS_SHIPPING", "shipping_myself"), "logistics_fulfilment" => env("SETTINGS_ECOMMERCE_LOGISTICS_FULFILMENT", "fulfilment_pickup")];

        $seller_data = (array) $storeOwner->extra_data;

        $this->data['logistics'] = [
            "seller_state" => "",
            "seller_country" => env('SETTINGS_COUNTRY', 'NG'),
            "settings" => $logistics_settings,
            "seller_address" => []
        ];

        // Fetch/Initiate Cache
        
        $cartCacheKey = "cartCache";

        $cartCacheDefault = [
            "address" => [
                "firstname" => "",
                "lastname" => "",
                "email" => "",
                "phone" => "",
                "address" => "",
                "state" => "",
                "country" => env('SETTINGS_COUNTRY', 'NG'),
                "latitude" => "0",
                "longitude" => "0"
            ]
        ];

        $cartCache = session('cartCache', $cartCacheDefault);

        // Process Address Content
        $address_firstname = !empty($request->address_firstname) ? $request->address_firstname : $cartCache["address"]["firstname"];
        $address_lastname = !empty($request->address_lastname) ? $request->address_lastname : $cartCache["address"]["lastname"];
        $address_email = !empty($request->address_email) ? $request->address_email : $cartCache["address"]["email"];
        $address_phone = !empty($request->address_phone) ? $request->address_phone : $cartCache["address"]["phone"];
        $address_address = !empty($request->address_address) ? $request->address_address : $cartCache["address"]["address"];
        $address_state = !empty($request->address_state) ? $request->address_state : $cartCache["address"]["state"];
        $address_country = !empty($request->address_country) ? $request->address_country : $cartCache["address"]["country"];
        $address_latitude = !empty($request->address_latitude) ? $request->address_latitude : $cartCache["address"]["latitude"];
        $address_longitude = !empty($request->address_longitude) ? $request->address_longitude : $cartCache["address"]["longitude"];

        // Save Address Status
        $cartCache["address"] = [
            "firstname" => $address_firstname,
            "lastname" => $address_lastname,
            "email" => $address_email,
            "phone" => $address_phone,
            "address" => $address_address,
            "state" => $address_state,
            "country" => $address_country,
            "latitude" => $address_latitude,
            "longitude" => $address_longitude
        ];

        $cartCache["storeOwner"] = (array) $storeOwner;


        // Save Seller Address
        $location = ['address' => '', 'address1' => '', 'address2' => '', 'state' => ['data' => ['id' => '']], 'country' => '', 'latitude' => '', 'longitude' => ''];
        # the location information
        
        $locations = $this->getLocations($sdk, $storeOwner);

        $location = !empty($locations) ? $locations->first() : $location;
        $location = (array) $location;
        $location['country'] = env('SETTINGS_COUNTRY', 'NG');
        // get Seller Geo Location details
        
        if (isset ($sOwner["extra_data"]["location"])) {
            $location['address'] = $sOwner["extra_data"]['location']['address'] ?? '';
            $location['latitude'] = $sOwner["extra_data"]['location']['latitude'] ?? '';
            $location['longitude'] = $sOwner["extra_data"]['location']['longitude'] ?? '';
        }

        $cartCache["address_seller"] = $location;


        // Process Cart Stages
        $cart_stages = [
            "address" => [
                "title" => "Enter Delivery Address",
                "active" => false
            ],
            "shipping" => [
                "title" => "Choose Shipping Type",
                "active" => false
            ],
            "review" => [
                "title" => "Review & Finalize Order",
                "active" => false
            ]
        ];

        $this->data['stages'] = [
            "stage" => "address",
            "data" => $cart_stages,
            //"countries" => $countries
        ];

        $this->data['cache'] = [
            "address" => $cartCache["address"]
        ];

        $stage_present = false;
        if ( !empty($request->stage) && in_array($request->stage, array_keys($cart_stages)) ) {
            $currentStage = $request->stage;
            $stage_present = true;
        } else {
            $currentStage = 'address';
        }
        $this->data['stages']['stage'] = $currentStage;
        $this->data['stages']['data'][$currentStage]['active'] = true;

        // Add Bank Transfer Details
        $payWithDetails = [];
        
        //$accounts = $this->getBankAccounts($sdk);
        $companyUsers = $sOwner["users"];
        $accounts = collect($companyUsers["data"][0]["bank_accounts"]);
        
        if (!empty($accounts) && $accounts->count() > 0) {
            $payWithDetails["bank_transfer"] = $accounts->first();
            $this->data['account'] = $accounts->first();
        } else {
            //$account_name = $request->user()->firstname . ' ' . $request->user()->lastname;
            $co = (array) $storeOwner; //$request->user()->company();
            $account_name = $co["name"];
            $this->data['bank_transfer_default'] = [
                'account_number' => '',
                'account_name' => $account_name,
                'json_data' => [
                    'bank_code' => ''
                ]
            ];
        }

        $this->data['payWithDetails'] = $payWithDetails;

        $this->data['banks'] = collect(Banks::BANK_CODES)->sort()->map(function ($name, $code) {
            return ['name' => $name, 'code' => $code];
        })->values();


        $stage_title = $stage_present ? $cart_stages[$currentStage]["title"] : 'Shopping Cart';

        $this->data['page']['header']['title'] = $storeOwner->name . ' Store' . ' | ' . $stage_title;
        //$this->data['cart'] = Home::getCartContent($request);
        $this->data['cart'] = $cartContents = $this->getCartContent($request);

        $this->data['env'] = [
            "CREDENTIAL_GOOGLE_API_KEY" => env('CREDENTIAL_GOOGLE_API_KEY', 'ABC'),
            "CREDENTIAL_PAYSTACK_KEY_PUBLIC" => env('CREDENTIAL_PAYSTACK_KEY_PUBLIC', 'ABC'),
            "CREDENTIAL_FLUTTERWAVE_KEY_PUBLIC" => env('CREDENTIAL_FLUTTERWAVE_KEY_PUBLIC', 'ABC'),
        ];

        $this->data['use_wallet'] = env('SETTINGS_ECOMMERCE_PAYMENT_USE_WALLET', true);
        $this->data['providers'] = [
            'payment' => env('SETTINGS_ECOMMERCE_PAYMENT_PROVIDER', 'flutterwave'),
            'logistics' => env('SETTINGS_ECOMMERCE_LOGISTICS_PROVIDER', 'kwik'),
        ];


        //Initiatialize Cache Storage For Order Management for this Company (for today)
        // $companyOrderManagementDailyKey = $this->getCompanyDailyOrderManagementKey($storeOwner);
        // if ( !Cache::has($companyOrderManagementDailyKey) ) {
        //     Cache::forever($companyOrderManagementDailyKey, [
        //         "orders" => [],
        //         "meta" => []
        //     ]);
        // }
        # Depreciated in favour of per order cache

        $getRandomOrderKey = 'tempOrderManagement_' . $this->getRandomOrderKey();
        Cache::put($getRandomOrderKey, [
            "order" => [],
            "payment" => [
                "status" => false,
                "provider" => [
                    "id" => env('SETTINGS_ECOMMERCE_PAYMENT_PROVIDER', 'flutterwave'),
                    "meta" => []
                ],
                "meta" => []
            ],
            "logistics" => [
                "status" => false,
                "provider" => [
                    "id" => env('SETTINGS_ECOMMERCE_LOGISTICS_PROVIDER', 'kwik'),
                    "meta" => []
                ],
                "meta" => []
            ],
        ], 60*60*24);

        $this->data['random_order_key'] = $cartCache["random_order_key"] = $getRandomOrderKey;


        // Save ALL to session
        session(['cartCache' => $cartCache]);


        return view('modules-ecommerce::webstore.cart', $this->data);
    }



    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeFromCartXhr(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|string'
        ]);
        # validate the request
        $cart = new CartManager($request);
        # create the cart manager
        $cart->remove($request->id);
        # remove the product from the cart
        return response()->json($cart->getCart());
    }

    /**
     * @param Request $request
     * @param Sdk     $sdk
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function checkoutXhr(Request $request, Sdk $sdk)
    {
        $this->validate($request, [
            'firstname' => 'required|string|max:30',
            'lastname' => 'required|string|max:30',
            'email' => 'required|email|max:80',
            'phone' => 'required',
            'address' => 'nullable|max:250'
        ]);
        # validate the request
        $storeOwner = $this->getCompanyViaDomain();
        # get the store owner
        $cartManager = new CartManager($request);
        $cart = (object) $cartManager->getCart();
        # get the cart
        $storeService = $sdk->createStoreService();
        # create the store service
        /**
         * Step 1: check for customer with this email
         * Step 2: create the customer
         */
        $customer = (clone $storeService)->addBodyParam('firstname', $request->firstname)
                                        ->addBodyParam('lastname', $request->lastname)
                                        ->addBodyParam('email', $request->email)
                                        ->addBodyParam('phone', $request->phone)
                                        ->addBodyParam('address', $request->address)
                                        ->send('POST', [$storeOwner->id, 'customers']);
        # we put step 1 & 2 in one call
        if (!$customer->isSuccessful()) {
            throw new \RuntimeException('Failed while checking your customer account... Please try again later.'); //$customer->getErrors()[0]['title']
        }
        $customer = $customer->getData(true);
        $orderData = [
            'title' => 'Order #'.($customer->orders_count + 1).' for '.$customer->firstname.' '.$customer->lastname,
            'description' => 'Order placed on web store at ' . Carbon::now()->format('D jS M, Y h:i a'),
            'currency' => $cart->currency,
            'amount' => $cart->total['raw'],
            'products' => [],
            'customers' => [$customer->id],
            'enable_reminder' => 0
        ];
        foreach ($cart->items as $cartItem) {
            $orderData['products'][] = ['id' => $cartItem['id'], 'quantity' => $cartItem['quantity'], 'price' => $cartItem['unit_price']];
        }
        $checkoutQuery = (clone $storeService);
        foreach ($orderData as $key => $value) {
            $checkoutQuery = $checkoutQuery->addBodyParam($key, $value);
        }
        # Step 3: create order
        $checkout = $checkoutQuery->send('POST', [$storeOwner->id, 'checkout']);
        # send the checkout query
        if (!$checkout->isSuccessful()) {
            throw new \RuntimeException('Could not add your order to the record. Please try again later.');
        }

        $data = $checkout->getData();
        if (!empty($checkout->meta) && !empty($checkout->meta['payment_url'])) {
            $data['payment_url'] = $checkout->meta['payment_url'];
        }


        // calculate splitting of money into SALES (the SMEs, VAS money), SHIPPING (the shipping charge), PARTER fee, and DORCAS fee
        // both partner + dorcas + flutterwave fees constitute transaction fees

        $partnerAdmin = DB::connection('core_mysql')->table("companies")->where('id', 1)->first(); 
        # get company data of admin
        $company_data = (array) $partnerAdmin->extra_data;
        if (isset($company_data["global_partner_settings"]["ecommerce"]) && !empty($company_data["global_partner_settings"]["ecommerce"]) ) {
            $partnerECommerce = $company_data["global_partner_settings"]["ecommerce"];
        } else {
            $partnerECommerce = [
                "transaction_fees" => [
                    "total" => 10,
                    "partner" => 2.5,
                    "dorcas" => 7.5
                ],
                "subaccounts" => [
                    "sales_sme" => "", //this is a joint escrow type for all SMEs
                    "sales_vas" => "",
                    "logistics" => "",
                    "partner" => "",
                    "dorcas" => ""
                ]
            ];
        }

        //$total_all = $cartContents["total"]["raw"];
        $cartItems = $cart->items;

        $sumSalesSME = 0;
        $sumLogistics = 0;

        foreach ($cartItems as $item) {
            $sumSalesSME += ( $item["isShipping"] == "no" ) ? ($item["unit_price"] * $item["quantity"]) : 0;
            $sumLogistics += ( $item["isShipping"] == "yes" ) ? ($item["unit_price"] * $item["quantity"]) : 0;
        }

        // Calculated moneys
        $total_shipping = $sumLogistics;
        $total_product = $sumSalesSME;

        $fees = $partnerECommerce["transaction_fees"];

        //dd([$sumSalesSME, $sumLogistics, $total_fees, ($sumSalesSME + $sumLogistics)]);

        $total_fees = round ( ($fees["total"]/100) * $sumSalesSME, 2);

        $amount_sales_sme = $sumSalesSME - $total_fees;
        $amount_logistics = $sumLogistics;

        $amount_partner = round ( ($fees["partner"]/100) * $total_fees, 2);
        $amount_dorcas = $total_fees - $amount_partner;

        $provider_payment_link = "";

        $paymentDataFlutterwave = [
            "tx_ref" => $data["id"],
            "amount" => $cart->total['raw'],
            "currency" => $cart->currency,
            "payment_options" => "card, ussd",
            "redirect_url" => url(route('webstore') . "/orders/" . $data["id"] . '/verify-payment') . '?' . http_build_query(['channel' => 'flutterwave', 'customer' => $customer->id]),
            //https://admin.store.enterprise.demo.dorcas.io/orders?channel=flutterwave&customer=d685ade4-6368-11ee-a756-06d4bd3590d6&status=cancelled&tx_ref=b2f3a52e-6369-11ee-8463-06d4bd3590d6
            // "meta" => [
            //     "consumer_id" => "",
            //     "consumer_mac" => ""
            // ],
            "customer" => [
                "email" => $customer->email,
                "phone_number" => $customer->phone,
                "name" => $customer->firstname . " " . $customer->lastname
            ],
            "customizations" => [
                "title" => $storeOwner->name,
                "description" => $data["title"],
                "logo" => $storeOwner->logo
            ],
            "subaccounts" => [
                [
                    "id" => $partnerECommerce["subaccounts"]["sales_sme"],
                    "transaction_charge_type" => "flat_subaccount",
                    "transaction_charge" => $amount_sales_sme,
                ],
                [
                    "id" => $partnerECommerce["subaccounts"]["logistics"],
                    "transaction_charge_type" => "flat_subaccount",
                    "transaction_charge" => $amount_logistics,
                ],
                [
                    "id" => $partnerECommerce["subaccounts"]["partner"],
                    "transaction_charge_type" => "flat_subaccount",
                    "transaction_charge" => $amount_partner,
                ],
                [
                    "id" => $partnerECommerce["subaccounts"]["dorcas"],
                    "transaction_charge_type" => "flat_subaccount",
                    "transaction_charge" => $amount_dorcas,
                ],
            ]

        ];


        $provider = env('SETTINGS_ECOMMERCE_PAYMENT_PROVIDER', 'flutterwave');
        $country = env('SETTINGS_COUNTRY', 'NG');
        $provider_config = ucfirst($provider). strtoupper($country) . '.php';
        $provider_class = ucfirst($provider). strtoupper($country) . 'Class.php';
        $provider_config_path = base_path('vendor/dorcas/modules-ecommerce/src/Config/Providers/Payments/' . ucfirst($provider). '/' . $provider_config);
        $config = require_once($provider_config_path);
        $provider_class_path = base_path('vendor/dorcas/modules-ecommerce/src/Config/Providers/Payments/' . ucfirst($provider). '/' . $provider_class);
        require_once($provider_class_path);

        $providerPaymentData = "paymentData" . ucfirst($provider);
        $paymentData = $$providerPaymentData;
        
        $c = $config["class"];

        $providerParams = [
            "provider" => $provider,
            "path" => "/payments",
            "params" => $paymentData
        ];
        
        $providerClass = new $c($providerParams);
        
        $payment_link = $providerClass->createWalletPaymentLink();
        
        
        if ($payment_link->status == "success") {
            $provider_payment_link = $payment_link->data->link;
        }

        $data['provider_payment_link'] = $provider_payment_link;


        // Update Daily Order Cache with temp order data
        $cC = session('cartCache');
        $temporaryOrderKey = $cC["random_order_key"];
        $temporaryOrderData = Cache::get($temporaryOrderKey);

        $orderManagementKey = $this->getOrderManagementKey($data);
        $thisOrder = [
            "order" => $data,
            "payment" => $temporaryOrderData["payment"],
            "logistics" => $temporaryOrderData["logstics"],
        ];
        Cache::forever($orderManagementKey, $thisOrder);


        // CLEAN UP TASKS
        Cache::forget('crm.customers.'.$storeOwner->id);
        
        Cache::forget($temporaryOrderKey); //temporary order cache removal
        $request->session()->forget('cartCache');
        # clear the cache
        $cartManager->clear();
        # clear the cart

        return response()->json($data, 202);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProviderShippingRoutesXhr(Request $request)
    {
        // Determine active Logistics provider
        $provider = env('SETTINGS_ECOMMERCE_LOGISTICS_PROVIDER', 'kwik');
        $country = env('SETTINGS_COUNTRY', 'NG');

        $provider_config = ucfirst($provider). strtoupper($country) . '.php';
        $provider_class = ucfirst($provider). strtoupper($country) . 'Class.php';

        $provider_config_path = __DIR__.'/../../Config/Providers/Logistics/' . ucfirst($provider). '/' . $provider_config;
        $config = require_once($provider_config_path);

        $provider_class_path = __DIR__.'/../../Config/Providers/Logistics/' . ucfirst($provider). '/' . $provider_class;
        require_once($provider_class_path);

        // Get Destination Address Details

        // Parse Shopper Origin Address
        $cartCache = session('cartCache');
        $s = $cartCache["storeOwner"]; // $request->user()->company(true, true);
        $sAddress = $cartCache["address_seller"];
        $sellerAdddress = [
            "address" => $sAddress["address"],
            "name" => $s["name"],
            "latitude" => $sAddress["latitude"],
            "longitude" => $sAddress["longitude"],
            "time" => Carbon::now(), //Carbon::now()->setTimezone(env('SETTINGS_TIMEZONE', 'Africa/Lagos'))
            "phone" => $s["phone"],
            "has_return_task" => false,
            "is_package_insured" => 0
        ];

        // Determine if its bike or car or planne depennding on inter state, 
        $vehicle_type = 0;
        /*
        0 then the vehicle type will be bike,
        1 then the vehicle type will be small
        2 then the vehicle type will be medium
        3 then the vehicle type will be large
        */

        // Determine if we want a cash on delivery option
        // DO WE CREATE 2 DELIVERY OPTIONS?!
        $cod = 1;

        $providerParams = [
            "vendor_id" => env('KWIK_VENDOR_ID', 3152),
            "vehicle_type" => $vehicle_type,
            "cod" => $cod,
            "order_key" => $cartCache["random_order_key"] //$request->random_order_key
        ];

        $c = $config["class"];
        $provider = new $c($providerParams);

        $from = $sellerAdddress;

        $to = [
            "address" => $cartCache["address"]["address"],
            "name" => $cartCache["address"]["firstname"] . " " . $cartCache["address"]["lastname"],
            "latitude" => $cartCache["address"]["latitude"],
            "longitude" => $cartCache["address"]["longitude"],
            "time" => Carbon::now(),
            "phone" => $cartCache["address"]["phone"],
            "has_return_task" => false,
            "is_package_insured" => 0
        ];

        $tempOrder = Cache::get($cartCache["random_order_key"]);
        $tempOrder["logistics"]["meta"]["address_from"] = $sellerAdddress;
        $tempOrder["logistics"]["meta"]["address_to"] = $to;
        Cache::put($cartCache["random_order_key"], $tempOrder);

        $costs = $provider->getCost($from, $to, $vehicle_type);

        $totalShippingCosts = $costs["ACTUAL_ORDER_PAYABLE_AMOUNT"] + $costs["TOTAL_SERVICE_CHARGE"];

        // Parse Cost like route data
        $parsedRoutes = [
            [
                "id" => "provider-" . $provider,
                "name" => $config["name"],
                "logo" => asset('vendor/modules-ecommerce/providers/' . $config["logo"]),
                "description" => "Delivery Estimate by " . $config["name"],
                "prices" => [
                    "data" => [
                        [
                            "currency" => "NGN",
                            "unit_price" => [
                                "raw" => $totalShippingCosts,
                                "formatted" => number_format($totalShippingCosts)
                            ]
                        ]
                    ]
                ],
                "debug" => [
                    "destination_from" => $from,
                    "destination_to" => $to,
                    "costs" => $costs
                ]
            ]
        ];

        $response = [
            "data" => $parsedRoutes,
            "meta" => "",
            "from" => $from,
            "to" => $to
        ];
        
        return response()->json($response);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addToCartXhr(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|string',
            'name' => 'required|string',
            'quantity' => 'nullable|numeric|min:1',
            'photo' => 'nullable|string|url',
            'unit_price' => 'required|numeric'
        ]);
        # validate the request
        $cart = new CartManager($request);
        # create the cart manager
        $isShipping = !empty($request->isShipping) ? $request->isShipping : 'no';  
        $cart->addToCart($request->id, $request->name, $request->unit_price, $request->input('quantity', 1), $request->photo, $isShipping);
        # adds the product to the cart
        return response()->json($cart->getCart());
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCartQuantitiesXhr(Request $request)
    {
        $this->validate($request, [
            'quantities' => 'required|array',
            'quantities.*.id' => 'required|string',
            'quantities.*.quantity' => 'required|numeric',
        ]);
        # validate the request
        $cart = new CartManager($request);
        # create the cart manager
        foreach ($request->quantities as $quantity) {
            $cart = $cart->updateQuantity($quantity['id'], $quantity['quantity']);
        }
        return response()->json($cart->commit()->getCart());
    }

    private function storeViewComposer(&$viewData, Request $request, Sdk $sdk, $company) {

        $domain = $request->session()->get('domain');
        $user = $request->user();

        //add store Categories
        $categories = Cache::remember('business.product-categories.'.$company->id, 30, function () use ($sdk, $company) {
            $query = $sdk->createBlogResource()->send('GET', [$company->id, 'categories']);
            # get the response
            if (!$query->isSuccessful() || empty($query->getData())) {
                return null;
            }
            return collect($query->getData())->map(function ($category) {
                return (object) $category;
            });
        });
        $viewData["productCategories"] = $categories;
    }


    /**
     * @param Request $request
     * @param string  $id
     *
     * @return string
     * @throws AuthorizationException
     */
    public function verifyProviderPayment(Request $request, string $id)
    {
        $final_message = "";

        $storeOwner = $this->getCompanyViaDomain();
        if (empty($storeOwner)) {
            abort(404, 'Could not find a store at this URL.');
        }
        $sOwner = (array) $storeOwner;
        $sdk = $this->authorizeSdkByCompany($sdk, $sOwner);

        if (empty($id)) {
            abort(404, 'Could not find an Order ID in your request');
        }
        $response = $sdk->createOrderResource($id)->addQueryArgument('include', 'customers:limit(10000|0)')->send('get');

        if (!$response->isSuccessful()) {
            abort(404, 'Could not find the order at this URL.');
        }
        $order = $response->getData(true);
        # try to get the order
        $company = $order->company;
        # retrieve the company
        if (!$request->has('channel') || !in_array($request->has('channel'), ['flutterwave', 'paystack'])) {
            abort(400, 'No valid payment channel was provided in the payment URL.');
        }

        // if (!$request->has('customer')) {
        //     abort(400, 'No customer id was provided in the payment URL.');
        // }

        if (!empty($request->customer)) {
            $response_customer = $sdk->createCustomerResource($request->customer)->send('get');
            # try to get the customer
            if (!$response_customer->isSuccessful()) {
                abort(500, 'We could not retrieve the customer information for this payment.');
            }
            $customer_record = $response_customer->getData();
            $customer = $customer->id === $customer_record->id ? $customer_record : $customer;
            # is this necessary
        }

        // From this point, we can display the payment status
        $reference = $request->tx_ref ?? '';
        $transaction_id = $request->transaction_id ?? '';
        $status = $request->status ?? '';
        $channel = $request->channel ?? 'flutterwave';

        $txn_data = [
            ['reference' => $reference],
            ['channel' => $channel],
            ['customer_id' => $customer_record->id],
        ];

        $txn = DB::table('payment_transactions')->where($txn_data)->first();

        if ( ($request->has('cancelled') && $request->cancelled == 'true' ) || ($request->has('status') && $request->status == 'cancelled' ) ) {
            
            $final_message = 'You cancelled the payment. You may try again at a later time.';

        } else {

            $transaction = null;
            # our transaction object


            $provider = env('SETTINGS_ECOMMERCE_PAYMENT_PROVIDER', 'flutterwave');
            $country = env('SETTINGS_COUNTRY', 'NG');
            $provider_config = ucfirst($provider). strtoupper($country) . '.php';
            $provider_class = ucfirst($provider). strtoupper($country) . 'Class.php';
            $provider_config_path = base_path('vendor/dorcas/modules-ecommerce/src/Config/Providers/Payments/' . ucfirst($provider). '/' . $provider_config);
            $config = require_once($provider_config_path);
            $provider_class_path = base_path('vendor/dorcas/modules-ecommerce/src/Config/Providers/Payments/' . ucfirst($provider). '/' . $provider_class);
            require_once($provider_class_path);
            
            $c = $config["class"];

    
            try {
                switch ($request->channel) {
                    case 'flutterwave':
                        if (!$request->has('tx_ref')) {
                            abort(400, 'No payment reference was provided by the R payment gateway.');
                        }
    
                        if ( $status == 'successful' ) {
        
                            $providerParams = [
                                "provider" => $provider,
                                "path" => "/transactions/verify_by_reference",
                                "method" => "GET",
                                "params" => [
                                    "tx_ref" => $reference
                                ]
                            ];
                            
                            $provider = new $c($providerParams);
                            
                            $verify = $provider->verifyTransaction();
                            
                            if ($verify->status !== "success") {
                                $transaction = $verify->data;
                            } else {
                                $transaction = [
                                    'channel' => 'flutterwave',
                                    'reference' => $reference,
                                    'amount' => null,
                                    'currency' => null,
                                    'response_code' => null,
                                    'response_description' => null,
                                    'json_payload' => '',
                                    'is_successful' => false
                                ];
                            }
    
                        } else {
    
                            $transaction = [
                                'channel' => 'flutterwave',
                                'reference' => $reference,
                                'amount' => null,
                                'currency' => null,
                                'response_code' => null,
                                'response_description' => null,
                                'json_payload' => '',
                                'is_successful' => false
                            ];
    
                        }
                        
                        break;
                    case 'paystack':
                        // if (!$request->has('reference')) {
                        //     abort(400, 'No payment reference was provided by the P payment gateway.');
                        // }
                        // $reference = $request->reference;
                        // $transaction = payment_verify_paystack($privateKeyDecrypted, $reference, $order);
                        break;
                }
            } catch (\UnexpectedValueException $e) {
                abort(400, $e->getMessage());
            } catch (\HttpException $e) {
                abort(500, $e->getMessage());
            } catch (\Throwable $e) {
                abort(500, 'Something went wrong: '. $e->getMessage());
            }
    
            // $txn = $order->transactions()->firstOrNew([
            //     'reference' => $reference,
            //     'channel' => $transaction['channel']
            // ]);
    
            // Transaction & Transaction Data initializers moved outside 
    
            if (!$txn) {
    
                try {
    
                    $txn_data['uuid'] = Uuid::uuid1()->toString();
                    $txn_data['order_id'] = $order->id;
                    $txn_data['amount'] = $transaction['amount'];
                    $txn_data['currency'] = $transaction['currency'];
                    $txn_data['response_code'] = $transaction['response_code'];
                    $txn_data['response_description'] = $transaction['response_description'];
                    $txn_data['json_payload'] = $transaction['json_payload'] ?? '';
                    $txn_data['is_successful'] = $transaction['is_successful'] ?? false;
        
                    $insertedId = DB::table('payment_transactions')->insertGetId($txn_data);
                    $txn = DB::table('payment_transactions')->find($insertedId);
    
                } catch (\Exception $e) {
                    abort(500, 'We encountered issues while saving the transaction. Kindly email your transaction reference (' . $reference . ') to support along with the message: '. $e->getMessage());
                }
    
            }
    
    
            # we try to get the instance if necessary
            if (!empty($txn->customer_id) && $txn->customer_id !== $customer->id) {
                # a different customer owns this transaction, than the person verifying it
                throw new AuthorizationException('This transaction does not belong to your account.');
            }
    
            # try to create the transaction, if required
            if (!$txn->is_successful) {
                abort(400, 'The payment transaction failed, try and make a successful payment to continue.');
            }
    
            $customer_order_model = $sdk->createOrderResource($order->id)->addBodyParam('id', $customer->id)
            ->addBodyParam('paid_at', Carbon::now())
            ->addBodyParam('is_paid', true);
            $customer_order_response = $customer_order_model->send('put',  ['customers']);
            if (!$customer_order_response->isSuccessful()) {
                $m = $customer_order_response->errors[0]['title'] ?? 'Failed while updating the customer order information.';
                //throw new \RuntimeException($m);
                abort(500, 'We encountered issues while saving the transaction. Kindly email your transaction reference (' . $reference . ') to support along with the message: '. $m);
            }
    
    
            // $customer = $order->customers()->where('customer_id', $customer->id)->first();
            // # get the customer with the Pivot
            // if (!$customer->pivot instanceof CustomerOrder) {
            //     abort(500, 'Something went wrong, we could not retrieve your purchase. Please report this to support along with your Payment reference: '.$reference);
            // }
            // $customerOrder = $customer->pivot;
            // $customerOrder->is_paid = true;
            // $customerOrder->paid_at = Carbon::now();
    
            // if (!$customerOrder->save()) {
            //     abort(500, 'Something went wrong, we could not mark your purchase as paid. Please report this to support along with your Payment reference: '.$reference);
            // }
    
            // http request to post to core endpoint
            $notification_params = [
                "user" => $company, //uuid
                "order" => $order,
                "customer" => $customer,
                "txn" => $txn,
            ];
            $notification_url = env('DORCAS_HOST_API', 'https://core.sample-dorcas.io') . "/notification-paid-invoice";
            
            $notification_response = Http::post($notification_url, $notification_params);
            if ($notification_response->successful()) {
                $data = $notification_response->json(); // Assuming the response is JSON
            } else {
                $statusCode = $notification_response->status();
                $error = $notification_response->body();
                abort(500, 'We encountered issues while sending an invoice notification. Kindly email your transaction reference (' . $reference . ') to support along with the message: '. $statusCode . ": " . $error);
            }
            //Notification::send($company->users->first(), new InvoicePaid($order, $customer, $txn));
            # send the notification to members of the company

            $final_message = 'Successfully completed order payment. Your reference is: ' . $reference;

        }


        $data = [
            'reference' => $reference,
            'txn' => $txn,
            'message' => $final_message,
            'company_name' => $company->name,
            'company_logo' => $company->logo,
            //'webstore_url' => "https://" . $company->domainIssuances->first()->prefix . ".store.dorcas.io"
            'webstore_url' => "https://" . $company->domainIssuances->first()->prefix . "." . env("DORCAS_BASE_DOMAIN", "store.dorcas.io")
        ];
        
        //return view('payment.payment-complete-response', $data);
        return view('modules-ecommerce::webstore.payment-response', $data);
    }



}