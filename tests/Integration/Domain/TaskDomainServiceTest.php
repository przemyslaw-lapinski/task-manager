<?php

namespace App\Tests\Integration\Domain;

use App\Infrastructure\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Uid\Uuid;

class TaskDomainServiceTest extends KernelTestCase
{
    public function testShouldCreateTaskCreateNewEvent(): void
    {
        $domainService = self::getContainer()->get('App\Domain\Task\Service\TaskDomainService');

        $userId = Uuid::v7();

        $taskId = $domainService->createTask(
            'Test Task',
            'This is a test task.',
            \App\Domain\Task\ValueObject\UserId::fromString($userId)
        );
        $this->assertInstanceOf(\App\Domain\Task\ValueObject\TaskId::class, $taskId);

        /**
         * @var \App\Infrastructure\Repository\EventRepository $eventDoctrineRepo
         */
        $eventDoctrineRepo = self::getContainer()->get('doctrine')->getRepository(\App\Infrastructure\Entity\Event::class);

        /** @var Event $event */
        $event = $eventDoctrineRepo->findOneBy(['aggregateId' => (string)$taskId, 'aggregateType' => 'task']);
        $this->assertNotNull($event);
        $this->assertEquals('Test Task', $event->getPayload()['title']);
        $this->assertEquals('This is a test task.', $event->getPayload()['description']);
        $this->assertEquals($userId, $event->getPayload()['assignedUserId']);
        $this->assertEquals('TODO', $event->getPayload()['status']);
    }

    public function testShouldChangeStatusCreateNewEvent(): void
    {
        $domainService = self::getContainer()->get('App\Domain\Task\Service\TaskDomainService');

        $userId = Uuid::v7();

        $taskId = $domainService->createTask(
            'Test Task',
            'This is a test task.',
            \App\Domain\Task\ValueObject\UserId::fromString($userId)
        );
        $this->assertInstanceOf(\App\Domain\Task\ValueObject\TaskId::class, $taskId);

        $domainService->changeStatus(
            $taskId,
            \App\Domain\Task\ValueObject\TaskStatus::IN_PROGRESS
        );

        /**
         * @var \App\Infrastructure\Repository\EventRepository $eventDoctrineRepo
         */
        $eventDoctrineRepo = self::getContainer()->get('doctrine')->getRepository(\App\Infrastructure\Entity\Event::class);

        $events = $eventDoctrineRepo->findBy(['aggregateId' => (string)$taskId, 'aggregateType' => 'task']);

        $this->assertCount(2, $events);
        $this->assertEquals('TODO', $events[1]->getPayload()['from']);
        $this->assertEquals('IN_PROGRESS', $events[1]->getPayload()['to']);
    }

    public function testShouldInsertTaskViewOnTaskCreatedEvent(): void
    {
        $domainService = self::getContainer()->get('App\Domain\Task\Service\TaskDomainService');

        $userId = Uuid::v7();

        $taskId = $domainService->createTask(
            'Test Task',
            'This is a test task.',
            \App\Domain\Task\ValueObject\UserId::fromString($userId)
        );
        $this->assertInstanceOf(\App\Domain\Task\ValueObject\TaskId::class, $taskId);

        /**
         * @var \App\Infrastructure\Repository\TaskViewRepository $taskViewDoctrineRepo
         */
        $taskViewDoctrineRepo = self::getContainer()->get('doctrine')->getRepository(\App\Infrastructure\Entity\TaskView::class);

        $taskView = $taskViewDoctrineRepo->find($taskId);

        $this->assertNotNull($taskView);
        $this->assertEquals('Test Task', $taskView->getTitle());
        $this->assertEquals('This is a test task.', $taskView->getDescription());
        $this->assertEquals($userId, $taskView->getAssignedUserId());
        $this->assertEquals('TODO', $taskView->getStatus());
    }

    public function testShouldUpdateTaskViewOnTaskStatusUpdatedEvent(): void
    {
        $domainService = self::getContainer()->get('App\Domain\Task\Service\TaskDomainService');

        $userId = Uuid::v7();

        $taskId = $domainService->createTask(
            'Test Task',
            'This is a test task.',
            \App\Domain\Task\ValueObject\UserId::fromString($userId)
        );
        $this->assertInstanceOf(\App\Domain\Task\ValueObject\TaskId::class, $taskId);

        $domainService->changeStatus(
            $taskId,
            \App\Domain\Task\ValueObject\TaskStatus::IN_PROGRESS
        );

        /**
         * @var \App\Infrastructure\Repository\TaskViewRepository $taskViewDoctrineRepo
         */
        $taskViewDoctrineRepo = self::getContainer()->get('doctrine')->getRepository(\App\Infrastructure\Entity\TaskView::class);

        $taskView = $taskViewDoctrineRepo->find($taskId);

        $this->assertNotNull($taskView);
        $this->assertEquals('IN_PROGRESS', $taskView->getStatus());
    }
}
