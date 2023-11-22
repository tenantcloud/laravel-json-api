<?php

namespace Tests;

use TenantCloud\JsonApi\JsonApiRegistry;
use TenantCloud\JsonApi\SchemaIncludeDefinition;
use Tests\Mocks\IncludeTestScheme;
use Tests\Mocks\TestUserSchema;

/**
 * @see SchemaIncludeDefinition
 */
class SchemaIncludeDefinitionTest extends TestCase
{
	private JsonApiRegistry $registry;

	protected function setUp(): void
	{
		parent::setUp();

		$this->registry = app(JsonApiRegistry::class);
		$this->registry->register(app(IncludeTestScheme::class));
		$this->registry->register(app(TestUserSchema::class));
	}

	public function testSetData(): void
	{
		$validation = true;
		$validator = SchemaIncludeDefinition::create(TestUserSchema::class, false, $validation);
		$schema = $validator->getSchemaClass();

		$this->assertTrue($validator->getValidation());
		$this->assertIsObject($validator->getSchemaClass());
		$this->assertSame('test_schema', $validator->getResourceType());

		// Test if another call
		$this->assertSame($schema, $validator->getSchemaClass());
	}

	public function testIsSingleMarker(): void
	{
		$validator = SchemaIncludeDefinition::create(TestUserSchema::class, false);
		$this->assertFalse($validator->isSingle());
	}
}
