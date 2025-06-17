<?php

namespace TenantCloud\JsonApi\AttributeContext;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use TenantCloud\JsonApi\DTO\RelationshipDTO;

class Relationships
{
	private array $parsedRelationships = [];

	public function __construct(private array $originalRelationships)
	{
		foreach ($this->originalRelationships as $key => $item) {
			if (!$item || !is_array($item)) {
				continue;
			}

			if (Arr::has($item, 'id') && Arr::has($item, 'type')) {
				$this->parsedRelationships[$key] = RelationshipDTO::from($item);
			} else {
				$this->parsedRelationships[$key] = [];

				foreach ($item as $value) {
					$this->parsedRelationships[$key][] = RelationshipDTO::from($value);
				}
			}
		}
	}

	public function original(): array
	{
		return $this->originalRelationships;
	}

	public function parsed(): array
	{
		return $this->parsedRelationships;
	}

	public function parsedAsArray(): array
	{
		$result = [];

		foreach ($this->parsedRelationships as $key => $value) {
			if ($value instanceof Arrayable) {
				$result[$key] = $value->toArray();

				continue;
			}

			$result[$key] = [];

			foreach ($value as $valueItem) {
				if ($valueItem instanceof Arrayable) {
					$result[$key][] = $valueItem->toArray();
				}
			}
		}

		return $result;
	}

	public function getOriginalByKey(string $key): ?array
	{
		return Arr::get($this->originalRelationships, $key);
	}

	public function getParsedByKey(string $key): ?array
	{
		return Arr::get($this->parsedRelationships, $key);
	}
}
