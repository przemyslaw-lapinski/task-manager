<?php

namespace App\Tests\Domain;

use App\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskDomainServiceTest extends WebTestCase
{
    public function testShouldCreateNewEvent(): void
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
        $event = $eventDoctrineRepo->findOneBy(['aggregateId' => (string)$taskId]);
        $this->assertNotNull($event);
        $this->assertEquals('Test Task', $event->getPayload()['title']);
        $this->assertEquals('This is a test task.', $event->getPayload()['description']);
        $this->assertEquals('user-1234', $event->getPayload()['assignedUserId']);
        $this->assertEquals('TODO', $event->getPayload()['status']);
    }
}
