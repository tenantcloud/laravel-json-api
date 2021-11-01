<?php

namespace Tests\Traits;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Testing\Assert as PHPUnit;
use Illuminate\Testing\TestResponse;

/**
 * Validation result with tests' related methods (assertions).
 */
class TestValidationResult
{
	private Validator $result;

	public function __construct(Validator $result)
	{
		$this->result = $result;
	}

	/**
	 * Same thing as {@see TestResponse::assertJsonValidationErrors()},
	 * except it works with plain validators and provides useful error messages. Thanks Laravel (no).
	 *
	 * @param string|array $expectedErrors
	 *
	 * @return static
	 */
	public function assertErrors($expectedErrors): self
	{
		$expectedErrors = Arr::wrap($expectedErrors);

		PHPUnit::assertNotEmpty($expectedErrors, 'No validation errors were provided.');

		$jsonErrors = $this->result
			->errors()
			->messages();

		$errorMessage = $jsonErrors
			? 'Response has the following JSON validation errors:' .
			PHP_EOL . PHP_EOL . json_encode($jsonErrors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL
			: 'Response does not have JSON validation errors.';

		foreach ($expectedErrors as $key => $value) {
			PHPUnit::assertArrayHasKey(
				(is_int($key)) ? $value : $key,
				$jsonErrors,
				"Failed to find a validation error in the response for key: '{$value}'" . PHP_EOL . PHP_EOL . $errorMessage
			);

			if (!is_int($key)) {
				$hasError = false;

				foreach (Arr::wrap($jsonErrors[$key]) as $jsonErrorMessage) {
					if (Str::contains($jsonErrorMessage, $value)) {
						$hasError = true;

						break;
					}
				}

				if (!$hasError) {
					PHPUnit::fail(
						"Failed to find a validation error in the response for key and message: '{$key}' => '{$value}'" . PHP_EOL . PHP_EOL . $errorMessage
					);
				}
			}
		}

		return $this;
	}

	/**
	 * Same thing as {@see TestResponse::assertJsonMissingValidationErrors()},
	 * except it works with plain validators and provides useful error messages. Thanks Laravel (no).
	 *
	 * @param string|array|null $expectedMissingErrorKeys
	 *
	 * @return static
	 */
	public function assertMissingErrors($expectedMissingErrorKeys = null): self
	{
		$errors = $this->result
			->errors()
			->messages();

		if (!$errors) {
			PHPUnit::assertTrue(true);

			return $this;
		}

		if ($expectedMissingErrorKeys === null && count($errors) > 0) {
			PHPUnit::fail(
				'Validation result has unexpected validation errors: ' . PHP_EOL . PHP_EOL .
				json_encode($errors, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
			);
		}

		foreach (Arr::wrap($expectedMissingErrorKeys) as $key) {
			PHPUnit::assertFalse(
				Arr::has($errors, $key),
				"Found unexpected validation error for key: '{$key}'" . PHP_EOL . PHP_EOL .
				json_encode(Arr::get($errors, $key), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
			);
		}

		return $this;
	}
}
