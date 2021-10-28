<?php

namespace TenantCloud\JsonApi;

use Tests\FunctionsTest;

/**
 * Array filter empty values
 *
 * @see FunctionsTest::testArrayFilterEmpty()
 */
function array_filter_empty(array $data): array
{
	$result = [];

	if (!count($data)) {
		return $result;
	}

	return array_filter(array_map(static function ($value) {
		$isNotEmptyString = is_string($value) && !empty($value);

		if ($isNotEmptyString && $value[0] === ' ') {
			return null;
		}

		return $value;
	}, $data));
}
