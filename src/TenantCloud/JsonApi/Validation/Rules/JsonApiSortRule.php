<?php

namespace TenantCloud\JsonApi\Validation\Rules;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Validation\Rule;
use Psr\Log\LoggerInterface;
use function TenantCloud\JsonApi\array_filter_empty;
use Tests\JsonApiSortsRuleTest;

/**
 * @see JsonApiSortsRuleTest
 */
class JsonApiSortRule implements Rule
{
	private array $availableSorts;

	private string $apiUrl;

	private array $wrongSorts = [];

	public function __construct(array $availableSorts, string $apiUrl)
	{
		$this->availableSorts = $availableSorts;
		$this->apiUrl = $apiUrl;
	}

	/**
	 * @param string $attribute
	 * @param mixed  $sort
	 */
	public function passes($attribute, $sort): bool
	{
		$sort = array_filter_empty(explode(',', $sort));

		$validatedSorts = array_intersect($this->availableSorts, $sort);

		$this->wrongSorts = array_diff($sort, $validatedSorts);

		if ($this->wrongSorts) {
			resolve(LoggerInterface::class)
				->debug('Wrong sorts are requested', [
					'wrong_sorts' => $this->wrongSorts,
					'route'       => $this->apiUrl,
				]);

			if (resolve(Repository::class)->get('json-api.strict_validation')) {
				return false;
			}
		}

		return true;
	}

	public function message(): string
	{
		return 'The requested sorts are not valid: ' . implode(', ', $this->wrongSorts);
	}
}
