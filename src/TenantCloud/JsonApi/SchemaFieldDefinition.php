<?php

namespace TenantCloud\JsonApi;

use TenantCloud\JsonApi\Interfaces\Context;
use Illuminate\Support\Arr;

/**
 * Class SchemaFieldDefinition
 */
class SchemaFieldDefinition
{
	/** @var callable|bool|null */
	private $authorizer;

	/** @var callable|null */
	private $fieldGetter;

	private string $fieldName;

	public function __construct(string $fieldName, $authorizer = true, callable $fieldGetter = null)
	{
		$this->fieldName = $fieldName;
		$this->authorizer = $authorizer;
		$this->fieldGetter = $fieldGetter;
	}

	public static function create(string $fieldName, $validator = true, callable $fieldGetter = null): self
	{
		return new static($fieldName, $validator, $fieldGetter);
	}

	public function setAuthorizer($authorizer): self
	{
		$this->authorizer = $authorizer;

		return $this;
	}

	public function setExtractor(?callable $fieldGetter): self
	{
		$this->fieldGetter = $fieldGetter;

		return $this;
	}

	public function fieldName(): string
	{
		return $this->fieldName;
	}

	public function authorize(Context $context)
	{
		if (is_callable($this->authorizer)) {
			return ($this->authorizer)($context);
		}

		return $this->authorizer;
	}

	public function getField($obj)
	{
		if (is_callable($this->fieldGetter)) {
			return ($this->fieldGetter)($obj);
		}

		return $this->defaultFieldGetter($obj);
	}

	private function defaultFieldGetter($obj)
	{
		if (is_array($obj)) {
			return Arr::get($obj, $this->fieldName);
		}

		return $obj->{$this->fieldName};
	}
}
