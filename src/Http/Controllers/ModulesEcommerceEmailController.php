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


class ModulesEcommercEmailController extends Controller {

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
        $this->data['page']['title'] .= ' &rsaquo; Email Manager';
        $this->data['header']['title'] = 'Email Manager';
        $this->data['selectedSubMenu'] = 'ecommerce-emails';
        $this->data['submenuAction'] = '<a v-on:click.prevent="createEmail" class="btn btn-primary btn-block">Add Email</a>';

        $config = $this->getCompany()->extra_data;
        # get the company configuration data
        $emails = [];
        # the emails collection
        if (!empty($config) && !empty($config['hosting'])) {
            # we actually have some hosting data
            try {
                $hosting = $config['hosting'][0];
                $whm = Website::getWhmClient($hosting['hosting_box_id']);
                # get the API client
                $emails = $whm->listEmails($hosting['domain'], $hosting['username'], 400);
                # list the email addresses on this domain
            } catch (\Exception $e) {
                $response = material_ui_html_response([$e->getMessage()])->setType(UiResponse::TYPE_ERROR);
                $request->session()->flash('UiResponse', $response);
            }
        }
        $this->setViewUiResponse($request);
        $this->data['emails'] = collect($emails)->map(function ($email) {
            return (object) $email;
        });
        # list the email addresses on this domain
        $this->data['domains'] = $domains = $this->getDomains($sdk);
        $this->data['isHostingSetup'] = !empty($config['hosting']) && !empty($domains) && $domains->count() > 0;
        return view('modules-ecommerce::email', $this->data);
    }
    
    /**
     * @param Request $request
     * @param Sdk     $sdk
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \League\Flysystem\FileNotFoundException
     * @throws \League\Flysystem\UnreadableFileException
     */
    public function post(Request $request, Sdk $sdk)
    {
        $this->validate($request, [
            'action' => 'required|string|in:setup_hosting,create_email',
            'username' => 'required_if:action,create_email|string',
            'domain' => 'required_if:action,create_email|string',
            'password' => 'required_if:action,create_email|string|min:8',
            'quota' => 'required_if:action,create_email|numeric',
        ]);
        # validate the request
        $action = $request->input('action');
        try {
            switch ($action) {
                case 'setup_hosting':
                    return (new Website)->post($request, $sdk);
                    break;
                default:
                    $config = $this->getCompany()->extra_data;
                    # get the company configuration data
                    if (empty($config) || empty($config['hosting'])) {
                        throw new \RuntimeException(
                            'You need to first setup hosting on your domain before you can create email accounts.'
                        );
                    }
                    $hosting = $config['hosting'][0];
                    $whm = Website::getWhmClient($hosting['hosting_box_id']);
                    # get the API client
                    $email = $whm->createEmail(
                        $request->input('username'),
                        $request->input('password'),
                        $request->input('domain'),
                        (int) $request->input('quota'),
                        0,
                        0,
                        $hosting['username']
                    );
                    if (empty($email)) {
                        throw new \RuntimeException('Could not create the email account.');
                    }
                    $response = tabler_ui_html_response(['Successfully created email account ' . $email])->setType(UiResponse::TYPE_SUCCESS);
            }
            
        } catch (\RuntimeException $e) {
            $response = tabler_ui_html_response([$e->getMessage()])->setType(UiResponse::TYPE_ERROR);
        }
        return redirect(url()->current())->with('UiResponse', $response);
    }


    /**
     * @param Request $request
     * @param string  $username
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \League\Flysystem\FileNotFoundException
     * @throws \League\Flysystem\UnreadableFileException
     */
    public function delete(Request $request, string $username)
    {
        $config = $this->getCompany()->extra_data;
        # get the company configuration data
        if (empty($config) || empty($config['hosting'])) {
            throw new \RuntimeException(
                'You need to first setup hosting on your domain before you can create email accounts.'
            );
        }
        $hosting = $config['hosting'][0];
        $whm = Website::getWhmClient($hosting['hosting_box_id']);
        # get the API client
        $deleted = $whm->deleteEmail($username, $hosting['domain'], 'remove', $hosting['username']);
        if (empty($deleted)) {
            throw new \RuntimeException('Could not remove the email account.');
        }
        return response()->json(['data' => ['Successfully removed the email account']]);
    }

}