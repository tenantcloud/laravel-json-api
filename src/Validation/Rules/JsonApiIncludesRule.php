<?php

namespace TenantCloud\JsonApi\Validation\Rules;

use Illuminate\Contracts\Validation\Rule;
use Psr\Log\LoggerInterface;
use Tests\JsonApiIncludesRuleTest;

use function TenantCloud\JsonApi\array_filter_empty;

/**
 * @see JsonApiIncludesRuleTest
 */
class JsonApiIncludesRule implements Rule
{
	private array $availableIncludes;

	private string $apiUrl;

	private array $wrongIncludes = [];

	public function __construct(array $availableIncludes, string $apiUrl)
	{
		$this->availableIncludes = $availableIncludes;
		$this->apiUrl = $apiUrl;
	}

	/**
	 * @param string $attribute
	 */
	public function passes($attribute, $include): bool
	{
		$include = array_filter_empty(explode(',', $include));

		$validatedIncludes = array_intersect($this->availableIncludes, $include);

		$this->wrongIncludes = array_diff($include, $validatedIncludes);

		if ($this->wrongIncludes) {
			resolve(LoggerInterface::class)
				->debug('Wrong includes are requested', [
					'wrong_includes' => $this->wrongIncludes,
					'route'          => $this->apiUrl,
				]);

			return false;
		}

		return true;
	}

	public function message(): string
	{
		return 'The requested includes are not valid: ' . implode(', ', $this->wrongIncludes);
	}
}
