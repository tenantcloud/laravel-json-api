<?php

namespace Tests;

use function TenantCloud\JsonApi\array_filter_empty;

class FunctionsTest extends TestCase
{
	public function testArrayFilterEmpty(): void
	{
		$testValues = [
			[
				'actual' => [
					'name' => 'Not Today',
				],
				'expected' => [
					'name' => 'Not Today',
				],
			],
			[
				'actual' => [
					'name' => ' ',
				],
				'expected' => [],
			],
			[
				'actual' => [
					'name' => '',
				],
				'expected' => [],
			],
			[
				'actual' => [
					'name' => null,
				],
				'expected' => [],
			],
			[
				'actual' => [
					'name'    => ' ',
					'company' => 'Company',
				],
				'expected' => [
					'company' => 'Company',
				],
			],
		];

		foreach ($testValues as $testCase) {
			$this->assertEquals($testCase['expected'], array_filter_empty($testCase['actual']));
		}
	}
}
