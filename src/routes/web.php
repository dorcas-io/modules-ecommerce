<?php

/*Route::group(['namespace' => 'Dorcas\ModulesEcommerce\Http\Controllers', 'middleware' => ['web']], function() {
    Route::get('sales', 'ModulesEcommerceController@index')->name('sales');
});*/


Route::group(['middleware' => ['auth'], 'namespace' => 'ECommerce', 'prefix' => 'apps/ecommerce'], function () {
    Route::get('/', 'ECommerce@index')->name('apps.ecommerce');
    
    Route::group(['prefix' => 'adverts'], function () {
        Route::get('/', 'Adverts@index')->name('apps.ecommerce.adverts');
        Route::post('/', 'Adverts@post');
    });
    
    Route::group(['prefix' => 'blog', 'namespace' => 'Blog'], function () {
        Route::get('/', 'Dashboard@index')->name('apps.ecommerce.blog');
        Route::post('/', 'Dashboard@blogSettings');
        Route::get('/categories', 'Categories@index')->name('apps.ecommerce.blog.categories');
    });
    
    Route::get('/domains', 'Domains\Domains@index')->name('apps.ecommerce.domains');
    Route::post('/domains', 'Domains\Domains@create');
    Route::group(['middleware' => ['pay_gate']], function () {
        Route::post('/domains/purchase', 'Domains\Domains@purchaseDomain')->name('apps.ecommerce.domains-purchase');
    });
    
    Route::get('/emails', 'Emails@index')->name('apps.ecommerce.emails');
    Route::post('/emails', 'Emails@post');
    Route::get('/online-store', 'OnlineStore@index')->name('apps.ecommerce.store');
    Route::get('/online-store/dashboard', 'OnlineStore@dashboard')->name('apps.ecommerce.store.dashboard');
    Route::post('/online-store/dashboard', 'OnlineStore@storeSettings');
    Route::get('/website', 'Website@index')->name('apps.ecommerce.website');
    Route::post('/website', 'Website@post');
});

Route::group(['middleware' => ['auth'], 'namespace' => 'Ajax', 'prefix' => 'xhr'], function () {

	Route::delete('/ecommerce/adverts/{id}', 'ECommerce\Adverts@delete');
    
    Route::post('/ecommerce/blog/categories', 'ECommerce\Blog@createCategory');
    Route::delete('/ecommerce/blog/categories/{id}', 'ECommerce\Blog@deleteCategory');
    Route::put('/ecommerce/blog/categories/{id}', 'ECommerce\Blog@updateCategory');
    
    Route::post('/ecommerce/blog', 'ECommerce\Blog@searchPosts');
    Route::delete('/ecommerce/blog/{id}', 'ECommerce\Blog@deletePost');
    
    Route::delete('/ecommerce/domains/issuances/{id}', 'ECommerce\Issuances@releaseSubdomain');
    Route::get('/ecommerce/domains/issuances/availability', 'ECommerce\Issuances@checkAvailability');
    
    Route::get('/ecommerce/domains/availability', 'ECommerce\Domains@checkAvailability');
    Route::delete('/ecommerce/domains/{id}', 'ECommerce\Domains@releaseDomain');
    
    Route::delete('/ecommerce/emails/{username}', 'ECommerce\Emails@delete');


    });

Route::group(['namespace' => 'Blog', 'middleware' => ['blog_verifier']], function () {
    Route::group(['prefix' => 'blog'], function () {
        Route::get('/', 'Home@index')->name('blog');
        Route::get('/posts', 'Home@index')->name('blog.posts');
        Route::get('/posts/{id}', 'Home@postDetails')->name('blog.posts.details');
        Route::get('/categories', 'Home@categories')->name('blog.categories');
        Route::get('/categories/{id}', 'Home@index')->name('blog.categories.single');
    });
    
    Route::group(['prefix' => 'blog-admin', 'middleware' => ['auth']], function () {
        Route::get('/new-post', 'Posts@newPost')->name('blog.admin.new-post');
        Route::post('/new-post', 'Posts@createPost');
        Route::get('/{id}/edit', 'Posts@editPost')->name('blog.admin.edit-post');
        Route::post('/{id}/edit', 'Posts@updatePost');
        
        Route::delete('/xhr/posts/{id}', 'Posts@deletePostXhr');
    });
});



?>