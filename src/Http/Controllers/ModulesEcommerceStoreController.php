<?php

namespace Dorcas\ModulesEcommerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Dorcas\ModulesEcommerce\Models\ModulesEcommerce;
use App\Dorcas\Hub\Utilities\UiResponse\UiResponse;
use App\Http\Controllers\HomeController;
use Hostville\Dorcas\Sdk;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Collection;
use GuzzleHttp\Psr7\Uri;


class ModulesEcommerceStoreController extends Controller {

    /**
     * Field names for the store settings to watch out for.
     *
     * @var array
     */
    protected $storeSettingsFields = [
        'store_instagram_id',
        'store_twitter_id',
        'store_facebook_page',
        'store_homepage',
        'store_terms_page',
        'store_ga_tracking_id',
        'store_custom_js',
        'store_paid_notifications_email'
    ];

    protected $storeLogisticsFields = [
        'logistics_shipping',
        'logistics_fulfilment',
    ];

    protected $storePaymentsFields = [
        'payment_option',
        'has_marketplace',
        'wallet_request',
        'wallet_action',
    ];
    
    public function __construct()
    {
        parent::__construct();
        $this->data = [
            'page' => ['title' => config('modules-ecommerce.title')],
            'header' => ['title' => config('modules-ecommerce.title')],
            'selectedMenu' => 'modules-ecommerce',
            'submenuConfig' => 'navigation-menu.modules-ecommerce.sub-menu',
            'submenuAction' => ''
        ];  
    }

    
    /**
     * @param Request $request
     * @param Sdk     $sdk
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request, Sdk $sdk)
    {
    
        $this->data['page']['title'] .= ' &rsaquo; Online Store Manager';
        $this->data['header']['title'] = 'Online Store Manager';
        $this->data['selectedSubMenu'] = 'ecommerce-store';
        $this->data['submenuAction'] = '';

        $this->data['dorcasEdition'] = (new \App\Http\Controllers\HubController())->getDorcasEdition();

        $multiTenant = config('dorcas.edition','business') === 'business' ? false : true;

        $this->setViewUiResponse($request);
        $this->data['storeSettings'] = self::getStoreSettings((array) $this->getCompany()->extra_data);
        $this->data['logisticsSettings'] = self::getLogisticsSettings((array) $this->getCompany()->extra_data);
        $this->data['logisticsFulfilmentCentre'] = env("SETTINGS_ECOMMERCE_LOGISTICS_FULFILMENT_CENTRE", false);
        $paymentSettings = self::getPaymentsSettings((array) $this->getCompany()->extra_data);
        $this->data['paymentSettings'] = $paymentSettings;
        # our store, logistics & paymennt settings container

        // Setup Payments
        $pSettings = (array) $paymentSettings;
        $payment_option = $pSettings['payment_option'] ?? '';
        $has_marketplace = $pSettings['has_marketplace'];

        $paymentSettingsAdvice = [
            "action" => "",
            "link_type" => "",
            "link" => "",
            "register" => "",
            "register_link" => ""
        ];
        $paymentOptionSelection = "Unknown";


        $availableIntegrations = config('dorcas.integrations');
        # get all the available integrations

        $installed = $this->getIntegrations($sdk);
        $installedNames = !empty($installed) && $installed->count() > 0 ? $installed->pluck('name')->all() : [];

        $paymentIntegrations = collect($availableIntegrations);

        $integrationName = "";

        switch ($payment_option) {
            
            case "use_bank_account":
                $paymentOptionSelection = "Use My Bank Account";
                $integrationName = "";
                $paymentSettingsAdvice["action"] = "Manage your Bank Account Settings Here";
                $paymentSettingsAdvice["link_type"] = "route";
                $paymentSettingsAdvice["link"] = "/mse/settings-banking";
                $paymentSettingsAdvice["register"] = "";
                $paymentSettingsAdvice["register_link"] = "";
            break;
            
            case "use_online_provider_paystack":
                $paymentOptionSelection = "Use My Paystack Account";
                $integrationName = "paystack";
                $paymentSettingsAdvice["action"] = "Manage your Paystack Settings Here";
                $paymentSettingsAdvice["link_type"] = "custom";
                $paymentSettingsAdvice["link"] = "viewPaymentSetting|paystack";
                $paymentSettingsAdvice["register"] = "Open A Paystack Account";
                $paymentSettingsAdvice["register_link"] = "https://dashboard.paystack.com/#/signup";
            break;
            
            case "use_online_provider_flutterwave":
                $integrationName = "rave";
                $paymentOptionSelection = "Use My Flutterwave Account";
                $paymentSettingsAdvice["action"] = "Manage your Fluttterwave Settings Here";
                $paymentSettingsAdvice["link_type"] = "custom";
                $paymentSettingsAdvice["link"] = "viewPaymentSetting|flutterwave";
                $paymentSettingsAdvice["register"] = "Open A Flutterwave Rave Account";
                $paymentSettingsAdvice["register_link"] = "https://app.flutterwave.com/register";
            break;

        }

        // If current Integration is not installed, Install it!
        if ( !empty($integrationName) && array_search($integrationName, $installedNames, true) === false ) {

            $targetIntegration = $paymentIntegrations->where('name', $integrationName);

            $targetIntegration = $targetIntegration->first();
            
            $installType = $targetIntegration["type"];
            $installName = $targetIntegration["name"];
            $installConfigurations = $targetIntegration["configurations"];
    
            $integrationId = $request->has('integration_id') ? $request->input('integration_id') : null;
    
            $resource = $sdk->createIntegrationResource($integrationId)->addBodyParam('type', $installType)
                                                        ->addBodyParam('name', $installName)
                                                        ->addBodyParam('configuration', $installConfigurations);
            $query = $resource->send(empty($integrationId) ? 'post' : 'put');
            # send request
            if (!$query->isSuccessful()) {
                // $message = $query->getErrors()[0]['title'] ?? 'Failed while trying to '. (empty($integrationId) ? 'install' : 'update') .' the Integration.';
                // throw new \RuntimeException($message);
            }
            $company = $request->user()->company(true, true);
            Cache::forget('integrations.'.$company->id);
        }

        $finalIntegrations = collect([]);
        $finalInstalled = $this->getIntegrations($sdk);
        $finalInstalledNames = !empty($finalInstalled) && $finalInstalled->count() > 0 ? $finalInstalled->pluck('name')->all() : [];
        foreach ($availableIntegrations as $index => $integration) {
            if (($installedIndex = array_search($integration['name'], $finalInstalledNames, true)) === false) {
                continue;
            }
            $installedIntegration = $finalInstalled->get($installedIndex);
            $integration['id'] = $installedIntegration->id;

            $configurations = collect($installedIntegration->configuration)->map(function ($config) {
                if ( isset($config['value']) && !empty($config['value']) ) {
                    $config['value'] = Crypt::decryptString($config['value']);
                }
                return $config;
            })->toArray();
            # encrypt values

            $integration['configurations'] = $configurations;
            # update the values

            $finalIntegrations->push($integration);
            # add the integration
        }
        $this->data['integration'] = !empty($integrationName) ? $finalIntegrations->where('name', $integrationName)->first() : [];

        $this->data['paymentOptionSelection'] = $paymentOptionSelection;
        $this->data['paymentSettingsAdvice'] = $paymentSettingsAdvice;

        $query = $sdk->createProductResource()->addQueryArgument('limit', 1)->send('get');
        $this->data['productCount'] = $query->isSuccessful() ? $query->meta['pagination']['total'] ?? 0 : 0;

        $this->data['subdomain'] = get_dorcas_subdomain($sdk);
       
        # set the subdomain
        if (!empty($this->data['subdomain'])) {
            //$storeUrl = 'store.' . $this->data['subdomain'];

            $subDomainSplit = explode('.', $this->data['subdomain']);
            $subDomainSuffix = $multiTenant ? $subDomainSplit[1] . "." . $subDomainSplit[2] : $subDomainSplit[1] . "." . $subDomainSplit[2];
            $storeUrl = $multiTenant ? $subDomainSplit[0] . '.store.' . $subDomainSuffix : $subDomainSplit[0] . '.store.' . $subDomainSuffix;
            # Store URL pattern changed for easy of wildcard SSL

            $domain = get_dorcas_domain();
            $subdomains = $this->getSubDomains($sdk);
            # returns ALL domains


            // RE-DO STORE URL
            $subdomain = get_dorcas_subdomain();

            $base_domain = new Uri(config('app.url'));
            $base_domain_host = $base_domain->getHost();
            
            if (env("DORCAS_EDITION","business") === "business") {
                $multiTenant = false;
                $dorcas_store_url = "https://store.".$subdomain;
            } elseif ( env("DORCAS_EDITION","business") === "community" || env("DORCAS_EDITION","business") === "enterprise" ) {
                $multiTenant = true;
                $parts = explode('.', str_replace("." . $base_domain_host, "", $subdomain) );
                $dorcas_store_url = "https://" .  $parts[0] . ".store." . $base_domain_host;
            }
    
            $storeURL = $dorcas_store_url;


            
            // $scheme = app()->environment() === 'production' ? 'https://' : 'http://';
            // $storeUrl = $scheme . $storeUrl;
            $storeUrl = $storeURL;
            
            

            $this->data['storeUrl'] = $storeUrl;
            $this->data['header']['title'] .= " ($storeUrl)";

            if (!empty($subdomain)) {
                //$this->data['header']['title'] .= ' (<a href="'.$storeUrl.'" target="_blank">'.$storeUrl.'</a>)';
            }

            $this->data['submenuAction'] = '
                <div class="dropdown"><button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">Store Actions</button>
                    <div class="dropdown-menu">
                        <a href="'.$storeUrl.'" class="dropdown-item" target="_blank">View Store</a>
                    </div>
                </div>
            ';
        }

        return view('modules-ecommerce::store', $this->data);
    }
    
    /**
     * @param array $configuration
     *
     * @return array
     */
    public static function getStoreSettings(array $configuration = []): array
    {
        $requiredStoreSettings = [
            'store_instagram_id',
            'store_twitter_id',
            'store_facebook_page',
            'store_homepage',
            'store_terms_page',
            'store_ga_tracking_id',
            'store_custom_js',
            'store_paid_notifications_email'
        ];
        $settings = $configuration['store_settings'] ?? [];
        # our store settings container
        foreach ($requiredStoreSettings as $key) {
            if (isset($settings[$key])) {
                continue;
            }
            $settings[$key] = '';
        }
        return $settings;
    }
    
