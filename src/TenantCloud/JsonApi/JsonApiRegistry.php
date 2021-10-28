<?php

namespace TenantCloud\JsonApi;

use Illuminate\Support\Arr;

class JsonApiRegistry
{
	/** @var BaseSchema[] */
	protected array $schemas = [];

	public function register(BaseSchema $schema): self
	{
		$this->schemas[$schema->getResourceType()] = $schema;

		return $this;
	}

	public function schemas(): array
	{
		return $this->schemas;
	}

	public function getSchema(string $key): ?BaseSchema
	{
		return Arr::get($this->schemas, $key);
	}
}
