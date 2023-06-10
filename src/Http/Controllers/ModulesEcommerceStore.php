<?php

namespace Dorcas\ModulesEcommerce\Http\Controllers;

use Hostville\Dorcas\Sdk;
use Illuminate\Http\Request;
use App\Dorcas\Support\CartManager;
use App\Http\Controllers\Controller;
//use App\Http\Controllers\ECommerce\OnlineStore;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Dorcas\ModulesEcommerce\Http\Controllers\ModulesEcommerceStoreController as Dashboard;

class ModulesEcommerceStore extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->data['page']['title'] = 'Store';
        $this->data['page']['header'] = ['title' => 'Store'];
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
        //dd($storeOwner);
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
            /*$req = $sdk->createStoreService();
            $req = $req->addQueryArgument('limit', $limit)
                            ->addQueryArgument('page', get_page_number($offset, $limit));
            if (!empty($type)) {
                $req = $req->addQueryArgument('product_type', $type);
            }
            if (!empty($parent)) {
                $req = $req->addQueryArgument('product_parent', $parent);
            }
            $variants = $req->send('get');
            # make the request
            if (!$variants->isSuccessful()) {
                # it failed
                $ms = $variants->errors[0]['title'] ?? '';
                throw new \RuntimeException('Failed while adding the product. '.$ms);
            }*/
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
            # something went wrong
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
        # get the store owner
        $this->data['storeSettings'] = Dashboard::getStoreSettings((array) $storeOwner->extra_data);
        # our store settings container
        if (empty($storeOwner)) {
            abort(404, 'Could not find a store at this URL.');
        }
        $this->data['storeOwner'] = $storeOwner;
        //$this->data['page']['title'] = $storeOwner->name . ' ' . $this->data['page']['title'];
        $this->data['page']['title'] = $storeOwner->name . ' | Shopping Cart';

        $this->data['countries'] = $countries = $this->getCountries($sdk);
        # get the countries listing
        $nigeria = !empty($countries) && $countries->count() > 0 ? $countries->where('iso_code', 'NG')->first() : null;
        # get the nigeria country model
        if (!empty($nigeria)) {
            $this->data['states'] = $this->getDorcasStates($sdk, $nigeria->id);
            # get the states
        }

        $company_data = (array) $storeOwner->extra_data;
        //$this->data['company_data'] = $company_data;
        $logistics_settings = $company_data['logistics_settings'] ?? ["logistics_shipping" => env("SETTINGS_ECOMMERCE_LOGISTICS_SHIPPING", "shipping_myself"), "logistics_fulfilment" => env("SETTINGS_ECOMMERCE_LOGISTICS_FULFILMENT", "fulfilment_pickup")];

        $this->data['logistics'] = [
            "seller_state" => "",
            "seller_country" => env('SETTINGS_COUNTRY', 'NG'),
            "settings" => $logistics_settings,
            "sdk" => $sdk
        ];

        // Fetch/Initiate Cache
        $cartCacheKey = "cartCache." . $storeOwner->id;
        if (Cache::has($cartCacheKey)) {
            $cartCache = Cache::get($cartCacheKey);
        } else {
            $cartCache = [
                "address" => [
                    "firstname" => "",
                    "lastname" => "",
                    "email" => "",
                    "phone" => "",
                    "address" => "34 Jaiye Oyedotun",
                    "state" => "Lagos",
                    "country" => "Nigeria"
                ]
            ];
        }

        // Process Address Content
        $address_firstname = !empty($request->address_firstname) ? $request->address_firstname : $cartCache["address"]["firstname"];
        $address_lastname = !empty($request->address_lastname) ? $request->address_lastname : $cartCache["address"]["lastname"];
        $address_email = !empty($request->address_email) ? $request->address_email : $cartCache["address"]["email"];
        $address_phone = !empty($request->address_phone) ? $request->address_phone : $cartCache["address"]["phone"];
        $address_address = !empty($request->address_address) ? $request->address_address : $cartCache["address"]["address"];
        $address_state = !empty($request->address_state) ? $request->address_state : $cartCache["address"]["state"];
        $address_country = !empty($request->address_country) ? $request->address_country : $cartCache["address"]["country"];

        // Save Address Status
        $cartCache["address"] = [
            "firstname" => $address_firstname,
            "lastname" => $address_lastname,
            "email" => $address_email,
            "phone" => $address_phone,
            "address" => $address_address,
            "state" => $address_state,
            "country" => $address_country
        ];
        Cache::forever($cartCacheKey, $cartCache);

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
            "countries" => $countries
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


        $stage_title = $stage_present ? $cart_stages[$stage_title]["title"] : 'Shopping Cart';

        $this->data['page']['header']['title'] = $storeOwner->name . ' Store' . ' | ' . $stage_title;
        //$this->data['cart'] = Home::getCartContent($request);
        $this->data['cart'] = $this->getCartContent($request);

        return view('modules-ecommerce::webstore.cart', $this->data);
    }

    // public function cart2(Request $request)
    // {
    //     $storeOwner = $this->getCompanyViaDomain();
    //     # get the store owner
    //     $this->data['storeSettings'] = Dashboard::getStoreSettings((array) $storeOwner->extra_data);
    //     # our store settings container
    //     if (empty($storeOwner)) {
    //         abort(404, 'Could not find a store at this URL.');
    //     }
    //     $this->data['storeOwner'] = $storeOwner;
    //     //$this->data['page']['title'] = $storeOwner->name . ' ' . $this->data['page']['title'];
    //     $this->data['page']['title'] = $storeOwner->name . ' | Shopping Cart';

    //     // Process Cart Stages
    //     $cart_stages = [
    //         "address" => [
    //             "title" => "Enter Delivery Address"
    //         ],
    //         "shipping" => [
    //             "title" => "Choose Shipping Type"
    //         ],
    //         "review" => [
    //             "title" => "Review & Finalize Order"
    //         ]
    //     ];

    //     $this->data['stages'] = [
    //         "stage" => "address",
    //         "data" => $cart_stages
    //     ];

    //     $stage_present = false;
    //     if ( !empty($request->stage) && in_array($request->stage, array_keys($cart_stages)) ) {
    //         $this->data['stages']['stage'] = $request->stage;
    //         $stage_present = true;
    //     }


    //     $stage_title = $stage_present ? $cart_stages[$stage_title]["title"] : 'Shopping Cart';

    //     $this->data['page']['header']['title'] = $storeOwner->name . ' Store' . ' | ' . $stage_title;
    //     //$this->data['cart'] = Home::getCartContent($request);
    //     $this->data['cart'] = $this->getCartContent($request);

    //     return view('modules-ecommerce::webstore.cart2', $this->data);
    // }

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
            'description' => 'Order placed on web store at '.Carbon::now()->format('D jS M, Y h:i a'),
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
        Cache::forget('crm.customers.'.$storeOwner->id);
        Cache::forget("cartAddress." . $storeOwner->id);
        # clear the cache
        $cartManager->clear();
        # clear the cart
        $data = $checkout->getData();
        if (!empty($checkout->meta) && !empty($checkout->meta['payment_url'])) {
            $data['payment_url'] = $checkout->meta['payment_url'];
        }
        return response()->json($data, 202);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProviderShippingRoutesXhr(Request $request)
    {
        // Get Destination Address Details

        // Parse Shopper Origin Address

        // Determine if its bike or car or planne depennding on inter state, 

        // Connect To API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, env('CREDENTIAL_ECOMMERCE_PROVIDER_URL', 'provider.com'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
            'domain_name' => env('CREDENTIAL_ECOMMERCE_PROVIDER_DOMAIN', 'provider.com'),
            'email' => env('CREDENTIAL_ECOMMERCE_PROVIDER_USERNAME', 'user@provider.com'),
            'password' => env('CREDENTIAL_ECOMMERCE_PROVIDER_PASSWORD', 'password'),
            'api_login' => 1,
        )));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          "Content-Type: application/json"
        ));
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        //var_dump($response);


        // Estimate Cost


        // Parse Cost like route data

        //Return
        
        return response()->json();
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


}