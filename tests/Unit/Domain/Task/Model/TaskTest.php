<?php

namespace App\Tests\Unit\Domain\Task\Model;
use App\Domain\Task\Event\TaskCreated;
use App\Domain\Task\Event\TaskStatusUpdated;
use App\Domain\Task\Model\Task;
use App\Domain\Task\Strategy\SimpleStatusTransitionStrategy;
use App\Domain\Task\ValueObject\TaskId;
use App\Domain\Task\ValueObject\TaskStatus;
use App\Domain\Task\ValueObject\UserId;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class TaskTest extends TestCase
{
    private function givenTaskCreated(
        string $title = 'Test',
        string $description = 'Desc',
        ?string $taskId = null,
        ?string $assignee = null,
        TaskStatus $status = null
    ): Task {
        $taskId    = $taskId ?? Uuid::v7()->toRfc4122();
        $assignee  = $assignee ?? Uuid::v4()->toRfc4122();
        $status    = $status ?? TaskStatus::fromString('TODO');

        $created = new TaskCreated(
            TaskId::fromString($taskId),
            UserId::fromString($assignee),
            $title,
            $description,
            $status,
            new \DateTimeImmutable()
        );

        return Task::reconstitute([$created]);
    }

    public function testReconstituteFromTaskCreatedSetsState(): void
    {
        $taskId = Uuid::v7()->toRfc4122();
        $assignee = Uuid::v4()->toRfc4122();

        $task = $this->givenTaskCreated(
            title: 'Feature',
            description: 'Implement X',
            taskId: $taskId,
            assignee: $assignee,
            status: TaskStatus::fromString('TODO')
        );

        self::assertSame($taskId, (string) $task->getId());
        self::assertSame([], $task->pullUncommittedEvents());
    }

    public function testChangeStatusEmitsEventAndUpdatesState(): void
    {
        $task = $this->givenTaskCreated(status: TaskStatus::fromString('TODO'));
        $strategy = new SimpleStatusTransitionStrategy();

        $task->changeStatus(TaskStatus::fromString('IN_PROGRESS'), $strategy);

        $events = $task->pullUncommittedEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(TaskStatusUpdated::class, $events[0]);

        /** @var TaskStatusUpdated $e */
        $e = $events[0];
        self::assertSame('TODO', $e->getFrom()->value);
        self::assertSame('IN_PROGRESS', $e->getTo()->value);
        self::assertSame($task->getId(), $e->getTaskId());
    }

    public function testChangeStatusThrowsWhenSameStatus(): void
    {
        $task = $this->givenTaskCreated(status: TaskStatus::fromString('TODO'));
        $strategy = new SimpleStatusTransitionStrategy();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Status is already TODO');

        $task->changeStatus(TaskStatus::fromString('TODO'), $strategy);
    }

    public function testChangeStatusThrowsOnIllegalTransition(): void
    {
        $task = $this->givenTaskCreated(status: TaskStatus::fromString('DONE'));
        $strategy = new SimpleStatusTransitionStrategy();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid status transition');

        $task->changeStatus(TaskStatus::fromString('IN_PROGRESS'), $strategy);
    }
}
