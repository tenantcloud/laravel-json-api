<?php

namespace TenantCloud\JsonApi\Validation\Rules;

use Illuminate\Contracts\Validation\Rule;
use Psr\Log\LoggerInterface;
use Tests\JsonApiRelationshipsRuleTest;

/**
 * @see JsonApiRelationshipsRuleTest
 */
class JsonApiRelationshipsRule implements Rule
{
	private ?array $wrongRelationships = [];

	public function __construct(
		private array $availableRelationships,
		private string $apiUrl
	) {}

	public function passes($attribute, $value)
	{
		$relationships = array_keys($value);

		$validatedIncludes = array_intersect($this->availableRelationships, $relationships);

		$this->wrongRelationships = array_diff($relationships, $validatedIncludes);

		if ($this->wrongRelationships) {
			resolve(LoggerInterface::class)
				->debug('Wrong relationships are used', [
					'wrong_relationships' => $this->wrongRelationships,
					'route'               => $this->apiUrl,
				]);

			return false;
		}

		return true;
	}

	public function message(): string
	{
		return 'The used relationships are not valid: ' . implode(', ', $this->wrongRelationships);
	}
}
