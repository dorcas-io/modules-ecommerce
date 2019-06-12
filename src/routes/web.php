<?php

Route::group(['namespace' => 'Dorcas\ModulesEcommerce\Http\Controllers', 'middleware' => ['web','auth'], 'prefix' => 'mec'], function() {
    Route::get('ecommerce-main', 'ModulesEcommerceController@index')->name('ecommerce-main');
    Route::get('ecommerce-domains', 'ModulesEcommerceController@domains')->name('ecommerce-domains');
    Route::post('ecommerce-domains', 'ModulesEcommerceController@create');
    Route::delete('/ecommerce-domains-issuances/{id}', 'ModulesEcommerceController@releaseSubdomain');
    Route::get('/ecommerce-domains-issuances-availability', 'ModulesEcommerceController@checkAvailability');
    Route::get('/ecommerce-domains-availability', 'ModulesEcommerceController@checkAvailability');
    Route::delete('/ecommerce-domains/{id}', 'ModulesEcommerceController@releaseDomain');
    Route::get('/ecommerce-website', 'ModulesEcommerceWebsiteController@index')->name('ecommerce-website');
    Route::post('/ecommerce-website', 'ModulesEcommerceWebsiteController@post');
    Route::get('/ecommerce-emails', 'ModulesEcommerceEmailController@index')->name('ecommerce-emails');
    Route::post('/ecommerce-emails', 'ModulesEcommerceEmailController@post');
    Route::delete('/ecommerce-emails/{username}', 'ModulesEcommerceEmailController@delete');
    Route::get('/ecommerce-adverts', 'ModulesEcommerceAdvertsController@index')->name('ecommerce-adverts');
    Route::post('/ecommerce-adverts', 'ModulesEcommerceAdvertsController@post');
    Route::get('/ecommerce-blog', 'ModulesEcommerceBlogController@index')->name('ecommerce-blog');
    Route::post('/ecommerce-blog', 'ModulesEcommerceBlogController@blogSettings');
    Route::post('/ecommerce-blog-categories', 'ECommerce\Blog@createCategory');
    Route::delete('/ecommerce-blog-categories/{id}', 'ECommerce\Blog@deleteCategory');
    Route::put('/ecommerce-blog-categories/{id}', 'ECommerce\Blog@updateCategory');

});

/*
Route::group(['middleware' => ['auth'], 'namespace' => 'ECommerce', 'prefix' => 'apps/ecommerce'], function () {

    
    Route::group(['prefix' => 'adverts'], function () {
        
    });
    
    
    Route::group(['middleware' => ['pay_gate']], function () {
        Route::post('/domains/purchase', 'Domains\Domains@purchaseDomain')->name('apps.ecommerce.domains-purchase');
    });
    

    Route::get('/online-store', 'OnlineStore@index')->name('apps.ecommerce.store');
    Route::get('/online-store/dashboard', 'OnlineStore@dashboard')->name('apps.ecommerce.store.dashboard');
    Route::post('/online-store/dashboard', 'OnlineStore@storeSettings');
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
    
    Route::group(['prefix' => 'blog-admin', 'middleware' => ['auth']], function () {
        Route::get('/new-post', 'Posts@newPost')->name('blog.admin.new-post');
        Route::post('/new-post', 'Posts@createPost');
        Route::get('/{id}/edit', 'Posts@editPost')->name('blog.admin.edit-post');
        Route::post('/{id}/edit', 'Posts@updatePost');
        
        Route::delete('/xhr/posts/{id}', 'Posts@deletePostXhr');
    });
});
*/


?>