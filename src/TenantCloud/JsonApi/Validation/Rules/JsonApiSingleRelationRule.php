<?php

namespace TenantCloud\JsonApi\Validation\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use TenantCloud\JsonApi\BaseSchema;
use Tests\JsonApiSingleRelationRuleTest;

/**
 * @see JsonApiSingleRelationRuleTest
 */
class JsonApiSingleRelationRule implements Rule
{
	private string $schemaClass;

	private string $errorMessage = 'Relationship structure is invalid.';

	public function __construct(string $schemaClass)
	{
		$this->schemaClass = $schemaClass;
	}

	public function passes($attribute, $value)
	{
		/** @var BaseSchema $schema */
		$schema = app($this->schemaClass);

		$relationship = Str::afterLast($attribute, '.');
		$data = Arr::get($value, 'data');

		// Allow null value for relationship.
		if ($data === null && Arr::has($value, 'data')) {
			return true;
		}

		if (!is_array($data)) {
			return false;
		}

		if ($this->isSingle($data)) {
			return $this->validate($data, $relationship, $schema);
		}

		foreach ($data as $item) {
			if (!is_array($item) || !$this->validate($item, $relationship, $schema)) {
				return false;
			}
		}

		return true;
	}

	public function message()
	{
		return $this->errorMessage;
	}

	protected function isSingle($value): bool
	{
		return Arr::has($value, 'id') && Arr::has($value, 'type');
	}

	protected function validate(array $value, string $relationship, BaseSchema $schema): bool
	{
		if (!Arr::has($value, 'id') || !Arr::has($value, 'type')) {
			$this->errorMessage = 'Relationship must include \'id\' and \'type\' keys.';

			return false;
		}

		$include = $schema->getIncludeDefinition($relationship);

		if (!$include) {
			$resource = $schema->getResourceType();
			$this->errorMessage = "Relationship {$relationship} does not exists for resource {$resource}.";

			return false;
		}

		if ($include->getResourceType() !== Arr::get($value, 'type')) {
			$type = $include->getResourceType();
			$this->errorMessage = "{$relationship} type does not must be {$type}.";

			return false;
		}

		if (!is_string(Arr::get($value, 'id'))) {
			$type = $include->getResourceType();
			$this->errorMessage = "Relationships {$relationship} 'id' must be string.";

			return false;
		}

		return true;
	}
}
