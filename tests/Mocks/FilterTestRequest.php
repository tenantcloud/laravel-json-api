<?php

namespace Tests\Mocks;

use Illuminate\Validation\Rule;
use TenantCloud\JsonApi\JsonApiRequest;

class FilterTestRequest extends JsonApiRequest
{
	public ?array $filterBeforeValidation = null;

	protected $schema = TestUserSchema::class;

	protected array $availableFilters = [
		'lease.expires_in',
	];

	public function authorize(): bool
	{
		return true;
	}

	public function rules(): array
	{
		return array_merge(parent::rules(), [
			'filter.lease.expires_in' => ['sometimes', 'string', Rule::in(['30_days', '60_days'])],
		]);
	}

	protected function prepareForValidation(): void
	{
		// Same as child requests in apps: read the filter with get() and merge it back.
		$this->filterBeforeValidation = $this->get('filter');

		$this->merge(['filter' => $this->filterBeforeValidation]);
	}
}
