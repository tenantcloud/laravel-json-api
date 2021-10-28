<?php

namespace TenantCloud\JsonApi\AttributeContext;

use Illuminate\Support\Arr;
use Tests\AttributeContext\FieldsTest;

/**
 * Class Fields
 *
 * @see FieldsTest
 */
class Fields
{
	/** @var array */
	private array $originalFields;

	/** @var array */
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