    /**
     * @param array $configuration
     *
     * @return array
     */
    public static function getLogisticsSettings(array $configuration = []): array
    {
        $requiredLogisticsSettings = [
            'logistics_shipping',
            'logistics_fulfilment',
        ];
        $settings = $configuration['logistics_settings'] ?? [];
        # our store settings container
        foreach ($requiredLogisticsSettings as $key) {
            if (isset($settings[$key])) {
                continue;
            }
            $settings[$key] = '';
        }
        return $settings;
    }
    
    /**
     * @param array $configuration
     *
     * @return array
     */
    public static function getPaymentsSettings(array $configuration = []): array
    {
        $requiredPaymentSettings = [
            'payment_option',
            'has_marketplace',
            'wallet_request',
            'wallet_action',
        ];
        $settings = $configuration['payments_settings'] ?? [];
        # our store settings container
        foreach ($requiredPaymentSettings as $key) {
            if (isset($settings[$key])) {
                continue;
            }
            $settings[$key] = '';
        }
        return $settings;
    }

    
    /**
     * @param Request $request
     * @param Sdk     $sdk
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function storeSettings(Request $request, Sdk $sdk)
    {
        try {
            $company = $this->getCompany();
            $configuration = (array) $company->extra_data;
            $storeSettings = $configuration['store_settings'] ?? [];
            # our store settings container
            $submitted = $request->only($this->storeSettingsFields);
            # get the submitted data
            foreach ($submitted as $key => $value) {
                if (empty($value)) {
                    unset($storeSettings[$key]);
                }
                $storeSettings[$key] = $value;
            }
            $configuration['store_settings'] = $storeSettings;
            # add the new store settings configuration
            $query = $sdk->createCompanyService()->addBodyParam('extra_data', $configuration)
                                                ->send('PUT');
            # send the request
            if (!$query->isSuccessful()) {
                # it failed
                $message = $query->errors[0]['title'] ?? '';
                throw new \RuntimeException('Failed while updating the store settings. '.$message);
            }
            $this->clearCache($sdk);
            $response = (tabler_ui_html_response(['Successfully updated your store information.']))->setType(UiResponse::TYPE_SUCCESS);
        } catch (\Exception $e) {
            $response = (tabler_ui_html_response([$e->getMessage()]))->setType(UiResponse::TYPE_ERROR);
        }

        /* START INTERCEPT GETTING STARTED REDIRECTS */
        $user = $request->user();
        //$company = $user->company(true, true);
        $storeSettings = self::getStoreSettings((array) $company->extra_data);
        $logisticsSettings = self::getLogisticsSettings((array) $company->extra_data);
        $paymentSettings = self::getPaymentsSettings((array) $company->extra_data);
        
