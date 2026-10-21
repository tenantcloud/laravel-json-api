<?php

namespace Tests;

use Illuminate\Contracts\Container\Container;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\Route;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use TenantCloud\JsonApi\JsonApiRegistry;
use Tests\Mocks\FilterTestRequest;
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
			->setContainer(app(Container::class))
			->setRouteResolver(fn () => new Route(['GET'], '/api_config', ['/api_config']));

		self::assertTrue($request->matchCurrentVersion(['==1.0']));
		self::assertFalse($request->matchCurrentVersion(['==2.0']));
	}

	public function testRequestMatchCurrentVersionWithLatestVersion(): void
	{
		/** @var ListTestRequest $request */
		$request = ListTestRequest::create(uri: 'test')
			->setContainer(app(Container::class))
			->setRouteResolver(fn () => new Route(['GET'], '/api_config', ['/api_config']));

		self::assertFalse($request->matchCurrentVersion(['==1.0']));
		self::assertFalse($request->matchCurrentVersion(['==2.0']));
		self::assertTrue($request->matchCurrentVersion(['>2.0']));
	}

	public static function filterFormsProvider(): iterable
	{
		yield 'nested' => [fn (string $value) => ['lease' => ['expires_in' => $value]]];

		yield 'dotted' => [fn (string $value) => ['lease.expires_in' => $value]];
	}

	#[DataProvider('filterFormsProvider')]
	public function testDottedAndNestedFiltersAreTheSame(callable $filter): void
	{
		$request = $this->makeFilterRequest($filter('30_days'));

		$request->validateResolved();

		self::assertSame(['lease' => ['expires_in' => '30_days']], $request->filterBeforeValidation);
		self::assertSame(['lease' => ['expires_in' => '30_days']], $request->context()->filters()->all());
	}

	#[DataProvider('filterFormsProvider')]
	public function testDottedAndNestedFiltersAreValidated(callable $filter): void
	{
		$request = $this->makeFilterRequest($filter('90'));

		try {
			$request->validateResolved();

			self::fail('Validation exception was expected.');
		} catch (ValidationException $exception) {
			self::assertArrayHasKey('filter.lease.expires_in', $exception->errors());
		}
	}

	private function makeFilterRequest(array $filter): FilterTestRequest
	{
		return FilterTestRequest::create(uri: 'test', parameters: ['filter' => $filter])
			->setContainer(app(Container::class))
			->setRedirector(app(Redirector::class))
			->setRouteResolver(fn () => new Route(['GET'], '/api_config', ['/api_config']));
	}
}
