<?php

namespace App\Domain\Task\Model;

use App\Domain\Task\Event\TaskCreated;
use App\Domain\Task\Event\TaskStatusUpdated;
use App\Domain\Task\Strategy\StatusTransitionStrategy;
use App\Domain\Task\ValueObject\TaskId;
use App\Domain\Task\ValueObject\TaskStatus;
use App\Domain\Task\ValueObject\UserId;

class Task
{
    private int $version = 0;

    private array $uncommittedEvents = [];

    private function __construct(
        private TaskId $id,
        private UserId $assignedUserId,
        private string $title,
        private string $description,
        private TaskStatus $status
    ) {}

    public function getId(): TaskId
    {
        return $this->id;
    }

    public static function reconstitute(iterable $events): self
    {
        $self = new self(
            id: TaskId::fromString(''),
            assignedUserId: UserId::fromString(''),
            title: '',
            description: '',
            status: TaskStatus::fromString('TODO')
        );
        foreach ($events as $e) { $self->apply($e); $self->version++; }
        return $self;
    }

    public function record(object $event): void
    {
        $this->apply($event);
        $this->uncommittedEvents[] = $event;
    }

    private function apply(object $event): void
    {
        match (true) {
            $event instanceof TaskCreated => $this->applyTaskCreated($event),
            $event instanceof TaskStatusUpdated => $this->applyTaskStatusUpdated($event),
            default => null
        };
    }

    private function applyTaskCreated(TaskCreated $e): void
    {
        $this->id = $e->getTaskId();
        $this->title = $e->getTitle();
        $this->description = $e->getDescription();
        $this->status = $e->getStatus();
        $this->assignedUserId = $e->getAssignedUserId();
    }

    private function applyTaskStatusUpdated(TaskStatusUpdated $e): void
    {
        $this->status = $e->getTo();
    }

    /** @return object[] */
    public function pullUncommittedEvents(): array
    {
        $e = $this->uncommittedEvents;
        $this->uncommittedEvents = [];
        return $e;
    }

    public function changeStatus(TaskStatus $newStatus, StatusTransitionStrategy $strategy): self
    {
        if ($this->status === $newStatus) {
            throw new \RuntimeException('Status is already ' . $newStatus->toString());
        }

        if (!$strategy->canTransition($this->status, $newStatus)) {
            throw new \RuntimeException(sprintf(
                'Invalid status transition from %s to %s',
                $this->status->value,
                $newStatus->value
            ));
        }

        $this->record(TaskStatusUpdated::now($this->id, $this->status, $newStatus, new \DateTimeImmutable()));

        return $this;
    }
}
