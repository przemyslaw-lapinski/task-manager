<?php

namespace App\Tests\Controller;

use App\Tests\Helpers\UserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    use UserTrait;

    private function getJwtToken($client, $email, $password = 'password')
    {
        $client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => $email,
            'password' => $password,
        ]));
        $data = json_decode($client->getResponse()->getContent(), true);
        return $data['token'] ?? null;
    }

    public function testMeEndpointAuthenticated()
    {
        $client = static::createClient();
        $user = $this->createUser('user@example.com', ['ROLE_USER'], $client);
        $token = $this->getJwtToken($client, 'user@example.com');

        $client->request('GET', '/api/me', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
        ]);
        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals($user->getEmail(), $data['email']);
    }

    public function testUsersEndpointAsAdmin()
    {
        $client = static::createClient();
        $this->createUser('admin@example.com', ['ROLE_ADMIN'], $client);
        $this->createUser('user2@example.com', ['ROLE_USER'], $client);
        $token = $this->getJwtToken($client, 'admin@example.com');

        $client->request('GET', '/api/users', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
        ]);
        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertGreaterThanOrEqual(2, count($data));
        $this->assertContains('admin@example.com', array_column($data, 'email'));
    }

    public function testUsersEndpointAsNonAdmin()
    {
        $client = static::createClient();
        $this->createUser('user3@example.com', ['ROLE_USER'], $client);
        $token = $this->getJwtToken($client, 'user3@example.com');

        $client->request('GET', '/api/users', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $token,
        ]);
        $this->assertResponseStatusCodeSame(403);
    }
}
