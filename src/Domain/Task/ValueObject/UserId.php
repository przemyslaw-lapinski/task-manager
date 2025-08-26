<?php

namespace App\Domain\Task\ValueObject;

class UserId
{
    private function __construct(private string $id)
    {
    }

    public static function fromString(string $id): self
    {
        return new self($id);
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
