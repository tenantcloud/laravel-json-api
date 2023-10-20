<?php

namespace Tests\Mocks;

use Illuminate\Support\Str;
use League\Fractal\Resource\ResourceInterface;
use TenantCloud\JsonApi\JsonApiTransformer;
use Tests\UserTransformerTest;

/**
 * @see UserTransformerTest
 */
class TestUserTransformer extends JsonApiTransformer
{
	public array $availableIncludes = [
		'test_include',
		'test_include_collection',
		'meta',
	];

	public function includeTestInclude($item): ?ResourceInterface
	{
		return $this->item(new TestUser(random_int(10, PHP_INT_MAX), Str::random()), $this, (new TestUserSchema())->getResourceType());
	}

	public function includeTestIncludeCollection($item): ?ResourceInterface
	{
		return $this->collection(
			[
				new TestUser(random_int(10, PHP_INT_MAX), Str::random()),
				new TestUser(random_int(10, PHP_INT_MAX), Str::random()),
			],
			$this,
			(new TestUserSchema())->getResourceType()
		);
	}

	public function includeMeta($item): ResourceInterface
	{
		return $this->item(
			$item,
			(new TestUserMetaTransformer())->setContext($this->context),
			app(TestUserMetaSchema::class)->getResourceType()
		);
	}
}
