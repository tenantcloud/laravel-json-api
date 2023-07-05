<?php

namespace TenantCloud\JsonApi\AttributeContext;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use TenantCloud\JsonApi\Enums\SortType;
use Tests\AttributeContext\SortTest;

/**
 * @see SortTest
 */
class Sort
{
	private array $sort = [];

	public function __construct(array $sortParams = [])
	{
		$this->setSort($sortParams);
	}

	public function setSort(array $sortParams): self
	{
		$sort = [];

		foreach ($sortParams as $sortParam) {
			$order = Str::startsWith($sortParam, '-') ? SortType::DESC : SortType::ASC;

			$sort[ltrim($sortParam, '-')] = $order;
		}

		$this->sort = $sort;

		return $this;
	}

	public function getAscending(string $key): ?string
	{
		return Arr::get($this->sort, $key);
	}

	public function attributes(): array
	{
		return array_keys($this->sort);
	}

	public function all(): array
	{
		return $this->sort;
	}

	public function removeField(string $key): self
	{
		Arr::forget($this->sort, $key);

		return $this;
	}
}
