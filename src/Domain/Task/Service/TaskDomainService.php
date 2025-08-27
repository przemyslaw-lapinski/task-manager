<?php

namespace App\Domain\Task\Service;

use App\Domain\Task\Factory\TaskFactory;
use App\Domain\Task\Repository\TaskEventSourcedRepositoryInterface;
use App\Domain\Task\Strategy\StatusTransitionStrategy;
use App\Domain\Task\ValueObject\TaskId;
use App\Domain\Task\ValueObject\TaskStatus;
use App\Domain\Task\ValueObject\UserId;

class TaskDomainService
{
    public function __construct(
        private TaskEventSourcedRepositoryInterface $repo,
        private TaskFactory $factory,
        private StatusTransitionStrategy $strategy,
    ) {}

    public function createTask(string $name, string $desc, UserId $assigned): TaskId
    {
        $id = TaskId::fromString(\Symfony\Component\Uid\Uuid::v7());
        $task = $this->factory->create($id, $name, $desc, $assigned);

        $this->repo->save($task);

        return $id;
    }

    public function changeStatus(TaskId $taskId, TaskStatus $to): void
    {
        $task = $this->repo->load($taskId);
        $task = $task->changeStatus($to, $this->strategy);

        $this->repo->save($task);
    }
}
