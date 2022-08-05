<?php

namespace TenantCloud\JsonApi\AttributeContext;

use Illuminate\Support\Arr;

class RelationShips
{
	private array $originalRelationships;

	private array $validatedRelationships = [];

	public function __construct(array $fields)
	{
		$this->originalRelationships = $fields;
	}

	public function original(): array
	{
		return $this->originalRelationships;
	}

	public function validated(): array
	{
		return $this->validatedRelationships;
	}

	public function addValidated(string $key, array $data, bool $force = false): self
	{
		if (!$force && in_array($key, $this->validatedRelationships, true)) {
			return $this;
		}

		Arr::set($this->validatedRelationships, $key, $data);

		return $this;
	}

	public function hasValidated(string $key): bool
	{
		return in_array($key, $this->validatedRelationships, true);
	}

	public function getOriginalByKey(string $key): ?array
	{
		return Arr::get($this->originalRelationships, "{$key}.data");
	}

	public function getValidatedByKey(string $key): ?array
	{
		return Arr::get($this->validatedRelationships, "{$key}.data");
	}
}
