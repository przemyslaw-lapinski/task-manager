<?php

namespace App\Application\Task\Handler;

use App\Application\Task\Command\CreateTaskCommand;
use App\Domain\Task\Service\TaskDomainService;
use App\Domain\Task\ValueObject\UserId;

class CreateTaskHandler
{
    public function __construct(private readonly TaskDomainService $service) {}

    public function __invoke(CreateTaskCommand $command): string
    {
        return $this->service->createTask(
            $command->name,
            $command->description,
            UserId::fromString($command->assignedUserId)
        );
    }
}
