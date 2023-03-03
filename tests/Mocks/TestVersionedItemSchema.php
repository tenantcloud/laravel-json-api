<?php

namespace Tests\Mocks;

use TenantCloud\JsonApi\BaseSchema;
use TenantCloud\JsonApi\RequestContext;
use TenantCloud\JsonApi\SchemaFieldDefinition;
use TenantCloud\JsonApi\SchemaIncludeDefinition;

class TestVersionedItemSchema extends BaseSchema
{
	protected string $resourceType = 'test_versioned_item';

	public function __construct()
	{
		$attributes = [
			'id',
			'name',
			SchemaFieldDefinition::create('non_versioned_field'),
			SchemaFieldDefinition::create('exact_version_field', static fn (RequestContext $context) => true)->versioned(['==1.0']),
			SchemaFieldDefinition::create('multiple_version_field', static fn (RequestContext $context) => true)->versioned(['==1.0', '==2.0']),
			SchemaFieldDefinition::create('complex_rule_version_field', static fn (RequestContext $context) => true)->versioned(['>=2.0']),
		];
		parent::__construct($attributes);

		// Includes
		$this->includes = [
			'test_user'            => SchemaIncludeDefinition::create(TestUserSchema::class, true, true),
			'test_version_include' => SchemaIncludeDefinition::create(IncludeTestScheme::class, true, true)->versioned(['>=1.0']),
		];
	}
}