        $storeSettingsFilled = collect($storeSettings);
        $logisticsSettingsFilled = collect($logisticsSettings);
        $paymentSettingsFilled = collect($paymentSettings);

        $allFilled = collect([$storeSettingsFilled, $logisticsSettingsFilled, $paymentSettingsFilled]);

        $hasAllNonEmptyCollections = $allFilled->every(function ($collection) {
            return $collection->filter(function ($value) {
                return !empty($value);
            })->isNotEmpty();
        });

        if ($hasAllNonEmptyCollections) {

            $gettingStartedRedirect = \Dorcas\ModulesDashboard\Http\Controllers\ModulesDashboardController::processGettingStartedRedirection($request, 'setup_store', $response);
            if ($gettingStartedRedirect) {
                return redirect(route('dashboard'))->with('UiResponse', $response);
            }

        }
        /* END INTERCEPT GETTING STARTED REDIRECTS */

        return redirect(url()->current())->with('UiResponse', $response);
    }


    /**
     * @param Request $request
     * @param Sdk     $sdk
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function storeLogistics(Request $request, Sdk $sdk)
    {
        try {
            $company = $this->getCompany();
            $configuration = (array) $company->extra_data;
            $logisticsSettings = $configuration['logistics_settings'] ?? [];
            # our store settings container
            $submitted = $request->only($this->storeLogisticsFields);
            # get the submitted data
            foreach ($submitted as $key => $value) {
                if (empty($value)) {
                    unset($logisticsSettings[$key]);
                }
                $logisticsSettings[$key] = $value;
            }
            $configuration['logistics_settings'] = $logisticsSettings;
            # add the new store settings configuration
            $query = $sdk->createCompanyService()->addBodyParam('extra_data', $configuration)
                                                ->send('PUT');
            # send the request
            if (!$query->isSuccessful()) {
                # it failed
                $message = $query->errors[0]['title'] ?? '';
                throw new \RuntimeException('Failed while updating the logistics settings. '.$message);
            }
            $this->clearCache($sdk);
            $response = (tabler_ui_html_response(['Successfully updated your Logistics Settings']))->setType(UiResponse::TYPE_SUCCESS);
        } catch (\Exception $e) {
            $response = (tabler_ui_html_response([$e->getMessage()]))->setType(UiResponse::TYPE_ERROR);
        }

        /* START INTERCEPT GETTING STARTED REDIRECTS */
        $user = $request->user();
        //$company = $user->company(true, true);
        $storeSettings = self::getStoreSettings((array) $company->extra_data);
        $logisticsSettings = self::getLogisticsSettings((array) $company->extra_data);
        $paymentSettings = self::getPaymentsSettings((array) $company->extra_data);
        
        $storeSettingsFilled = collect($storeSettings);
        $logisticsSettingsFilled = collect($logisticsSettings);
        $paymentSettingsFilled = collect($paymentSettings);

        $allFilled = collect([$storeSettingsFilled, $logisticsSettingsFilled, $paymentSettingsFilled]);

        $hasAllNonEmptyCollections = $allFilled->every(function ($collection) {
            return $collection->filter(function ($value) {
                return !empty($value);
            })->isNotEmpty();
        });

        if ($hasAllNonEmptyCollections) {

            $gettingStartedRedirect = \Dorcas\ModulesDashboard\Http\Controllers\ModulesDashboardController::processGettingStartedRedirection($request, 'setup_store', $response);
            if ($gettingStartedRedirect) {
                return redirect(route('dashboard'))->with('UiResponse', $response);
            }

        }
        /* END INTERCEPT GETTING STARTED REDIRECTS */
        
        return redirect(route('ecommerce-store'))->with('UiResponse', $response);
    }


    /**
     * @param Request $request
     * @param Sdk     $sdk
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function storePayments(Request $request, Sdk $sdk)
    {
        try {
            $company = $this->getCompany();
            $configuration = (array) $company->extra_data;
            $paymentsSettings = $configuration['payments_settings'] ?? [];
            # our payment settings container

            $wallet_request = $request->has('wallet_request') ? $request->input('wallet_action') : null;
            # do something with this later

            if ($wallet_request) {

                $params = [];

                $wallet_response = $this->activateWallet($request, $params);

                if (!$wallet_response->getData()->status) {
                    throw new \RuntimeException('Failed while activating Payment Wallet (' . $wallet_response->getData()->message  . ')');
                }

                //process wallet reponse
                // {
                //     "status": "success",
                //     "message": "Payout subaccount created",
                //     "data": {
                //         "id": 195950,
                //         "account_reference": "PSA0E649679F84901775",
                //         "account_name": "Example User",
                //         "barter_id": "234000002650333",
                //         "email": "user@gmail.com",
                //         "mobilenumber": "09010000000",
                //         "country": "US",
                //         "nuban": "8543374352",
                //         "bank_name": "Wema Bank PLC",
                //         "bank_code": "035",
                //         "status": "ACTIVE",
                //         "created_at": "2023-08-30T19:48:25.000Z"
                //     }
                // }
                $paymentsSettings["wallet"] = [
                    "status" => "succcess",
                    "data" => (array) $wallet_response->data
                ];

            }

            $submitted = $request->only($this->storePaymentsFields);
            # get the submitted data
            foreach ($submitted as $key => $value) {
                if (empty($value)) {
                    unset($paymentsSettings[$key]);
                }
                $paymentsSettings[$key] = $value;
            }
            $paymentsSettings["has_marketplace"] = env("DORCAS_EDITION","business") === "community" || env("DORCAS_EDITION","business") === "enterprise" ? true : false;
            $configuration['payments_settings'] = $paymentsSettings;
            # add the new store settings configuration
            $query = $sdk->createCompanyService()->addBodyParam('extra_data', $configuration)
                                                ->send('PUT');
            # send the request
            if (!$query->isSuccessful()) {
                # it failed
                $message = $query->errors[0]['title'] ?? '';
                throw new \RuntimeException('Failed while updating the payments settings. '.$message);
            }
            $this->clearCache($sdk);
            if (empty($wallet_request)) {
                $success_message = 'Successfully updated your Payments Settings';
            } else {
                if ($wallet_request == "activate") {
                    $success_message = 'Successfully activatetd Payment Wallet';
                }
            }
            $response = (tabler_ui_html_response([$success_message]))->setType(UiResponse::TYPE_SUCCESS);
        } catch (\Exception $e) {
            $response = (tabler_ui_html_response([$e->getMessage()]))->setType(UiResponse::TYPE_ERROR);
        }

        /* START INTERCEPT GETTING STARTED REDIRECTS */
        $user = $request->user();
        //$company = $user->company(true, true);
        $storeSettings = self::getStoreSettings((array) $company->extra_data);
        $logisticsSettings = self::getLogisticsSettings((array) $company->extra_data);
        $paymentSettings = self::getPaymentsSettings((array) $company->extra_data);
        
        $storeSettingsFilled = collect($storeSettings);
        $logisticsSettingsFilled = collect($logisticsSettings);
        $paymentSettingsFilled = collect($paymentSettings);

        $allFilled = collect([$storeSettingsFilled, $logisticsSettingsFilled, $paymentSettingsFilled]);

        $hasAllNonEmptyCollections = $allFilled->every(function ($collection) {
            return $collection->filter(function ($value) {
                return !empty($value);
            })->isNotEmpty();
        });

        if ($hasAllNonEmptyCollections) {

            $gettingStartedRedirect = \Dorcas\ModulesDashboard\Http\Controllers\ModulesDashboardController::processGettingStartedRedirection($request, 'setup_store', $response);
            if ($gettingStartedRedirect) {
                return redirect(route('dashboard'))->with('UiResponse', $response);
            }

        }
        /* END INTERCEPT GETTING STARTED REDIRECTS */

        return redirect(route('ecommerce-store'))->with('UiResponse', $response);
    }

    
    /**
     * @param Sdk     $sdk
     *
     * @return null
     */
    protected function clearCache(Sdk $sdk)
    {
        $subdomains = $this->getSubDomains($sdk);
        if (empty($subdomains) || $subdomains->count() === 0) {
            # none found
            return null;
        }
        foreach ($subdomains as $sub) {
            Cache::forget('domain_' . $sub->prefix);
        }
    }


    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function activateWallet(Request $request)
    {
        // Determine active Logistics provider
        $provider = env('SETTINGS_ECOMMERCE_PAYMENT_PROVIDER', 'flutterwave');
        $country = env('SETTINGS_COUNTRY', 'NG');

        $provider_config = strtolower($provider . '_' . $country) . '.php';
        $provider_class = ucfirst($provider). strtoupper($country) . 'Class.php';

        $provider_config_path = __DIR__.'/../../config/providers/payments/' . $provider. '/' . $provider_config;
        $config = require_once($provider_config_path);

        $provider_class_path = __DIR__.'/../../config/providers/payments/' . $provider. '/' . $provider_class;
        require_once($provider_class_path);


        // Parse Shopper Origin Address
        $user = $request->user();
        $company = $user->company();

        $providerParams = [
            "account_name" => $user->firstname . " " . $user->lastname,
            "email" => $user->email,
            "mobilenumber" => $this->format_intl_code($user->phone, "234"),
            "country" => $country
        ];


        $c = $config["class"];

        $provider = new $c($providerParams);

        $activation = $provider->activate();

        $response_status = $activation->status === "success" ? true : false;

        $response_message = $activation->status === "success" ? "Wallet Activation Succcessful" : "Wallet Activation Failed &raquo; " . $activation->message;

        $response_data = $activation->status === "success" ? $activation->data : [];

        $response = [
            "status" => $response_status,
            "message" => $response_message,
            "data" => $response_data,
        ];
        
        return response()->json($response);
    }

    function format_intl_code($phone, $code) {

        $phone = preg_replace("/\s+/", "", $phone);
        $phone = str_replace("+" . $code,"", $phone);
        $phone = str_replace("+","", $phone);
        $LearnerPhone = substr($phone, 0, 1) == "0" ? $code . substr($phone, 1) : $phone;
    
        return $LearnerPhone;
    
    }



    public function wallet_index(Request $request, Sdk $sdk)
    {
        $this->data['page']['title'] .= ' &rsaquo; Wallet';
        $this->data['header']['title'] = 'eCommerce Wallet';
        $this->data['selectedSubMenu'] = 'ecommerce-wallet';
        $this->data['submenuAction'] = '';

        $this->setViewUiResponse($request);
        $this->data['company'] = $company = $request->user()->company(true, true);
        # get the company information

        $company_data = (array) $company->extra_data;
        $this->data['company_data'] = $company_data;

        $paymentsSettings = $company_data['payments_settings'] ?? [];
        # our payment settings container
        
        $this->data['wallet_enabled'] = isset($paymentsSettings['wallet']) && !empty($paymentsSettings['wallet']) ? true : false;

        $this->data['wallet_data'] = isset($paymentsSettings['wallet']) && !empty($paymentsSettings['wallet']) ? $paymentsSettings['wallet']['data'] : [];

        return view('modules-ecommerce::wallet', $this->data);
    }

    public function wallet_post(Request $request, Sdk $sdk)
    {
        $this->validate($request, [
            'name' => 'required_if:action,update_business|string|max:100',
            'registration' => 'nullable|string|max:30',
            'phone' => 'required_if:action,update_business|string|max:30',
            'email' => 'required_if:action,update_business|email|max:80',
            'website' => 'nullable|string|max:80',
            'address1' => 'required_if:action,update_location|string|max:100',
            'address2' => 'nullable|string|max:100',
            'city' => 'required_if:action,update_location|string|max:100',
            'state' => 'required_if:action,update_location|string|max:50',
        ]);
        # validate the request
        try {
            $company = $request->user()->company(true, true);
            # get the company information

            if ($request->action === 'update_business') {
                # update the business information
                $query = $sdk->createCompanyService()
                                ->addBodyParam('name', $request->name, true)
                                ->addBodyParam('registration', $request->input('registration', ''))
                                ->addBodyParam('phone', $request->input('phone', ''))
                                ->addBodyParam('email', $request->input('email', ''))
                                ->addBodyParam('website', $request->input('website', ''))
                                ->send('PUT');
                # send the request
                if (!$query->isSuccessful()) {
                    throw new \RuntimeException('Failed while updating your business information. Please try again.');
                }
                $message = ['Successfully updated business information for '.$request->name];
            } else {
                # update address information

                $locations = $this->getLocations($sdk);
                $location = !empty($locations) ? $locations->first() : null;
                $query = $sdk->createLocationResource();
                # get the query
                $query = $query->addBodyParam('address1', $request->address1)
                                ->addBodyParam('address2', $request->address2)
                                ->addBodyParam('city', $request->city)
                                ->addBodyParam('state', $request->state);
                # add the payload
                if (!empty($location)) {
                    $response = $query->send('PUT', [$location->id]);
                } else {
                    $response = $query->send('POST');
                }
                if (!$response->isSuccessful()) {
                    throw new \RuntimeException('Sorry but we encountered issues while updating your address information.');
                }
                Cache::forget('business.locations.'.$company->id);
                # forget the cache data

                $updated_locations = $this->getLocations($sdk); // recache immediately

                // Update Geo Location in company meta data
                $company = $request->user()->company(true, true);
                
                $configuration = !empty($company->extra_data) ? $company->extra_data : [];

                if (empty($configuration['location'])) {
                    $configuration['location'] = [];
                }
                $configuration['location']['address'] = $request->input('address1') . " " . $request->input('address2');
                $configuration['location']['latitude'] = $request->input('latitude');
                $configuration['location']['longitude'] = $request->input('longitude');
                $configuration['location']['address'] = $request->input('address1');

                $queryL = $sdk->createCompanyService()->addBodyParam('extra_data', $configuration)
                                                    ->send('post');
                # send the request
                if (!$queryL->isSuccessful()) {
                    throw new \RuntimeException('Failed while updating your geo-location data. Please try again.');
                }


                $message = ['Successfully updated your company address information.'];
            }
            $response = (tabler_ui_html_response($message))->setType(UiResponse::TYPE_SUCCESS);
        } catch (\Exception $e) {
            $response = (tabler_ui_html_response([$e->getMessage()]))->setType(UiResponse::TYPE_ERROR);
        }

        $gettingStartedRedirect = \Dorcas\ModulesDashboard\Http\Controllers\ModulesDashboardController::processGettingStartedRedirection($request, 'setup_pickup_address', $response);
        if ($gettingStartedRedirect) {
            return redirect(route('dashboard'))->with('UiResponse', $response);
        }

        return redirect(url()->current())->with('UiResponse', $response);
    }


 }