<?php

namespace Tests\Mocks;

use TenantCloud\JsonApi\BaseSchema;
use TenantCloud\JsonApi\RequestContext;
use TenantCloud\JsonApi\SchemaFieldDefinition;
use TenantCloud\JsonApi\SchemaIncludeDefinition;

class IncludeTestScheme extends BaseSchema
{
	protected string $resourceType = 'include_test_schema';

	public function __construct()
	{
		$attributes = [
			'id',
			SchemaFieldDefinition::create('bool_allowed_attribute'),
			SchemaFieldDefinition::create('bool_banned_attribute', static fn (RequestContext $context) => false),
			SchemaFieldDefinition::create('bool_callback_attribute', static fn (RequestContext $context) => true)->versioned(['==2.0']),
		];
		parent::__construct($attributes);

		// Includes
		$this->includes = [
			'test_schema' => SchemaIncludeDefinition::create(TestUserSchema::class, true, true)->versioned(['==1.0']),
		];
	}
}
