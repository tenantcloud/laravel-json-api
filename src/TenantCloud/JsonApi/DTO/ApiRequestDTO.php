<?php

namespace TenantCloud\JsonApi\DTO;

use TenantCloud\DataTransferObjects\DataTransferObject;
use TenantCloud\JsonApi\RequestContext;

/**
 * DTO for {@see RequestContext}
 *
 * @method array getSort()
 * @method array getFilter()
 * @method int   getPage()
 * @method array getInclude()
 * @method array getFields()
 * @method self  setSort(array $sort)
 * @method self  setFilter(array $filters)
 * @method self  setPage(int $page)
 * @method self  setInclude(array $includes)
 * @method self  setFields(array $fields)
 */
class ApiRequestDTO extends DataTransferObject
{
	protected array $fields = [
		'sort',
		'filter',
		'page',
		'include',
		'fields',
	];
}
