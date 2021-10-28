<?php

namespace TenantCloud\JsonApi\Validation\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Log\LogManager;
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

	public function passes($attribute, $include)
	{
		$include = array_filter_empty(explode(',', $include));

		$validatedIncludes = array_intersect($this->availableIncludes, $include);

		$wrongIncludes = array_diff($include, $validatedIncludes);

		if ($wrongIncludes) {
			resolve(LogManager::class)
				->debug('Wrong includes are requested', [
					'wrong_includes' => $wrongIncludes,
					'route'          => $this->apiUrl,
				]);
		}

		return true;
	}

	public function message()
	{
		return trans('exceptions.not_valid_includes');
	}
}
