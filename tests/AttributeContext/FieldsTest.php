<?php

namespace Tests\AttributeContext;

use TenantCloud\JsonApi\AttributeContext\Fields;
use Tests\TestCase;

/**
 * @see Fields
 */
class FieldsTest extends TestCase
{
	public function testOriginalFilters(): void
	{
		$filters = [
			'test' => $this->faker->name,
		];

		$fields = new Fields($filters);

		$this->assertSame($filters, $fields->original());
		$this->assertSame($filters['test'], $fields->getOriginalByKey('test'));
		$this->assertSame([], $fields->getOriginalByKey('not_valid_key'));
	}

	public function testValidatedFilters(): void
	{
		$fields = new Fields([]);

		$key = 'key';
		$data = [
			'test' => $this->faker->name,
		];
		$fields->addValidated($key, $data);

		$this->assertSame($data, $fields->validated()[$key]);
	}
}
