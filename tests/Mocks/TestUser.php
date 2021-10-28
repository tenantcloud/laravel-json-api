<?php

namespace Tests\Mocks;

class TestUser
{
	public bool $valid = true;

	public int $id;

	public string $name;

	public function __construct(int $id, string $name)
	{
		$this->id = $id;
		$this->name = $name;
	}

	public function isValid(): bool
	{
		return $this->valid;
	}

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'isValid' => $this->isValid(),
		];
	}
}
