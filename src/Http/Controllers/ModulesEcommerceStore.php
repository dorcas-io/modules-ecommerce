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
    public function index(Request $request, string $slug = null)
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
        $this->data['page']['header']['title'] = $product->name . ' | ' . $storeOwner->name . ' Store';
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
        $json = json_decode(file_get_contents($url));
        # request the data
        if (empty($json->data)) {
            # something went wrong
            abort(500, 'Something went wrong while getting the product.');
        }
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
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function cart(Request $request)
    {
        $storeOwner = $this->getCompanyViaDomain();
        # get the store owner
        $this->data['storeSettings'] = Dashboard::getStoreSettings((array) $storeOwner->extra_data);
        # our store settings container
        if (empty($storeOwner)) {
            abort(404, 'Could not find a store at this URL.');
        }
        $this->data['storeOwner'] = $storeOwner;
        $this->data['page']['title'] = $storeOwner->name . ' ' . $this->data['page']['title'];
        //$this->data['cart'] = Home::getCartContent($request);
        $this->data['cart'] = $this->getCartContent($request);
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
            'phone' => 'nullable|max:250'
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
            throw new \RuntimeException('Failed while checking your customer account... Please try again later.');
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




}