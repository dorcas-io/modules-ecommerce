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


class ModulesEcommerceAdvertsController extends Controller {

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

        $this->setViewUiResponse($request);
        $this->data['storeSettings'] = self::getStoreSettings((array) $this->getCompany()->extra_data);
        # our store settings container
        $query = $sdk->createProductResource()->addQueryArgument('limit', 1)->send('get');
        $this->data['productCount'] = $query->isSuccessful() ? $query->meta['pagination']['total'] ?? 0 : 0;
        $this->data['subdomain'] = get_dorcas_subdomain($sdk);
        # set the subdomain
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
            'store_custom_js'
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