<?php

namespace TenantCloud\JsonApi;

use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Foundation\Http\FormRequest;
use TenantCloud\JsonApi\DTO\ApiRequestDTO;
use TenantCloud\JsonApi\Interfaces\Context;
use TenantCloud\JsonApi\Interfaces\Schema;
use TenantCloud\JsonApi\Validation\Rules\JsonApiRelationshipsRule;
use TenantCloud\JsonApi\Validation\Rules\JsonApiSingleRelationRule;
use Tests\JsonApiStoreUpdateRequestTest;

/**
 * @see JsonApiStoreUpdateRequestTest
 */
abstract class JsonApiStoreUpdateRequest extends FormRequest
{
	/** @var Schema|string */
	protected $schema;

	protected array $availableRelationships = [];

	private ?RequestContext $context;

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

	public function validateResolved(): void
	{
		$this->preValidateBasic();

		parent::validateResolved();
	}

	protected function passedValidation(): self
	{
		$this->makeContext();
		$this->authorizeSchema();

		return $this;
	}

	/**
	 * Do basic validation for validity of basic post/put jsonApi request parameters, then
	 * replace request data with plain entity attributes to have error messages without mapping.
	 */
	protected function preValidateBasic(): void
	{
		$rules = [
			'data'                 => ['required', 'array'],
			'data.type'            => ['required', 'string'],
			'data.attributes'      => ['required', 'array'],
			'data.relationships'   => ['sometimes', 'array', new JsonApiRelationshipsRule($this->availableRelationships, $this->route()->uri)],
			'data.relationships.*' => ['array:data', new JsonApiSingleRelationRule($this->schema)],
		];
		$factory = $this->container->make(ValidationFactory::class);
		$validator = $factory->make($this->validationData(), $rules);

		if ($validator->fails()) {
			$this->failedValidation($validator);
		}

		$this->replace(array_merge($this->input('data.attributes', []), $this->input('data.relationships', [])));
	}

	private function makeContext(): self
	{
		/** @var Schema $schema */
		$schema = app($this->schema);

		$this->context = new RequestContext(
			$this->user(),
			ApiRequestDTO::create()->setRelationships($this->only($this->availableRelationships)),
			$schema->getResourceType()
		);

		return $this;
	}
}
