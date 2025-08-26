<?php

namespace App\Infrastructure\EventStore\Support;

class EventNameResolver
{
    private array $map = [
        'task.created' => \App\Domain\Task\Event\TaskCreated::class,
        'task.status_updated' => \App\Domain\Task\Event\TaskStatusUpdated::class,
    ];

    public function toName(string $fqcn): string
    {
        $flip = array_flip($this->map);
        return $flip[$fqcn] ?? $fqcn;
    }

    public function toFqcn(string $name): string
    {
        return $this->map[$name] ?? $name;
    }
}
