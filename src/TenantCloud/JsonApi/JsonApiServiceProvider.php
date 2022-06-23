<?php

namespace TenantCloud\JsonApi;

use Illuminate\Support\ServiceProvider;

class JsonApiServiceProvider extends ServiceProvider
{
	public function boot()
	{
		$this->publishes([
			__DIR__ . '/config/json-api.php' => config_path('json-api.php'),
		]);
	}

	public function register()
	{
		parent::register();

		$this->mergeConfigFrom(__DIR__ . '/config/json-api.php', 'json-api');

		$this->app->singleton(JsonApiRegistry::class);
	}
}
