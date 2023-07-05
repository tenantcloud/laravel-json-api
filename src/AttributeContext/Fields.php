<?php

namespace TenantCloud\JsonApi\AttributeContext;

use Illuminate\Support\Arr;
use Tests\AttributeContext\FieldsTest;

/**
 * @see FieldsTest
 */
class Fields
{
	private array $originalFields;

	private array $validatedFields = [];

	public function __construct(array $fields)
	{
		$this->originalFields = $fields;
	}

	public function addValidated(string $key, array $data): self
	{
		Arr::set($this->validatedFields, $key, $data);

		return $this;
	}

	public function validated(): array
	{
		return $this->validatedFields;
	}

	public function getOriginalByKey(string $key)
	{
		return Arr::get($this->originalFields, $key, []);
	}

	public function original(): array
	{
		return $this->originalFields;
	}
}
