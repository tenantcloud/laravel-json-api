<?php

namespace Tests\Mocks;

use TenantCloud\JsonApi\BaseSchema;
use TenantCloud\JsonApi\SchemaFieldDefinition;

class TestUserMetaSchema extends BaseSchema
{
	protected string $resourceType = 'test_user_meta_schema';

	public function __construct()
	{
		$attributes = [
			SchemaFieldDefinition::create('id')->setExtractor(fn (TestUser $user) => (string) $user->id),
			SchemaFieldDefinition::create('is_valid')->setExtractor(fn (TestUser $user) => $user->valid),
			SchemaFieldDefinition::create('is_invalid')->setExtractor(fn (TestUser $user) => !$user->valid),
		];
		parent::__construct($attributes);
	}
}
