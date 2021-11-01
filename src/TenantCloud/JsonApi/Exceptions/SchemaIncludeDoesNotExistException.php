<?php

namespace TenantCloud\JsonApi\Exceptions;

use Exception;

class SchemaIncludeDoesNotExistException extends Exception
{
	public function __construct(string $resource, string $include)
	{
		parent::__construct("Json api schema for resource '{$resource}' does not contain '{$include}' include.");
	}
}
