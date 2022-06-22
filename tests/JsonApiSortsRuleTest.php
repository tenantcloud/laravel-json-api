<?php

namespace Tests;

use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use TenantCloud\JsonApi\Validation\Rules\JsonApiSortRule;

/**
 * @see JsonApiSortRule
 */
class JsonApiSortsRuleTest extends TestCase
{
	public function testSuccess(): void
	{
		$sort = Str::random(10);

		$this->assertEmpty($this->validate(new JsonApiSortRule([$sort, Str::random(8)], $this->faker->word), $sort));
	}

	public function testError(): void
	{
		$apiUrl = $this->faker->word;
		$wrongSort = Str::random(9);

		$this->partialMock(LoggerInterface::class)
			->shouldReceive('debug')
			->withArgs(function (string $message, array $context) use ($wrongSort, $apiUrl) {
				$this->assertSame('Wrong sorts are requested', $message);
				$this->assertSame([
					'wrong_sorts' => [$wrongSort],
					'route'       => $apiUrl,
				], $context);

				return true;
			})
			->once();

		$errors = $this->validate(new JsonApiSortRule([Str::random(10), Str::random(8)], $apiUrl), $wrongSort);

		$this->assertSame("The requested sorts are not valid: {$wrongSort}", head($errors));
	}

	private function validate(JsonApiSortRule $rule, string $value): array
	{
		return validator(
			[
				'field' => $value,
			],
			[
				'field' => $rule,
			]
		)->errors()->all();
	}
}
