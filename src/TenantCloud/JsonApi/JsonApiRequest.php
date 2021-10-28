<?php

namespace TenantCloud\JsonApi;

use TenantCloud\JsonApi\DTO\ApiRequestDTO;
use TenantCloud\JsonApi\Interfaces\Context;
use TenantCloud\JsonApi\Interfaces\Schema;
use App\Validation\Rules\JsonApiIncludesRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Class JsonApiRequest
 */
abstract class JsonApiRequest extends FormRequest
{
	/** @var array */
	protected array $availableIncludes = [];

	/** @var array */
	protected array $availableFilters = [];

	/** @var array */
	protected array $availableSorts = [];

	protected ?Schema $schema;

	private ?RequestContext $context;

	public function rules(): array
	{
		return [
			'fields'   => ['array', 'max:30'],
			'fields.*' => ['string', 'max:1000'],
			'sort'     => ['string', 'max:500'],
			'filter'   => ['array', 'max:30'],
			'include'  => ['string', 'max:500', new JsonApiIncludesRule($this->availableIncludes, $this->route()->uri)],
			'page'     => ['integer', 'min:1'],
		];
	}

	public function authorizeSchema(): self
	{
		/** @var Schema $schema */
		$schema = app($this->schema);

		$schema->validate($this->context);

		return $this;
	}

	public function authorize(): bool
	{
		return true;
	}

	public function context(): ?Context
	{
		return $this->context;
	}

	protected function passedValidation(): self
	{
		$this->makeContext();
		$this->authorizeSchema();

		return $this;
	}

	final protected function transformParameters(): self
	{
		$this->transformSorts();
		$this->transformFields();
		$this->transformFilters();
		$this->transformInclude();

		return $this;
	}

	private function makeContext(): self
	{
		/** @var Schema $schema */
		$schema = app($this->schema);

		$this->transformParameters();
		$data = ApiRequestDTO::from($this->all());

		$this->context = new RequestContext($this->user(), $data, $schema->getResourceType());

		return $this;
	}

	private function transformSorts(): void
	{
		$sort = explode(',', $this->get('sort', ''));
		$validatedSorts = [];

		foreach ($sort as $value) {
			if (in_array($this->parseSortField($value), $this->availableSorts, true)) {
				$validatedSorts[] = $value;
			}
		}

		$this->merge(['sort' => $validatedSorts]);
	}

	private function parseSortField(string $value): string
	{
		if (Str::startsWith($value, '-')) {
			$value = Str::substr($value, 1);
		}

		return $value;
	}

	private function transformFields(): void
	{
		$fields = $this->get('fields', []);

		$newFields = [];

		foreach ($fields as $field => $values) {
			$newFields[$field] = explode(',', $values);
		}

		$this->merge(['fields' => $newFields]);
	}

	private function transformFilters(): void
	{
		$filters = Arr::dot(Arr::get($this->all(), 'filter', []));
		$newFilter = [];

		foreach ($filters as $filter => $values) {
			foreach ($this->availableFilters as $availableFilter) {
				if (Str::startsWith($filter, $availableFilter)) {
					Arr::set($newFilter, $filter, $values);
				}
			}
		}

		$this->merge(['filter' => $newFilter]);
	}

	private function transformInclude(): void
	{
		$include = explode(',', $this->get('include', ''));

		$this->merge(['include' => array_filter_empty(array_intersect($this->availableIncludes, $include))]);
	}
}
