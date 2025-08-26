<?php

namespace App\Domain\Aggregate;

use App\Domain\ValueObject\TaskId;
use App\Domain\ValueObject\TaskStatus;
use App\Domain\ValueObject\UserId;

class Task
{
    private TaskId $id;
    private string $title;
    private string $description;
    private TaskStatus $status;
    private UserId $assignedUserId;
    private int $version = 0;

    private array $uncommittedEvents = [];

    private function __construct() {}

    public static function create(
        TaskId $id,
        UserId $assignedUserId,
        string $title,
        string $description,
        TaskStatus $status
    ): self {
        $task = new self();
//        $task->record(new TaskCreated($id, $name, $description, $assignedUserId));
        //TODO:

        return $task;
    }

    public function changeStatus(TaskStatus $newStatus): void
    {
        if ($this->status === $newStatus) {
            return;
        }

        $oldStatus = $this->status;
//        $this->record(new TaskStatusUpdated($this->id, $oldStatus, $newStatus));
        //TODO:
    }
}
