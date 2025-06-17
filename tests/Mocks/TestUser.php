<?php

namespace Tests\Mocks;

class TestUser
{
	public bool $valid = true;

	public function __construct(
		public int $id,
		public string $name
	) {}

	public function isValid(): bool
	{
		return $this->valid;
	}

	public function toArray(): array
	{
		return [
			'id'      => $this->id,
			'name'    => $this->name,
			'isValid' => $this->isValid(),
		];
	}
}
