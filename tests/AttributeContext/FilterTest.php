<?php

namespace Tests\AttributeContext;

use TenantCloud\JsonApi\AttributeContext\Filter;
use Tests\TestCase;

/**
 * Class FilterTest
 *
 * @see Filter
 */
class FilterTest extends TestCase
{
	public function testSetFilters()
	{
		$filters = [
			'test' => $this->faker->name,
		];

		$filterObj = new Filter();
		$this->assertSame([], $filterObj->all());

		$filterObj = new Filter($filters);
		$this->assertSame($filters, $filterObj->all());
	}

	public function testGetFilter()
	{
		$key = 'test';

		$filters = [
			$key => $this->faker->name,
		];

		$filterObj = new Filter();
		$this->assertNull($filterObj->getOne($key));

		$filterObj = new Filter($filters);
		$this->assertSame($filters[$key], $filterObj->getOne($key));
		$this->assertNull($filterObj->getOne($key . '1'));
	}

	public function testOnlyFilter()
	{
		$onlyKeys = ['test1'];
		$filters = [
			'test1' => $this->faker->name,
			'test2' => $this->faker->name,
		];

		$filterObj = new Filter();
		$this->assertSame([], $filterObj->only($onlyKeys));

		$filterObj = new Filter($filters);
		$this->assertArrayHasKeys($onlyKeys, $filterObj->only($onlyKeys));
		$this->assertArrayNotHasKeys(['test2'], $filterObj->only($onlyKeys));
	}

	public function testSetFilter()
	{
		$key = 'test';
		$value = $this->faker->name;

		$filterObj = new Filter();
		$filterObj->setFilter($key, $value);

		$this->assertSame($value, $filterObj->getOne($key));
	}
}
