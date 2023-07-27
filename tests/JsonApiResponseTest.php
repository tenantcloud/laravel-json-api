<?php

namespace Tests;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use TenantCloud\APIVersioning\Version\LatestVersion;
use TenantCloud\JsonApi\DTO\ApiRequestDTO;
use TenantCloud\JsonApi\JsonApiRegistry;
use TenantCloud\JsonApi\JsonApiResponse;
use TenantCloud\JsonApi\RequestContext;
use Tests\Mocks\ListTestRequest;
use Tests\Mocks\TestUser;
use Tests\Mocks\TestUserSchema;
use Tests\Mocks\TestUserTransformer;

/**
 * @see JsonApiResponse
 */
class JsonApiResponseTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		$jsonApiSchemaRegistry = $this->app->make(JsonApiRegistry::class);
		$jsonApiSchemaRegistry->register(app(TestUserSchema::class));
	}

	public function testCollection(): void
	{
		$user = new TestUser(1, 'name');
		$users = collect([$user]);

		$data = ApiRequestDTO::create()->setFields([
			'user' => ['id', 'name'],
		]);
		$type = app(TestUserSchema::class)->getResourceType();

		$context = new RequestContext($user, $data, $type);
		$context->fields()->addValidated($type, ['id', 'name']);

		$response = (new JsonApiResponse($users, new TestUserTransformer()))
			->setContext($context)
			->serialize();

		$this->assertSame($type, Arr::get($response, 'data.0.type'));
		$this->assertEquals($user->id, Arr::get($response, 'data.0.id'));
		$this->assertSame($user->name, Arr::get($response, 'data.0.attributes.name'));
		$this->assertNull(Arr::get($response, 'meta.pagination'));
	}

	public function testNull(): void
	{
		$user = new TestUser(1, 'name');

		$data = ApiRequestDTO::create()->setFields([
			'user' => ['id', 'name'],
		]);
		$type = app(TestUserSchema::class)->getResourceType();

		$context = new RequestContext($user, $data, $type);
		$context->fields()->addValidated($type, ['id', 'name']);

		$response = (new JsonApiResponse(null, new TestUserTransformer()))
			->setContext($context)
			->setResponseCode(Response::HTTP_CREATED);

		$request = Request::createFrom(request());
		$jsonResponse = $response->toResponse($request);

		$this->assertSame(Response::HTTP_CREATED, $jsonResponse->status());
	}

	public function testPagination(): void
	{
		$user = new TestUser(1, 'name');
		$users = new LengthAwarePaginator([$user], 1, 15);

		$data = ApiRequestDTO::create();
		$type = app(TestUserSchema::class)->getResourceType();

		$context = new RequestContext($user, $data, $type);
		$context->fields()->addValidated($type, ['id', 'name']);

		$response = (new JsonApiResponse($users, new TestUserTransformer()))
			->setContext($context)
			->serialize();

		$this->assertSame($type, Arr::get($response, 'data.0.type'));
		$this->assertEquals($user->id, Arr::get($response, 'data.0.id'));
		$this->assertSame($user->name, Arr::get($response, 'data.0.attributes.name'));
		$this->assertSame(1, Arr::get($response, 'meta.pagination.total'));
	}

	public function testItem(): void
	{
		$user = new TestUser(1, 'name');

		$data = ApiRequestDTO::create();
		$type = app(TestUserSchema::class)->getResourceType();

		$context = new RequestContext($user, $data, $type);
		$context->fields()->addValidated($type, ['id', 'name']);

		$response = (new JsonApiResponse($user, new TestUserTransformer()))
			->setContext($context)
			->serialize();

		$this->assertSame($type, Arr::get($response, 'data.type'));
		$this->assertEquals($user->id, Arr::get($response, 'data.id'));
		$this->assertSame($user->name, Arr::get($response, 'data.attributes.name'));
	}

	public function testWithMeta(): void
	{
		$user = new TestUser(1, 'name');
		$users = new LengthAwarePaginator([$user], 1, 15);

		$data = ApiRequestDTO::create();
		$type = app(TestUserSchema::class)->getResourceType();

		$context = new RequestContext($user, $data, $type);
		$context->fields()->addValidated($type, ['id', 'name']);

		$response = (new JsonApiResponse($users, new TestUserTransformer()))
			->setContext($context)
			->setMeta(['key' => 'value'])
			->serialize();

		$this->assertSame($type, Arr::get($response, 'data.0.type'));
		$this->assertEquals($user->id, Arr::get($response, 'data.0.id'));
		$this->assertSame($user->name, Arr::get($response, 'data.0.attributes.name'));
		$this->assertSame(1, Arr::get($response, 'meta.pagination.total'));
		$this->assertSame('value', Arr::get($response, 'meta.key'));
	}

	public function testRequestWithLatestVersion(): void
	{
		$request = ListTestRequest::create('test')->setContainer(app(\Illuminate\Contracts\Container\Container::class))
			->setRouteResolver(fn () => new Route(['POST'], '/api_config', ['/api_config']));

		$request->validateResolved();

		self::assertEquals(new LatestVersion(), $request->context()->version());
	}
}
