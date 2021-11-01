<?php

namespace TenantCloud\JsonApi;

use TenantCloud\JsonApi\Interfaces\Schema;
use Tests\SchemaIncludeDefinitionTest;

/**
 * @see SchemaIncludeDefinitionTest
 */
class SchemaIncludeDefinition
{
	private string $schemaClass;

	private ?Schema $schema = null;

	/** @var callable|bool|null */
	private $validation;

	/** @var callable|bool|null */
	private $postAuthorizeUsing;

	public function __construct(string $schema, $validation = null, $postAuthorizeUsing = null)
	{
		$this->schemaClass = $schema;
		$this->validation = $validation;
		$this->postAuthorizeUsing = $postAuthorizeUsing;
	}

	public static function create(string $schema, $validation = null, $postAuthorizeUsing = null): self
	{
		return new static($schema, $validation, $postAuthorizeUsing);
	}

	public function postAuthorizeUsing(callable $postAuthorizeUsing): self
	{
		$this->postAuthorizeUsing = $postAuthorizeUsing;

		return $this;
	}

	public function getValidation()
	{
		return $this->validation;
	}

	public function getSchemaClass(): Schema
	{
		if ($this->schema) {
			return $this->schema;
		}

		$this->schema = app($this->schemaClass);

		return $this->schema;
	}

	public function getResourceType(): string
	{
		return $this->getSchemaClass()->getResourceType();
	}
}
