<?php

namespace App\Domain\Task\ValueObject;

enum TaskStatus: string
{
    case TODO = 'TODO';
    case IN_PROGRESS = 'IN_PROGRESS';
    case DONE = 'DONE';

    public static function fromString(string $status): self
    {
        return match (strtoupper($status)) {
            'TODO' => self::TODO,
            'IN_PROGRESS' => self::IN_PROGRESS,
            'DONE' => self::DONE,
            default => throw new \InvalidArgumentException("Invalid task status: $status"),
        };
    }
}
