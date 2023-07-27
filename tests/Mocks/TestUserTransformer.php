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
		'meta',
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
		return $this->modelRelationItem($item, 'test_relation', $this, TestUserSchema::class);
	}

	public function includeTestIncludeCollection($item): ?ResourceInterface
	{
		return $this->modelRelationCollection($item, 'test_relation_collection', $this, TestUserSchema::class);
	}

	public function includeMeta($item): ResourceInterface
	{
		return $this->item(
			$item,
			(new TestUserMetaTransformer())->setFields($this->getFields()),
			app(TestUserMetaSchema::class)->getResourceType()
		);
	}
}
