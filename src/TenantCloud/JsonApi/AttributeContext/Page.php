<?php

namespace TenantCloud\JsonApi\AttributeContext;

use Tests\AttributeContext\PageTest;

/**
 * Class Page
 *
 * @see PageTest
 */
class Page
{
	/** @var int */
	private int $page = 1;

	/**
	 * @return $this
	 */
	public function setPage(int $page): self
	{
		$this->page = $page;

		return $this;
	}

	public function getPage(): int
	{
		return $this->page;
	}
}
