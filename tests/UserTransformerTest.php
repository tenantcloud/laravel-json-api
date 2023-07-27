<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\Resource\NullResource;
use League\Fractal\Resource\ResourceInterface;
use Mockery;
use Tests\Mocks\TestUserSchema;
use Tests\Mocks\TestUserTransformer;

/**
 * @see TestUserTransformer
 */
class UserTransformerTest extends TestCase
{
	private TestUserTransformer $transformer;

	protected function setUp(): void
	{
		parent::setUp();

		$this->transformer = new class () extends TestUserTransformer {
			public function includeTestInclude($item): ?ResourceInterface
			{
				return $this->modelRelationItem($item, 'test_relation', $this, TestUserSchema::class);
			}

			public function includeTestIncludeCollection($item): ?ResourceInterface
			{
				return $this->modelRelationCollection($item, 'test_relation_collection', $this, TestUserSchema::class);
			}
		};
	}

	public function testIncludeNotLoadedCollection(): void
	{
		$response = $this->transformer->includeTestIncludeCollection(
			Mockery::mock(Model::class)
				->shouldReceive('relationLoaded')
				->once()
				->andReturnNull()
				->getMock()
		);

		$this->assertNull($response);
	}

	public function testIncludeNotLoadedItem(): void
	{
		$response = $this->transformer->includeTestInclude(
			Mockery::mock(Model::class)
				->shouldReceive('relationLoaded')
				->once()
				->andReturnNull()
				->getMock()
		);

		$this->assertNull($response);
	}

	public function testIncludeEmptyItem(): void
	{
		$response = $this->transformer->includeTestInclude(
			Mockery::mock(Model::class)
				->shouldReceive('relationLoaded')
				->once()
				->andReturnTrue()
				->getMock()
				->shouldReceive('getAttribute')
				->with('test_relation')
				->once()
				->andReturnNull()
				->getMock()
		);

		$this->assertInstanceOf(NullResource::class, $response);
	}
}
