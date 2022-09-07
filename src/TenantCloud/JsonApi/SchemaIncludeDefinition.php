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

	private bool $isSingle;

	public function __construct(string $schema, bool $isSingle = true, $validation = null, $postAuthorizeUsing = null)
	{
		$this->isSingle = $isSingle;
		$this->schemaClass = $schema;
		$this->validation = $validation;
		$this->postAuthorizeUsing = $postAuthorizeUsing;
	}

	public static function create(string $schema, bool $isSingle = true, $validation = null, $postAuthorizeUsing = null): self
	{
		return new static($schema, $isSingle, $validation, $postAuthorizeUsing);
	}

	public function postAuthorizeUsing(callable $postAuthorizeUsing): self
	{
		$this->postAuthorizeUsing = $postAuthorizeUsing;

		return $this;
	}

	public function isSingle(): bool
	{
		return $this->isSingle;
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
