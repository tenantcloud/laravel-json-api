<?php

namespace Tests;

use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use TenantCloud\JsonApi\Schema\ExampleSchema;
use TenantCloud\JsonApi\Validation\Rules\JsonApiRelationshipsRule;
use TenantCloud\JsonApi\Validation\Rules\JsonApiSingleRelationRule;

/**
 * @see JsonApiSingleRelationRule
 */
class JsonApiSingleRelationRuleTest extends TestCase
{
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

//	public function testError(): void
//	{
//		$apiUrl = $this->faker->word;
//		$wrongRelation = Str::random(9);
//
//		$errors = $this->validate(new JsonApiSingleRelationRule([Str::random(10), Str::random(8)], $apiUrl), $wrongRelation);
//
//		$this->assertSame("The used relationships are not valid: {$wrongRelation}", head($errors));
//	}

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
