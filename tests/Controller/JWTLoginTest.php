<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class JWTLoginTest extends WebTestCase
{
    public function testLoginWithValidCredentialsReturnsJWT(): void
    {
        $client = static::createClient();

        $user = new \App\Infrastructure\Entity\User();
        $user->setEmail('test@example.com');
        $user->setPassword('test1234');
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'test@example.com',
            'password' => 'test1234',
        ]));

        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $data);
        $this->assertNotEmpty($data['token']);
    }

    public function testLoginWithInvalidCredentialsReturnsError(): void
    {
        $client = static::createClient();

        $user = new \App\Infrastructure\Entity\User();
        $user->setEmail('test@example.com');
        $user->setPassword('test1234');
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]));

        $response = $client->getResponse();
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testImportedUserCanAuthenticate(): void
    {
        $client = static::createClient();
        $kernel = self::bootKernel();
        $application = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
        $application->setAutoExit(false);

        $application->run(new \Symfony\Component\Console\Input\ArrayInput([
            'command' => 'app:fetch-users',
        ]));

        $email = 'Sincere@april.biz';
        $password = 'secret123';

        $client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'email' => $email,
            'password' => $password,
        ]));

        $response = $client->getResponse();
        $this->assertResponseIsSuccessful();
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $data);
        $this->assertNotEmpty($data['token']);
    }
}
