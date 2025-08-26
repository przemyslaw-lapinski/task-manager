<?php

namespace App\Domain\Task\Repository;

use App\Domain\Task\Aggregate\Task;
use App\Domain\Task\ValueObject\TaskId;

interface TaskEventSourcedRepositoryInterface
{
    public function load(TaskId $id): Task;
    public function save(Task $task): void;
}
