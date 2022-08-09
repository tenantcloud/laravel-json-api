<?php

namespace TenantCloud\JsonApi\AttributeContext;

use Illuminate\Support\Arr;
use TenantCloud\JsonApi\DTO\RelationshipDTO;

class RelationShips
{
	private array $originalRelationships;

	private array $parsedRelationships = [];

	public function __construct(array $relationships)
	{
		$this->originalRelationships = $relationships;

		foreach ($this->originalRelationships as $key => $relationship) {
			$item = Arr::get($relationship, 'data');

			if (!$item) {
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

	public function getOriginalByKey(string $key): ?array
	{
		return Arr::get($this->originalRelationships, $key);
	}

	public function getParsedByKey(string $key): ?array
	{
		return Arr::get($this->parsedRelationships, $key);
	}
}
