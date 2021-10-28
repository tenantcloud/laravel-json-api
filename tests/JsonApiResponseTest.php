<?php

namespace Tests;

use TenantCloud\JsonApi\DTO\ApiRequestDTO;
use TenantCloud\JsonApi\JsonApiResponse;
use TenantCloud\JsonApi\RequestContext;
use TenantCloud\JsonApi\Schema\User\UserSchema;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Tests\Mocks\TestUserTransformer;
use Tests\TestCase;

/**
 * Class JsonApiResponseTest
 *
 * @see JsonApiResponse
 */
class JsonApiResponseTest extends TestCase
{
	use DatabaseTransactions;

	public function testCollection(): void
	{
		$user = $this->seeder->generateLandlord();
		$users = User::query()->where('id', $user->id)->get();

		$data = ApiRequestDTO::create()->setFields([
			'user' => ['id', 'name'],
		]);
		$type = app(UserSchema::class)->getResourceType();

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
		$user = $this->seeder->generateLandlord();

		$data = ApiRequestDTO::create()->setFields([
			'user' => ['id', 'name'],
		]);
		$type = app(UserSchema::class)->getResourceType();

		$context = new RequestContext($user, $data, $type);
		$context->fields()->addValidated($type, ['id', 'name']);

		$response = (new JsonApiResponse(null, new TestUserTransformer()))
			->setContext($context)
			->respondWithStatusCreated();

		$request = Request::createFrom(request());
		$jsonResponse = $response->toResponse($request);

		$this->assertSame(Response::HTTP_CREATED, $jsonResponse->status());
	}

	public function testPagination(): void
	{
		$user = $this->seeder->generateLandlord();
		$users = User::query()->where('id', $user->id)->paginate();

		$data = ApiRequestDTO::create();
		$type = app(UserSchema::class)->getResourceType();

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
		$user = $this->seeder->generateLandlord();

		$data = ApiRequestDTO::create();
		$type = app(UserSchema::class)->getResourceType();

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
		$user = $this->seeder->generateLandlord();
		$users = User::query()->where('id', $user->id)->paginate();

		$data = ApiRequestDTO::create();
		$type = app(UserSchema::class)->getResourceType();

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
}
