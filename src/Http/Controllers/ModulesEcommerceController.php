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
use Illuminate\Auth\Access\AuthorizationException;
use App\Exceptions\RecordNotFoundException;
//use App\Http\Controllers\ECommerce\Website;
use App\Http\Middleware\PaidPlanGate;
use GuzzleHttp\Exception\ServerException;
use App\Dorcas\Hub\Utilities\DomainManager\HostingManager;
use App\Dorcas\Hub\Utilities\DomainManager\ResellerClubClient;
use App\Dorcas\Hub\Utilities\DomainManager\Upperlink;
use App\Events\ECommerce\DomainDelete;

use Dorcas\ModulesEcommerce\Http\Controllers\ModulesEcommerceWebsiteController as Website;


class ModulesEcommerceController extends Controller {

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

    public function index(Request $request)
    {
    	//$this->data['availableModules'] = HomeController::SETUP_UI_COMPONENTS;
    	return view('modules-ecommerce::index', $this->data);
    }

    /**
     * @param Request $request
     * @param Sdk     $sdk
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function domains(Request $request,  Sdk $sdk)
    {
        $this->data['page']['title'] .= ' &rsaquo; Domains Manager';
        $this->data['header']['title'] = 'Domains Manager';
        $this->data['selectedSubMenu'] = 'ecommerce-domains';
        $this->data['submenuAction'] = '';

        $this->setViewUiResponse($request);

        $config = (array) $this->getCompany()->extra_data;
        $this->data['domains'] = $domains = $this->getDomains($sdk);
        $domain = get_dorcas_domain();
        $subdomains = $this->getSubDomains($sdk);
        if (!empty($subdomains)) {
            $this->data['subdomains'] = $this->getSubDomains($sdk)->filter(function ($subdomain) use ($domain) {
                return $subdomain->domain['data']['domain'] === $domain;
            });
        } else {
            $this->data['subdomains'] = [];
        }
        $this->data['claimComNgDomain'] = $this->hasFreeNgDomain($domains);
        $this->data['isHostingSetup'] = !empty($config['hosting']) && !empty($domains) && $domains->count() > 0;
        $this->data['hostingConfig'] = $config['hosting'] ?? [];
        if (!empty($config['hosting'])) {
            $this->data['nameservers'] = collect($config['hosting'][0]['options'])->filter(function ($entry, $key) {
                return starts_with($key, 'nameserver') && !empty($entry);
            })->values()->sort();
        }

        return view('modules-ecommerce::domains', $this->data);
    }

    /**
     * Check if has a free com.ng domain to claim
     *
     * @param Collection|null $domains
     *
     * @return bool
     */
    public function hasFreeNgDomain(Collection $domains = null): bool
    {
        $planCost = (float) $this->getCompany()->plan['data']['price_monthly']['raw'];
        if (empty($domains) || $domains->count() === 0) {
            $comNgDomains = 0;
        } else {
            $comNgDomains = $domains->filter(function ($domain) {
                return ends_with($domain->domain, 'com.ng');
            })->count();
        }
        return $planCost > 0 && $comNgDomains === 0;
    }


