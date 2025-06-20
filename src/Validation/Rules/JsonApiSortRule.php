<?php

namespace TenantCloud\JsonApi\Validation\Rules;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use Tests\JsonApiSortsRuleTest;

use function TenantCloud\JsonApi\array_filter_empty;

/**
 * @see JsonApiSortsRuleTest
 */
class JsonApiSortRule implements Rule
{
	private array $wrongSorts = [];

	public function __construct(
		private array $availableSorts,
		private string $apiUrl
	) {}

	/**
	 * @param string $attribute
	 * @param string $sort
	 */
	public function passes($attribute, $sort): bool
	{
		$sorts = $this->prepareSorts($sort);

		$validatedSorts = array_intersect($this->availableSorts, $sorts);

		$this->wrongSorts = array_diff($sorts, $validatedSorts);

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

	private function prepareSorts(string $sort): array
	{
		$sorts = array_filter_empty(explode(',', $sort));

		return array_map(function (string $sortField) {
			if (Str::startsWith($sortField, '-')) {
				return mb_substr($sortField, 1);
			}

			return $sortField;
		}, $sorts);
	}
}
