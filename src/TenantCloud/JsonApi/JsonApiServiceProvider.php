<?php

namespace TenantCloud\JsonApi;

use Illuminate\Support\ServiceProvider;

class JsonApiServiceProvider extends ServiceProvider
{
	public function register()
	{
		$this->app->singleton(JsonApiRegistry::class);
	}
}
