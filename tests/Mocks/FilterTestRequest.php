<?php

namespace Tests\Mocks;

use Illuminate\Support\Arr;
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
		$this->filterBeforeValidation = Arr::get($this->all(), 'filter');
	}
}
