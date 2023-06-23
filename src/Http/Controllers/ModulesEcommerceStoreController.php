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
        $this->data['paymentSettings'] = [
            "payment_option" => "use_bank_account",
            "has_marketplace" => "no"
        ];
        # our store settings container
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
            # our store settings container
            $submitted = $request->only($this->storePaymentsFields);
            # get the submitted data
            foreach ($submitted as $key => $value) {
                if (empty($value)) {
                    unset($paymentsSettings[$key]);
                }
                $paymentsSettings[$key] = $value;
            }
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
            $response = (tabler_ui_html_response(['Successfully updated your Payments Settings']))->setType(UiResponse::TYPE_SUCCESS);
        } catch (\Exception $e) {
            $response = (tabler_ui_html_response([$e->getMessage()]))->setType(UiResponse::TYPE_ERROR);
        }
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


 }