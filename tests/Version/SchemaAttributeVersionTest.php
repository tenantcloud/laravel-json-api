<?php

namespace Tests\Version;

use Generator;
use Illuminate\Support\Arr;
use TenantCloud\APIVersioning\Version;
use TenantCloud\JsonApi\DTO\ApiRequestDTO;
use TenantCloud\JsonApi\RequestContext;
use Tests\Mocks\TestUser;
use Tests\Mocks\TestUserSchema;
use Tests\Mocks\TestVersionedItemSchema;
use Tests\TestCase;

class SchemaAttributeVersionTest extends TestCase
{
	public function testNoVersionedFields(): void
	{
		$user = new TestUser(1, 'name');

		$data = ApiRequestDTO::create()->setFields([
			'test_schema' => ['name', 'bool_allowed_attribute', 'bool_callback_attribute'],
		]);
		/** @var TestUserSchema $schema */
		$schema = app(TestUserSchema::class);
		$context = new RequestContext($user, $data, $schema->getResourceType());
		$schema->validate($context);

		self::assertEquals(
			[
				'name',
				'bool_allowed_attribute',
				'bool_callback_attribute',
				'id',
			],
			Arr::get($context->fields()->validated(), $schema->getResourceType())
		);
	}

	/**
	 * @dataProvider versionProvider
	 *
	 * @param mixed $version
	 * @param mixed $expectedFields
	 */
	public function testAllowedVersionField($version, $expectedFields): void
	{
		$user = new TestUser(1, 'name');

		$data = ApiRequestDTO::create()->setFields([
			'test_schema' => ['name', 'non_versioned_field', 'exact_version_field', 'multiple_version_field', 'complex_rule_version_field'],
		]);
		/** @var TestVersionedItemSchema $schema */
		$schema = app(TestVersionedItemSchema::class);
		$context = new RequestContext($user, $data, $schema->getResourceType(), $version);
		$schema->validate($context);

		self::assertEquals(
			$expectedFields,
			array_unique(Arr::get($context->fields()->validated(), $schema->getResourceType()))
		);
	}

	/**
	 * @dataProvider versionProvider
	 *
	 * @param mixed $version
	 * @param mixed $expectedFields
	 */
	public function testLatestVersion($version, $expectedFields): void
	{
		// Set current version 3.0
		config()->set('api-versioning.latest_version', $version);
		$user = new TestUser(1, 'name');

		$data = ApiRequestDTO::create()->setFields([
			'test_schema' => ['name', 'non_versioned_field', 'exact_version_field', 'multiple_version_field', 'complex_rule_version_field'],
		]);
		/** @var TestVersionedItemSchema $schema */
		$schema = app(TestVersionedItemSchema::class);
		$context = new RequestContext($user, $data, $schema->getResourceType(), Version::currentLatestVersion());

		$schema->validate($context);

		self::assertEquals(
			$expectedFields,
			array_unique(Arr::get($context->fields()->validated(), $schema->getResourceType()))
		);
	}

	/**
	 * @dataProvider versionProvider
	 *
	 * @param mixed $version
	 * @param mixed $expectedFields
	 */
	public function testDefaultFieldsWithVersion($version, $expectedFields): void
	{
		$user = new TestUser(1, 'name');

		$data = ApiRequestDTO::create();
		/** @var TestVersionedItemSchema $schema */
		$schema = app(TestVersionedItemSchema::class);
		$context = new RequestContext($user, $data, $schema->getResourceType(), $version);
		$schema->validate($context);

		self::assertEquals(
			$expectedFields,
			array_unique(Arr::get($context->fields()->validated(), $schema->getResourceType()))
		);
	}

	public function versionProvider(): Generator
	{
		yield '1.0' => [
			'version'         => '1.0',
			'expected_fields' => [
				'id',
				'name',
				'non_versioned_field',
				'exact_version_field', // ==1.0
				'multiple_version_field', // ==1.0, ==2.0
			],
		];

		yield '2.0' => [
			'version'         => '2.0',
			'expected_fields' => [
				'id',
				'name',
				'non_versioned_field',
				'multiple_version_field', // ==1.0, ==2.0
				'complex_rule_version_field', // >=2.0
			],
		];

		yield '3.0' => [
			'version'         => '3.0',
			'expected_fields' => [
				'id',
				'name',
				'non_versioned_field',
				'complex_rule_version_field', // >=2.0
			],
		];
	}
}
