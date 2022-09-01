<?php

namespace Tests;

use Illuminate\Validation\ValidationException;
use ReflectionProperty;
use TenantCloud\JsonApi\JsonApiStoreUpdateRequest;
use Tests\Mocks\TestUserSchema;
use Tests\Traits\TestValidationResult;

/**
 * @see JsonApiStoreUpdateRequest
 */
class JsonApiStoreUpdateRequestTest extends TestCase
{
	protected JsonApiStoreUpdateRequest $testRequest;

	protected function setUp(): void
	{
		parent::setUp();

		$this->testRequest = new class () extends JsonApiStoreUpdateRequest {
			protected $schema = TestUserSchema::class;

			protected array $availableRelationships = [
				'test_include',
			];

			public function rules(): array
			{
				return [
					'name'            => ['required', 'string', 'max:255'],
					'array1.*.id'     => ['nullable', 'string'],
					'test_include.id' => ['sometimes', 'required', 'numeric'],
				];
			}

			public function getRouteResolver()
			{
				return function () {
					return new class () {
						public $uri = '';
					};
				};
			}
		};
	}

	public function testNoData(): void
	{
		$this->validate([])
			->assertErrors([
				'data',
				'data.type',
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
				'relationships' => [
					'test_include' => [
						'data' => [
							'type' => 'include_test_schema',
							'id'   => '123',
						],
					],
				],
			],
		])
			->assertMissingErrors();
	}

	public function testWrongRelationshipsStructure(): void
	{
		$this->validate([
			'data' => [
				'type'       => 'string',
				'attributes' => [
					'name' => $this->faker->word,
					'test_include' => $this->faker->word,
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
				'relationships' => [
					'test_include' => [
						'data' => [
							'type' => 'include_test_schema',
							'id'   => 'asdasd',
						],
					],
				],
			],
		])
			->assertErrors([
				'array1.0.id',
				'test_include.id',
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
			$validator = $exception->validator;
		} finally {
			if (!$validator) {
				$ref = new ReflectionProperty(get_class($request), 'validator');
				$ref->setAccessible(true);

				$validator = $ref->getValue($request);
			}
		}

		return new TestValidationResult($validator);
	}
}
