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

        $this->data['page']['title'] .= ' &rsaquo; Adverts Manager';
        $this->data['header']['title'] = 'Adverts Manager';
        $this->data['selectedSubMenu'] = 'ecommerce-adverts';
        $this->data['submenuAction'] = '<a href="#" v-on:click.prevent="createAdvert" class="btn btn-primary btn-block">Add Advert</a>';

        $this->setViewUiResponse($request);
        $this->data['adverts'] = $this->getAdverts($sdk);

        return view('modules-ecommerce::adverts', $this->data);
    }
    
    /**
     * @param Request $request
     * @param Sdk     $sdk
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function post(Request $request, Sdk $sdk)
    {
        $this->validate($request,[
            'title' => 'required|string|max:80',
            'type' => 'required|string',
            'redirect_url' => 'nullable|string',
            'is_default' => 'required|string|in:0,1',
            'image' => 'required_without:advert_id|image'
        ]);
        # validate the request
        //dd($request);
        try {
            if (!$request->has('redirect_url')) {
                $redirectUrl = $request->input('redirect_url');
                $redirectUrl = starts_with($redirectUrl, ['http', 'https']) ? $redirectUrl : 'http://' . $redirectUrl;
                $request->request->set('redirect_url', $redirectUrl);
            }
            $advertId = $request->has('advert_id') ? $request->input('advert_id') : null;
            $resource = $sdk->createAdvertResource($advertId);
            $payload = $request->only(['title', 'type', 'redirect_url', 'is_default']);
            foreach ($payload as $key => $value) {
                $resource->addBodyParam($key, $value);
            }
            if ($request->has('image')) {
                $file = $request->file('image');
                $resource->addMultipartParam('image', file_get_contents($file->getRealPath(), false), $file->getClientOriginalName());
            }
            $response = $resource->send('post');
            # send the request
            if (!$response->isSuccessful()) {
                # it failed
                $message = $response->errors[0]['title'] ?? '';
                throw new \RuntimeException('Failed while '. (empty($advertId) ? 'adding' : 'updating') .' the advert. '.$message);
            }
            $company = $this->getCompany();
            Cache::forget('adverts.'.$company->id);
            $response = (tabler_ui_html_response(['Successfully '. (empty($advertId) ? 'added' : 'updated the') .' advert.']))->setType(UiResponse::TYPE_SUCCESS);
        } catch (\Exception $e) {
            $response = (tabler_ui_html_response([$e->getMessage()]))->setType(UiResponse::TYPE_ERROR);
        }
        return redirect(url()->current())->with('UiResponse', $response);
    }


    /**
     * @param Request $request
     * @param Sdk     $sdk
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delete(Request $request, Sdk $sdk, string $id)
    {
        $model = $sdk->createAdvertResource($id);
        $response = $model->send('delete');
        # make the request
        if (!$response->isSuccessful()) {
            // do something here
            throw new RecordNotFoundException($response->errors[0]['title'] ?? 'Failed while deleting the advert.');
        }
        $company = $request->user()->company(true, true);
        Cache::forget('adverts.'.$company->id);
        $this->data = $response->getData();
        return response()->json($this->data);
    }

}