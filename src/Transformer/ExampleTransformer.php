<?php

namespace TenantCloud\JsonApi\Transformer;

use TenantCloud\JsonApi\JsonApiTransformer;

class ExampleTransformer extends JsonApiTransformer
{
	public array $availableIncludes = [
		'example_include',
	];
}
