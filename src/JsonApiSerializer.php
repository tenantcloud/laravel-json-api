<?php

/*
 * This file is part of the League\Fractal package.
 *
 * (c) Phil Sturgeon <me@philsturgeon.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace TenantCloud\JsonApi;

use League\Fractal\Resource\ResourceInterface;
use League\Fractal\Serializer\JsonApiSerializer as Serializer;
use Tests\JsonApiSerializerTest;

/**
 * Packs 'meta' include as normal item's meta.
 * It's good when you want to request only some of the meta attributes.
 *
 * @see JsonApiSerializerTest
 */
class JsonApiSerializer extends Serializer
{
	public function mergeIncludes(array $transformedData, array $includedData): array
	{
		if ($this->isMetaInclude($includedData)) {
			$transformedData['meta'] = array_merge(
				array_key_exists('meta', $transformedData) ? $transformedData['meta'] : [],
				$includedData['meta']['data']['attributes']
			);
		}

		return parent::mergeIncludes($transformedData, $includedData);
	}

	public function includedData(ResourceInterface $resource, array $data): array
	{
		$filteredData = array_filter($data, fn (array $item) => !$this->isMetaInclude($item));

		return parent::includedData($resource, $filteredData);
	}

	public function injectData(array $data, array $rawIncludedData): array
	{
		return parent::injectData($data, array_filter($rawIncludedData, fn (array $item) => !$this->isMetaInclude($item)));
	}

	protected function isMetaInclude(array $item): bool
	{
		return array_key_exists('meta', $item);
	}
}
