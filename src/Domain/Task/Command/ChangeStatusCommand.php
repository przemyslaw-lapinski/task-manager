<?php

namespace App\Domain\Task\Command;

class ChangeStatusCommand
{
    public function __construct(
        public string $taskId,
        public string $toStatus
    ) {}
}
