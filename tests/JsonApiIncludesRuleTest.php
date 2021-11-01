<?php

namespace Tests;

use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use TenantCloud\JsonApi\Validation\Rules\JsonApiIncludesRule;

/**
 * @see JsonApiIncludesRule
 */
class JsonApiIncludesRuleTest extends TestCase
{
	public function testSuccess(): void
	{
		$include = Str::random(10);

		$this->assertEmpty($this->validate(new JsonApiIncludesRule([$include, Str::random(8)], $this->faker->word), $include));
	}

	public function testError(): void
	{
		$apiUrl = $this->faker->word;
		$wrongInclude = Str::random(9);

		$this->partialMock(LoggerInterface::class)
			->shouldReceive('debug')
			->withArgs(function (string $message, array $context) use ($wrongInclude, $apiUrl) {
				$this->assertSame('Wrong includes are requested', $message);
				$this->assertSame([
					'wrong_includes' => [$wrongInclude],
					'route'          => $apiUrl,
				], $context);

				return true;
			})
			->once();

		$errors = $this->validate(new JsonApiIncludesRule([Str::random(10), Str::random(8)], $apiUrl), $wrongInclude);

		$this->assertSame(trans('exceptions.not_valid_json_api_request'), head($errors));
	}

	private function validate(JsonApiIncludesRule $rule, string $value): array
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
