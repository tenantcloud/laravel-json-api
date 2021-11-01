<?php

namespace TenantCloud\JsonApi\Schema;

use TenantCloud\JsonApi\BaseSchema;
use TenantCloud\JsonApi\RequestContext;
use TenantCloud\JsonApi\SchemaFieldDefinition;
use TenantCloud\JsonApi\SchemaIncludeDefinition;
use Tests\Mocks\TestUser;

class ExampleSchema extends BaseSchema
{
	protected string $resourceType = 'example';

	public function __construct()
	{
		$attributes = [
			'id',
			'field',
			SchemaFieldDefinition::create('field_with_validation', static function (RequestContext $context) {
				/** @var TestUser $user */
				$user = $context->user();

				return $user->isValid();
			}),
		];

		parent::__construct($attributes);

		// Includes
		$this->includes = [
			'example_include' => SchemaIncludeDefinition::create(ExampleIncludedSchema::class, true),
		];
	}
}
