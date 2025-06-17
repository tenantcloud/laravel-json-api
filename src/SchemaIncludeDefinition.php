<?php

namespace TenantCloud\JsonApi;

use TenantCloud\APIVersioning\Constraint\ConstraintChecker;
use TenantCloud\JsonApi\Interfaces\Context;
use TenantCloud\JsonApi\Interfaces\Schema;
use Tests\SchemaIncludeDefinitionTest;

/**
 * @see SchemaIncludeDefinitionTest
 */
class SchemaIncludeDefinition
{
	private ?Schema $schema = null;

	/** @var callable|bool|null */
	private $validation;

	/** @var callable|bool|null */
	private $postAuthorizeUsing;

	private ?array $availableVersionRules = null;

	public function __construct(
		private string $schemaClass,
		private bool $isSingle = true,
		$validation = null,
		$postAuthorizeUsing = null
	) {
		$this->validation = $validation;
		$this->postAuthorizeUsing = $postAuthorizeUsing;
	}

	public static function create(string $schema, bool $isSingle = true, $validation = null, $postAuthorizeUsing = null): self
	{
		return new static($schema, $isSingle, $validation, $postAuthorizeUsing);
	}

	public function versioned(array $versionRules): self
	{
		$this->availableVersionRules = $versionRules;

		return $this;
	}

	public function validateVersion(Context $context): bool
	{
		if (!$this->availableVersionRules || !$context->version()) {
			return true;
		}

		return resolve(ConstraintChecker::class)->compareVersions($context->version(), $this->availableVersionRules);
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
