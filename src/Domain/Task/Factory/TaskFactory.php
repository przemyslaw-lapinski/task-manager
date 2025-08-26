<?php

namespace App\Domain\Task\Factory;

use App\Domain\Task\Aggregate\Task;
use App\Domain\Task\Event\TaskCreated;
use App\Domain\Task\ValueObject\TaskId;
use App\Domain\Task\ValueObject\UserId;

class TaskFactory
{
    public function create(TaskId $id, string $name, string $desc, UserId $assigned): Task
    {
        $task = Task::reconstitute([]);
        $task->record(TaskCreated::now($id, $assigned, $name, $desc));
        return $task;
    }
}
