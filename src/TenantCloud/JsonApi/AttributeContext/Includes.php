<?php

namespace TenantCloud\JsonApi\AttributeContext;

use Tests\AttributeContext\IncludesTest;

/**
 * Class Includes
 *
 * @see IncludesTest
 */
class Includes
{
	/** @var array */
	private array $originalIncludes;

	/** @var array */
	private array $validatedIncludes = [];

	public function __construct(array $includes = [])
	{
		$this->originalIncludes = $includes;
	}

	public function all(): array
	{
		return $this->originalIncludes;
	}

	public function getValidatedIncludes(): array
	{
		return $this->validatedIncludes;
	}

	public function hasValidated(string $key): bool
	{
		return in_array($key, $this->validatedIncludes, true);
	}

	public function addValidated(string $key): self
	{
		if (in_array($key, $this->validatedIncludes)) {
			return $this;
		}

		$this->validatedIncludes[] = $key;

		return $this;
	}
}
