<?php

namespace App\Tests\Feature\GraphQL;

use App\Tests\Helpers\AuthTrait;
use App\Tests\Helpers\GraphQLTrait;
use App\Tests\Helpers\UserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Uid\Uuid;

class TaskMutationResolverTest extends WebTestCase
{
    use GraphQLTrait, UserTrait, AuthTrait;

    public function testCreateTaskPersistEventAndReturnsTrueOverHttp(): void
    {
        $vars = [
            'title' => 'New Task',
            'description' => 'Some desc',
            'assignedUserId' => Uuid::v4()->toRfc4122(),
        ];

        $mutation = <<<'GQL'
            mutation($title: String!, $description: String!, $assignedUserId: ID!) {
              createTask(title: $title, description: $description, assignedUserId: $assignedUserId)
            }
        GQL;

        $client = static::createClient();

        $this->createUser('user@example.com', ['ROLE_USER'], $client);
        $token = $this->getJwtToken($client, 'user@example.com');

        $response = $this->sendGraphQL($client, $mutation, $token, $vars);

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('createTask', $response['data']);
        $this->assertTrue($response['data']['createTask']);

        /** @var \App\Infrastructure\Repository\EventRepository $eventDoctrineRepo */
        $eventDoctrineRepo = self::getContainer()->get('doctrine')->getRepository(\App\Infrastructure\Entity\Event::class);

        $event = $eventDoctrineRepo->findOneBy(['aggregateType' => 'task']);
        $this->assertNotNull($event);
    }

    public function testUpdateTaskPersistEventAndReturnsTrueOverHttp(): void
    {
        $client = static::createClient();

        $this->createUser('user@example.com', ['ROLE_USER'], $client);
        $token = $this->getJwtToken($client, 'user@example.com');

        $vars = [
            'title' => 'New Task',
            'description' => 'Some desc',
            'assignedUserId' => Uuid::v4()->toRfc4122(),
        ];

        $mutation = <<<'GQL'
            mutation($title: String!, $description: String!, $assignedUserId: ID!) {
              createTask(title: $title, description: $description, assignedUserId: $assignedUserId)
            }
        GQL;

        $this->sendGraphQL($client, $mutation, $token, $vars);

        /** @var \App\Infrastructure\Repository\EventRepository $eventDoctrineRepo */
        $eventDoctrineRepo = self::getContainer()->get('doctrine')->getRepository(\App\Infrastructure\Entity\Event::class);

        $event = $eventDoctrineRepo->findOneBy(['aggregateType' => 'task']);
        $this->assertNotNull($event);

        $taskId = $event->getAggregateId();

        $vars = [
            'taskId' => $taskId,
            'toStatus' => 'IN_PROGRESS',
        ];

        $mutation = <<<'GQL'
            mutation($taskId: ID!, $toStatus: TaskStatus!) {
              changeTaskStatus(taskId: $taskId, toStatus: $toStatus)
            }
        GQL;

        $response = $this->sendGraphQL($client, $mutation, $token, $vars);

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('changeTaskStatus', $response['data']);
        $this->assertTrue($response['data']['changeTaskStatus']);

        /** @var \App\Infrastructure\Repository\EventRepository $eventDoctrineRepo */
        $eventDoctrineRepo = self::getContainer()->get('doctrine')->getRepository(\App\Infrastructure\Entity\Event::class);

        $event = $eventDoctrineRepo->findOneBy(['aggregateType' => 'task', 'eventName' => "task.status_updated"]);

        $this->assertNotNull($event);
        $this->assertEquals('IN_PROGRESS', $event->getPayload()['to']);
    }
}
