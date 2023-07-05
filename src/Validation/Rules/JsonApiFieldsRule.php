<?php

namespace TenantCloud\JsonApi\Validation\Rules;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use TenantCloud\JsonApi\JsonApiRegistry;
use Tests\JsonApiFieldsRuleTest;

/**
 * @see JsonApiFieldsRuleTest
 */
class JsonApiFieldsRule implements Rule
{
	private string $apiUrl;

	private array $wrongFields = [];

	private string $errorType = '';

	public function __construct(string $apiUrl)
	{
		$this->apiUrl = $apiUrl;
	}

	/**
	 * @param string $attribute
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

			$this->wrongFields = [$schemaName];
			$this->errorType = 'schema';

			return !resolve(Repository::class)->get('json-api.strict_validation');
		}

		$availableFields = array_keys($schema->getAttributes());
		$requestedFields = explode(',', $fields);
		$this->wrongFields = array_diff($requestedFields, $availableFields);

		if ($this->wrongFields) {
			resolve(LoggerInterface::class)
				->debug('Wrong json api fields are requested', [
					'schema'       => $schemaName,
					'wrong_fields' => array_values($this->wrongFields),
					'route'        => $this->apiUrl,
				]);

			$this->errorType = 'fields';

			if (resolve(Repository::class)->get('json-api.strict_validation')) {
				return false;
			}
		}

		return true;
	}

	public function message(): string
	{
		return "The requested {$this->errorType} not valid: " . implode(', ', $this->wrongFields);
	}
}
