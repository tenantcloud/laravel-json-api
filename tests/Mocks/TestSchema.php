<?php

namespace Tests\Mocks;

use TenantCloud\JsonApi\BaseSchema;
use TenantCloud\JsonApi\RequestContext;
use TenantCloud\JsonApi\SchemaFieldDefinition;
use TenantCloud\JsonApi\SchemaIncludeDefinition;

/**
 * Class TestSchema
 */
class TestSchema extends BaseSchema
{
	protected string $resourceType = 'test_schema';

	public function __construct()
	{
		$attributes = [
			'id',
			'name',
			SchemaFieldDefinition::create('bool_allowed_attribute'),
			SchemaFieldDefinition::create('bool_banned_attribute', static fn (RequestContext $context) => false),
			SchemaFieldDefinition::create('bool_callback_attribute', static fn (RequestContext $context) => true),
		];
		parent::__construct($attributes);

		// Includes
		$this->includes = [
			'test_include' => SchemaIncludeDefinition::create(IncludeTestScheme::class, true),
		];
	}
}
