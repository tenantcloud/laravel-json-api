<?php

namespace Tests\AttributeContext;

use App\Enum\SortType;
use TenantCloud\JsonApi\AttributeContext\Sort;
use Tests\TestCase;

/**
 * Class SortTest
 *
 * @see Sort
 */
class SortTest extends TestCase
{
	public function testSetSortInConstructor()
	{
		// Json api parse ASC\DESC by minus '-' symbol at the start of parameter.
		$sortAttributes = ['asc', '-desc'];
		$expectedAttributes = [
			'asc'  => SortType::ASC,
			'desc' => SortType::DESC,
		];
		$sortObj = new Sort();
		$this->assertSame([], $sortObj->all());

		$sortObj = new Sort($sortAttributes);
		$this->assertSame($expectedAttributes, $sortObj->all());
		$this->assertSame(array_keys($expectedAttributes), $sortObj->attributes());
	}

	public function testGetAttributes()
	{
		$sortParameters = ['test'];
		$sortObj = new Sort();
		$this->assertSame([], $sortObj->attributes());

		$sortObj->setSort($sortParameters);
		$this->assertSame($sortParameters, $sortObj->attributes());
	}

	public function testAddAndRemoveSortAttribute()
	{
		$ascSortParameter = 'test_asc';
		$descSortParameter = '-test_desc';
		$expectedAbcSortParameter = ['test_asc' => SortType::ASC];
		$expectedDescSortParameter = ['test_desc' => SortType::DESC];

		$sortObj = new Sort();
		$sortObj->setSort([$ascSortParameter, $descSortParameter]);
		$this->assertSame(SortType::ASC, $sortObj->getAscending('test_asc'));
		$this->assertSame(SortType::DESC, $sortObj->getAscending('test_desc'));
		$this->assertSame(array_merge($expectedAbcSortParameter, $expectedDescSortParameter), $sortObj->all());

		$sortObj->removeField('test_asc');
		$this->assertNull($sortObj->getAscending('test_asc'));
		$this->assertSame(SortType::DESC, $sortObj->getAscending('test_desc'));
	}
}
