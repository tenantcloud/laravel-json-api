<?php

namespace TenantCloud\JsonApi;

use App\Http\Controllers\ControllerTraits\ApiTrait;
use TenantCloud\JsonApi\Interfaces\Context;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\ForwardsCalls;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;
use League\Fractal\Serializer\JsonApiSerializer;
use League\Fractal\TransformerAbstract;
use Tests\JsonApiResponseTest;

/**
 * Class JsonResponse
 *
 * @see JsonApiResponseTest
 *
 * @method self respondWithStatusUpdated()
 * @method self respondWithStatusCreated()
 */
class JsonApiResponse implements Responsable
{
	use ApiTrait;
	use ForwardsCalls;

	/** @var mixed */
	protected $meta = [];

	/** @var JsonApiTransformer */
	protected $transformer;

	/** @var LengthAwarePaginator|SupportCollection|null */
	protected $items;

	/** @var Context */
	protected $context;

	protected string $responseMethod = '';

	public function __construct($items, TransformerAbstract $transformer)
	{
		$this->items = $items;
		$this->transformer = $transformer;
	}

	public function __call($name, $arguments)
	{
		if (Str::startsWith($name, 'respondWithStatus')) {
			$this->responseMethod = mb_substr($name, 17);

			return $this;
		}

		$this->forwardCallTo($this, $name, $arguments);
	}

	/**
	 * @param Request $request
	 */
	public function toResponse($request): JsonResponse
	{
		return $this->forwardCallTo($this, $this->getResponseMethod(), [$this->serialize()]);
	}

	public function serialize(): array
	{
		$includes = $this->context->includes()->getValidatedIncludes();

		$this->transformer->setMeta($this->meta);

		$serialize = (new Manager())
			->setSerializer(new JsonApiSerializer())
			->parseIncludes($includes)
			->createData($this->makeResource()->setMeta($this->transformer->getMeta()))
			->toArray();

		// Unset links key (we wont to extend all serializer for cut links key from response for pagination collection only.
		// For ArraySerializer we cut this key in ApiTrait::buildPagination() function.
		Arr::forget($serialize, 'links');

		return $serialize;
	}

	public function setMeta(array $meta): self
	{
		$this->meta = $meta;

		return $this;
	}

	public function setContext(Context $context): self
	{
		$this->context = $context;

		return $this;
	}

	protected function getResponseMethod(): string
	{
		return 'respond' . ($this->responseMethod ?: 'Ok');
	}

	protected function makeResource()
	{
		switch (true) {
			case $this->items === null:
				return new NullResource($this->items, $this->getTransformer(), $this->context->resourceType());
			case $this->items instanceof LengthAwarePaginator:
				$resource = new Collection($this->items, $this->getTransformer(), $this->context->resourceType());

				$resource->setPaginator(new IlluminatePaginatorAdapter($this->items));

				return $resource;
			case $this->items instanceof SupportCollection:
				return new Collection($this->items, $this->getTransformer(), $this->context->resourceType());

			default:
				return new Item($this->items, $this->getTransformer(), $this->context->resourceType());
		}
	}

	private function getTransformer(): JsonApiTransformer
	{
		return $this->transformer
			->setFields($this->context->fields()->validated());
	}
}
