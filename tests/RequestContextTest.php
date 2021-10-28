<?php

namespace Tests;

use TenantCloud\JsonApi\DTO\ApiRequestDTO;
use TenantCloud\JsonApi\Enums\SortType;
use TenantCloud\JsonApi\RequestContext;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AttributeContext\PageTest;
use Tests\TestCase;

/**
 * Class RequestContextTest
 *
 * @see RequestContext
 */
class RequestContextTest extends TestCase
{
	use DatabaseTransactions;

	public function testConstructorSetDataObject()
	{
		$user = factory(User::class)->create();
		$data = ApiRequestDTO::create();

		$context = new RequestContext($user, $data, $this->faker->name);

		$this->assertSame([], $context->sort()->all());
		$this->assertSame([], $context->filters()->all());
		$this->assertSame(PageTest::DEFAULT_PAGE, $context->page()->getPage());
		$this->assertSame([], $context->includes()->all());
		$this->assertSame([], $context->fields()->original());

		$filledData = $this->makeContextData();
		$data = ApiRequestDTO::from($filledData);

		$context = new RequestContext($user, $data, $this->faker->name);

		$expectedSortData = [
			'asc_test'  => SortType::ASC,
			'desc_test' => SortType::DESC,
		];
		$this->assertSame($expectedSortData, $context->sort()->all());
		$this->assertSame($filledData['filter'], $context->filters()->all());
		$this->assertSame($filledData['page'], $context->page()->getPage());
		$this->assertSame($filledData['include'], $context->includes()->all());
		$this->assertSame($filledData['fields'], $context->fields()->original());
	}

	public function testResourceType()
	{
		$resourceType = $this->faker->name;
		$user = factory(User::class)->create();
		$data = ApiRequestDTO::create();

		$context = new RequestContext($user, $data, $resourceType);

		$this->assertSame($resourceType, $context->resourceType());
	}

	protected function makeContextData(): array
	{
		return [
			'sort'    => ['asc_test', '-desc_test'],
			'filter'  => ['test' => $this->faker->name],
			'page'    => 2,
			'include' => ['test'],
			'fields'  => ['test' => $this->faker->name],
		];
	}
}