    /**
     * @param Request $request
     * @param Sdk     $sdk
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function create(Request $request, Sdk $sdk)
    {
        $company = $this->getCompany();
        # get the company
        $this->validate($request, [
            'domain' => 'required|string|max:80',
            'extension' => 'required_with:purchase_domain|string|max:80',
        ]);
        # validate the request
        try {
            if ($request->has('reserve_subdomain')) {
                # to reserve a subdomain
                $response = $sdk->createDomainResource()->addBodyParam('prefix', $request->domain)->send('post',
                    ['issuances']);
                # send the request
                if (!$response->isSuccessful()) {
                    # it failed
                    $message = $response->errors[0]['title'] ?? '';
                    throw new \RuntimeException('Failed while reserving the ' . $request->domain . ' subdomain. ' . $message);
                }
                Cache::forget('ecommerce.subdomains.' . $company->id);
                $response = tabler_ui_html_response(['Successfully reserved the subdomain.'])->setType(UiResponse::TYPE_SUCCESS);
    
            } elseif ($request->has('setup_hosting')) {
                # setup hosting on the domain
                return (new Website)->post($request, $sdk);
        
            } elseif ($request->has('purchase_domain')) {
                # purchase a domain
                $planCost = PaidPlanGate::checkPricingOnCompanyPlan($this->getCompany());
                # get the cost of the company's plan
                if ($planCost === null || $planCost <= 0) {
                    # we have no plan pricing, or it's a free plan
                    throw new AuthorizationException('This feature is only available on the paid plans.');
                }
                $hostingManager = new HostingManager($sdk);
                $availableServers = $hostingManager->getCurrentServerStatus()->filter(function ($server) {
                    return $server->remaining_spots > 0;
                });
                # get the available servers
                $hostingServer = $availableServers->random();
                # select one of the available servers at random
                if ($request->extension === 'com.ng') {
                    # buying a .com.ng domain - we use Upperlink Registrars
                    //list($customerId, $contactId) = self::registerUpperlinkCustomer($request, $sdk);
                    $manager = new Upperlink();
                    $config = [
                        'extension' => $request->extension,
                        'ns' => $hostingServer->ns,
                        'user' => $request->user(),
                        'company' => $this->getCompany(),
                        'id_protection' => false
                    ];
                    $json = $manager->registerDomain($request->domain, $config);
                    if (!empty($json['error']) || $json['result'] !== 'success') {
                        throw new \RuntimeException(
                            $json['message'] ?? 'Something went wrong while creating your order. Please try again.'
                        );
                    }
                    $json['order_source'] = 'Upperlink';
                    
                } else {
                    # all others, we use ResellerClub
                    list($customerId, $contactId) = self::registerResellerClubCustomer($request, $sdk);
                    $manager = new ResellerClubClient();
                    $config = [
                        'extension' => $request->extension,
                        'customer_id' => $customerId,
                        'contact_id' => $contactId,
                        'invoice_option' => 'NoInvoice',
                        'ns' => $hostingServer->ns
                    ];
                    $json = $manager->registerDomain($request->domain, $config);
                    if (!empty($json['status']) && strtolower($json['status']) !== 'success') {
                        throw new \RuntimeException(
                            $json['message'] ?? 'Something went wrong while creating your order. Please try again.'
                        );
                    }
                    $json['order_source'] = 'ResellerClub';
                }
                $domain = $request->domain . '.' . $request->extension;
                $messages = ['Successfully purchased the domain '.$domain];
                $query = $sdk->createDomainResource()->addBodyParam('domain', $domain)
                                                        ->addBodyParam('configuration', ['order_info' => $json])
                                                        ->addBodyParam('hosting_box_id', $hostingServer->id)
                                                        ->send('POST');
                # adds the domain for the company
                if (!$query->isSuccessful()) {
                    $messages[] = 'Could not add the domain to your list of purchased domain. Kindly contact support to report this.';
                }
                Cache::forget('ecommerce.domains.'.$company->id);
                $response = tabler_ui_html_response($messages)->setType(UiResponse::TYPE_SUCCESS);
                
            } elseif ($request->has('add_domain')) {
                $hostingManager = new HostingManager($sdk);
                $availableServers = $hostingManager->getCurrentServerStatus()->filter(function ($server) {
                    return $server->remaining_spots > 0;
                });
                # get the available servers
                $hostingServer = $availableServers->random();
                # select one of the available servers at random
                $query = $sdk->createDomainResource()->addBodyParam('domain', $request->input('domain'))
                                                    ->addBodyParam('configuration', ['order_info' => ['order_source' => 'existing_domain']])
                                                    ->addBodyParam('hosting_box_id', $hostingServer->id)
                                                    ->send('POST');
                # adds the domain for the company
                if (!$query->isSuccessful()) {
                    $messages[] = 'Could not add the domain to your list of domains. Kindly contact support to report this.';
                }
                Cache::forget('ecommerce.domains.'.$company->id);
                $messages = ['Successfully added the domain.'];
                $response = tabler_ui_html_response($messages)->setType(UiResponse::TYPE_SUCCESS);
            }
        } catch (ServerException $e) {
            $message = json_decode((string) $e->getResponse()->getBody(), true);
            $response = tabler_ui_html_response([$message['message']])->setType(UiResponse::TYPE_ERROR);
        } catch (\Exception $e) {
            $response = tabler_ui_html_response([$e->getMessage()])->setType(UiResponse::TYPE_ERROR);
        }
        return redirect(url()->current())->with('UiResponse', $response);
    }
    



    /**
     * @param Request $request
     * @param Sdk     $sdk
     *
     * @return mixed|\SimpleXMLElement
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected static function registerResellerClubCustomer(Request $request, Sdk $sdk)
    {
        try {
            $company = $request->user()->company(true, true);
            # the company
            $manager = new ResellerClubClient();
            $customerId = $manager->registerCustomer($request->user(), $company);
            $contactId = $manager->registerContact($request->user(), $company, $customerId);
            $extraData = (array) $company->extra_data;
            if (empty($extraData['reseller_club_customer_id'])) {
                $extraData['reseller_club_customer_id'] = $customerId;
            }
            if (empty($extraData['reseller_club_contact_id'])) {
                $extraData['reseller_club_contact_id'] = $contactId;
            }
            $sdk->createCompanyService()->addBodyParam('extra_data', $extraData)->send('PUT');
            return [$customerId, $contactId];
        } catch (ServerException $e) {
            $response = json_decode((string) $e->getResponse()->getBody(), true);
            Log::error('Failed to add customer. Reason: '. $response['message'], $response);
            return $response['message'];
            
        } catch (\Exception $e) {
            Log::error('Failed to add customer. Reason: '. $e->getMessage());
        }
        return null;
    }
    
    /**
     * @param Request $request
     * @param Sdk     $sdk
     *
     * @return array|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected static function registerUpperlinkCustomer(Request $request, Sdk $sdk)
    {
        try {
            $company = $request->user()->company(true, true);
            # the company
            $manager = new Upperlink();
            $customerId = $manager->registerCustomer($request->user(), $company);
            $contactId = $manager->registerContact($request->user(), $company, $customerId);
            $extraData = (array) $company->extra_data;
            if (empty($extraData['upperlink_customer_id'])) {
                $extraData['upperlink_customer_id'] = $customerId;
            }
            if (empty($extraData['upperlink_contact_id'])) {
                $extraData['upperlink_contact_id'] = $contactId;
            }
            $sdk->createCompanyService()->addBodyParam('extra_data', $extraData)->send('PUT');
            return [$customerId, $contactId];
        } catch (ServerException $e) {
            $response = json_decode((string) $e->getResponse()->getBody(), true);
            Log::error('Failed to add customer. Reason: '. $response['message'], $response);
            return $response['message'];
            
        } catch (\Exception $e) {
            Log::error('Failed to add customer. Reason: '. $e->getMessage());
        }
        return null;
    }


    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkAvailability(Request $request)
    {
        $extension = $request->input('extension', 'com');
        $manager = $extension === 'com.ng' ? new Upperlink() : new ResellerClubClient();
        $status = $manager->checkAvailability($request->domain, [$request->extension]);
        $response = [];
        foreach ($status as $domain => $info) {
            $response = $info;
            break;
        }
        return response()->json($response);
    }
    
    /**
     * @param Request $request
     * @param Sdk     $sdk
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function releaseDomain(Request $request, Sdk $sdk, string $id)
    {
        $query = $sdk->createDomainResource($id)->send('delete');
        # make the request
        if (!$query->isSuccessful()) {
            // do something here
            throw new \RuntimeException($query->errors[0]['title'] ?? 'Something went wrong while deleting the domain.');
        }
        $domain = $query->getData();
        event(new DomainDelete($domain, $sdk->getAuthorizationToken()));
        # trigger the event
        $company = $this->getCompany();
        Cache::forget('ecommerce.domains.'.$company->id);
        return response()->json($domain);
    }
    
    /**
     * @param Request $request
     * @param Sdk     $sdk
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function registerCustomer(Request $request, Sdk $sdk)
    {
        $company = $this->getCompany();
        # the company
        $manager = new ResellerClubClient();
        $customerId = $manager->registerCustomer($request->user(), $company);
        $extraData = (array) $company->extra_data;
        if (empty($extraData['reseller_club_customer_id'])) {
            $extraData['reseller_club_customer_id'] = $customerId;
            $sdk->createCompanyService()->addBodyParam('extra_data', $extraData)->send('PUT');
        }
        return response()->json(['customer_id' => $customerId]);
    }



}