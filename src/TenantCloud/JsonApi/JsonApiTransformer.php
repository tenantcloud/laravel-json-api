<?php

namespace TenantCloud\JsonApi;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\TransformerAbstract;
use TenantCloud\JsonApi\Exceptions\SchemaDoesNotExistException;

class JsonApiTransformer extends TransformerAbstract
{
	protected array $fields = [];

	protected array $meta = [];

	protected ?Closure $itemMetaCallback = null;

	public function transform($item): array
	{
		$data = $this->transformSchemaFields($item);
		$meta = is_callable($this->getItemMetaCallback()) ? $this->getItemMetaCallback()($item) : null;

		if ($meta) {
			$data['meta'] = $meta;
		}

		return $data;
	}

	public function setFields(array $fields): self
	{
		$this->fields = $fields;

		return $this;
	}

	public function getFields(): array
	{
		return $this->fields;
	}

	public function setItemMetaCallback(?callable $callable): self
	{
		$this->itemMetaCallback = $callable;

		return $this;
	}

	public function getItemMetaCallback(): ?callable
	{
		return $this->itemMetaCallback;
	}

	public function setMeta(array $meta): self
	{
		$this->meta = $meta;

		return $this;
	}

	public function getMeta(): array
	{
		return $this->meta;
	}

	public function getFieldsByResourceType(): array
	{
		$resourceKey = $this->getCurrentScope()->getResource()->getResourceKey();

		return Arr::get($this->fields, $resourceKey) ?? ['id'];
	}

	public function jsonApiItem(Model $model, string $relation, self $transformer, string $schema): ?ResourceInterface
	{
		if (!$model->relationLoaded($relation)) {
			return null;
		}

		if (!$model->{$relation}) {
			return $this->null();
		}

		return $this->item(
			$model->{$relation},
			$transformer->setFields($this->getFields()),
			app($schema)->getResourceType()
		);
	}

	public function jsonApiCollection(Model $model, string $relation, self $transformer, string $schema): ?Collection
	{
		if (!$model->relationLoaded($relation)) {
			return null;
		}

		return $this->collection(
			$model->{$relation},
			$transformer->setFields($this->getFields()),
			app($schema)->getResourceType()
		);
	}

	private function transformSchemaFields($item): array
	{
		$data = [];
		$fields = $this->getFieldsByResourceType();

		$schema = app(JsonApiRegistry::class)->getSchema($this->getCurrentScope()->getResource()->getResourceKey());

		if (!$schema) {
			throw new SchemaDoesNotExistException($this->getCurrentScope()->getResource()->getResourceKey());
		}

		foreach ($fields as $field) {
			$fieldDefinition = $schema->getAttributeExpression($field);

			if ($fieldDefinition) {
				$data[$field] = $schema->getAttributeExpression($field)->getField($item);
			} else {
				// If in any case we dont have field definition but have key we use default extractor.
				$data[$field] = (new SchemaFieldDefinition($field))->getField($item);
			}
		}

		return $data;
	}
}
