<?php

namespace TenantCloud\JsonApi\Enums;

class SortType
{
	public const ASC = 'ASC';
	public const DESC = 'DESC';

	public static function values(): array
	{
		return [
			static::ASC,
			static::DESC,
		];
	}
}
