<?php

namespace App\Domain\Event;

use App\Domain\ValueObject\TaskId;
use App\Domain\ValueObject\TaskStatus;
use App\Domain\ValueObject\UserId;

class TaskCreated
{
    public function __construct(
        private TaskId $taskId,
        private UserId $userId,
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

    public function getUserId(): UserId
    {
        return $this->userId;
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
        TaskStatus $status
    ): self {
        return new self(
            $taskId,
            $userId,
            $title,
            $description,
            $status,
            new \DateTimeImmutable()
        );
    }

    public function toPayload(): array
    {
        return [
            'taskId' => (string) $this->taskId,
            'userId' => (string) $this->userId,
            'title' => $this->title,
            'description' => $this->description,
            'status' => (string) $this->status,
            'occurredAt' => $this->occurredAt->format('Y-m-d H:i:s'),
        ];
    }

    public static function fromPayload(array $payload): self
    {
        return new self(
            TaskId::fromString($payload['taskId']),
            UserId::fromString($payload['userId']),
            $payload['title'],
            $payload['description'],
            TaskStatus::fromString($payload['status']),
            new \DateTimeImmutable($payload['occurredAt'])
        );
    }
}
