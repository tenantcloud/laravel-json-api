<?php

namespace TenantCloud\JsonApi\Validation\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use TenantCloud\JsonApi\JsonApiRegistry;

/**
 * @see JsonApiFieldsRuleTest
 */
class JsonApiFieldsRule implements Rule
{
	private string $apiUrl;

	public function __construct(string $apiUrl)
	{
		$this->apiUrl = $apiUrl;
	}

	/**
	 * @param string $attribute
	 * @param mixed  $fields
	 */
	public function passes($attribute, $fields): bool
	{
		$schemaName = Str::afterLast($attribute, '.');

		$schema = app(JsonApiRegistry::class)->getSchema($schemaName);

		if (!$schema) {
			resolve(LoggerInterface::class)
				->debug('Wrong schema when retrieving json api fields', [
					'schema'            => $schemaName,
					'request_field_key' => $attribute,
					'route'             => $this->apiUrl,
				]);

			return true;
		}

		$availableFields = array_keys($schema->getAttributes());
		$requestedFields = explode(',', $fields);
		$wrongFields = array_diff($requestedFields, $availableFields);

		if ($wrongFields) {
			resolve(LoggerInterface::class)
				->debug('Wrong json api fields are requested', [
					'schema'       => $schemaName,
					'wrong_fields' => array_values($wrongFields),
					'route'        => $this->apiUrl,
				]);
		}

		return true;
	}

	public function message(): string
	{
		return trans('exceptions.not_valid_json_api_request');
	}
}
