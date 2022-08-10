<?php

namespace Tests;

use Illuminate\Support\Str;
use TenantCloud\JsonApi\Schema\ExampleSchema;
use TenantCloud\JsonApi\Validation\Rules\JsonApiSingleRelationRule;

/**
 * @see JsonApiSingleRelationRule
 */
class JsonApiSingleRelationRuleTest extends TestCase
{
	public function testSuccess(): void
	{
		$this->assertEmpty($this->validate(new JsonApiSingleRelationRule(ExampleSchema::class), [
			'example_include' => ['data' => ['id' => 1, 'type' => 'example_include']],
		]));
	}

	public function testMultipleRelationItemsSuccess(): void
	{
		$data = [
			'example_include' => [
				'data' => [
					['id' => 1, 'type' => 'example_include'],
					['id' => 2, 'type' => 'example_include'],
				],
			],
		];

		$this->assertEmpty($this->validate(new JsonApiSingleRelationRule(ExampleSchema::class), $data));
	}

	public function testSingleRelationInvalidStructure(): void
	{
		$data = [
			'example_include' => [
				'data' => [
					'id' => 2,
				],
			],
		];

		$errors = $this->validate(new JsonApiSingleRelationRule(ExampleSchema::class), $data);
		$this->assertSame('Relationship structure is invalid.', head($errors));

		$data = [
			'example_include' => [
				'data' => [
					'type' => 'example_include',
				],
			],
		];

		$errors = $this->validate(new JsonApiSingleRelationRule(ExampleSchema::class), $data);
		$this->assertSame('Relationship structure is invalid.', head($errors));
	}

	public function testMultipleRelationInvalidStructure(): void
	{
		$data = [
			'example_include' => [
				'data' => [
					['id' => 2],
				],
			],
		];

		$errors = $this->validate(new JsonApiSingleRelationRule(ExampleSchema::class), $data);
		$this->assertSame('Relationship must include \'id\' and \'type\' keys.', head($errors));
	}

	public function testMultipleRelationPartInvalidStructure(): void
	{
		$data = [
			'example_include' => [
				'data' => [
					['id' => 2],
					['id' => 2, 'type' => 'example_include'],
				],
			],
		];

		$errors = $this->validate(new JsonApiSingleRelationRule(ExampleSchema::class), $data);
		$this->assertSame('Relationship must include \'id\' and \'type\' keys.', head($errors));
	}

	public function testParseSingleItem(): void
	{
		$data = [
			'example_include' => [
				'data' => [
					'id'   => 1,
					'type' => 'example_include',
					['id'  => 2, 'type' => 'example_include'],
				],
			],
		];

		$this->assertEmpty($this->validate(new JsonApiSingleRelationRule(ExampleSchema::class), $data));
	}

	public function testParseMultipleRelationItem(): void
	{
		$data = [
			'example_include' => [
				'data' => [
					'id'  => 1,
					['id' => 2, 'type' => 'example_include'],
				],
			],
		];

		$errors = $this->validate(new JsonApiSingleRelationRule(ExampleSchema::class), $data);
		$this->assertSame('Relationship structure is invalid.', head($errors));
	}

	public function testNoItems(): void
	{
		$this->assertEmpty($this->validate(new JsonApiSingleRelationRule(ExampleSchema::class), []));
	}

	public function testEmptyNotExistedRelationSkip(): void
	{
		$data = [
			Str::random(8) => [
				'data' => [],
			],
		];
		$this->assertEmpty($this->validate(new JsonApiSingleRelationRule(ExampleSchema::class), $data));
	}

	public function testNotEmptyNotExistedRelationError(): void
	{
		$data = [
			Str::random(8) => [
				'data' => ['id' => 1],
			],
		];

		$this->assertSame('Relationship structure is invalid.', head($this->validate(new JsonApiSingleRelationRule(ExampleSchema::class), $data)));
	}

	private function validate(JsonApiSingleRelationRule $rule, array $value): array
	{
		return validator(
			[
				'data' => ['relationships' => $value],
			],
			[
				'data.relationships.*' => ['array:data', $rule],
			]
		)->errors()->all();
	}
}
