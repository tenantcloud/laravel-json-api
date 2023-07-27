<?php

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
		if ($this->hasMetaInclude($includedData)) {
			$transformedData['meta'] = array_merge(
				array_key_exists('meta', $transformedData) ? $transformedData['meta'] : [],
				$includedData['meta']['data']['attributes']
			);
		}

		return parent::mergeIncludes($transformedData, $includedData);
	}

	public function includedData(ResourceInterface $resource, array $data): array
	{
		return parent::includedData($resource, $this->removeMetaInclude($data));
	}

	public function injectData(array $data, array $rawIncludedData): array
	{
		return parent::injectData($data, $this->removeMetaInclude($rawIncludedData));
	}

	protected function removeMetaInclude(array $included): array
	{
		$removeMeta = fn (array $data) => array_filter($data, fn (string $key) => $key != 'meta', ARRAY_FILTER_USE_KEY);

		return array_map(fn (array $includes) => $removeMeta($includes), $included);
	}

	protected function hasMetaInclude(array $item): bool
	{
		return array_key_exists('meta', $item);
	}
}
