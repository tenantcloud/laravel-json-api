<?php

namespace TenantCloud\JsonApi\Validation\Rules;

use Illuminate\Contracts\Validation\Rule;
use Psr\Log\LoggerInterface;
use function TenantCloud\JsonApi\array_filter_empty;
use Tests\Backend\Unit\Validation\Rules\JsonApiIncludesRuleTest;

/**
 * @see JsonApiIncludesRuleTest
 */
class JsonApiIncludesRule implements Rule
{
	private array $availableIncludes;

	private string $apiUrl;

	public function __construct(array $availableIncludes, string $apiUrl)
	{
		$this->availableIncludes = $availableIncludes;
		$this->apiUrl = $apiUrl;
	}

	/**
	 * @param string $attribute
	 * @param mixed  $include
	 */
	public function passes($attribute, $include): bool
	{
		$include = array_filter_empty(explode(',', $include));

		$validatedIncludes = array_intersect($this->availableIncludes, $include);

		$wrongIncludes = array_diff($include, $validatedIncludes);

		if ($wrongIncludes) {
			resolve(LoggerInterface::class)
				->debug('Wrong includes are requested', [
					'wrong_includes' => $wrongIncludes,
					'route'          => $this->apiUrl,
				]);

			return false;
		}

		return true;
	}

	public function message(): string
	{
		return trans('exceptions.not_valid_json_api_request');
	}
}
