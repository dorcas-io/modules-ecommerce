<?php

namespace Dorcas\ModulesEcommerce\Http\Controllers;

use App\Http\Controllers\ECommerce\OnlineStore;
use Hostville\Dorcas\Sdk;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Dorcas\Hub\Utilities\UiResponse\UiResponse;
use App\Exceptions\DeletingFailedException;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Dorcas\ModulesEcommerce\Http\Controllers\ModulesEcommerceBlogController as Dashboard;

class ModulesEcommerceBlog extends Controller {

    /**
     * Home constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->data['page']['title'] = 'Blog';
        $this->data['page']['header'] = ['title' => 'Blog'];
    }
    
    /**
     * @param Request     $request
     * @param string|null $slug
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request, Sdk $sdk, string $slug = null)
    {
        $blogOwner = $this->getCompanyViaDomain();
        # get the store owner
        if (empty($blogOwner)) {
            abort(404, 'Could not find a blog at this URL.');
        }
        $this->data['categorySlug'] = $slug;
        $this->data['defaultSearch'] = $request->get('q', '');
        $this->data['blogOwner'] = $blogOwner;

        ///why do I  have  to  mannually add blogname  and blog settins

        //$domain = $request->session()->get('domain');
        //$blogOwner2 = (object) $domain->owner['data'];
        //$settings = Dashboard::getBlogSettings((array) $blogOwner->extra_data);
        //$this->data['blogSettings'] = $settings;

        # get the blog owner
        //$this->data['blogCategories'] = $this->listBlogCategories($sdk, $blogOwner);

        $this->blogViewComposer($this->data, $request, $sdk, $blogOwner);

        if ($request->session()->has('dorcas_referrer')) {
            $referrer =  $request->session()->get('dorcas_referrer', ["mode" => "", "value" => ""]);
            $this->data['page']['title'] = strtoupper($referrer["value"]) . "'s " . $this->data['page']['title'];
            $this->data['page']['header']['title'] = strtoupper($referrer["value"]) . "'s Blog";
            $this->data['blogName'] = strtoupper($referrer["value"]) . "'s Blog";
        } else {
            $this->data['page']['title'] = $blogOwner->name . ' ' . $this->data['page']['title'];
            $this->data['page']['header']['title'] = $blogOwner->name . ' '  . $this->data['page']['title'];
            $this->data['blogName'] = $blogOwner->name . " Blog";
        }

        return view('modules-ecommerce::blog.timeline', $this->data);
    }
    
    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function categories()
    {
        return redirect()->route('blog');
    }
    
    /**
     * @param Request $request
     * @param Sdk     $sdk
     * @param string  $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function postDetails(Request $request, Sdk $sdk, string $id)
    {
        $this->data['breadCrumbs'] = [
            'crumbs' => [
                ['text' => 'Home', 'href' => route('blog')],
                ['text' => 'Posts', 'href' => route('blog.posts')],
                ['text' => 'Reading', 'href' => route('blog.posts.details', [$id])],
            ]
        ];
        $blogOwner = $this->getCompanyViaDomain();
        # get the blog owner
        if (empty($blogOwner)) {
            abort(404, 'Could not find a blog at this URL.');
        }
        $query = $sdk->createBlogResource($blogOwner->id)->addQueryArgument('slug', $id)->send('GET', ['posts']);
        if (!$query->isSuccessful()) {
            abort(500, $query->getErrors()[0]['title'] ?? 'Something went wrong while fetching the blog post.');
        }

        $this->blogViewComposer($this->data, $request, $sdk, $blogOwner);

        $this->data['post'] = $post = $query->getData(true);
        $this->data['page']['title'] = $post->title . ' | Blog';
        $this->data['page']['header']['title'] = $post->title;
        return view('modules-ecommerce::blog.post-details', $this->data);
    }

    /**
     * @param Request $request
     * @param Sdk     $sdk
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function newPost(Request $request, Sdk $sdk)
    {
        $this->data['page']['title'] = 'New Post | BlogAdmin';
        $this->data['page']['header'] = ['title' => 'New Post | Blog'];
        $this->data['breadCrumbs'] = [
            'crumbs' => [
                ['text' => 'Home', 'href' => route('blog')],
                ['text' => 'New Post', 'href' => route('blog.admin.new-post'), 'isActive' => true],
            ]
        ];

        $this->blogViewComposer($this->data, $request, $sdk, $blogOwner);
    
        $this->setViewUiResponse($request);
        $this->data['categories'] = $this->getBlogCategories($sdk);
        return view('modules-ecommerce::blog.new-post', $this->data);
    }
    
    /**
     * @param Request $request
     * @param Sdk     $sdk
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createPost(Request $request, Sdk $sdk)
    {
        $rules = [
            'title' => 'required|string|max:80',
            'summary' => 'required|string',
            'categories' => 'nullable|array',
            'categories.*' => 'required_with:categories|string',
            'is_published' => 'nullable|numeric|in:0,1',
            'publish_at' => 'nullable|date_format:"d-m-Y H:i"'
        ];
        $this->getValidationFactory()->make($request->all(), $rules, [], [])->validate();
        # validate the request
        try {
            $postId = $request->has('post_id') ? $request->input('post_id') : null;
            $resource = $sdk->createBlogResource();
            $payload = $request->only(['title', 'summary', 'content', 'retain_photo']);
            foreach ($payload as $key => $value) {
                $resource->addBodyParam($key, $value);
            }
            if ($request->has('categories')) {
                $categories = $request->input('categories', []);
                if ($request->has('image')) {
                    foreach ($categories as $id) {
                        $resource->addMultipartParam('categories[]', $id);
                    }
                } else {
                    $resource->addBodyParam('categories', $categories);
                }
            }
            if ($request->has('publish_at') && !empty($request->input('publish_at'))) {
                $date = Carbon::createFromFormat('d-m-Y H:i', $request->input('publish_at'));
                $resource->addBodyParam('publish_at', $date->format('d/m/Y H:i'));
                $resource->addBodyParam('is_published', 0);
            } else {
                $resource->addBodyParam('is_published', 1);
            }
            if ($request->has('image')) {
                $file = $request->file('image');
                $resource->addMultipartParam('image', file_get_contents($file->getRealPath(), false), $file->getClientOriginalName());
            }
            $response = $resource->send('post', ['posts', !empty($postId) ? $postId : '']);
            # send the request
            if (!$response->isSuccessful()) {
                # it failed
                $message = $response->errors[0]['title'] ?? '';
                throw new \RuntimeException('Failed while '. (empty($postId) ? 'adding' : 'updating') .' the blog post. '.$message);
            }
            $response = (bootstrap_ui_html_response(['Successfully '. (empty($postId) ? 'added' : 'updated the') .' blog post.']))->setType(UiResponse::TYPE_SUCCESS);
        } catch (\Exception $e) {
            $response = (bootstrap_ui_html_response([$e->getMessage()]))->setType(UiResponse::TYPE_ERROR);
        }
        return redirect(url()->current())->with('UiResponse', $response);
    }
    
    /**
     * @param Request $request
     * @param Sdk     $sdk
     * @param string  $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function editPost(Request $request, Sdk $sdk, string $id)
    {
        $this->data['page']['title'] = 'Edit Post | BlogAdmin';
        $this->data['page']['header'] = ['title' => 'Edit Post | Blog'];
        $this->data['breadCrumbs'] = [
            'crumbs' => [
                ['text' => 'Home', 'href' => route('blog')],
                ['text' => 'Edit Post', 'href' => route('blog.admin.new-post'), 'isActive' => true],
            ]
        ];

        $this->blogViewComposer($this->data, $request, $sdk, $blogOwner);
    
        $this->setViewUiResponse($request);
        $this->data['categories'] = $this->getBlogCategories($sdk);
        $this->data['post'] = $this->getPost($sdk, $id);
        return view('modules-ecommerce::blog.new-post', $this->data);
    }
    
    /**
     * @param Request $request
     * @param Sdk     $sdk
     * @param string  $id
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updatePost(Request $request, Sdk $sdk, string $id)
    {
        $request->request->set('post_id', $id);
        return $this->createPost($request, $sdk);
    }
    
    /**
     * @param Request $request
     * @param Sdk     $sdk
     * @param string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deletePostXhr(Request $request, Sdk $sdk, string $id)
    {
        $query = $sdk->createBlogResource()->send('DELETE', ['posts', $id]);
        if (!$query->isSuccessful()) {
            // do something here
            throw new DeletingFailedException($query->errors[0]['title'] ?? 'Could not delete the selected post.');
        }
        return response()->json($query->getData());
    }
    
    /**
     * @param Sdk    $sdk
     * @param string $id
     *
     * @return array|mixed|object
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getPost(Sdk $sdk, string $id)
    {
        $response = $sdk->createBlogResource()->send('get', ['posts', $id]);
        if (!$response->isSuccessful()) {
            throw new DeletingFailedException($query->errors[0]['title'] ?? 'Could not load the selected post.');
        }
        return $response->getData(true);
    }

    public function redirectRoute(Request $request)
    {
        return '';
    }

    private function blogViewComposer(&$viewData, Request $request, Sdk $sdk, $company) {

        $domain = $request->session()->get('domain');
        $user = $request->user();

        //add blogCategories
        $categories = Cache::remember('business.blog-categories.'.$company->id, 30, function () use ($sdk, $company) {
            $query = $sdk->createBlogResource()->send('GET', [$company->id, 'categories']);
            # get the response
            if (!$query->isSuccessful() || empty($query->getData())) {
                return null;
            }
            return collect($query->getData())->map(function ($category) {
                return (object) $category;
            });
        });
        $viewData["blogCategories"] = $categories;

        //add  blogOwner
        $viewData["blogOwner"] = $company;

        //add blogDomain
        //$blogDomain = 'https://'.$domain->prefix . '.' . $domain->domain['data']['domain'].'/blog';
        $blogDomain = 'https://'.$domain->prefix . '.blog.' . $domain->domain['data']['domain'].'/';
        $viewData["blogDomain"] = $blogDomain;

        //add blogSettings & blogName
        $settings = Dashboard::getBlogSettings((array) $company->extra_data);
        $viewData["blogSettings"] = $settings;
        $defaultBlogName = $company->name . ' Blog';
        $viewData["blogName"] = !empty($settings['blog_name']) ? $settings['blog_name'] : $defaultBlogName;

        //add blogAdministrator & partner
        $blogOwner = (object) $domain->owner['data'];
        $blogAdministrator = !empty($user) && $user->company['data']['id'] === $blogOwner->id ? $user : null;
        $firstUser = !empty($blogOwner->users['data']) && !empty($blogOwner->users['data'][0]) ? (object) $blogOwner->users['data'][0] : null;
        $partner = !empty($firstUser) && !empty($firstUser->partner['data']) ? (object) $firstUser->partner['data'] : null;

        if (!empty($blogAdministrator)) {
            $viewData["blogAdministrator"] = $blogAdministrator;
        }
        if (!empty($partner)) {
            $viewData["partnerHubConfig"] = $partner->extra_data['hubConfig'] ?? [];
        }


    }



}