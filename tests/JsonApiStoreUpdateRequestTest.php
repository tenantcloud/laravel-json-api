<?php

namespace Tests;

use TenantCloud\JsonApi\JsonApiStoreUpdateRequest;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;
use Tests\Backend\Library\Traits\TestValidationResult;
use Tests\Mocks\TestSchema;
use Tests\NonPublicAccessibleTrait;
use Tests\TestCase;

/**
 * @see JsonApiStoreUpdateRequest
 */
class JsonApiStoreUpdateRequestTest extends TestCase
{
	use DatabaseTransactions;
	use NonPublicAccessibleTrait;

	protected JsonApiStoreUpdateRequest $testRequest;

	protected function setUp(): void
	{
		parent::setUp();

		$this->testRequest = new class() extends JsonApiStoreUpdateRequest {
			protected $schema = TestSchema::class;

			public function rules(): array
			{
				return [
					'name'        => ['required', 'string', 'max:255'],
					'array1.*.id' => ['nullable', 'string'],
				];
			}
		};
	}

	public function testNoData(): void
	{
		$this->validate([])
			->assertErrors([
				'data',
				'data.type',
				'data.attributes',
			]);
	}

	public function testCorrectData(): void
	{
		$this->validate([
			'data' => [
				'type'       => 'string',
				'attributes' => [
					'name' => $this->faker->word,
				],
			],
		])
			->assertMissingErrors();
	}

	public function testInCorrectData(): void
	{
		$this->validate([
			'data' => [
				'type'       => 'string',
				'attributes' => [
					'name'   => $this->faker->word,
					'array1' => [
						[
							'id' => $this->faker->randomNumber(),
						],
					],
				],
			],
		])
			->assertErrors([
				'array1.0.id',
			]);
	}

	/**
	 * Validate given data using the validator.
	 */
	private function validate(array $data): TestValidationResult
	{
		$request = $this->testRequest::createFrom(request())
			->setContainer($this->app)
			->setRedirector($this->app['redirect']);

		$request->merge($data);

		$validator = null;

		try {
			$request->validateResolved();
		} catch (ValidationException $exception) {
			$validator = $this->getNonPublicProperty($exception, 'validator');
		} finally {
			if (!$validator) {
				$validator = $this->getNonPublicProperty($request, 'validator');
			}
		}

		return new TestValidationResult($validator);
	}
}
