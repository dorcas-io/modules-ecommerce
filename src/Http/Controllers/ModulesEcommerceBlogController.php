
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
use App\Exceptions\RecordNotFoundException;


class ModulesEcommerceBlogController extends Controller {

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
        $this->data['page']['title'] .= ' &rsaquo; Blog Manager';
        $this->data['header']['title'] = 'Blog Manager';
        $this->data['selectedSubMenu'] = 'ecommerce-blog';
        $this->data['submenuAction'] = '
            <div class="dropdown"><button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">Blog Actions</button>
                <div class="dropdown-menu">
                    <a href="#" v-on:click.prevent="newField" class="btn btn-primary btn-block">Add Category</a>
                    <a :href="blogUrl" class="dropdown-item" target="_blank">New Post</a>
                </div>
            </div>
        ';


        $this->setViewUiResponse($request);
        $subdomain = get_dorcas_subdomain();
        if (!empty($subdomain)) {
            $this->data['page']['header']['title'] .= ' (Blog: '.$subdomain.'/blog)';
        }
        $postsCount = 0;
        $query = $sdk->createBlogResource()->addQueryArgument('limit', 1)->send('get');
        if ($query->isSuccessful()) {
            $postsCount = $query->meta['pagination']['total'] ?? 0;
        }
        $this->data['categories'] = $this->getBlogCategories($sdk);
        $this->data['subdomain'] = get_dorcas_subdomain($sdk);
        # set the subdomain
        if (!empty($this->data['subdomain'])) {
            $this->data['blogUrl'] = $this->data['subdomain'] . '/blog-admin/new-post?token=' . $sdk->getAuthorizationToken();
        }
        $this->data['blogSettings'] = self::getBlogSettings((array) $this->getCompany()->extra_data);
        # our store settings container
        $this->data['postsCount'] = $postsCount;
        return view('modules-ecommerce::blog', $this->data);
    }
    
    /**
     * @param array $configuration
     *
     * @return array
     */
    public static function getBlogSettings(array $configuration = []): array
    {
        $requiredStoreSettings = ['blog_name', 'blog_instagram_id', 'blog_twitter_id', 'blog_facebook_page', 'blog_terms_page'];
        $settings = $configuration['blog_settings'] ?? [];
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
    public function blogSettings(Request $request, Sdk $sdk)
    {
        try {
            $company = $this->getCompany();
            $configuration = (array) $company->extra_data;
            $blogSettings = $configuration['blog_settings'] ?? [];
            # our store settings container
            $submitted = $request->only(['blog_name', 'blog_instagram_id', 'blog_twitter_id', 'blog_facebook_page', 'blog_terms_page']);
            # get the submitted data
            foreach ($submitted as $key => $value) {
                if (empty($value)) {
                    unset($blogSettings[$key]);
                }
                $blogSettings[$key] = $value;
            }
            $configuration['blog_settings'] = $blogSettings;
            # add the new store settings configuration
            $query = $sdk->createCompanyService()->addBodyParam('extra_data', $configuration)->send('PUT');
            # send the request
            if (!$query->isSuccessful()) {
                # it failed
                $message = $query->errors[0]['title'] ?? '';
                throw new \RuntimeException('Failed while updating the blog settings. '.$message);
            }
            $subdomains = $this->getSubDomains($sdk);
            if (!empty($subdomains)) {
                $subdomain = $subdomains->filter(function ($s) {
                    $domain = $s->domain['data'];
                    return $domain['domain'] === 'dorcas.ng';
                })->first();
                if (!empty($subdomain)) {
                    Cache::forget('domain_' . $subdomain->prefix);
                }
            }
            $response = (tabler_ui_html_response(['Successfully updated your blog information.']))->setType(UiResponse::TYPE_SUCCESS);
        } catch (\Exception $e) {
            $response = (tabler_ui_html_response([$e->getMessage()]))->setType(UiResponse::TYPE_ERROR);
        }
        return redirect(url()->current())->with('UiResponse', $response);
    }


    /**
     * @param Request $request
     * @param Sdk     $sdk
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createCategory(Request $request, Sdk $sdk)
    {
        $name = $request->input('name', null);
        $response = $sdk->createBlogResource()->addBodyParam('name', $name)->send('POST', ['categories']);
        # send the request
        if (!$response->isSuccessful()) {
            // do something here
            throw new \RuntimeException($response->errors[0]['title'] ?? 'Failed while creating the blog category.');
        }
        $company = $request->user()->company(true, true);
        Cache::forget('business.blog-categories.'.$company->id);
        $this->data = $response->getData();
        return response()->json($this->data);
    }
    
    /**
     * @param Request $request
     * @param Sdk     $sdk
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deleteCategory(Request $request, Sdk $sdk, string $id)
    {
        $model = $sdk->createBlogResource();
        $response = $model->send('delete', ['categories', $id]);
        # make the request
        if (!$response->isSuccessful()) {
            // do something here
            throw new RecordNotFoundException($response->errors[0]['title'] ?? 'Failed while deleting the category.');
        }
        $company = $request->user()->company(true, true);
        Cache::forget('business.blog-categories.'.$company->id);
        $this->data = $response->getData();
        return response()->json($this->data);
    }
    
    /**
     * @param Request $request
     * @param Sdk     $sdk
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function updateCategory(Request $request, Sdk $sdk, string $id)
    {
        $model = $sdk->createBlogResource();
        $response = $model->addBodyParam('name', $request->input('name'))
                            ->addBodyParam('slug', $request->input('slug'))
                            ->addBodyParam('update_slug', $request->input('update_slug'))
                            ->send('PUT', ['categories', $id]);
        # make the request
        if (!$response->isSuccessful()) {
            // do something here
            throw new RecordNotFoundException($response->errors[0]['title'] ?? 'Failed while updating the category.');
        }
        $company = $request->user()->company(true, true);
        Cache::forget('business.blog-categories.'.$company->id);
        $this->data = $response->getData();
        return response()->json($this->data);
    }

}