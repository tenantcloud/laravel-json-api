<?php

namespace TenantCloud\JsonApi;

use Illuminate\Support\Arr;
use TenantCloud\APIVersioning\Constraint\ConstraintChecker;
use TenantCloud\JsonApi\Interfaces\Context;

class SchemaFieldDefinition
{
	/** @var callable|bool|null */
	private $authorizer;

	/** @var callable|null */
	private $fieldGetter;

	private string $fieldName;

	private ?array $availableVersionRules;

	public function __construct(
		string $fieldName,
		$authorizer = true,
		callable $fieldGetter = null,
		array $availableVersionRules = null
	) {
		$this->fieldName = $fieldName;
		$this->authorizer = $authorizer;
		$this->fieldGetter = $fieldGetter;
		$this->availableVersionRules = $availableVersionRules;
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

	public function getField($obj, RequestContext $context)
	{
		if (is_callable($this->fieldGetter)) {
			return ($this->fieldGetter)($obj, $context);
		}

		return $this->defaultFieldGetter($obj);
	}

	public function versioned(array $versionRules): self
	{
		$this->availableVersionRules = $versionRules;

		return $this;
	}

	public function getConstraints(): ?array
	{
		return $this->availableVersionRules;
	}

	public function validateVersion(Context $context): bool
	{
		if (!$this->availableVersionRules || !$context->version()) {
			return true;
		}

		return resolve(ConstraintChecker::class)->compareVersions($context->version(), $this->availableVersionRules);
	}

	private function defaultFieldGetter($obj)
	{
		if (is_array($obj)) {
			return Arr::get($obj, $this->fieldName);
		}

		return $obj->{$this->fieldName};
	}
}
