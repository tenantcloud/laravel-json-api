<?php

namespace Tests\Version;

use Generator;
use Illuminate\Support\Arr;
use TenantCloud\JsonApi\DTO\ApiRequestDTO;
use TenantCloud\JsonApi\RequestContext;
use Tests\Mocks\TestUser;
use Tests\Mocks\TestVersionedItemSchema;
use Tests\TestCase;

class SchemaIncludeVersionTest extends TestCase
{
	public function testNoVersion(): void
	{
		$user = new TestUser(1, 'name');

		$data = ApiRequestDTO::create()->setInclude([
			'test_user',
			'test_version_include',
		]);

		/** @var TestVersionedItemSchema $schema */
		$schema = app(TestVersionedItemSchema::class);
		$context = new RequestContext($user, $data, $schema->getResourceType());
		$schema->validate($context);

		self::assertEquals(
			[
				'test_user',
				'test_version_include',
			],
			$context->includes()->getValidatedIncludes()
		);
	}

	/**
	 * @dataProvider versionsProvider
	 *
	 * @param mixed $version
	 * @param mixed $expectedIncludes
	 */
	public function testExactVersion($version, $expectedIncludes): void
	{
		$user = new TestUser(1, 'name');

		$data = ApiRequestDTO::create()->setInclude([
			'test_user',
			'test_version_include',
		]);

		/** @var TestVersionedItemSchema $schema */
		$schema = app(TestVersionedItemSchema::class);
		$context = new RequestContext($user, $data, $schema->getResourceType(), $version);
		$schema->validate($context);

		self::assertEquals(
			$expectedIncludes,
			$context->includes()->getValidatedIncludes()
		);
	}

	public function testIncludeNestedVersionedInclude(): void
	{
		$user = new TestUser(1, 'name');

		$data = ApiRequestDTO::create()->setInclude([
			'test_version_include.test_schema',
		]);

		/** @var TestVersionedItemSchema $schema */
		$schema = app(TestVersionedItemSchema::class);
		$context = new RequestContext($user, $data, $schema->getResourceType(), '2.0');
		$schema->validate($context);

		self::assertEmpty($context->includes()->getValidatedIncludes());

		$context = new RequestContext($user, $data, $schema->getResourceType(), '1.0');
		$schema->validate($context);

		self::assertEquals(['test_version_include.test_schema'], $context->includes()->getValidatedIncludes());
	}

	public function testNestedIncludeVersionedAttributes(): void
	{
		$user = new TestUser(1, 'name');

		$data = ApiRequestDTO::create()->setInclude([
			'test_version_include.test_schema',
		])
			->setFields([
				'include_test_schema' => ['bool_allowed_attribute', 'bool_callback_attribute'],
				'test_schema'         => ['name', 'bool_allowed_attribute', 'bool_callback_attribute'],
			]);

		/** @var TestVersionedItemSchema $schema */
		$schema = app(TestVersionedItemSchema::class);
		$context = new RequestContext($user, $data, $schema->getResourceType(), '1.0');
		$schema->validate($context);

		self::assertEquals(['name', 'bool_allowed_attribute', 'id'], Arr::get($context->fields()->validated(), 'test_schema'));

		/** @var TestVersionedItemSchema $schema */
		$schema = app(TestVersionedItemSchema::class);
		$context = new RequestContext($user, $data->setFields([]), $schema->getResourceType(), '1.0');
		$schema->validate($context);

		self::assertEquals(['id', 'name', 'bool_allowed_attribute'], array_unique(Arr::get($context->fields()->validated(), 'test_schema')));
	}

	public function versionsProvider(): Generator
	{
		yield '1.0' => [
			'version'           => '1.0',
			'expected_includes' => [
				'test_user',
				'test_version_include',
			],
		];

		yield '2.0' => [
			'version'           => '2.0',
			'expected_includes' => [
				'test_user',
				'test_version_include',
			],
		];
	}
}
