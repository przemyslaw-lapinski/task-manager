<?php

namespace App\Tests\Domain;

use App\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskDomainServiceTest extends WebTestCase
{
    public function testShouldCreateTaskCreateNewEvent(): void
    {
        $domainService = self::getContainer()->get('App\Domain\Task\Service\TaskDomainService');
        $taskId = $domainService->createTask(
            'Test Task',
            'This is a test task.',
            \App\Domain\Task\ValueObject\UserId::fromString('user-1234')
        );
        $this->assertInstanceOf(\App\Domain\Task\ValueObject\TaskId::class, $taskId);

        /**
         * @var \App\Repository\EventRepository $eventDoctrineRepo
         */
        $eventDoctrineRepo = self::getContainer()->get('doctrine')->getRepository(\App\Entity\Event::class);

        /** @var Event $event */
        $event = $eventDoctrineRepo->findOneBy(['aggregateId' => (string)$taskId, 'aggregateType' => 'task']);
        $this->assertNotNull($event);
        $this->assertEquals('Test Task', $event->getPayload()['title']);
        $this->assertEquals('This is a test task.', $event->getPayload()['description']);
        $this->assertEquals('user-1234', $event->getPayload()['assignedUserId']);
        $this->assertEquals('TODO', $event->getPayload()['status']);
    }

    public function testShouldChangeStatusCreateNewEvent(): void
    {
        $domainService = self::getContainer()->get('App\Domain\Task\Service\TaskDomainService');
        $taskId = $domainService->createTask(
            'Test Task',
            'This is a test task.',
            \App\Domain\Task\ValueObject\UserId::fromString('user-1234')
        );
        $this->assertInstanceOf(\App\Domain\Task\ValueObject\TaskId::class, $taskId);

        $domainService->changeStatus(
            $taskId,
            \App\Domain\Task\ValueObject\TaskStatus::IN_PROGRESS
        );

        /**
         * @var \App\Repository\EventRepository $eventDoctrineRepo
         */
        $eventDoctrineRepo = self::getContainer()->get('doctrine')->getRepository(\App\Entity\Event::class);

        $events = $eventDoctrineRepo->findBy(['aggregateId' => (string)$taskId, 'aggregateType' => 'task']);

        $this->assertCount(2, $events);
        $this->assertEquals('TODO', $events[1]->getPayload()['from']);
        $this->assertEquals('IN_PROGRESS', $events[1]->getPayload()['to']);
    }
}
