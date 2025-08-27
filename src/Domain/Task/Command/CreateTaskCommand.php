<?php

namespace App\Domain\Task\Command;

class CreateTaskCommand
{
    public function __construct(
        public string $name,
        public string $description,
        public string $assignedUserId
    ) {}
}
