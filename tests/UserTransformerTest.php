<?php

namespace Tests;

use Illuminate\Database\Eloquent\Model;
use League\Fractal\Resource\NullResource;
use Mockery;
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

		$this->transformer = app(TestUserTransformer::class);
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
