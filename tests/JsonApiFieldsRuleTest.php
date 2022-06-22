<?php

namespace Tests;

use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use ReflectionProperty;
use TenantCloud\JsonApi\JsonApiRegistry;
use TenantCloud\JsonApi\Validation\Rules\JsonApiFieldsRule;
use Tests\Mocks\TestUserSchema;

/**
 * @see JsonApiFieldsRule
 */
class JsonApiFieldsRuleTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		$jsonApiSchemaRegistry = $this->app->make(JsonApiRegistry::class);
		$jsonApiSchemaRegistry->register(app(TestUserSchema::class));
	}

	public function testSuccess(): void
	{
		$fields = implode(',', array_keys(resolve(TestUserSchema::class)->getAttributes()));

		$this->assertEmpty($this->validate(new JsonApiFieldsRule($this->faker->word), $fields));
	}

	public function testWrongFieldWithoutStrictValidation(): void
	{
		config()->set('json-api.strict_validation', false);

		$fields = implode(',', array_keys(resolve(TestUserSchema::class)->getAttributes()));
		$wrongField = Str::random(10);
		$apiUrl = $this->faker->word;

		$this->partialMock(LoggerInterface::class)
			->shouldReceive('debug')
			->withArgs(function (string $message, array $context) use ($wrongField, $apiUrl) {
				$this->assertSame('Wrong json api fields are requested', $message);
				$this->assertSame([
					'schema'       => $this->getNonPublicProperty(resolve(TestUserSchema::class), 'resourceType'),
					'wrong_fields' => [$wrongField],
					'route'        => $apiUrl,
				], $context);

				return true;
			})
			->once();

		$errors = $this->validate(new JsonApiFieldsRule($apiUrl), $fields . ',' . $wrongField);

		$this->assertEmpty($errors);
	}

	public function testWrongFieldWithStrictValidation(): void
	{
		config()->set('json-api.strict_validation', true);

		$fields = implode(',', array_keys(resolve(TestUserSchema::class)->getAttributes()));
		$wrongField = Str::random(10);
		$apiUrl = $this->faker->word;

		$this->partialMock(LoggerInterface::class)
			->shouldReceive('debug')
			->withArgs(function (string $message, array $context) use ($wrongField, $apiUrl) {
				$this->assertSame('Wrong json api fields are requested', $message);
				$this->assertSame([
					'schema'       => $this->getNonPublicProperty(resolve(TestUserSchema::class), 'resourceType'),
					'wrong_fields' => [$wrongField],
					'route'        => $apiUrl,
				], $context);

				return true;
			})
			->once();

		$errors = $this->validate(new JsonApiFieldsRule($apiUrl), $fields . ',' . $wrongField);

		$this->assertSame(["The requested fields not valid: {$wrongField}"], $errors);
	}

	public function testWrongSchemaWithoutStrictValidation(): void
	{
		config()->set('json-api.strict_validation', false);

		$fields = implode(',', array_keys(resolve(TestUserSchema::class)->getAttributes()));
		$apiUrl = $this->faker->word;
		$wrongSchema = Str::random(10);

		$this->partialMock(LoggerInterface::class)
			->shouldReceive('debug')
			->withArgs(function (string $message, array $context) use ($wrongSchema, $apiUrl) {
				$this->assertSame('Wrong schema when retrieving json api fields', $message);
				$this->assertSame([
					'schema'            => $wrongSchema,
					'request_field_key' => 'field.' . $wrongSchema,
					'route'             => $apiUrl,
				], $context);

				return true;
			})
			->once();

		$errors = $this->validate(new JsonApiFieldsRule($apiUrl), $fields, $wrongSchema);

		$this->assertEmpty($errors);
	}

	public function testWrongSchemaWithStrictValidation(): void
	{
		config()->set('json-api.strict_validation', true);

		$fields = implode(',', array_keys(resolve(TestUserSchema::class)->getAttributes()));
		$apiUrl = $this->faker->word;
		$wrongSchema = Str::random(10);

		$this->partialMock(LoggerInterface::class)
			->shouldReceive('debug')
			->withArgs(function (string $message, array $context) use ($wrongSchema, $apiUrl) {
				$this->assertSame('Wrong schema when retrieving json api fields', $message);
				$this->assertSame([
					'schema'            => $wrongSchema,
					'request_field_key' => 'field.' . $wrongSchema,
					'route'             => $apiUrl,
				], $context);

				return true;
			})
			->once();

		$errors = $this->validate(new JsonApiFieldsRule($apiUrl), $fields, $wrongSchema);

		$this->assertSame(["The requested schema not valid: {$wrongSchema}"], $errors);
	}

	private function validate(JsonApiFieldsRule $rule, string $value, string $schema = null): array
	{
		$schema ??= $this->getNonPublicProperty(resolve(TestUserSchema::class), 'resourceType');

		return validator(
			[
				'field' => [
					$schema => $value,
				],
			],
			[
				'field.*' => $rule,
			]
		)->errors()->all();
	}

	/**
	 * @param $obj
	 *
	 * @return mixed
	 */
	private function getNonPublicProperty($obj, string $property)
	{
		$ref = new ReflectionProperty(get_class($obj), $property);
		$ref->setAccessible(true);

		return $ref->getValue($obj);
	}
}
