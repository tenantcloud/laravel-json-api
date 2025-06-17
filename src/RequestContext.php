<?php

namespace TenantCloud\JsonApi;

use TenantCloud\APIVersioning\Version\Version;
use TenantCloud\JsonApi\AttributeContext\Fields;
use TenantCloud\JsonApi\AttributeContext\Filter;
use TenantCloud\JsonApi\AttributeContext\Includes;
use TenantCloud\JsonApi\AttributeContext\Page;
use TenantCloud\JsonApi\AttributeContext\Relationships;
use TenantCloud\JsonApi\AttributeContext\Sort;
use TenantCloud\JsonApi\DTO\ApiRequestDTO;
use TenantCloud\JsonApi\Interfaces\Context;
use Tests\RequestContextTest;

/**
 * @see RequestContextTest
 *
 * @template-covariant TUser
 *
 * @implements Context<TUser>
 */
class RequestContext implements Context
{
	/** @var TUser */
	protected $user;

	protected Fields $fields;

	protected Includes $includes;

	protected Filter $filters;

	protected Page $page;

	protected Sort $sort;

	protected Relationships $relationships;

	/**
	 * @param TUser $user
	 */
	public function __construct(
		$user,
		ApiRequestDTO $params,
		protected ?string $resourceType = null,
		protected ?Version $version = null
	) {
		$this->user = $user;

		$this->sort = new Sort($params->getSort() ?? []);
		$this->filters = new Filter($params->getFilter() ?? []);
		$this->page = (new Page())->setPage($params->getPage() ?? 1);
		$this->includes = new Includes($params->getInclude() ?? []);
		$this->fields = new Fields($params->getFields() ?? []);
		$this->relationships = new Relationships($params->getRelationships() ?? []);
	}

	/**
	 * @return TUser
	 */
	public function user()
	{
		return $this->user;
	}

	public function fields(): Fields
	{
		return $this->fields;
	}

	public function includes(): Includes
	{
		return $this->includes;
	}

	public function filters(): Filter
	{
		return $this->filters;
	}

	public function page(): Page
	{
		return $this->page;
	}

	public function sort(): Sort
	{
		return $this->sort;
	}

	public function relationships(): Relationships
	{
		return $this->relationships;
	}

	public function resourceType(): ?string
	{
		return $this->resourceType;
	}

	public function version(): ?Version
	{
		return $this->version;
	}
}
