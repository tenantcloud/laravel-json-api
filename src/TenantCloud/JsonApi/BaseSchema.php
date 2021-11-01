<?php

namespace TenantCloud\JsonApi;

use Exception;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use TenantCloud\JsonApi\Exceptions\DuplicateSchemaFieldDefinitionException;
use TenantCloud\JsonApi\Exceptions\IncludeDoesNotAuthorized;
use TenantCloud\JsonApi\Exceptions\SchemaIncludeDoesNotExistException;
use TenantCloud\JsonApi\Interfaces\Context;
use TenantCloud\JsonApi\Interfaces\Schema;

/**
 * Class BaseSchema
 */
abstract class BaseSchema implements Schema
{
	protected string $primaryAttribute = 'id';

	protected array $attributes = [];

	protected array $includes = [];

	protected array $resources = [];

	protected array $meta = [];

	protected string $resourceType = '';

	protected bool $isShowAttributesInIncluded = true;

	public function __construct(array $attributes)
	{
		$this->validateResourceType();
		$this->initAttributes($attributes);
	}

	public function getPrimaryAttribute(): string
	{
		return $this->primaryAttribute;
	}

	public function getAttributes(): array
	{
		return $this->attributes;
	}

	public function getAttributeExpression(string $key): ?SchemaFieldDefinition
	{
		return Arr::get($this->attributes, $key, null);
	}

	public function getIncludes(): array
	{
		return array_keys($this->includes);
	}

	public function getIncludeDefinition(string $key): ?SchemaIncludeDefinition
	{
		return Arr::get($this->includes, $key);
	}

	public function getResourceType(): string
	{
		return $this->resourceType;
	}

	public function getPrimaryMeta(): array
	{
		return $this->meta;
	}

	public function isShowAttributesInIncluded(): bool
	{
		return $this->isShowAttributesInIncluded;
	}

	public function validate(Context $context): Schema
	{
		// We need validate includes before fields to prevent load all nested includes for fields authorize.
		$this->validateInclude($context);

		// Validate schema and includes attributes. Only validate authorized includes fields.
		$this->validateAttributes($context);

		return $this;
	}

	public function validateAttributes(?Context $context): self
	{
		if (!$context) {
			throw new InvalidArgumentException('No context');
		}

		$availableResources = [$this->getResourceType() => $this->getAttributes()];

		// We use only validates includes to prevent load all nested includes.
		/* @var SchemaIncludeDefinition $include */
		foreach ($context->includes()->getValidatedIncludes() as $includeKey) {
			$include = $this->getNestedIncludeByKey($includeKey);

			if (!$include) {
				continue;
			}

			$schema = $include->getSchemaClass();

			// Make record ['resourceType' => ['key' => callback|bool|null]]
			$availableResources[$schema->getResourceType()] = $schema->getAttributes();
		}

		foreach ($availableResources as $resourceKey => $attributes) {
			// $attributes - ['key' => callback|bool|null]]
			$validatedAttributes = [];

			$fields = $context->fields()->getOriginalByKey($resourceKey);

			$allowedAttributes = count($fields) ? Arr::only($attributes, $fields) : $attributes;

			foreach ($allowedAttributes as $attributeKey => $definition) {
				/** @var SchemaFieldDefinition $definition */
				if ($definition->authorize($context)) {
					$validatedAttributes[] = $attributeKey;
				}
			}

			$validatedAttributes[] = $this->primaryAttribute;

			$context->fields()->addValidated($resourceKey, $validatedAttributes);
		}

		return $this;
	}

	public function validateInclude(?Context $context): self
	{
		if (!$context) {
			throw new InvalidArgumentException('No context');
		}

		$includes = $context->includes()->all();

		foreach ($includes as $include) {
			try {
				$this->authorizeNestedInclude($this, $context, explode('.', $include));
				$context->includes()->addValidated($include);
			} catch (IncludeDoesNotAuthorized $e) {
				// We do not throw exception for not authorized includes.
			}
		}

		return $this;
	}

	/**
	 * Get include definition by nested key.
	 */
	private function getNestedIncludeByKey(string $key): ?SchemaIncludeDefinition
	{
		$include = null;
		$includeTree = explode('.', $key);
		$schema = $this;

		foreach ($includeTree as $includeKey) {
			$include = $schema->getIncludeDefinition($includeKey);

			if (!$include) {
				return $include;
			}

			$schema = $include->getSchemaClass();
		}

		return $include;
	}

	private function authorizeNestedInclude(Schema $schema, Context $context, array $nestedKeys): void
	{
		$baseInclude = array_shift($nestedKeys);

		$include = $schema->getIncludeDefinition($baseInclude);

		if (!$include) {
			throw new SchemaIncludeDoesNotExistException($schema->getResourceType(), $baseInclude);
		}

		$validation = $include->getValidation();
		$expectation = is_callable($validation) ? $validation($context) : $validation;

		if (!$expectation) {
			throw new IncludeDoesNotAuthorized();
		}

		if ($nestedKeys) {
			$this->authorizeNestedInclude($include->getSchemaClass(), $context, $nestedKeys);
		}
	}

	private function validateResourceType(): void
	{
		$isOk = (is_string($this->getResourceType()) === true && empty($this->getResourceType()) === false);

		if ($isOk === false) {
			throw new InvalidArgumentException('Resource type is not set for Schema: ' . static::class . '.');
		}
	}

	private function initAttributes(array $attributes): self
	{
		$associativeAttributes = [];

		foreach ($attributes as $attribute) {
			// If we get only field name we create default field definition.
			if (is_string($attribute)) {
				$attribute = SchemaFieldDefinition::create($attribute);
			}

			/* @var SchemaFieldDefinition $attribute */
			$field = $attribute->fieldName();

			if (Arr::has($associativeAttributes, $field)) {
				throw new DuplicateSchemaFieldDefinitionException($field, class_basename($this));
			}

			$associativeAttributes[$field] = $attribute;
		}

		$this->attributes = $associativeAttributes;

		return $this;
	}
}
