<?php

namespace App\Domain\Task\Event;

use App\Domain\Common\Event\DomainEvent;
use App\Domain\Task\ValueObject\TaskId;
use App\Domain\Task\ValueObject\TaskStatus;
use App\Domain\Task\ValueObject\UserId;

class TaskCreated implements DomainEvent
{
    public function __construct(
        private TaskId $taskId,
        private UserId $assignedUserId,
        private string $title,
        private string $description,
        private TaskStatus $status,
        private \DateTimeImmutable $occurredAt,
    ) {
    }

    public function getTaskId(): TaskId
    {
        return $this->taskId;
    }

    public function getAssignedUserId(): UserId
    {
        return $this->assignedUserId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getStatus(): TaskStatus
    {
        return $this->status;
    }

    public function getOccurredAt(): \DateTimeInterface
    {
        return $this->occurredAt;
    }

    public static function now(
        TaskId $taskId,
        UserId $userId,
        string $title,
        string $description,
    ): self {
        return new self(
            $taskId,
            $userId,
            $title,
            $description,
            $status = TaskStatus::TODO,
            new \DateTimeImmutable(),
        );
    }

    public function toPayload(): array
    {
        return [
            'taskId' => (string) $this->taskId,
            'assignedUserId' => (string) $this->assignedUserId,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status->value,
            'occurredAt' => $this->occurredAt->format('Y-m-d H:i:s'),
        ];
    }

    public static function fromPayload(array $payload): self
    {
        return new self(
            TaskId::fromString($payload['taskId']),
            UserId::fromString($payload['assignedUserId']),
            $payload['title'],
            $payload['description'],
            TaskStatus::fromString($payload['status']),
            new \DateTimeImmutable($payload['occurredAt'])
        );
    }
}
