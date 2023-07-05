<?php

namespace TenantCloud\JsonApi\Interfaces;

use TenantCloud\APIVersioning\Version\Version;
use TenantCloud\JsonApi\AttributeContext\Fields;
use TenantCloud\JsonApi\AttributeContext\Filter;
use TenantCloud\JsonApi\AttributeContext\Includes;
use TenantCloud\JsonApi\AttributeContext\Page;
use TenantCloud\JsonApi\AttributeContext\Sort;

/**
 * @template-covariant  TUser
 */
interface Context
{
	/**
	 * @return TUser
	 */
	public function user();

	public function fields(): Fields;

	public function page(): Page;

	public function includes(): Includes;

	public function filters(): Filter;

	public function sort(): Sort;

	public function resourceType(): ?string;

	public function version(): ?Version;
}
