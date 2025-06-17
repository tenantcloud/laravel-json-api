<?php

namespace TenantCloud\JsonApi;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Traits\ForwardsCalls;
use League\Fractal\Manager;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;
use League\Fractal\TransformerAbstract;
use TenantCloud\JsonApi\Interfaces\Context;
use Tests\JsonApiResponseTest;

/**
 * @see JsonApiResponseTest
 */
class JsonApiResponse implements Responsable
{
	use ForwardsCalls;

	protected $meta = [];

	/** @var LengthAwarePaginator|SupportCollection|null */
	protected $items;

	/** @var Context */
	protected $context;

	protected int $responseCode = 200;

	public function __construct(
		$items,
		/** @var JsonApiTransformer */
		protected TransformerAbstract $transformer
	) {
		$this->items = $items;
	}

	/**
	 * @param Request $request
	 */
	public function toResponse($request): JsonResponse
	{
		return response()->json($this->serialize(), $this->responseCode);
	}

	public function setResponseCode(int $responseCode): self
	{
		$this->responseCode = $responseCode;

		return $this;
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
			case $this->items instanceof CursorPaginator:
				$resource = new Collection($this->items, $this->getTransformer(), $this->context->resourceType());

				$resource->setCursor(
					new Cursor(
						$this->items->cursor()?->encode(),
						$this->items->previousCursor()?->encode(),
						$this->items->nextCursor()?->encode(),
						$this->items->count()
					)
				);

				return $resource;

			default:
				return new Item($this->items, $this->getTransformer(), $this->context->resourceType());
		}
	}

	private function getTransformer(): JsonApiTransformer
	{
		return $this->transformer->setContext($this->context);
	}
}
