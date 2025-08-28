<?php

namespace App\Application\Task\Command;

use App\Domain\Task\ValueObject\UserId;

class CreateTaskCommand
{
    public function __construct(
        public string $name,
        public string $description,
        public string $assignedUserId
    ) {}
}
