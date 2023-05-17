<?php
use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

$request = app()->make('request');
$currentHost = $request->header('host');
$defaultUri = new Uri(config('app.url'));
try {
    //dd([$currentHost,$defaultUri->getHost()]);
    $domainInfo = (new App\Http\Middleware\ResolveCustomSubdomain())->splitHost($currentHost);
    //dd(array($currentHost,$domainInfo, $domainInfo->getService()));
} catch (RuntimeException $e) {
    $domainInfo = null;
}



Route::get('/mec/ecommerce-domains-issuances-availability-register', 'Dorcas\ModulesEcommerce\Http\Controllers\ModulesEcommerceController@checkAvailabilitySubdomainRegister');


Route::group(['namespace' => 'Dorcas\ModulesEcommerce\Http\Controllers', 'middleware' => ['web','auth'], 'prefix' => 'mec'], function() {
    Route::get('ecommerce-main', 'ModulesEcommerceController@index')->name('ecommerce-main');
    Route::get('ecommerce-domains', 'ModulesEcommerceController@domains')->name('ecommerce-domains');
    Route::post('ecommerce-domains', 'ModulesEcommerceController@create');
    Route::delete('/ecommerce-domains-issuances/{id}', 'ModulesEcommerceController@releaseSubdomain');
    Route::get('/ecommerce-domains-issuances-availability', 'ModulesEcommerceController@checkAvailabilitySubdomain');
    Route::get('/ecommerce-domains-availability', 'ModulesEcommerceController@checkAvailability');
    Route::delete('/ecommerce-domains/{id}', 'ModulesEcommerceController@releaseDomain');
    Route::get('/ecommerce-website', 'ModulesEcommerceWebsiteController@index')->name('ecommerce-website');
    Route::post('/ecommerce-website', 'ModulesEcommerceWebsiteController@post');
    Route::get('/ecommerce-emails', 'ModulesEcommerceEmailController@index')->name('ecommerce-emails');
    Route::post('/ecommerce-emails', 'ModulesEcommerceEmailController@post');
    Route::delete('/ecommerce-emails/{username}', 'ModulesEcommerceEmailController@delete');
    Route::get('/ecommerce-adverts', 'ModulesEcommerceAdvertsController@index')->name('ecommerce-adverts');
    Route::post('/ecommerce-adverts', 'ModulesEcommerceAdvertsController@post');
    Route::delete('/ecommerce-adverts/{id}', 'ModulesEcommerceAdvertsController@delete');
    Route::get('/ecommerce-blog', 'ModulesEcommerceBlogController@index')->name('ecommerce-blog');
    Route::post('/ecommerce-blog', 'ModulesEcommerceBlogController@blogSettings');
    Route::post('/ecommerce-blog-categories', 'ModulesEcommerceBlogController@createCategory');
    Route::delete('/ecommerce-blog-categories/{id}', 'ModulesEcommerceBlogController@deleteCategory');
    Route::put('/ecommerce-blog-categories/{id}', 'ModulesEcommerceBlogController@updateCategory');
    Route::get('/ecommerce-store', 'ModulesEcommerceStoreController@index')->name('ecommerce-store');
    Route::post('/ecommerce-store', 'ModulesEcommerceStoreController@storeSettings');

    Route::post('/payment-verify', 'ModulesEcommerceController@verifyTransaction');

});


$storeSubDomain = !empty($domainInfo) && $domainInfo->getService() === 'store' ?
    $currentHost : 'store' . $defaultUri->getHost();

Route::prefix('store')->group(function () {
    Route::get('/', 'Dorcas\ModulesEcommerce\Http\Controllers\ModulesEcommerceStore@redirectRoute');
    Route::get('/categories/{id?}', 'Dorcas\ModulesEcommerce\Http\Controllers\ModulesEcommerceStore@redirectRoute');
    Route::get('/products/{id?}', 'Dorcas\ModulesEcommerce\Http\Controllers\ModulesEcommerceStore@redirectRoute');
    Route::get('/cart', 'Dorcas\ModulesEcommerce\Http\Controllers\ModulesEcommerceStore@redirectRoute');
});

Route::domain($storeSubDomain)->namespace('Dorcas\ModulesEcommerce\Http\Controllers')->middleware(['web','web_store'])->group(function () {
    Route::get('/', 'ModulesEcommerceStore@index')->name('webstore');
    Route::get('/categories', 'ModulesEcommerceStore@categories')->name('webstore.categories');
    Route::get('/categories/{id}', 'ModulesEcommerceStore@index')->name('webstore.categories.single');
    Route::get('/products', 'ModulesEcommerceStore@products')->name('webstore.products');
    Route::get('/products/{id}', 'ModulesEcommerceStore@productDetails')->name('webstore.products.details');
    Route::get('/cart', 'ModulesEcommerceStore@cart')->name('webstore.cart');
    Route::get('/product-quick-view/{id}', 'ModulesEcommerceStore@quickView')->name('webstore.quick-view');
    Route::delete('/xhr/cart', 'ModulesEcommerceStore@removeFromCartXhr');
    Route::post('/xhr/cart', 'ModulesEcommerceStore@addToCartXhr');
    Route::post('/xhr/cart/checkout', 'ModulesEcommerceStore@checkoutXhr');
    Route::get('/xhr/cart/update-quantities', 'ModulesEcommerceStore@updateCartQuantitiesXhr');
});



