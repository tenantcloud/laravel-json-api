<?php

namespace TenantCloud\JsonApi\Interfaces;

use TenantCloud\JsonApi\SchemaIncludeDefinition;

/**
 * Interface SchemaInterface
 */
interface Schema
{
	public function getPrimaryAttribute(): string;

	public function getIncludeDefinition(string $key): ?SchemaIncludeDefinition;

	public function getResourceType(): string;

	public function getAttributes(): array;

	public function getIncludes(): array;

	public function getPrimaryMeta(): array;

	public function validateAttributes();

	public function validate(Context $context): self;
}
