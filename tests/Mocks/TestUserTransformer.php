<?php

namespace Tests\Mocks;

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
	];

	/**
	 * @param TestUser $item
	 */
	public function transform($item): array
	{
		return $item->toArray();
	}

	public function includeTestInclude($item): ?ResourceInterface
	{
		return $this->jsonApiItem($item, 'test_relation', $this, TestUserSchema::class);
	}

	public function includeTestIncludeCollection($item): ?ResourceInterface
	{
		return $this->jsonApiCollection($item, 'test_relation_collection', $this, TestUserSchema::class);
	}
}
