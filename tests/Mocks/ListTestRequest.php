<?php

namespace Tests\Mocks;

use TenantCloud\JsonApi\JsonApiRequest;

class ListTestRequest extends JsonApiRequest
{
	protected $schema = TestUserSchema::class;

	public function authorize(): bool
	{
		return true;
	}
}
