<?php

namespace TenantCloud\JsonApi\Exceptions;

use Exception;

class SchemaDoesNotExistException extends Exception
{
	public function __construct(string $resource)
	{
		parent::__construct("Json api schema for resource '{$resource}' does not exists.");
	}
}
