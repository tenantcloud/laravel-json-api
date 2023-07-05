<?php

namespace TenantCloud\JsonApi\Schema;

use TenantCloud\JsonApi\BaseSchema;
use TenantCloud\JsonApi\RequestContext;
use TenantCloud\JsonApi\SchemaFieldDefinition;
use Tests\Mocks\TestUser;

class ExampleIncludedSchema extends BaseSchema
{
	protected string $resourceType = 'example_include';

	public function __construct()
	{
		$attributes = [
			'id',
			'name',
			SchemaFieldDefinition::create('field_with_validation', static function (RequestContext $context) {
				/** @var TestUser $user */
				$user = $context->user();

				return $user && $user->isValid();
			}),
		];

		parent::__construct($attributes);
	}
}
