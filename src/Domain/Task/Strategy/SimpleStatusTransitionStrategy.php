<?php

namespace App\Domain\Task\Strategy;

use App\Domain\Task\ValueObject\TaskStatus;

class SimpleStatusTransitionStrategy implements StatusTransitionStrategy
{

    public function canTransition(TaskStatus $from, TaskStatus $to): bool
    {
        return match ($from) {
            TaskStatus::TODO => $to === TaskStatus::IN_PROGRESS,
            TaskStatus::IN_PROGRESS => in_array($to, [TaskStatus::TODO, TaskStatus::DONE], true),
            TaskStatus::DONE => false,
        };
    }
}
