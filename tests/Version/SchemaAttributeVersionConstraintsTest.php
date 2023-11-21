<?php

namespace Tests\Version;

use TenantCloud\APIVersioning\Version\VersionParser;
use TenantCloud\JsonApi\DTO\ApiRequestDTO;
use TenantCloud\JsonApi\RequestContext;
use TenantCloud\JsonApi\SchemaFieldDefinition;
use Tests\Mocks\DuplicateAttributesTestSchema;
use Tests\Mocks\TestUser;
use Tests\TestCase;

class SchemaAttributeVersionConstraintsTest extends TestCase
{
	public function testNoConstraintDuplicatedFieldRetrieveWhenNoVersionReceived(): void
	{
		$user = new TestUser(1, 'name');

		$data = ApiRequestDTO::create();
		/** @var DuplicateAttributesTestSchema $schema */
		$schema = app(DuplicateAttributesTestSchema::class);
		$context = new RequestContext($user, $data, $schema->getResourceType());

		$schemaField = $schema->getAttributeExpression('duplicate_attribute', $context);

		self::assertNull($schemaField->getConstraints());
	}

	public function testNoConstraintDuplicatedFieldRetrieveWhenVersionReceived(): void
	{
		$user = new TestUser(1, 'name');

		$data = ApiRequestDTO::create();
		/** @var DuplicateAttributesTestSchema $schema */
		$schema = app(DuplicateAttributesTestSchema::class);
		$context = new RequestContext($user, $data, $schema->getResourceType(), app(VersionParser::class)->parse('1.0'));

		$schemaField = $schema->getAttributeExpression('duplicate_attribute', $context);

		self::assertNull($schemaField->getConstraints());
	}

	public function testRetrieveDefinitionWithConstrainWhenVersionIsMatched(): void
	{
		$constraints = ['==1.0', '==2.0'];
		$user = new TestUser(1, 'name');

		$data = ApiRequestDTO::create();
		$schema = new DuplicateAttributesTestSchema([
			SchemaFieldDefinition::create('duplicate_attribute'),
			SchemaFieldDefinition::create('duplicate_attribute')->versioned($constraints),
		]);

		$context = new RequestContext($user, $data, $schema->getResourceType(), app(VersionParser::class)->parse('1.0'));
		$schemaField = $schema->getAttributeExpression('duplicate_attribute', $context);
		self::assertEquals($constraints, $schemaField->getConstraints());

		$context = new RequestContext($user, $data, $schema->getResourceType(), app(VersionParser::class)->parse('2.0'));
		$schemaField = $schema->getAttributeExpression('duplicate_attribute', $context);
		self::assertEquals($constraints, $schemaField->getConstraints());
	}

	public function testRetrieveDefaultDefinitionWhenExistedVersionedAndDefaultObjects(): void
	{
		$constraints = ['==1.0', '==2.0'];
		$user = new TestUser(1, 'name');

		$data = ApiRequestDTO::create();
		$schema = new DuplicateAttributesTestSchema([
			SchemaFieldDefinition::create('duplicate_attribute'),
			SchemaFieldDefinition::create('duplicate_attribute')->versioned($constraints),
		]);

		$context = new RequestContext($user, $data, $schema->getResourceType(), app(VersionParser::class)->parse('3.0'));
		$schemaField = $schema->getAttributeExpression('duplicate_attribute', $context);
		self::assertNull($schemaField->getConstraints());
	}

	public function testChooseBestMatchForVersionConstraints(): void
	{
		$user = new TestUser(1, 'name');

		$data = ApiRequestDTO::create();
		$schema = new DuplicateAttributesTestSchema([
			SchemaFieldDefinition::create('attribute')->versioned(['>=2.0']),
			SchemaFieldDefinition::create('attribute')->versioned(['>=1.0']),
		]);

		$context = new RequestContext($user, $data, $schema->getResourceType(), app(VersionParser::class)->parse('3.0'));
		$schemaField = $schema->getAttributeExpression('attribute', $context);
		self::assertEquals(['>=2.0'], $schemaField->getConstraints());
	}

	public function testNoConstraintFound(): void
	{
		$user = new TestUser(1, 'name');

		$data = ApiRequestDTO::create();
		$schema = new DuplicateAttributesTestSchema([
			SchemaFieldDefinition::create('attribute')->versioned(['==2.0']),
			SchemaFieldDefinition::create('attribute')->versioned(['==1.0']),
		]);

		$context = new RequestContext($user, $data, $schema->getResourceType(), app(VersionParser::class)->parse('3.0'));
		$schemaField = $schema->getAttributeExpression('attribute', $context);
		self::assertNull($schemaField);
	}
}
