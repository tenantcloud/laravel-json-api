<?php

namespace TenantCloud\JsonApi\AttributeContext;

use Illuminate\Support\Arr;
use Tests\AttributeContext\FilterTest;

/**
 * @see FilterTest
 */
class Filter
{
	public function __construct(private array $filters = []) {}

	public function all(): array
	{
		return $this->filters;
	}

	public function only(array $keys): array
	{
		return Arr::only($this->filters, $keys);
	}

	public function setFilter(string $key, $value): self
	{
		Arr::set($this->filters, $key, $value);

		return $this;
	}

	public function getOne(string $key)
	{
		return Arr::get($this->filters, $key);
	}
}
