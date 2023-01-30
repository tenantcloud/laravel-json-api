<?php

namespace TenantCloud\JsonApi;

use Closure;
use Illuminate\Support\Arr;
use League\Fractal\TransformerAbstract;
use TenantCloud\JsonApi\Exceptions\SchemaDoesNotExistException;

class JsonApiTransformer extends TransformerAbstract
{
	/** @var array<array-key, string> */
	protected array $fields = [];

	/** @var array<array-key, mixed> */
	protected array $meta = [];

	/** @var ?Closure($item):array<array-key, mixed> */
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

	/**
	 * @param array<array-key, string> $fields
	 *
	 * @return static
	 */
	public function setFields(array $fields): self
	{
		$this->fields = $fields;

		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getFields(): array
	{
		return $this->fields;
	}

	/**
	 * @param callable($item):array<array-key, mixed>|null $callable
	 *
	 * @return static
	 */
	public function setItemMetaCallback(?callable $callable): self
	{
		$this->itemMetaCallback = is_callable($callable) ? Closure::fromCallable($callable) : null;

		return $this;
	}

	/**
	 * @return callable($item):array<array-key, mixed>|null
	 */
	public function getItemMetaCallback(): ?callable
	{
		return $this->itemMetaCallback;
	}

	/**
	 * @return static
	 */
	public function setMeta(array $meta): self
	{
		$this->meta = $meta;

		return $this;
	}

	/**
	 * @return mixed[]
	 */
	public function getMeta(): array
	{
		return $this->meta;
	}

	public function getFieldsByResourceType(): array
	{
		$resourceKey = $this->getCurrentScope()->getResource()->getResourceKey();

		return Arr::get($this->fields, $resourceKey) ?? ['id'];
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
				// If in any case we don't have field definition but have key we use default extractor.
				$data[$field] = (new SchemaFieldDefinition($field))->getField($item);
			}
		}

		return $data;
	}
}
