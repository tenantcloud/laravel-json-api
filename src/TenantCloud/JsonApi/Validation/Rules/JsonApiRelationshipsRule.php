<?php


namespace TenantCloud\JsonApi\Validation\Rules;


use Illuminate\Contracts\Validation\Rule;

class JsonApiRelationshipsRule implements Rule
{
	private string $schema;

	private string $apiUrl;

	public function __construct(string $schema, string $apiUrl)
	{
		$this->schema = $schema;
		$this->apiUrl = $apiUrl;
	}

	public function passes($attribute, $value)
	{
		// TODO: Implement passes() method.
	}

	public function message()
	{
		// TODO: Implement message() method.
	}
}
