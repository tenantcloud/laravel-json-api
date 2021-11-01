<?php

namespace TenantCloud\JsonApi\AttributeContext;

use Illuminate\Support\Arr;
use Tests\AttributeContext\FilterTest;

/**
 * @see FilterTest
 */
class Filter
{
	private array $filters;

	public function __construct(array $filters = [])
	{
		$this->filters = $filters;
	}

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
