<?php

namespace Tests;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use TenantCloud\JsonApi\DTO\ApiRequestDTO;
use TenantCloud\JsonApi\JsonApiRegistry;
use TenantCloud\JsonApi\JsonApiResponse;
use TenantCloud\JsonApi\JsonApiSerializer;
use TenantCloud\JsonApi\RequestContext;
use Tests\Mocks\TestUser;
use Tests\Mocks\TestUserMetaSchema;
use Tests\Mocks\TestUserSchema;
use Tests\Mocks\TestUserTransformer;

/**
 * @see JsonApiSerializer
 */
class JsonApiSerializerTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		$jsonApiSchemaRegistry = $this->app->make(JsonApiRegistry::class);
		$jsonApiSchemaRegistry->register(app(TestUserSchema::class));
		$jsonApiSchemaRegistry->register(app(TestUserMetaSchema::class));
	}

	public function testListWithMeta(): void
	{
		$user = new TestUser(1, 'name');
		$user2 = new TestUser(2, 'name2');
		$users = new LengthAwarePaginator([$user, $user2], 1, 15);

		$data = ApiRequestDTO::create()
			->setInclude(['meta']);
		$type = app(TestUserSchema::class)->getResourceType();
		$field = $this->faker->randomElement(['is_valid', 'is_invalid']);

		$context = new RequestContext($user, $data, $type);
		$context->fields()
			->addValidated($type, ['id', 'name'])
			->addValidated(app(TestUserMetaSchema::class)->getResourceType(), ['id', $field]);
		$context->includes()->addValidated('meta');

		$response = (new JsonApiResponse($users, new TestUserTransformer()))
			->setContext($context)
			->setMeta(['key' => 'value'])
			->serialize();

		$this->assertSame($type, Arr::get($response, 'data.0.type'));
		$this->assertEquals($user->id, Arr::get($response, 'data.0.id'));
		$this->assertSame($user->name, Arr::get($response, 'data.0.attributes.name'));
		$this->assertSame([$field], array_keys(Arr::get($response, 'data.0.meta')));
		$this->assertSame($field === 'is_valid' && $user->valid, Arr::get($response, 'data.0.meta.' . $field));
		$this->assertSame($field === 'is_valid' && $user->valid, Arr::get($response, 'data.1.meta.' . $field));
		$this->assertArrayNotHasKey('included', $response);
		$this->assertArrayNotHasKey('relationships', Arr::get($response, 'data.0'));
		$this->assertSame(1, Arr::get($response, 'meta.pagination.total'));
		$this->assertSame('value', Arr::get($response, 'meta.key'));
	}

	public function testItemWithMeta(): void
	{
		$user = new TestUser(1, 'name');

		$data = ApiRequestDTO::create()
			->setInclude(['meta']);
		$type = app(TestUserSchema::class)->getResourceType();
		$field = $this->faker->randomElement(['is_valid', 'is_invalid']);

		$context = new RequestContext($user, $data, $type);
		$context->fields()
			->addValidated($type, ['id', 'name'])
			->addValidated(app(TestUserMetaSchema::class)->getResourceType(), ['id', $field]);
		$context->includes()->addValidated('meta');

		$response = (new JsonApiResponse($user, new TestUserTransformer()))
			->setContext($context)
			->setMeta(['key' => 'value'])
			->serialize();

		$this->assertSame($type, Arr::get($response, 'data.type'));
		$this->assertEquals($user->id, Arr::get($response, 'data.id'));
		$this->assertSame($user->name, Arr::get($response, 'data.attributes.name'));
		$this->assertSame([$field], array_keys(Arr::get($response, 'data.meta')));
		$this->assertSame($field === 'is_valid' && $user->valid, Arr::get($response, 'data.meta.' . $field));
		$this->assertSame('value', Arr::get($response, 'meta.key'));
	}

	public function testListWithoutMeta(): void
	{
		$user = new TestUser(1, 'name');
		$user2 = new TestUser(2, 'name2');
		$users = new LengthAwarePaginator([$user, $user2], 1, 15);

		$data = ApiRequestDTO::create();
		$type = app(TestUserSchema::class)->getResourceType();
		$field = $this->faker->randomElement(['is_valid', 'is_invalid']);

		$context = new RequestContext($user, $data, $type);
		$context->fields()
			->addValidated($type, ['id', 'name'])
			->addValidated(app(TestUserMetaSchema::class)->getResourceType(), ['id', $field]);

		$response = (new JsonApiResponse($users, new TestUserTransformer()))
			->setContext($context)
			->setMeta(['key' => 'value'])
			->serialize();

		$this->assertSame($type, Arr::get($response, 'data.0.type'));
		$this->assertEquals($user->id, Arr::get($response, 'data.0.id'));
		$this->assertSame($user->name, Arr::get($response, 'data.0.attributes.name'));
		$this->assertNull(Arr::get($response, 'data.0.meta'));
		$this->assertNull(Arr::get($response, 'data.1.meta'));
		$this->assertSame(1, Arr::get($response, 'meta.pagination.total'));
		$this->assertSame('value', Arr::get($response, 'meta.key'));
	}
}
