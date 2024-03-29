<?php

namespace Tests;

use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use TenantCloud\JsonApi\Validation\Rules\JsonApiRelationshipsRule;

/**
 * @see JsonApiRelationshipsRule
 */
class JsonApiRelationshipsRuleTest extends TestCase
{
	public function testSuccess(): void
	{
		$relationship = Str::random(10);

		$this->assertEmpty($this->validate(new JsonApiRelationshipsRule([$relationship, Str::random(8)], $this->faker->word), $relationship));
	}

	public function testEmptyArraySuccess(): void
	{
		$this->assertEmpty(validator(
			[
				'field' => [],
			],
			[
				'field' => new JsonApiRelationshipsRule([Str::random(8)], $this->faker->word),
			]
		)->errors()->all());
	}

	public function testEmptyArrayAndEmptyAvailableRelationshipsSuccess(): void
	{
		$this->assertEmpty(validator(
			[
				'field' => [],
			],
			[
				'field' => new JsonApiRelationshipsRule([], $this->faker->word),
			]
		)->errors()->all());
	}

	public function testEmptyAvailableRelationshipsError(): void
	{
		$relationship = Str::random(10);

		$errors = $this->validate(new JsonApiRelationshipsRule([], $this->faker->word), $relationship);
		$this->assertSame("The used relationships are not valid: {$relationship}", head($errors));
	}

	public function testError(): void
	{
		$apiUrl = $this->faker->word;
		$wrongRelation = Str::random(9);

		$this->partialMock(LoggerInterface::class)
			->shouldReceive('debug')
			->withArgs(function (string $message, array $context) use ($wrongRelation, $apiUrl) {
				$this->assertSame('Wrong relationships are used', $message);
				$this->assertSame([
					'wrong_relationships' => [$wrongRelation],
					'route'               => $apiUrl,
				], $context);

				return true;
			})
			->once();

		$errors = $this->validate(new JsonApiRelationshipsRule([Str::random(10), Str::random(8)], $apiUrl), $wrongRelation);

		$this->assertSame("The used relationships are not valid: {$wrongRelation}", head($errors));
	}

	private function validate(JsonApiRelationshipsRule $rule, string $value): array
	{
		return validator(
			[
				'field' => [$value => ['data' => []]],
			],
			[
				'field' => $rule,
			]
		)->errors()->all();
	}
}
