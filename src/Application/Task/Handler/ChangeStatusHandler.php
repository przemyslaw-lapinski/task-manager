<?php

namespace App\Application\Task\Handler;

use App\Application\Task\Command\ChangeStatusCommand;
use App\Domain\Task\Service\TaskDomainService;
use App\Domain\Task\ValueObject\TaskId;
use App\Domain\Task\ValueObject\TaskStatus;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
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
