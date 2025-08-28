<?php

namespace App\Tests\Feature\GraphQL;

use App\Tests\Helpers\AuthTrait;
use App\Tests\Helpers\GraphQLTrait;
use App\Tests\Helpers\UserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Uid\Uuid;

class TaskQueryResolverTest extends WebTestCase
{
    use GraphQLTrait, UserTrait, AuthTrait;

    public function testCreateTaskDispatchesAndReturnsTrueOverHttp(): void
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
    }
}
