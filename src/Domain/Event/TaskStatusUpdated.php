<?php

namespace App\Domain\Event;

use App\Domain\ValueObject\TaskId;
use App\Domain\ValueObject\TaskStatus;

class TaskStatusUpdated
{
    public function __construct(
        private TaskId $taskId,
        private TaskStatus $from,
        private TaskStatus $to,
        private \DateTimeImmutable $occurredAt
    ) {
    }

    public static function now(
        TaskId $taskId,
        TaskStatus $from,
        TaskStatus $to
    ): self {
        return new self(
            $taskId,
            $from,
            $to,
            new \DateTimeImmutable()
        );
    }

    public function toPayload(): array
    {
        return [
            'task_id' => (string)$this->taskId,
            'from' => $this->from->value,
            'to' => $this->to->value,
            'occurred_at' => $this->occurredAt->format('Y-m-d H:i:s'),
        ];
    }

    public static function fromPayload(array $payload): self
    {
        return new self(
            TaskId::fromString($payload['task_id']),
            TaskStatus::fromString($payload['from']),
            TaskStatus::fromString($payload['to']),
            new \DateTimeImmutable($payload['occurred_at'])
        );
    }

    public function getTaskId(): TaskId
    {
        return $this->taskId;
    }

    public function getFrom(): TaskStatus
    {
        return $this->from;
    }

    public function getTo(): TaskStatus
    {
        return $this->to;
    }

    public function getOccurredAt(): \DateTimeInterface
    {
        return $this->occurredAt;
    }
}
