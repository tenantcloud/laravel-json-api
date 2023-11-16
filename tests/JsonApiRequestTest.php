<?php

namespace Tests;

use Illuminate\Routing\Route;
use TenantCloud\JsonApi\JsonApiRegistry;
use Tests\Mocks\ListTestRequest;
use Tests\Mocks\TestUserSchema;

class JsonApiRequestTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		$jsonApiSchemaRegistry = $this->app->make(JsonApiRegistry::class);
		$jsonApiSchemaRegistry->register(app(TestUserSchema::class));
	}

	public function testRequestMatchCurrentVersion(): void
	{
		/** @var ListTestRequest $request */
		$request = ListTestRequest::create(uri: 'test', server: $this->transformHeadersToServerVars(['Version' => '1.0']))
			->setContainer(app(\Illuminate\Contracts\Container\Container::class))
			->setRouteResolver(fn () => new Route(['GET'], '/api_config', ['/api_config']));

		self::assertTrue($request->matchCurrentVersion(['==1.0']));
		self::assertFalse($request->matchCurrentVersion(['==2.0']));
	}

	public function testRequestMatchCurrentVersionWithLatestVersion(): void
	{
		/** @var ListTestRequest $request */
		$request = ListTestRequest::create(uri: 'test')
			->setContainer(app(\Illuminate\Contracts\Container\Container::class))
			->setRouteResolver(fn () => new Route(['GET'], '/api_config', ['/api_config']));

		self::assertFalse($request->matchCurrentVersion(['==1.0']));
		self::assertFalse($request->matchCurrentVersion(['==2.0']));
		self::assertTrue($request->matchCurrentVersion(['>2.0']));
	}
}
