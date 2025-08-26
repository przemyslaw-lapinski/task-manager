<?php

namespace App\Domain\ValueObject;

enum TaskStatus: string
{
    case PENDING = 'PENDING';
    case IN_PROGRESS = 'IN_PROGRESS';
    case COMPLETED = 'COMPLETED';

    public static function fromString(string $status): self
    {
        return match (strtoupper($status)) {
            'PENDING' => self::PENDING,
            'IN_PROGRESS' => self::IN_PROGRESS,
            'COMPLETED' => self::COMPLETED,
            default => throw new \InvalidArgumentException("Invalid task status: $status"),
        };
    }
}
