<?php

namespace Tests\AttributeContext;

use TenantCloud\JsonApi\AttributeContext\Includes;
use Tests\TestCase;

/**
 * Class IncludesTest
 *
 * @see Includes
 */
class IncludesTest extends TestCase
{
	public function testSetOriginalIncludesInConstructor()
	{
		$originalIncludes = [
			'test',
		];

		$includesObj = new Includes();
		$this->assertSame([], $includesObj->all());

		$includesObj = new Includes($originalIncludes);
		$this->assertSame($originalIncludes, $includesObj->all());
	}

	public function testAddValidatedIncludes()
	{
		$key = 'key';
		$includesObj = new Includes();

		$includesObj->addValidated($key);
		$this->assertContains($key, $includesObj->getValidatedIncludes());

		$includesObj->addValidated($key);

		// Assert no duplicates in $includesObj->getValidatedIncludes()
		$this->assertFalse(count($includesObj->getValidatedIncludes()) > count(array_unique($includesObj->getValidatedIncludes())));
	}

	public function testGetValidatedIncludes()
	{
		$key = 'key';
		$key1 = 'key1';

		$includesObj = new Includes();
		$this->assertSame([], $includesObj->getValidatedIncludes());

		$includesObj->addValidated($key);
		$includesObj->addValidated($key1);

		$this->assertSame([$key, $key1], $includesObj->getValidatedIncludes());
	}
}
