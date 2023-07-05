<?php

namespace TenantCloud\JsonApi\DTO;

use TenantCloud\DataTransferObjects\DataTransferObject;

/**
 * DTO for {@see RequestContext}
 *
 * @method string getId()
 * @method string getType()
 * @method self   setId(string $id)
 * @method self   setType(string $type)
 * @method bool   hasId()
 * @method bool   hasType()
 */
class RelationshipDTO extends DataTransferObject
{
	protected array $fields = [
		'id',
		'type',
	];
}
