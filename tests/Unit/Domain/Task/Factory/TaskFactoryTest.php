<?php

namespace App\Tests\Unit\Domain\Task\Factory;

use App\Domain\Task\Event\TaskCreated;
use App\Domain\Task\Factory\TaskFactory;
use App\Domain\Task\Model\Task;
use App\Domain\Task\ValueObject\TaskId;
use App\Domain\Task\ValueObject\UserId;
use Symfony\Component\Uid\Uuid;

class TaskFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateEmitsTaskCreatedAndSetsAggregateState(): void
    {
        $factory   = new TaskFactory();
        $taskId    = TaskId::fromString(Uuid::v7()->toRfc4122());
        $assignee  = UserId::fromString(Uuid::v4()->toRfc4122());
        $title      = 'task';
        $desc      = 'Created via factory test';

        $task = $factory->create($taskId, $title, $desc, $assignee);

        self::assertInstanceOf(Task::class, $task);
        self::assertSame($taskId, $task->getId());

        $events = $task->pullUncommittedEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(TaskCreated::class, $events[0]);

        /** @var TaskCreated $e */
        $e = $events[0];
        self::assertSame($taskId, $e->getTaskId());
        self::assertSame($assignee, $e->getAssignedUserId());
        self::assertSame($title, $e->getTitle());
        self::assertSame($desc, $e->getDescription());

        self::assertSame([], $task->pullUncommittedEvents());
    }
}
