<?php

namespace TenantCloud\JsonApi\Exceptions;

use Exception;

class DuplicateSchemaFieldDefinitionException extends Exception
{
	public function __construct(string $field, string $schema)
	{
		parent::__construct("Duplicate field '{$field}' in {$schema} json api schema class.");
	}
}
