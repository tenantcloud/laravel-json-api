<?php

namespace Tests;

use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\TestCase as BaseTestCase;
use TenantCloud\JsonApi\JsonApiServiceProvider;

class TestCase extends BaseTestCase
{
	use WithFaker;

	protected function assertArrayHasKeys(array $keys, array $item): void
	{
		foreach ($keys as $key) {
			$this->assertArrayHasKey($key, $item);
		}
	}

	protected function assertArrayNotHasKeys(array $keys, array $item): void
	{
		foreach ($keys as $key) {
			$this->assertArrayNotHasKey($key, $item);
		}
	}

	protected function getPackageProviders($app)
	{
		return [
			JsonApiServiceProvider::class,
		];
	}
}
