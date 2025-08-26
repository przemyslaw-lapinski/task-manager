<?php

namespace App\Domain\Task\Strategy;

use App\Domain\Task\ValueObject\TaskStatus;

interface StatusTransitionStrategy
{
    public function canTransition(TaskStatus $from, TaskStatus $to): bool;
}
