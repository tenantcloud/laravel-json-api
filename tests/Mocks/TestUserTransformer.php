<?php

namespace Tests\Mocks;

use TenantCloud\JsonApi\JsonApiTransformer;
use App\Models\User;

class TestUserTransformer extends JsonApiTransformer
{
	/**
	 * @param TestUser $item
	 *
	 * @return array
	 */
	public function transform($item): array
	{
		return $item->toArray();
	}
}
