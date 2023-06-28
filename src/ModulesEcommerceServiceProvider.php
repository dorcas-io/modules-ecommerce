<?php

namespace Dorcas\ModulesEcommerce;
use Illuminate\Support\ServiceProvider;

class ModulesEcommerceServiceProvider extends ServiceProvider {

	public function boot()
	{
		$this->loadRoutesFrom(__DIR__.'/routes/web.php');
		$this->loadViewsFrom(__DIR__.'/resources/views', 'modules-ecommerce');
		$this->publishes([
			__DIR__.'/config/modules-ecommerce.php' => config_path('modules-ecommerce.php'),
		], 'dorcas-modules');
		$this->publishes([
			__DIR__.'/assets' => public_path('vendor/modules-ecommerce')
		], 'dorcas-modules');
	}

	public function register()
	{
		//add menu config
		$this->mergeConfigFrom(
	        __DIR__.'/config/navigation-menu.php', 'navigation-menu.modules-ecommerce.sub-menu'
	     );
	}

}


?>