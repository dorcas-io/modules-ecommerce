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
use App\Dorcas\Hub\Enum\Banks;


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


        $transfer_bank_available = false;

        $accounts = $this->getBankAccounts($sdk);

        if (!empty($accounts) && $accounts->count() > 0) {

            $bank = $accounts->first();

            $banks = collect(Banks::BANK_CODES)->sort()->map(function ($name, $code) {
                return ['name' => $name, 'code' => $code];
            })->values();

            $bank_name = $banks->where('code', $bank["json_data"]["bank_code"])->pluck('name')->first();
            $account_number = $bank["account_number"];
            $account_name = $bank["account_name"];

            $this->data['bank_details'] = $bank_details = [
                "bank_name" => $bank_name,
                "account_name" => $account_name,
                "account_number" => $account_number
            ];

            $transfer_bank_available = true;

        }

        $tthis->data['transfer_bank_available'] = $transfer_bank_available;


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

                $paymentsSettings["wallet"] = [
                    "status" => "succcess",
                    "data" => (array) $wallet_response->getData()->data
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
                    $success_message = 'Successfully activated Payment Wallet';
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

        $wallet_balances = [];

        if (isset($paymentsSettings['wallet']['data']) && isset($paymentsSettings['wallet']['data']['account_reference']) && !empty($paymentsSettings['wallet']['data']['account_reference']) ) {
            $wb = $this->getWalletBalances($request, $paymentsSettings['wallet']['data']['account_reference']);
            if ($wb->getData()->status) {
                $wallet_balances = (array) $wb->getData()->data;
            }
        }

        $this->data['wallet_balances'] = $wallet_balances;

        $transfer_available = false;
        $transfer_bank_available = false;
        $transfer_amount_available = 0;
        $transfer_status = "";
        $transfer_currency = "NGN";
        $transfer_amount_maximum = 0;
        $transfer_fee = 0;

        $bank_details = [
            "bank_name" => "",
            "account_name" => "",
            "account_number" => ""
        ];

        $accounts = $this->getBankAccounts($sdk);

        if (!empty($accounts) && $accounts->count() > 0) {

            $bank = $accounts->first();

            $banks = collect(Banks::BANK_CODES)->sort()->map(function ($name, $code) {
                return ['name' => $name, 'code' => $code];
            })->values();

            $bank_name = $banks->where('code', $bank["json_data"]["bank_code"])->pluck('name')->first();
            $account_number = $bank["account_number"];
            $account_name = $bank["account_name"];

            $this->data['bank_details'] = $bank_details = [
                "bank_name" => $bank_name,
                "account_name" => $account_name,
                "account_number" => $account_number
            ];

            $transfer_bank_available = true;

        } else {

            $transfer_status = "Transfer Unavailable &raquo; Setup Banking Information | ";

        }


        // determine transfer amount available
        $total_available = ($wallet_balances[0])->available_balance;

        if ($total_available > 0) {
            // estimate amount to transfer that
            $te = $this->getTransferEstimate()->getData()->data;
            //$transfer_estimate = $te->getData()->data;
            // "currency": "NGN",
            // "fee_type": "value",
            // "fee": 26.875
            $transfer_fee = $te["fee"];
            $transfer_net = $total_available - $transfer_fee;
            $transfer_amount_available = $transfer_net > 0 ? $transfer_net : 0;
            $transfer_currency = $te["currency"];
        } else {
            $transfer_status = "Transfer Unavailable &raquo; Insufficient Balance | ";
        }


        if ($transfer_bank_available && $transfer_amount_available > 0) {
            $transfer_available = true;
        }
        
        $this->data['transfer_bank_available'] = $transfer_bank_available;
        $this->data['bank_details'] = $bank_details;
        $this->data['transfer_available'] = $transfer_available;
        $this->data['transfer_amount_available'] = $transfer_amount_available;
        $this->data['transfer_amount_maximum'] = $transfer_amount_maximum;
        $this->data['transfer_currency'] = $transfer_currency;
        $this->data['transfer_status'] = $transfer_status;
        $this->data['transfer_fee'] = $transfer_fee;

        return view('modules-ecommerce::wallet', $this->data);
    }

    public function wallet_transfer(Request $request, Sdk $sdk)
    {
        $this->validate($request, [
            'destination' => 'required|in:bank,wallet',
            'amount' => 'required|numeric',
            'currency' => 'required',
            //'phone' => 'required_if:destination,bank|string|max:30',
        ]);
        # validate the request

        $transfer_issue = "";

        try {

            $user = $request->user();

            $company = $user->company(true, true);
            # get the company information

            $company_data = (array) $company->extra_data;
            # get the company meta data

            $accounts = $this->getBankAccounts($sdk);

            if (!empty($accounts) && $accounts->count() > 0) {
    
                $bank = $accounts->first();
    
                $banks = collect(Banks::BANK_CODES)->sort()->map(function ($name, $code) {
                    return ['name' => $name, 'code' => $code];
                })->values();
    
                $bank_name = $banks->where('code', $bank["json_data"]["bank_code"])->pluck('name')->first();
                $account_number = $bank["account_number"];
                $account_name = $bank["account_name"];
    
            } else {
    
                $transfer_issue = "Transfer Unavailable &raquo; Setup Banking Information | ";
    
            }

            $transfer_address = "";
            # get actual address

            $debit_subaccount = "";

            $paymentsSettings = $company_data['payments_settings'] ?? [];
    
            if (isset($paymentsSettings['wallet']['data']) && isset($paymentsSettings['wallet']['data']['account_reference']) && !empty($paymentsSettings['wallet']['data']['account_reference']) ) {
                $debit_subaccount = $paymentsSettings['wallet']['data']['account_reference'];
            } else {
                $transfer_issue = "Transfer Unavailable &raquo; Unable to Get Wallet Account Reference | ";
            }

            $transfer_params = [
                "debit_subaccount" => $debit_subaccount,
                "account_bank" => $bank_name,
                "account_number" => $account_number,
                "amount" => $request->amount,
                "narration" => "Wallet Transfer",
                "currency" => $request->currency,
                "beneficiary_name" => $account_name,
                "reference" => "Wallet Transfer",
                "debit_currency" => $request->currency,
                "meta" => [
                    "first_name" => $user->firstname,
                    "last_name" => $user->lastname,
                    "email" => $user->email,
                    "mobile_number" => $user->email,
                    "recipient_address" => $transfer_address
                ]
            ];


            if (empty($transfer_issue)) {

                $transfer = $this->transferFromWallet($request, $request->destination, $transfer_params);

            }
            
        } catch (\Exception $e) {

            //throw new \RuntimeException($e->getMessage());
            $transfer_issue = $e->getMessage();
            
        }

        $response_status = empty($transfer_issue) ? true : false;

        $response_message = empty($transfer_issue) ? "Transfer Succcessful" : "Transfer Failed with Error: " . $transfer_issue;

        $response_data = empty($transfer_issue) ? $transfer->data : [];

        $response = [
            "status" => $response_status,
            "message" => $response_message,
            "data" => $response_data,
        ];

        return response()->json($response);
    }

    /**
     * @param Request $request
     *
     */
    private function setupProvider(Request $request)
    {
        // Determine active Paayment provider
        $provider = env('SETTINGS_ECOMMERCE_PAYMENT_PROVIDER', 'flutterwave');
        $country = env('SETTINGS_COUNTRY', 'NG');

        $provider_config = ucfirst($provider). strtoupper($country) . '.php';
        $provider_class = ucfirst($provider). strtoupper($country) . 'Class.php';

        $provider_config_path = __DIR__.'/../../Config/Providers/Payments/' . ucfirst($provider). '/' . $provider_config;
        $config = require_once($provider_config_path);

        $provider_class_path = __DIR__.'/../../Config/Providers/Payments/' . ucfirst($provider). '/' . $provider_class;
        require_once($provider_class_path);

        return $config["class"];
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

        $provider_config = ucfirst($provider). strtoupper($country) . '.php';
        $provider_class = ucfirst($provider). strtoupper($country) . 'Class.php';

        $provider_config_path = __DIR__.'/../../Config/Providers/Payments/' . ucfirst($provider). '/' . $provider_config;
        $config = require_once($provider_config_path);

        $provider_class_path = __DIR__.'/../../Config/Providers/Payments/' . ucfirst($provider). '/' . $provider_class;
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


    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWalletBalances(Request $request, $accountReference)
    {
        // Determine active Logistics provider
        $provider = env('SETTINGS_ECOMMERCE_PAYMENT_PROVIDER', 'flutterwave');
        $country = env('SETTINGS_COUNTRY', 'NG');

        $provider_config = ucfirst($provider). strtoupper($country) . '.php';
        $provider_class = ucfirst($provider). strtoupper($country) . 'Class.php';

        $provider_config_path = __DIR__.'/../../Config/Providers/Payments/' . ucfirst($provider). '/' . $provider_config;
        $config = require_once($provider_config_path);

        $provider_class_path = __DIR__.'/../../Config/Providers/Payments/' . ucfirst($provider). '/' . $provider_class;
        require_once($provider_class_path);


        // Parse Shopper Origin Address
        $user = $request->user();
        $company = $user->company();

        $providerParams = [
            "account_reference" => $accountReference
        ];


        $c = $config["class"];

        $provider = new $c($providerParams);

        $balances = $provider->getWalletBalances();

        $response_status = $balances->status === "success" ? true : false;

        $response_message = $balances->status === "success" ? "Wallet Balance Fetch Successful" : "Wallet Balance Fetch Failed &raquo; " . $balances->message;

        $response_data = $balances->status === "success" ? $balances->data : [];

        $response = [
            "status" => $response_status,
            "message" => $response_message,
            "data" => [$response_data], //assume there are multiple?
        ];
        
        return response()->json($response);
    }



    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTransferEstimate(Request $request, $amount, $currency = "NGN")
    {
        $c = $this->setupProvider($request);

        $user = $request->user();
        $company = $user->company();

        $providerParams = [
            "amount" => $amount,
            "currency" => $currency
        ];

        $provider = new $c($providerParams);

        $estimate = $provider->getTransferEstimate();

        $response_status = $estimate->status === "success" ? true : false;

        $response_message = $estimate->status === "success" ? "Transfer Estimate Fetch Successful" : "Transfer Estimate Fetch Failed &raquo; " . $estimate->message;

        $response_data = $estimate->status === "success" ? $estimate->data : [];

        $response = [
            "status" => $response_status,
            "message" => $response_message,
            "data" => $response_data, //assume there are multiple?
        ];
        
        return response()->json($response);
    }


    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function transferFromWallet(Request $request, $destination, $params)
    {
        $c = $this->setupProvider($request);

        $user = $request->user();
        $company = $user->company();

        $providerParams = [
            "destination" => $destination,
            "params_transfer" => $params
        ];

        $c = $config["class"];

        $provider = new $c($providerParams);

        $transfer = $provider->transferFromWallet();

        $response_status = $transfer->status === "success" ? true : false;

        $response_message = $transfer->status === "success" ? "Transfer Successful" : "Transfer Failed &raquo; " . $transfer->message;

        $response_data = $transfer->status === "success" ? $transfer->data : [];

        $response = [
            "status" => $response_status,
            "message" => $response_message,
            "data" => $response_data,
        ];
        
        return response()->json($response);
    }
    



 }