$blogSubDomain = !empty($domainInfo) && $domainInfo->getService() === 'blog' ?
    $currentHost : 'blog' . $defaultUri->getHost();


Route::prefix('blog')->group(function () {
    Route::get('/', 'Dorcas\ModulesEcommerce\Http\Controllers\ModulesEcommerceBlog@redirectRoute');
    Route::get('/posts/{id?}', 'Dorcas\ModulesEcommerce\Http\Controllers\ModulesEcommerceBlog@redirectRoute');
    Route::get('/categories/{id?}', 'Dorcas\ModulesEcommerce\Http\Controllers\ModulesEcommerceBlog@redirectRoute');
    Route::get('/new-post', 'Dorcas\ModulesEcommerce\Http\Controllers\ModulesEcommerceBlog@redirectRoute');
});

Route::domain($blogSubDomain)->namespace('Dorcas\ModulesEcommerce\Http\Controllers')->middleware(['web','blog_verifier'])->group(function () {

    Route::get('/', 'ModulesEcommerceBlog@index')->name('blog');
    Route::get('/posts', 'ModulesEcommerceBlog@index')->name('blog.posts');
    Route::get('/posts/{id}', 'ModulesEcommerceBlog@postDetails')->name('blog.posts.details');
    Route::get('/categories', 'ModulesEcommerceBlog@categories')->name('blog.categories');
    Route::get('/categories/{id}', 'ModulesEcommerceBlog@index')->name('blog.categories.single');

    Route::get('/admin-blog/new-post', 'ModulesEcommerceBlog@newPost')->name('blog.admin.new-post');
    Route::post('/admin-blog/new-post', 'ModulesEcommerceBlog@createPost');
    Route::get('/admin-blog/{id}/edit', 'ModulesEcommerceBlog@editPost')->name('blog.admin.edit-post');
    Route::post('/admin-blog/{id}/edit', 'ModulesEcommerceBlog@updatePost');
    
    Route::delete('/admin-blog/xhr/posts/{id}', 'ModulesEcommerceBlog@deletePostXhr');

    Route::get('/{referralData}', function($referralData)
    {
        if (substr_count($referralData, "/") > 0) {
            $values = explode("/", $referralData);
            if ($values[0]=="r") {
                $referrer = [];
                $referrer["value"] = $values[1];
                if (ctype_digit($values[1])) {
                    $referrer["mode"] = "id";
                    //return $referrer;
                    $app = app();
                    $controller = $app->make('Dorcas\ModulesEcommerce\Http\Controllers\ModulesEcommerceBlog');
                    $request = $app->make('request');
                    $request->session()->put('dorcas_referrer', $referrer);
                    return $controller->callAction('index', $parameters = array($request));
                } elseif (!ctype_digit($values[1])  && !is_numeric($values[1])) {
                    $referrer["mode"] = "username";
                    //return $referrer;
                    $app = app();
                    $controller = $app->make('Dorcas\ModulesEcommerce\Http\Controllers\ModulesEcommerceBlog');
                    $request = $app->make('request');
                    $request->session()->put('dorcas_referrer', $referrer);
                    return $controller->callAction('index', $parameters = array($request));
                }
            }
        }
    })->where('referralData', '.*');


});




/*
Route::group(['middleware' => ['auth'], 'namespace' => 'ECommerce', 'prefix' => 'apps/ecommerce'], function () {

    
    
    Route::group(['middleware' => ['pay_gate']], function () {
        Route::post('/domains/purchase', 'Domains\Domains@purchaseDomain')->name('apps.ecommerce.domains-purchase');
    });
    
});

Route::group(['middleware' => ['auth'], 'namespace' => 'Ajax', 'prefix' => 'xhr'], function () {

	Route::delete('/ecommerce/adverts/{id}', 'ECommerce\Adverts@delete');
    
    Route::post('/ecommerce/blog', 'ECommerce\Blog@searchPosts');
    Route::delete('/ecommerce/blog/{id}', 'ECommerce\Blog@deletePost');


    });

Route::group(['namespace' => 'Blog', 'middleware' => ['blog_verifier']], function () {
    Route::group(['prefix' => 'blog'], function () {
        Route::get('/', 'Home@index')->name('blog');
        Route::get('/posts', 'Home@index')->name('blog.posts');
        Route::get('/posts/{id}', 'Home@postDetails')->name('blog.posts.details');
        Route::get('/categories', 'Home@categories')->name('blog.categories');
        Route::get('/categories/{id}', 'Home@index')->name('blog.categories.single');
    });
    
    Route::group(['prefix' => 'admin-blog', 'middleware' => ['auth']], function () {
        Route::get('/new-post', 'Posts@newPost')->name('blog.admin.new-post');
        Route::post('/new-post', 'Posts@createPost');
        Route::get('/{id}/edit', 'Posts@editPost')->name('blog.admin.edit-post');
        Route::post('/{id}/edit', 'Posts@updatePost');
        
        Route::delete('/xhr/posts/{id}', 'Posts@deletePostXhr');
    });
});
*/


?>