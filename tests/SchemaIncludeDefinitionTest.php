<?php

namespace Tests;

use TenantCloud\JsonApi\Exceptions\DuplicateSchemaFieldDefinitionException;
use TenantCloud\JsonApi\JsonApiRegistry;
use TenantCloud\JsonApi\SchemaIncludeDefinition;
use Tests\Mocks\DuplicateAttributesTestSchema;
use Tests\Mocks\IncludeTestScheme;
use Tests\Mocks\TestSchema;
use Tests\TestCase;

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
		$this->registry->register(app(TestSchema::class));
	}

	public function testSetData()
	{
		$validation = true;
		$validator = SchemaIncludeDefinition::create(TestSchema::class, $validation);
		$schema = $validator->getSchemaClass();

		$this->assertTrue($validator->getValidation());
		$this->assertIsObject($validator->getSchemaClass());
		$this->assertSame('test_schema', $validator->getResourceType());

		// Test if another call
		$this->assertSame($schema, $validator->getSchemaClass());
	}

	public function testDuplicateAttributesThrowException()
	{
		$this->expectException(DuplicateSchemaFieldDefinitionException::class);
		$this->registry->register(app(DuplicateAttributesTestSchema::class));
	}
}
