<?php

namespace Tests\Mocks;

use TenantCloud\JsonApi\JsonApiTransformer;

class TestUserTransformer extends JsonApiTransformer
{
	/**
	 * @param TestUser $item
	 */
	public function transform($item): array
	{
		return $item->toArray();
	}
}
