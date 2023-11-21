<?php

namespace Tests\Mocks;

use TenantCloud\JsonApi\BaseSchema;
use TenantCloud\JsonApi\RequestContext;
use TenantCloud\JsonApi\SchemaFieldDefinition;
use TenantCloud\JsonApi\SchemaIncludeDefinition;

class DuplicateAttributesTestSchema extends BaseSchema
{
	protected string $resourceType = 'test_duplicate_fields_schema';

	public function __construct(array $overrideAttributes = [])
	{
		$attributes = [
			SchemaFieldDefinition::create('duplicate_attribute'),
			SchemaFieldDefinition::create('duplicate_attribute', static fn (RequestContext $context) => false),
			SchemaFieldDefinition::create('bool_callback_attribute', static fn (RequestContext $context) => true),
		];
		parent::__construct(array_merge($overrideAttributes, $attributes));

		// Includes
		$this->includes = [
			'test_include' => SchemaIncludeDefinition::create(IncludeTestScheme::class, true),
		];
	}
}
