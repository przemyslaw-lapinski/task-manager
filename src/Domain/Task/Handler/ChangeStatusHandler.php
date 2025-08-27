<?php

namespace App\Domain\Task\Handler;

use App\Domain\Task\Command\ChangeStatusCommand;
use App\Domain\Task\Service\TaskDomainService;
use App\Domain\Task\ValueObject\TaskId;
use App\Domain\Task\ValueObject\TaskStatus;
use App\Domain\Task\ValueObject\UserId;

class ChangeStatusHandler
{
    public function __construct(private TaskDomainService $service) {}

    public function __invoke(ChangeStatusCommand $command): void
    {
        $this->service->changeStatus(
            TaskId::fromString($command->taskId),
            TaskStatus::fromString($command->toStatus)
        );
    }
}
